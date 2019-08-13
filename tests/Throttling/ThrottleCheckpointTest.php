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

namespace Cartalyst\Sentinel\Tests\Throttling;

use Mockery as m;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Cartalyst\Sentinel\Users\EloquentUser;
use Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository;

class ThrottleCheckpointTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function can_login_the_user_without_being_throttled()
    {
        $throttle = m::mock(IlluminateThrottleRepository::class);
        $throttle->shouldReceive('globalDelay')->once()->andReturn(0);
        $throttle->shouldReceive('userDelay')->once()->andReturn(0);

        $user = m::mock(EloquentUser::class);

        $checkpoint = new ThrottleCheckpoint($throttle);

        $status = $checkpoint->login($user);

        $this->assertTrue($status);
    }

    /** @test */
    public function can_check_if_the_user_is_being_throttled()
    {
        $throttle = m::mock(IlluminateThrottleRepository::class);

        $user = m::mock(EloquentUser::class);

        $checkpoint = new ThrottleCheckpoint($throttle);

        $status = $checkpoint->check($user);

        $this->assertTrue($status);
    }

    /** @test */
    public function can_log_a_throttling_event()
    {
        $throttle = m::mock(IlluminateThrottleRepository::class);
        $throttle->shouldReceive('globalDelay')->once();
        $throttle->shouldReceive('userDelay')->once();
        $throttle->shouldReceive('log')->once();

        $user = m::mock(EloquentUser::class);

        $checkpoint = new ThrottleCheckpoint($throttle);

        $checkpoint->fail($user);

        $this->assertTrue(true); // TODO: Add proper assertion later
    }

    /** @test */
    public function testWithIpAddress()
    {
        $throttle = m::mock(IlluminateThrottleRepository::class);
        $throttle->shouldReceive('globalDelay')->once();
        $throttle->shouldReceive('ipDelay')->once();
        $throttle->shouldReceive('userDelay')->once();
        $throttle->shouldReceive('log')->once();

        $user = m::mock(EloquentUser::class);

        $checkpoint = new ThrottleCheckpoint($throttle, '127.0.0.1');

        $status = $checkpoint->fail($user);

        $this->assertTrue(true); // TODO: Add proper assertion later
    }

    /** @test */
    public function testFailedLogin()
    {
        $this->expectException(ThrottlingException::class);
        $this->expectExceptionMessage('Too many unsuccessful attempts have been made globally, logins are locked for another [10] second(s).');

        $throttle = m::mock(IlluminateThrottleRepository::class);
        $throttle->shouldReceive('globalDelay')->once()->andReturn(10);

        $user = m::mock(EloquentUser::class);

        $checkpoint = new ThrottleCheckpoint($throttle);

        $checkpoint->login($user);
    }

    /** @test */
    public function testThrowsExceptionWithIpDelay()
    {
        $this->expectException(ThrottlingException::class);
        $this->expectExceptionMessage('Suspicious activity has occured on your IP address and you have been denied access for another [10] second(s).');

        $throttle = m::mock(IlluminateThrottleRepository::class);
        $throttle->shouldReceive('globalDelay')->once();
        $throttle->shouldReceive('ipDelay')->once()->andReturn(10);

        $user = m::mock(EloquentUser::class);

        $checkpoint = new ThrottleCheckpoint($throttle, '127.0.0.1');

        $checkpoint->fail($user);
    }

    /** @test */
    public function testThrowsExceptionWithUserDelay()
    {
        $this->expectException(ThrottlingException::class);
        $this->expectExceptionMessage('Too many unsuccessful login attempts have been made against your account. Please try again after another [10] second(s).');

        $throttle = m::mock(IlluminateThrottleRepository::class);
        $throttle->shouldReceive('globalDelay')->once();
        $throttle->shouldReceive('ipDelay')->once()->andReturn(0);
        $throttle->shouldReceive('userDelay')->once()->andReturn(10);

        $user = m::mock(EloquentUser::class);

        $checkpoint = new ThrottleCheckpoint($throttle, '127.0.0.1');

        $checkpoint->fail($user);
    }

    /** @test */
    public function testGetThrottlingExceptionAttributes()
    {
        $throttle = m::mock(IlluminateThrottleRepository::class);
        $throttle->shouldReceive('globalDelay')->once();
        $throttle->shouldReceive('ipDelay')->once()->andReturn(0);
        $throttle->shouldReceive('userDelay')->once()->andReturn(10);

        $user = m::mock(EloquentUser::class);

        try {
            $checkpoint = new ThrottleCheckpoint($throttle, '127.0.0.1');

            $checkpoint->fail($user);
        } catch (ThrottlingException $e) {
            $this->assertSame(10, $e->getDelay());
            $this->assertSame('user', $e->getType());
            $this->assertLessThanOrEqual(10000, Carbon::now()->addSeconds(10)->diffInMicroseconds($e->getFree()));
        }
    }
}
