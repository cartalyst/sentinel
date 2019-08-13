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
    /** @test */
    public function it_can_get_a_cookie()
    {
        $cookie = new NativeCookie('__sentinel');

        $this->assertNull($cookie->get());

        $_COOKIE['__sentinel'] = json_encode('bar');

        $this->assertSame('bar', $cookie->get());
    }
}
