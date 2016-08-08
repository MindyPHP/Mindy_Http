<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05.08.16
 * Time: 23:04
 */

namespace Mindy\Http;

use Exception;
use function GuzzleHttp\Psr7\stream_for;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Http
 * @package Mindy\Http
 * @property CookieCollection cookie
 */
class Http
{
    use LegacyHttp;

    /**
     * @var callable
     */
    public $routeResolver;
    /**
     * @var array
     */
    public $settings = [];
    /**
     * @var CookieCollection
     */
    public $cookie;
    /**
     * @var Collection
     */
    public $get;
    /**
     * @var Collection
     */
    public $post;
    /**
     * @var HttpSession
     */
    public $session;
    /**
     * @var array
     */
    protected $defaultSettings = [
        'responseChunkSize' => 4096,
    ];
    /**
     * @var callable
     */
    protected $middleware;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;

    /**
     * Http constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }

        $this->request = Request::fromGlobals();
        $this->response = new Response();
        $this->cookie = new CookieCollection($this->getRequest()->getCookieParams());
        $this->get = new Collection($this->getRequest()->getQueryParams());
        $this->post = new Collection($this->getRequest()->getServerParams());
        $this->files = new Collection($this->getRequest()->getUploadedFiles());

        $sessionData = isset($_SESSION) && $_SESSION ? $_SESSION : [];
        $this->session = new HttpSession([
            'collection' => new SessionCollection($sessionData),
            'autoStart' => false,
            'iniOptions' => [
                'gc_maxlifetime' => 60 * 60 * 24
            ]
        ]);
        $this->flash = new FlashCollection($this->session);

        $csrfValidator = null;
        if (class_exists('\Mindy\Security\SecurityManager')) {
            $csrfValidator = function($data) {
                return \Mindy\Base\Mindy::app()->security->validateData($data);
            };
        }
        $this->csrf = new Csrf($this, [
            'validator' => $csrfValidator
        ]);
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Request|\Psr\Http\Message\ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Helper method, which returns true if the provided response must not output a body and false
     * if the response could have a body.
     *
     * @see https://tools.ietf.org/html/rfc7231
     *
     * @param ResponseInterface $response
     * @return bool
     */
    public function isEmptyResponse(ResponseInterface $response)
    {
        if (method_exists($response, 'isEmpty')) {
            return $response->isEmpty();
        }
        return in_array($response->getStatusCode(), [204, 205, 304]);
    }

    /**
     * Apply middlewares to response
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    protected function withMiddleware(ResponseInterface $response)
    {
        if ($this->middleware === null) {
            return $response;
        }

        $middleware = $this->middleware;
        return $middleware($this->request, $response);
    }

    /**
     * Send the response the client
     *
     * @param ResponseInterface $response
     */
    public function send(ResponseInterface $response)
    {
        $response = $this->withMiddleware($response);
        // Send response
        if (!headers_sent()) {
            // Status
            header(sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));
            // Headers
            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }
        // Body
        if (!$this->isEmptyResponse($response)) {
            $body = $response->getBody();
            if ($body->isSeekable()) {
                $body->rewind();
            }
            $chunkSize = $this->getSettings()['responseChunkSize'];
            $contentLength = $response->getHeaderLine('Content-Length');
            if (!$contentLength) {
                $contentLength = $body->getSize();
            }
            if (isset($contentLength)) {
                $amountToRead = $contentLength;
                while ($amountToRead > 0 && !$body->eof()) {
                    $data = $body->read(min($chunkSize, $amountToRead));
                    echo $data;

                    $amountToRead -= strlen($data);

                    if (connection_status() != CONNECTION_NORMAL) {
                        break;
                    }
                }
            } else {
                while (!$body->eof()) {
                    echo $body->read($chunkSize);
                    if (connection_status() != CONNECTION_NORMAL) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * Return request global settings
     * @return array
     */
    public function getSettings()
    {
        return array_merge($this->defaultSettings, $this->settings);
    }

    /**
     * Refreshes the current page.
     * The effect of this method call is the same as user pressing the
     * refresh button on the browser (without post data).
     * @param string $anchor the anchor that should be appended to the redirection URL.
     * Defaults to empty. Make sure the anchor starts with '#' if you want to specify it.
     */
    public function refresh($anchor = '')
    {
        $this->redirect($this->getRequest()->getRequestTarget() . $anchor);
    }

    /**
     * @throws Exception
     */
    public function redirect()
    {
        $url = $data = $status = null;
        $args = func_get_args();
        switch (count($args)) {
            case 3:
                list($route, $data, $status) = $args;
                $url = $this->resolveRoute($route, $data);
                break;
            case 2:
                list($url, $status) = $args;
                break;
            case 1:
                list($url) = $args;
                if (is_object($url) && method_exists($url, 'getAbsoluteUrl()')) {
                    $url = $url->getAbsoluteUrl();
                }
                break;
        }
        $response = $this->getResponse()
            ->withStatus($status)
            ->withHeader('Location', $url);
        $this->send($response);
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     * @return $this
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @param $route
     * @param null $data
     * @return mixed
     * @throws Exception
     */
    public function resolveRoute($route, $data = null)
    {
        if (!$this->routeResolver) {
            throw new Exception('Unknown route resolver');
        }
        return call_user_func_array($this->routeResolver, [$route, $data]);
    }

    /**
     * Send response with application/json headers
     * @param $data
     */
    public function json($data)
    {
        $body = !is_string($data) ? json_encode($data) : $data;
        $response = $this->response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(stream_for($body));
        $this->send($response);
    }

    /**
     * Shortcut for text/html response
     * @param $html
     */
    public function html($html)
    {
        $response = $this->response
            ->withStatus(200)
            ->withHeader('Content-Type', 'text/html')
            ->withBody(stream_for($html));
        $this->send($response);
    }

    /**
     * @return bool
     */
    public function isXhr()
    {
        return $this->getRequest()->isXhr();
    }
}