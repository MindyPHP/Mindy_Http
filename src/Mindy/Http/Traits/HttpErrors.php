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


use Mindy\Base\Exception\HttpException;

trait HttpErrors
{
    /**
     * @param $code
     * @param null $message
     * @throws HttpException
     */
    public function error($code, $message = null)
    {
        $codes = [
            400 => 'Invalid request. Please do not repeat this request again.',
            403 => 'You are not authorized to perform this action.',
            404 => 'The requested page does not exist.',
            500 => 'Error',
        ];

        // CoreModule::t($message === null ? $codes[$code] : $message, [], 'errors')
        throw new HttpException($code, $message === null ? $codes[$code] : $message);
    }
}
