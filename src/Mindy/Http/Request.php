<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 12/06/14.06.2014 19:46
 */

namespace Mindy\Http;


use Mindy\Base\Mindy;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;

class Request
{
    use Configurator, Accessors;

    /**
     * @var boolean whether cookies should be validated to ensure they are not tampered. Defaults to false.
     */
    public $enableCookieValidation = false;
    /**
     * @var boolean whether to enable CSRF (Cross-Site Request Forgery) validation. Defaults to false.
     * By setting this property to true, forms submitted to an Yii Web application must be originated
     * from the same application. If not, a 400 HTTP exception will be raised.
     * Note, this feature requires that the user client accepts cookie.
     * You also need to use {@link CHtml::form} or {@link CHtml::statefulForm} to generate
     * the needed HTML forms in your pages.
     * @see http://seclab.stanford.edu/websec/csrf/csrf.pdf
     */
    public $enableCsrfValidation = true;

    /**
     * @var CookieCollection
     */
    public $cookies;
    /**
     * @var HttpCollection
     */
    public $get;
    /**
     * @var HttpCollection
     */
    public $post;
    /**
     * @var HttpCollection
     */
    public $put;
    /**
     * @var HttpCollection
     */
    public $delete;
    /**
     * @var HttpCollection
     */
    public $patch;
    /**
     * @var FilesCollection
     */
    public $files;
    /**
     * @var SessionCollection
     */
    public $session;
    /**
     * @var \Mindy\Http\Http
     */
    public $http;
    /**
     * @var \Mindy\Http\Csrf
     */
    public $csrf;
    /**
     * @var \Modules\User\Components\Flash
     */
    public $flash;

    public function init()
    {
        $this->http = new Http();

        $this->get = new HttpCollection($_GET);
        $this->post = new HttpCollection($_POST);
        $this->files = new FilesCollection($_FILES);
        $this->put = new HttpCollection($this->http->getIsPutRequest() ? $_POST : []);
        $this->delete = new HttpCollection($this->http->getIsDeleteRequest() ? $_POST : []);
        $this->patch = new HttpCollection($this->http->getIsPatchRequest() ? $_POST : []);

        // TODO
        $this->flash = new \Modules\User\Components\Flash;

        $sm = Mindy::app()->securityManager;

        $this->cookies = new CookieCollection([
            'securityManager' => $sm,
            'enableCookieValidation' => $this->enableCookieValidation
        ]);

        $this->session = Mindy::app()->session;
        $this->csrf = new Csrf($sm, $this->cookies, $this->http, [
            'enableCsrfValidation' => $this->enableCsrfValidation
        ]);
    }

    public function getIsAjax()
    {
        return $this->http->getIsAjaxRequest();
    }

    public function getIsPost()
    {
        return $this->http->getIsPostRequest();
    }

    public function getParam($name, $defaultValue = null)
    {
        return $this->get->get($name, $this->post->get($name, $defaultValue));
    }

    public function getPath()
    {
        return $this->http->getPath();
    }

    public function getHost()
    {
        return $this->http->getHost();
    }

    public function redirect($url, $data = null, $statusCode = 302)
    {
        $this->http->redirect($url, $data, $statusCode);
    }

    public function getDomain()
    {
        return $this->http->getHostInfo();
    }
}
