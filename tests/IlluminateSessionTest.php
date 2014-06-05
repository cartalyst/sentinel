<?php namespace Cartalyst\Sentinel\Tests;
/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the license.txt file.
 *
 * @package    Sentinel
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\Sentinel\Sessions\IlluminateSession;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class IlluminateSessionTest extends PHPUnit_Framework_TestCase {

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
		$session = new IlluminateSession($store = m::mock('Illuminate\Session\Store'), 'foo');
		$store->shouldReceive('put')->with('foo', 'bar')->once();
		$session->put('bar');
	}

	public function testGet()
	{
		$session = new IlluminateSession($store = m::mock('Illuminate\Session\Store'), 'foo');
		$store->shouldReceive('get')->with('foo')->once()->andReturn('bar');
		$this->assertEquals('bar', $session->get());
	}

	public function testForget()
	{
		$session = new IlluminateSession($store = m::mock('Illuminate\Session\Store'), 'foo');
		$store->shouldReceive('forget')->with('foo')->once();
		$session->forget();
	}

}
