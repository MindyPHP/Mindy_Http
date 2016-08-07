<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05.08.16
 * Time: 22:29
 */

//ini_set('session.use_trans_sid', false);
//ini_set('session.use_cookies', false);
//ini_set('session.use_only_cookies', true);
//ini_set('session.cache_limiter', '');

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/Security.php');

use function GuzzleHttp\Psr7\stream_for;
use Mindy\Http\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr7Middlewares\Middleware\ResponseTime;
use Relay\RelayBuilder;

function d() {
    $debug = debug_backtrace();
    $args = func_get_args();
    $data = array(
        'data' => $args,
        'debug' => array(
            'file' => isset($debug[0]['file']) ? $debug[0]['file'] : null,
            'line' => isset($debug[0]['line']) ? $debug[0]['line'] : null,
        )
    );
    \Mindy\Helper\Dumper::dump($data);
    die();
}

//Set a stream factory used by some middlewares
//(Required only if Zend\Diactoros\Stream is not detected)
\Psr7Middlewares\Middleware::setStreamFactory(function ($file, $mode) {
    return stream_for(fopen($file, $mode));
});


$relay = (new RelayBuilder())->newInstance([
//    new BasicAuthentication(['max' => '210690']),
    new ResponseTime(),
//    (new Honeypot())->autoInsert(true),
    function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
        $response = $next($request, $response);
        return $response->withHeader('blblbl', 123);
    }
]);
$http = new Http([
    'middleware' => $relay,
]);

function getHtml($http) {
    ob_start();
    include('html.php');
    $out = ob_get_clean();
    foreach ($http->flash->all() as $message) {
        echo $message['class'] . ' - ' . $message['value'];
    }
    return $out;
}

//$http->cookie->set('foo', 'bar');
$http->session->set('foo', 'bar');
//$http->flash->set(\Mindy\Http\FlashCollection::SUCCESS, 'bar');
//$http->session->get('foo');
//$http->cookie->remove('foo');
$http->html(getHtml($http));

//$http->send(new \Mindy\Http\RedirectResponse('http://google.com', 302));
//$http->html(getHtml());

//$data = json_encode(['foo' => 'bar']);
//$body = \GuzzleHttp\Psr7\stream_for($data);
//
//$response = (new \GuzzleHttp\Psr7\Response())
//    ->withBody($body)
//    ->withStatus(200)
//    ->withHeader('Content-Type', 'application/json');
//respond($response);