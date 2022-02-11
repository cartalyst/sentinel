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
 * @version    6.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2022, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Sentinel\Tests\Throttling;

use Mockery as m;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Cartalyst\Sentinel\Users\UserInterface;
use Cartalyst\Sentinel\Throttling\EloquentThrottle;
use Cartalyst\Sentinel\Users\IlluminateUserRepository;

class IlluminateThrottleRepositoryTest extends TestCase
{
    protected $query;

    protected $model;

    protected $models;

    protected $users;

    protected $throttle;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->models = m::mock(Collection::class);

        $this->query = m::mock(Builder::class);

        $this->model = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle');
        $this->model->shouldReceive('newQuery')->andReturn($this->query);

        $this->users = m::mock(IlluminateUserRepository::class);

        $this->throttle = m::mock(
            'Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]',
            [$this->users]
        );
        $this->throttle->shouldReceive('createModel')->andReturn($this->model);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    public function testConstructor()
    {
        $throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]', [
            EloquentThrottle::class, 1, 2, 3, 4, 5, 6,
        ]);

        $this->assertSame(1, $throttle->getGlobalInterval());
        $this->assertSame(2, $throttle->getGlobalThresholds());
        $this->assertSame(3, $throttle->getIpInterval());
        $this->assertSame(4, $throttle->getIpThresholds());
        $this->assertSame(5, $throttle->getUserInterval());
        $this->assertSame(6, $throttle->getUserThresholds());
    }

    /** @test */
    public function testGlobalDelayWithIntegerThreshold1()
    {
        $first = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle');
        $first->shouldReceive('getAttribute')->andReturn(Carbon::createFromTimestamp(time()));

        $this->models->shouldReceive('count')->andReturn(6);
        $this->models->shouldReceive('first')->andReturn($first);

        $this->throttle->setGlobalInterval(10);
        $this->throttle->setGlobalThresholds(5);

        $this->query->shouldReceive('get')->andReturn($this->models);
        $this->query->shouldReceive('where')->with('type', 'global')->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturnSelf();

        $this->assertEqualsWithDelta(10, $this->throttle->globalDelay(), 3);
    }

    /** @test */
    public function testGlobalDelayWithIntegerThreshold2()
    {
        $first = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle');
        $first->shouldReceive('getAttribute')->andReturn(Carbon::createFromTimestamp(time()));

        $this->models->shouldReceive('count')->andReturn(6);
        $this->models->shouldReceive('first')->andReturn($first);

        $this->throttle->setGlobalInterval(10);
        $this->throttle->setGlobalThresholds(200);

        $this->query->shouldReceive('get')->andReturn($this->models);
        $this->query->shouldReceive('where')->with('type', 'global')->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturnSelf();

        $this->assertEqualsWithDelta(0, $this->throttle->globalDelay(), 3);
    }

    /** @test */
    public function testGlobalDelayWithArrayThresholds1()
    {
        $last = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle');
        $last->shouldReceive('getAttribute')->andReturn(Carbon::createFromTimestamp(time()));

        $this->models->shouldReceive('count')->andReturn(6);
        $this->models->shouldReceive('last')->andReturn($last);

        $this->throttle->setGlobalInterval(10);
        $this->throttle->setGlobalThresholds([5 => 3, 10 => 10]);

        $this->query->shouldReceive('get')->andReturn($this->models);
        $this->query->shouldReceive('where')->with('type', 'global')->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturnSelf();

        $this->assertEqualsWithDelta(3, $this->throttle->globalDelay(), 3);
    }

    /** @test */
    public function testGlobalDelayWithArrayThresholds2()
    {
        $last = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle');
        $last->shouldReceive('getAttribute')->andReturn(Carbon::createFromTimestamp(time()));

        $this->models->shouldReceive('count')->andReturn(11);
        $this->models->shouldReceive('last')->andReturn($last);

        $this->throttle->setGlobalInterval(10);
        $this->throttle->setGlobalThresholds([5 => 3, 10 => 10]);

        $this->query->shouldReceive('get')->andReturn($this->models);
        $this->query->shouldReceive('where')->with('type', 'global')->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturnSelf();

        $this->assertEqualsWithDelta(10, $this->throttle->globalDelay(), 3);
    }

    /** @test */
    public function testGlobalDelayWithArrayThresholds3()
    {
        $last = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle');
        $last->shouldReceive('getAttribute')->andReturn(Carbon::createFromTimestamp(time() - 200));

        $this->models->shouldReceive('count')->andReturn(11);
        $this->models->shouldReceive('last')->andReturn($last);

        $this->throttle->setGlobalInterval(10);
        $this->throttle->setGlobalThresholds([5 => 33, 10 => 100]);

        $this->query->shouldReceive('get')->andReturn($this->models);
        $this->query->shouldReceive('where')->with('type', 'global')->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturnSelf();

        $this->assertEqualsWithDelta(0, $this->throttle->globalDelay(), 3);
    }

    /** @test */
    public function testIpDelayWithIntegerThreshold()
    {
        $first = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle');
        $first->shouldReceive('getAttribute')->andReturn(Carbon::createFromTimestamp(time()));

        $this->models->shouldReceive('count')->andReturn(6);
        $this->models->shouldReceive('first')->andReturn($first);

        $this->throttle->setIpInterval(10);
        $this->throttle->setIpThresholds(5);

        $this->query->shouldReceive('get')->andReturn($this->models);
        $this->query->shouldReceive('where')->with('type', 'ip')->andReturnSelf();
        $this->query->shouldReceive('where')->with('ip', '127.0.0.1')->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturnSelf();

        $this->assertEqualsWithDelta(10, $this->throttle->ipDelay('127.0.0.1'), 3);
    }

    /** @test */
    public function testIpDelayWithArrayThresholds1()
    {
        $last = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle');
        $last->shouldReceive('getAttribute')->andReturn(Carbon::createFromTimestamp(time()));

        $this->models->shouldReceive('count')->andReturn(6);
        $this->models->shouldReceive('last')->andReturn($last);

        $this->throttle->setIpInterval(10);
        $this->throttle->setIpThresholds([5 => 3, 10 => 10]);

        $this->query->shouldReceive('get')->andReturn($this->models);
        $this->query->shouldReceive('where')->with('type', 'ip')->andReturnSelf();
        $this->query->shouldReceive('where')->with('ip', '127.0.0.1')->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturnSelf();

        $this->assertEqualsWithDelta(3, $this->throttle->ipDelay('127.0.0.1'), 3);
    }

    /** @test */
    public function testIpDelayWithArrayThresholds2()
    {
        $last = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle');
        $last->shouldReceive('getAttribute')->andReturn(Carbon::createFromTimestamp(time()));

        $this->models->shouldReceive('count')->andReturn(11);
        $this->models->shouldReceive('last')->andReturn($last);

        $this->throttle->setIpInterval(10);
        $this->throttle->setIpThresholds([5 => 3, 10 => 10]);

        $this->query->shouldReceive('get')->andReturn($this->models);
        $this->query->shouldReceive('where')->with('type', 'ip')->andReturnSelf();
        $this->query->shouldReceive('where')->with('ip', '127.0.0.1')->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturnSelf();

        $this->assertEqualsWithDelta(10, $this->throttle->ipDelay('127.0.0.1'), 3);
    }

    /** @test */
    public function testUserDelayWithIntegerThreshold()
    {
        $user = m::mock(UserInterface::class);
        $user->shouldReceive('getUserId')->andReturn(1);

        $first = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle');
        $first->shouldReceive('getAttribute')->andReturn(Carbon::createFromTimestamp(time()));

        $this->models->shouldReceive('count')->andReturn(6);
        $this->models->shouldReceive('first')->andReturn($first);

        $this->throttle->setUserInterval(10);
        $this->throttle->setUserThresholds(5);

        $this->query->shouldReceive('get')->andReturn($this->models);
        $this->query->shouldReceive('where')->with('type', 'user')->andReturnSelf();
        $this->query->shouldReceive('where')->with('user_id', 1)->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturnSelf();

        $this->assertEqualsWithDelta(10, $this->throttle->userDelay($user), 3);
    }

    /** @test */
    public function testUserDelayWithArrayThresholds1()
    {
        $user = m::mock(UserInterface::class);
        $user->shouldReceive('getUserId')->andReturn(1);

        $last = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle');
        $last->shouldReceive('getAttribute')->andReturn(Carbon::createFromTimestamp(time()));

        $this->models->shouldReceive('count')->andReturn(6);
        $this->models->shouldReceive('last')->andReturn($last);

        $this->throttle->setUserInterval(10);
        $this->throttle->setUserThresholds([5 => 3, 10 => 10]);

        $this->query->shouldReceive('get')->andReturn($this->models);
        $this->query->shouldReceive('where')->with('type', 'user')->andReturnSelf();
        $this->query->shouldReceive('where')->with('user_id', 1)->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturnSelf();

        $this->assertEqualsWithDelta(3, $this->throttle->userDelay($user), 3);
    }

    /** @test */
    public function testUserDelayWithArrayThresholds2()
    {
        $user = m::mock(UserInterface::class);
        $user->shouldReceive('getUserId')->andReturn(1);

        $last = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle');
        $last->shouldReceive('getAttribute')->andReturn(Carbon::createFromTimestamp(time()));

        $this->models->shouldReceive('count')->andReturn(11);
        $this->models->shouldReceive('last')->andReturn($last);

        $this->throttle->setUserInterval(10);
        $this->throttle->setUserThresholds([5 => 3, 10 => 10]);

        $this->query->shouldReceive('get')->andReturn($this->models);
        $this->query->shouldReceive('where')->with('type', 'user')->andReturnSelf();
        $this->query->shouldReceive('where')->with('user_id', 1)->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturnSelf();

        $this->assertEqualsWithDelta(10, $this->throttle->userDelay($user), 3);
    }

    /** @test */
    public function testDelayHandlesNoThrottle()
    {
        $this->models->shouldReceive('count')->andReturn(0);

        $this->query->shouldReceive('where')->andReturn($this->query);
        $this->query->shouldReceive('get')->andReturn($this->models);

        $this->assertSame($this->throttle->GlobalDelay(), 0);
    }

    /** @test */
    public function testLog()
    {
        $user = m::mock(UserInterface::class);
        $user->shouldReceive('getUserId')->once()->andReturn(1);

        $this->model->shouldReceive('fill');
        $this->model->shouldReceive('save')->times(3);
        $this->model->shouldReceive('setAttribute');

        $this->query->shouldReceive('where')->with('type', 'global')->andReturnSelf();
        $this->query->shouldReceive('where')->with('id', '=', '');

        $this->assertNull( // TODO: Add proper assertion later
            $this->throttle->log('127.0.0.1', $user)
        );
    }
}
