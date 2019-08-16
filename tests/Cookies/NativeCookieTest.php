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

namespace Cartalyst\Sentinel\Tests\Cookies;

use PHPUnit\Framework\TestCase;
use Cartalyst\Sentinel\Cookies\NativeCookie;

class NativeCookieTest extends TestCase
{

    /**
     * @runInSeparateProcess
     * @test
     * */
    public function it_can_set_different_options_for_cookie()
    {
        $options = [
            'name' => 'foo',
            'domain' => 'bar',
            'path' => 'foobar',
            'secure' => true,
            'http_only' => true
        ];
        $cookie = new NativeCookie($options);

        $this->assertNull($cookie->put('mockCookie'));

        $headers = xdebug_get_headers();

        $this->assertStringContainsString('foo=%22mockCookie%22;',$headers[0]);
        $this->assertStringContainsString('path=foobar;',$headers[0]);
        $this->assertStringContainsString('domain=bar;',$headers[0]);
    }

    /**
     * @runInSeparateProcess
     * @test
     * */
    public function it_can_set_a_cookie()
    {
        $cookie = new NativeCookie('__sentinel');
        $expires = (time() + (2628000 * 60)) - time();

        $this->assertNull($cookie->put('mockCookie'));

        $headers = xdebug_get_headers();

        $this->assertStringContainsString('__sentinel=%22mockCookie%22;',$headers[0]);
        $this->assertStringContainsString('Max-Age=' . $expires . ';',$headers[0]);
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
     * */
    public function it_can_forget_a_cookie()
    {
        $cookie = new NativeCookie('__sentinel');
        $expires = 0;

        $this->assertNull($cookie->forget());

        $headers = xdebug_get_headers();

        $this->assertStringContainsString('__sentinel=null;',$headers[0]);
        $this->assertStringContainsString('Max-Age=' . $expires . ';',$headers[0]);
    }
}
