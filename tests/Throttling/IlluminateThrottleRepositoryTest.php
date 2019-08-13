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
 * @version    2.0.17
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2017, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Tests\Throttling;

use Mockery as m;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Cartalyst\Sentinel\Users\UserInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Cartalyst\Sentinel\Throttling\EloquentThrottle;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\ConnectionResolverInterface;
use Cartalyst\Sentinel\Users\IlluminateUserRepository;

class IlluminateThrottleRepositoryTest extends TestCase
{
    protected $query;

    protected $model;

    protected $users;

    protected $throttle;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->query = m::mock(Builder::class);

        $this->model = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle[newQuery]');
        $this->model->shouldReceive('newQuery')->andReturn($this->query);

        $this->users = m::mock(IlluminateUserRepository::class);

        $this->throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]', [$this->users]);
        $this->throttle->shouldReceive('createModel')->andReturn($this->model);

        $connection = m::mock(Connection::class)->makePartial();
        $connection->shouldReceive('getQueryGrammar')->andReturn(m::mock(Grammar::class));
        $connection->shouldReceive('getPostProcessor')->andReturn(m::mock(Processor::class));

        $connectionResolver = m::mock(ConnectionResolverInterface::class);
        $connectionResolver->shouldReceive('connection')->andReturn($connection);

        $this->model->setConnectionResolver($connectionResolver);
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
        $first = new EloquentThrottle();
        $first->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $first->created_at = Carbon::createFromTimestamp(time());

        $models = m::mock(Collection::class);
        $models->shouldReceive('count')->andReturn(6);
        $models->shouldReceive('first')->andReturn($first);

        $this->throttle->setGlobalInterval(10);
        $this->throttle->setGlobalThresholds(5);

        $this->query->shouldReceive('get')->andReturn($models);
        $this->query->shouldReceive('where')->with('type', 'global')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturn($this->query);

        $this->assertEqualsWithDelta(10, $this->throttle->globalDelay(), 3);
    }

    /** @test */
    public function testGlobalDelayWithIntegerThreshold2()
    {
        $first = new EloquentThrottle();
        $first->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $first->created_at = Carbon::createFromTimestamp(time());

        $models = m::mock(Collection::class);
        $models->shouldReceive('count')->andReturn(6);
        $models->shouldReceive('first')->andReturn($first);

        $this->throttle->setGlobalInterval(10);
        $this->throttle->setGlobalThresholds(200);

        $this->query->shouldReceive('get')->andReturn($models);
        $this->query->shouldReceive('where')->with('type', 'global')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturn($this->query);

        $this->assertEqualsWithDelta(0, $this->throttle->globalDelay(), 3);
    }

    /** @test */
    public function testGlobalDelayWithArrayThresholds1()
    {
        $last = new EloquentThrottle();
        $last->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $last->created_at = Carbon::createFromTimestamp(time());

        $models = m::mock(Collection::class);
        $models->shouldReceive('count')->andReturn(6);
        $models->shouldReceive('last')->andReturn($last);

        $this->throttle->setGlobalInterval(10);
        $this->throttle->setGlobalThresholds([5 => 3, 10 => 10]);

        $this->query->shouldReceive('get')->andReturn($models);
        $this->query->shouldReceive('where')->with('type', 'global')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturn($this->query);

        $this->assertEqualsWithDelta(3, $this->throttle->globalDelay(), 3);
    }

    /** @test */
    public function testGlobalDelayWithArrayThresholds2()
    {
        $last = new EloquentThrottle();
        $last->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $last->created_at = Carbon::createFromTimestamp(time());

        $models = m::mock(Collection::class);
        $models->shouldReceive('count')->andReturn(11);
        $models->shouldReceive('last')->andReturn($last);

        $this->throttle->setGlobalInterval(10);
        $this->throttle->setGlobalThresholds([5 => 3, 10 => 10]);

        $this->query->shouldReceive('get')->andReturn($models);
        $this->query->shouldReceive('where')->with('type', 'global')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturn($this->query);

        $this->assertEqualsWithDelta(10, $this->throttle->globalDelay(), 3);
    }

    /** @test */
    public function testGlobalDelayWithArrayThresholds3()
    {
        $last = new EloquentThrottle();
        $last->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $last->created_at = Carbon::createFromTimestamp(time() - 200);

        $models = m::mock(Collection::class);
        $models->shouldReceive('count')->andReturn(11);
        $models->shouldReceive('last')->andReturn($last);

        $this->throttle->setGlobalInterval(10);
        $this->throttle->setGlobalThresholds([5 => 33, 10 => 100]);

        $this->query->shouldReceive('get')->andReturn($models);
        $this->query->shouldReceive('where')->with('type', 'global')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturn($this->query);

        $this->assertEqualsWithDelta(0, $this->throttle->globalDelay(), 3);
    }

    /** @test */
    public function testIpDelayWithIntegerThreshold()
    {
        $first = new EloquentThrottle();
        $first->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $first->created_at = Carbon::createFromTimestamp(time());

        $models = m::mock(Collection::class);
        $models->shouldReceive('count')->andReturn(6);
        $models->shouldReceive('first')->andReturn($first);

        $this->throttle->setIpInterval(10);
        $this->throttle->setIpThresholds(5);

        $this->query->shouldReceive('get')->andReturn($models);
        $this->query->shouldReceive('where')->with('type', 'ip')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('ip', '127.0.0.1')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturn($this->query);

        $this->assertEqualsWithDelta(10, $this->throttle->ipDelay('127.0.0.1'), 3);
    }

    /** @test */
    public function testIpDelayWithArrayThresholds1()
    {
        $last = new EloquentThrottle();
        $last->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $last->created_at = Carbon::createFromTimestamp(time());

        $models = m::mock(Collection::class);
        $models->shouldReceive('count')->andReturn(6);
        $models->shouldReceive('last')->andReturn($last);

        $this->throttle->setIpInterval(10);
        $this->throttle->setIpThresholds([5 => 3, 10 => 10]);

        $this->query->shouldReceive('get')->andReturn($models);
        $this->query->shouldReceive('where')->with('type', 'ip')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('ip', '127.0.0.1')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturn($this->query);

        $this->assertEqualsWithDelta(3, $this->throttle->ipDelay('127.0.0.1'), 3);
    }

    /** @test */
    public function testIpDelayWithArrayThresholds2()
    {
        $last = new EloquentThrottle();
        $last->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $last->created_at = Carbon::createFromTimestamp(time());

        $models = m::mock(Collection::class);
        $models->shouldReceive('count')->andReturn(11);
        $models->shouldReceive('last')->andReturn($last);

        $this->throttle->setIpInterval(10);
        $this->throttle->setIpThresholds([5 => 3, 10 => 10]);

        $this->query->shouldReceive('get')->andReturn($models);
        $this->query->shouldReceive('where')->with('type', 'ip')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('ip', '127.0.0.1')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturn($this->query);

        $this->assertEqualsWithDelta(10, $this->throttle->ipDelay('127.0.0.1'), 3);
    }

    /** @test */
    public function testUserDelayWithIntegerThreshold()
    {
        $user = m::mock(UserInterface::class);
        $user->shouldReceive('getUserId')->andReturn(1);

        $first = new EloquentThrottle();
        $first->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $first->created_at = Carbon::createFromTimestamp(time());

        $models = m::mock(Collection::class);
        $models->shouldReceive('count')->andReturn(6);
        $models->shouldReceive('first')->andReturn($first);

        $this->throttle->setUserInterval(10);
        $this->throttle->setUserThresholds(5);

        $this->query->shouldReceive('get')->andReturn($models);
        $this->query->shouldReceive('where')->with('type', 'user')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('user_id', 1)->andReturn($this->query);
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturn($this->query);

        $this->assertEqualsWithDelta(10, $this->throttle->userDelay($user), 3);
    }

    /** @test */
    public function testUserDelayWithArrayThresholds1()
    {
        $user = m::mock(UserInterface::class);
        $user->shouldReceive('getUserId')->andReturn(1);

        $last = new EloquentThrottle();
        $last->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $last->created_at = Carbon::createFromTimestamp(time());

        $models = m::mock(Collection::class);
        $models->shouldReceive('count')->andReturn(6);
        $models->shouldReceive('last')->andReturn($last);

        $this->throttle->setUserInterval(10);
        $this->throttle->setUserThresholds([5 => 3, 10 => 10]);

        $this->query->shouldReceive('get')->andReturn($models);
        $this->query->shouldReceive('where')->with('type', 'user')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('user_id', 1)->andReturn($this->query);
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturn($this->query);

        $this->assertEqualsWithDelta(3, $this->throttle->userDelay($user), 3);
    }

    /** @test */
    public function testUserDelayWithArrayThresholds2()
    {
        $user = m::mock(UserInterface::class);
        $user->shouldReceive('getUserId')->andReturn(1);

        $last = new EloquentThrottle();
        $last->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $last->created_at = Carbon::createFromTimestamp(time());

        $models = m::mock(Collection::class);
        $models->shouldReceive('count')->andReturn(11);
        $models->shouldReceive('last')->andReturn($last);

        $this->throttle->setUserInterval(10);
        $this->throttle->setUserThresholds([5 => 3, 10 => 10]);

        $this->query->shouldReceive('get')->andReturn($models);
        $this->query->shouldReceive('where')->with('type', 'user')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('user_id', 1)->andReturn($this->query);
        $this->query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEqualsWithDelta(time() - 10, $interval->getTimestamp(), 3);

            return true;
        }))->andReturn($this->query);

        $this->assertEqualsWithDelta(10, $this->throttle->userDelay($user), 3);
    }

    /** @test */
    public function testLog()
    {
        $user = m::mock(UserInterface::class);
        $user->shouldReceive('getUserId')->once()->andReturn(1);

        $this->query->shouldReceive('where')->with('type', 'global')->andReturn($this->query);
        $this->query->shouldReceive('where')->with('id', '=', '');

        $this->model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $this->model->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $this->model->getConnection()->getQueryGrammar()->shouldReceive('compileUpdate')->twice();
        $this->model->getConnection()->getQueryGrammar()->shouldReceive('prepareBindingsForUpdate')->andReturn([]);
        $this->model->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId')->once();
        $this->model->getConnection()->shouldReceive('update')->twice();

        $this->throttle->log('127.0.0.1', $user);

        $this->assertTrue(true); // TODO: Add proper assertion later
    }
}
