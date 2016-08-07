<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05.08.16
 * Time: 21:02
 */

require(__DIR__ . '/vendor/autoload.php');

$request = new \Mindy\Http\Request(
    $_GET,
    $_POST,
    $_FILES,
    getallheaders(),
    $_COOKIE,
    $_SERVER
);

$response = new \Mindy\Http\Response();

$queue = [];
$middleware = new \Mindy\Http\Middleware($queue, function($class) {
    return new $class;
});
$response = $middleware->__invoke($request, $response);
$response->withStatus(200);

//echo 'GET<br/>';
//var_dump($request->get->all());
//echo 'POST<br/>';
//var_dump($request->post->all());
//echo 'FILES<br/>';
//var_dump($request->files->all());
//echo 'COOKIES<br/>';
//var_dump($request->cookies->all());
//echo 'HEADERS<br/>';
//var_dump($request->headers->all());