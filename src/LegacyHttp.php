<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 08/08/16
 * Time: 10:09
 */

namespace Mindy\Http;

/**
 * Class LegacyHttp
 * @package Mindy\Http
 */
trait LegacyHttp
{
    /**
     * @return string
     */
    public function getPath()
    {
        return $this->getRequest()->getRequestTarget();
    }

    /**
     * @return bool
     */
    public function getIsPost()
    {
        return $this->getRequest()->getIsPost();
    }

    /**
     * @return bool
     */
    public function getIsAjax()
    {
        return $this->getRequest()->getIsAjax();
    }
}