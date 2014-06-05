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

use Cartalyst\Sentinel\Swipe\SentinelSwipe;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class SentinelSwipeTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testResponse()
	{
		$swipe = new SentinelSwipe('email@example.com', 'password', 'key', 'code', '127.0.0.1');

		$this->markTestIncomplete();
	}

	public function testSaveNumber()
	{
		$swipe = new SentinelSwipe('email@example.com', 'password', 'key', 'code', '127.0.0.1');

		$this->markTestIncomplete();
	}

	public function testCheckAnswer()
	{
		$swipe = new SentinelSwipe('email@example.com', 'password', 'key', 'code', '127.0.0.1');

		$this->markTestIncomplete();
	}

}
