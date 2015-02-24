<?php

/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Sentinel
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\tests;

use Cartalyst\Sentinel\Cookies\NativeCookie;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class NativeCookieTest extends PHPUnit_Framework_TestCase
{
    /**
     * Close mockery.
     *
     * @return void
     */
    public function tearDown()
    {
        m::close();
    }

    public function testGet()
    {
        $cookie = new NativeCookie('__sentinel');
        $this->assertNull($cookie->get());

        $_COOKIE['__sentinel'] = serialize('bar');
        $this->assertEquals('bar', $cookie->get());
    }
}
