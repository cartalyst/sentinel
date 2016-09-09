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
 * @version    2.0.13
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2016, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\tests;

use Cartalyst\Sentinel\Sessions\FuelPHPSession;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class FuelPHPSessionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup resources and dependencies.
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        require_once __DIR__.'/stubs/fuelphp/Fuel/Core/Session_Driver.php';
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
        $session = new FuelPHPSession($store = m::mock('Fuel\Core\Session_Driver'), 'foo');
        $store->shouldReceive('set')->with('foo', 'bar')->once();
        $session->put('bar');
    }

    public function testGet()
    {
        $session = new FuelPHPSession($store = m::mock('Fuel\Core\Session_Driver'), 'foo');
        $store->shouldReceive('get')->with('foo')->once()->andReturn('bar');
        $this->assertEquals('bar', $session->get());
    }

    public function testForget()
    {
        $session = new FuelPHPSession($store = m::mock('Fuel\Core\Session_Driver'), 'foo');
        $store->shouldReceive('delete')->with('foo')->once();
        $session->forget();
    }
}
