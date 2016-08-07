<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05.08.16
 * Time: 19:58
 */

namespace Mindy\Http\Tests;

use Mindy\Http\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testGetHeaders()
    {
        $headers = ["Content-Type: text/html"];
        $r = new Request([], [], [], $headers, [], []);
        $headers = $r->headers->all();
        $this->assertEquals(['Content-Type' => ['text/html']], $headers);
        $this->assertEquals(['text/html'], $r->headers->get('Content-Type'));

        $r = new Request([], [], [], getallheaders(), [], []);
        $headers = $r->headers->all();
        $this->assertEquals([], $headers);
    }

    public function testGetGet()
    {
        $r = new Request(['pk' => 1]);
        $this->assertEquals(['pk' => 1], $r->get->all());
        $this->assertEquals(1, $r->get->get('pk'));
    }
}
