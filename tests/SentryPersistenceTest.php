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

use Cartalyst\Sentinel\Persistence\SentinelPersistence;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class SentinelPersistenceTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testCheckWithNoSessionOrCookie()
	{
		$persistence = new SentinelPersistence($session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface'));
		$session->shouldReceive('get')->once();
		$cookie->shouldReceive('get')->once();
		$this->assertNull($persistence->check());
	}

	public function testCheckWithSession()
	{
		$persistence = new SentinelPersistence($session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface'));
		$session->shouldReceive('get')->once()->andReturn('foo');
		$this->assertEquals('foo', $persistence->check());
	}

	public function testCheckWithCookie()
	{
		$persistence = new SentinelPersistence($session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface'));
		$session->shouldReceive('get')->once();
		$cookie->shouldReceive('get')->once()->andReturn('bar');
		$this->assertEquals('bar', $persistence->check());
	}

	public function testAdd()
	{
		$persistence = new SentinelPersistence($session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface'));
		$persistable = m::mock('Cartalyst\Sentinel\Persistence\PersistableInterface');
		$persistable->shouldReceive('generatePersistenceCode')->once()->andReturn('code');
		$session->shouldReceive('put')->with('code')->once();
		$persistable->shouldReceive('addPersistenceCode')->once();
		$persistence->add($persistable);
	}

	public function testAddAndRemember()
	{
		$persistence = new SentinelPersistence($session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface'));
		$persistable = m::mock('Cartalyst\Sentinel\Persistence\PersistableInterface');
		$persistable->shouldReceive('generatePersistenceCode')->once()->andReturn('code');
		$session->shouldReceive('put')->with('code')->once();
		$cookie->shouldReceive('put')->with('code')->once();
		$persistable->shouldReceive('addPersistenceCode')->once();
		$persistence->addAndRemember($persistable);
	}

	public function testRemove()
	{
		$persistence = m::mock('Cartalyst\Sentinel\Persistence\SentinelPersistence[check]', array($session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface')));
		$persistable = m::mock('Cartalyst\Sentinel\Persistence\PersistableInterface');
		$persistence->shouldReceive('check')->once()->andReturn('code');
		$session->shouldReceive('forget')->once();
		$cookie->shouldReceive('forget')->once();
		$persistable->shouldReceive('removePersistenceCode')->once()->andReturn('code');
		$persistence->remove($persistable);
	}

	public function testFlush()
	{
		$persistence = m::mock('Cartalyst\Sentinel\Persistence\SentinelPersistence[check]', array($session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface')));
		$persistable = m::mock('Cartalyst\Sentinel\Persistence\PersistableInterface');
		$session->shouldReceive('forget')->once();
		$cookie->shouldReceive('forget')->once();
		$persistable->shouldReceive('getPersistenceCodes')->once()->andReturn(['code1', 'code2']);
		$persistable->shouldReceive('removePersistenceCode')->once()->andReturn('code1');
		$persistable->shouldReceive('removePersistenceCode')->once()->andReturn('code2');
		$persistence->flush($persistable);
	}

}
