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
 * @version    2.1.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\tests;

use Cartalyst\Sentinel\Cookies\FuelPHPCookie;
use Mockery as m;
use PHPUnit_Framework_TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class FuelPHPCookieTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup resources and dependencies.
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        require_once __DIR__.'/stubs/fuelphp/Fuel/Core/Cookie.php';
    }

    /**
     * Close mockery.
     *
     * @return void
     */
    public function tearDown()
    {
        m::close();
    }

    public function testPut()
    {
        $cookie = new FuelPHPCookie('foo');
        $cookie->put('bar');

        $this->assertTrue(isset($_SERVER['__cookie.set']));
        $result = $_SERVER['__cookie.set'];
        $this->assertCount(3, $result);
        list($key, $value, $expire) = $result;

        $this->assertEquals('foo', $key);
        $this->assertEquals(serialize('bar'), $value);
        $this->assertEquals(2628000, $expire);
        unset($_SERVER['__cookie.set']);
    }

    public function testGet()
    {
        $cookie = new FuelPHPCookie('foo');
        $this->assertEquals('baz', $cookie->get());
    }

    public function testForget()
    {
        $cookie = new FuelPHPCookie('foo');
        $this->assertFalse(isset($_SERVER['__cookie.delete']));
        $cookie->forget();
        $this->assertTrue($_SERVER['__cookie.delete']);
        unset($_SERVER['__cookie.delete']);
    }
}
