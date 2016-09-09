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

use Carbon\Carbon;
use Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class ThrottleCheckpointTest extends PHPUnit_Framework_TestCase
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

    public function testLogin()
    {
        $checkpoint = new ThrottleCheckpoint($throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository'));

        $throttle->shouldReceive('globalDelay')->once()->andReturn(0);
        $throttle->shouldReceive('userDelay')->once()->andReturn(0);

        $checkpoint->login(m::mock('Cartalyst\Sentinel\Users\EloquentUser'));
    }

    /**
     * @expectedException \Cartalyst\Sentinel\Checkpoints\ThrottlingException
     */
    public function testFailedLogin()
    {
        $checkpoint = new ThrottleCheckpoint($throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository'));

        $throttle->shouldReceive('globalDelay')->once()->andReturn(10);

        $checkpoint->login(m::mock('Cartalyst\Sentinel\Users\EloquentUser'));
    }

    public function testCheck()
    {
        $checkpoint = new ThrottleCheckpoint($throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository'));

        $checkpoint->check(m::mock('Cartalyst\Sentinel\Users\EloquentUser'));
    }

    public function testFail()
    {
        $checkpoint = new ThrottleCheckpoint($throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository'));

        $throttle->shouldReceive('globalDelay')->once();
        $throttle->shouldReceive('userDelay')->once();
        $throttle->shouldReceive('log')->once();

        $checkpoint->fail(m::mock('Cartalyst\Sentinel\Users\EloquentUser'));
    }

    public function testWithIpAddress()
    {
        $checkpoint = new ThrottleCheckpoint($throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository'), '127.0.0.1');

        $throttle->shouldReceive('globalDelay')->once();
        $throttle->shouldReceive('ipDelay')->once();
        $throttle->shouldReceive('userDelay')->once();
        $throttle->shouldReceive('log')->once();

        $checkpoint->fail(m::mock('Cartalyst\Sentinel\Users\EloquentUser'));
    }

    /**
     * @expectedException \Cartalyst\Sentinel\Checkpoints\ThrottlingException
     */
    public function testThrowsExceptionWithIpDelay()
    {
        $checkpoint = new ThrottleCheckpoint($throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository'), '127.0.0.1');

        $throttle->shouldReceive('globalDelay')->once();
        $throttle->shouldReceive('ipDelay')->once()->andReturn(10);

        $checkpoint->fail(m::mock('Cartalyst\Sentinel\Users\EloquentUser'));
    }

    /**
     * @expectedException \Cartalyst\Sentinel\Checkpoints\ThrottlingException
     */
    public function testThrowsExceptionWithUserDelay()
    {
        $checkpoint = new ThrottleCheckpoint($throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository'), '127.0.0.1');

        $throttle->shouldReceive('globalDelay')->once();
        $throttle->shouldReceive('ipDelay')->once()->andReturn(0);
        $throttle->shouldReceive('userDelay')->once()->andReturn(10);

        $checkpoint->fail(m::mock('Cartalyst\Sentinel\Users\EloquentUser'));
    }

    public function testGetThrottlingExceptionAttributes()
    {
        $checkpoint = new ThrottleCheckpoint($throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository'), '127.0.0.1');

        $throttle->shouldReceive('globalDelay')->once();
        $throttle->shouldReceive('ipDelay')->once()->andReturn(0);
        $throttle->shouldReceive('userDelay')->once()->andReturn(10);

        try {
            $checkpoint->fail(m::mock('Cartalyst\Sentinel\Users\EloquentUser'));
        } catch (ThrottlingException $e) {
            $this->assertEquals(10, $e->getDelay());
            $this->assertEquals('user', $e->getType());
            $this->assertEquals(Carbon::now()->addSeconds(10), $e->getFree());
        }
    }
}
