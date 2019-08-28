<?php

/*
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Sentinel
 * @version    3.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2019, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Cookies;

use Mockery as m;
use PHPUnit\Framework\TestCase;

function setcookie($name, $value, $expires, $path, $domain, $secure, $httponly)
{
    return NativeCookieTest::$globalFunctions->setcookie(
        $name,
        $value,
        $expires,
        $path,
        $domain,
        $secure,
        $httponly
    );
}

class NativeCookieTest extends TestCase
{
    public static $globalFunctions;

    protected function setUp(): void
    {
        self::$globalFunctions = m::mock();
    }

    /**
     * @runInSeparateProcess
     * @test
     */
    public function it_can_set_different_options_for_cookie()
    {
        $options = [
            'name'      => 'foo',
            'domain'    => 'bar',
            'path'      => 'foobar',
            'secure'    => true,
            'http_only' => true,
        ];
        $cookie = new NativeCookie($options);

        self::$globalFunctions->shouldReceive('setcookie')->with(
            'foo',
            json_encode('mockCookie'),
            time() + (2628000 * 60),
            'foobar',
            'bar',
            true,
            true
        );

        $this->assertNull($cookie->put('mockCookie'));
    }

    /**
     * @runInSeparateProcess
     * @test
     */
    public function it_can_set_a_cookie()
    {
        $cookie  = new NativeCookie('__sentinel');
        $expires = (time() + (2628000 * 60)) - time();

        self::$globalFunctions->shouldReceive('setcookie')->with(
            '__sentinel',
            json_encode('mockCookie'),
            time() + (2628000 * 60),
            '/',
            '',
            false,
            false
        );

        $this->assertNull($cookie->put('mockCookie'));
    }

    /** @test */
    public function it_can_get_a_cookie()
    {
        $cookie = new NativeCookie('__sentinel');

        $this->assertNull($cookie->get());

        $_COOKIE['__sentinel'] = json_encode('bar');

        $this->assertSame('bar', $cookie->get());
    }

    /**
     * @runInSeparateProcess
     * @test
     */
    public function it_can_forget_a_cookie()
    {
        $cookie = new NativeCookie('__sentinel');

        self::$globalFunctions->shouldReceive('setcookie')->with(
            '__sentinel',
            'null',
            time() + (-2628000 * 60),
            '/',
            '',
            false,
            false
        );

        $this->assertNull($cookie->forget());
    }
}
