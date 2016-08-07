<?php

namespace Mindy\Http;

/**
 * Class CookieCollection
 * @package Mindy\Http
 * @method get($key, $defaultValue = null) Cookie
 */
class CookieCollection extends Collection
{
    /**
     * Constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $name => $value) {
            $this->set($name, $value);
        }
    }

    /**
     * @param $key
     * @param $value
     * @return $this|void
     */
    public function set($key, $value)
    {
        $cookie = $value instanceof Cookie ? $value : new Cookie($key, $value);
        parent::set($key, $cookie);
        setcookie($cookie->name, $cookie->value, $cookie->expire, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly);
        return $this;
    }

    /**
     * Deletes a cookie.
     * @param $key
     */
    public function remove($key)
    {
        $name = $key;
        if ($key instanceof Cookie) {
            $name = $key->name;
        }

        /** @var Cookie $cookie */
        if ($this->has($name)) {
            $cookie = $this->get($name);
            setcookie($cookie->name, '', 0, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly);
        }
        parent::remove($name);
    }
}