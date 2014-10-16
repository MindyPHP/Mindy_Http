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
 * @date 04/09/14.09.2014 17:07
 */

namespace Mindy\Http\Traits;


use Mindy\Exception\HttpException;

trait HttpErrors
{
    public function errorMessage($code)
    {
        $codes = [
            400 => 'Invalid request. Please do not repeat this request again.',
            403 => 'You are not authorized to perform this action.',
            404 => 'The requested page does not exist.',
            500 => 'Error',
        ];
        return isset($codes[$code]) ? $codes[$code] : 'Unknown error';
    }

    /**
     * @param $code
     * @param null $message
     * @throws HttpException
     */
    public function error($code, $message = null)
    {
        // CoreModule::t($message === null ? $codes[$code] : $message, [], 'errors')
        throw new HttpException($code, $message === null ? $this->errorMessage($code) : $message);
    }
}
