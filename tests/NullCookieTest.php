<?php

namespace Cartalyst\Sentinel\tests;

use Cartalyst\Sentinel\Cookies\NullCookie;
use PHPUnit_Framework_TestCase;

class NullCookieTest extends PHPUnit_Framework_TestCase
{
    public function testPut()
    {
        $cookie = new NullCookie();
        $this->assertNull($cookie->put('cookie'));
    }

    public function testGet()
    {
        $cookie = new NullCookie();
        $this->assertNull($cookie->get());
    }

    public function testForget()
    {
        $cookie = new NullCookie();
        $this->assertNull($cookie->forget());
    }
}
