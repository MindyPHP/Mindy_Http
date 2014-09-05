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
 * @date 10/06/14.06.2014 14:03
 */

namespace Mindy\Http;

use Mindy\Base\Exception\Exception;
use Mindy\Helper\Collection;
use Mindy\Helper\Traits\Configurator;

/**
 * CCookieCollection implements a collection class to store cookies.
 *
 * You normally access it via {@link CHttpRequest::getCookies()}.
 *
 * Since CCookieCollection extends from {@link CMap}, it can be used
 * like an associative array as follows:
 * <pre>
 * $cookies[$name]=new Cookie($name,$value); // sends a cookie
 * $value=$cookies[$name]->value; // reads a cookie value
 * unset($cookies[$name]);  // removes a cookie
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.0
 */
class CookieCollection extends Collection
{
    /**
     * @var bool
     */
    public $enableCookieValidation = true;
    public $securityManager;

    /**
     * Constructor.
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        if (!isset($config['data'])) {
            $cookies = [];
            if ($this->enableCookieValidation) {
                foreach ($_COOKIE as $name => $value) {
                    if (is_string($value) && ($value = $this->securityManager->validateData($value)) !== false) {
                        $cookies[$name] = new Cookie($name, @unserialize($value));
                    }
                }
            } else {
                foreach ($_COOKIE as $name => $value) {
                    $cookies[$name] = new Cookie($name, $value);
                }
            }
            $this->exchangeArray($cookies);
        }
    }

    /**
     * Adds a cookie with the specified name.
     * This overrides the parent implementation by performing additional
     * operations for each newly added Cookie object.
     * @param mixed $key Cookie name.
     * @param Cookie $value Cookie object.
     * @throws Exception if the item to be inserted is not a Cookie object.
     */
    public function add($key, $value)
    {
        $cookie = $value instanceof Cookie ? $value : new Cookie($key, $value);
        parent::add($key, $cookie);
        $value = $cookie->value;
        if ($this->enableCookieValidation) {
            $value = $this->securityManager->hashData(serialize($value));
        }

        setcookie($cookie->name, $value, $cookie->expire, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly);
    }

    /**
     * Deletes a cookie.
     * @param $key
     */
    public function remove($key)
    {
        $name = $key;
        if($key instanceof Cookie) {
            $name = $key->name;
        }

        if($this->has($name)) {
            $cookie = $this->get($name);
            setcookie($cookie->name, '', 0, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly);
        }
        parent::remove($name);
    }
}
