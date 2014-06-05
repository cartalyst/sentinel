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

use Cartalyst\Sentinel\Sessions\NativeSession;
use Mockery as m;
use PHPUnit_Framework_TestCase;
use stdClass;

class NativeSessionTest extends PHPUnit_Framework_TestCase {

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
		$session = new NativeSession('__sentinel');

		$class = new stdClass;
		$class->foo = 'bar';

		$session->put($class);
		$this->assertEquals(serialize($class), $_SESSION['__sentinel']);
		unset($_SESSION['__sentinel']);
	}

	public function testGet()
	{
		$session = new NativeSession('__sentinel');
		$this->assertNull($session->get());

		$class = new stdClass;
		$class->foo = 'bar';
		$_SESSION['__sentinel'] = serialize($class);

		$this->assertEquals($class, $session->get());
		unset($_SESSION['__sentinel']);
	}

	public function testForget()
	{
		$_SESSION['__sentinel'] = 'bar';

		$session = new NativeSession('__sentinel');

		$this->assertEquals('bar', $_SESSION['__sentinel']);
		$session->forget();
		$this->assertFalse(isset($_SESSION['__sentinel']));
	}

}
