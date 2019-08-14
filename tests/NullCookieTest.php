<?php

/**
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
 * @version    2.0.18
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2019, Cartalyst LLC
 * @link       http://cartalyst.com
 */

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
