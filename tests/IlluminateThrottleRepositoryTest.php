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
use Cartalyst\Sentinel\Throttling\EloquentThrottle;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class IlluminateThrottleRepositoryTest extends PHPUnit_Framework_TestCase
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

    public function testConstructor()
    {
        $throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]', [
            'Cartalyst\Sentinel\Throttling\EloquentThrottle', 1, 2, 3, 4, 5, 6,
        ]);

        $this->assertEquals(1, $throttle->getGlobalInterval());
        $this->assertEquals(2, $throttle->getGlobalThresholds());
        $this->assertEquals(3, $throttle->getIpInterval());
        $this->assertEquals(4, $throttle->getIpThresholds());
        $this->assertEquals(5, $throttle->getUserInterval());
        $this->assertEquals(6, $throttle->getUserThresholds());
    }

    public function testGlobalDelayWithIntegerThreshold1()
    {
        $startTime = time();

        $throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]');
        $throttle->setGlobalInterval(10);
        $throttle->setGlobalThresholds(5);

        $throttle->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle[newQuery]'));
        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('where')->with('type', 'global')->andReturn($query);

        $query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEquals(time() - 10, $interval->getTimestamp(), '', 3);
            return true;
        }))->andReturn($query);
        $query->shouldReceive('get')->andReturn($models = m::mock('Illuminate\Database\Eloquent\Collection'));

        $models->shouldReceive('count')->andReturn(6);
        $models->shouldReceive('first')->andReturn($first = new EloquentThrottle);

        $first->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $first->created_at = Carbon::createFromTimestamp(time());

        $this->assertEquals(10, $throttle->globalDelay(), '', 3);
    }

    public function testGlobalDelayWithIntegerThreshold2()
    {
        $throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]');
        $throttle->setGlobalInterval(10);
        $throttle->setGlobalThresholds(200);

        $throttle->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle[newQuery]'));
        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('where')->with('type', 'global')->andReturn($query);

        $query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEquals(time() - 10, $interval->getTimestamp(), '', 3);
            return true;
        }))->andReturn($query);
        $query->shouldReceive('get')->andReturn($models = m::mock('Illuminate\Database\Eloquent\Collection'));

        $models->shouldReceive('count')->andReturn(6);
        $models->shouldReceive('first')->andReturn($first = new EloquentThrottle);

        $first->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $first->created_at = Carbon::createFromTimestamp(time());

        $this->assertEquals(0, $throttle->globalDelay(), '', 3);
    }

    public function testGlobalDelayWithArrayThresholds1()
    {
        $throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]');
        $throttle->setGlobalInterval(10);
        $throttle->setGlobalThresholds([5 => 3, 10 => 10]);

        $throttle->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle[newQuery]'));
        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('where')->with('type', 'global')->andReturn($query);

        $query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEquals(time() - 10, $interval->getTimestamp(), '', 3);
            return true;
        }))->andReturn($query);
        $query->shouldReceive('get')->andReturn($models = m::mock('Illuminate\Database\Eloquent\Collection'));

        $models->shouldReceive('count')->andReturn(6);
        $models->shouldReceive('last')->andReturn($last = new EloquentThrottle);

        $last->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $last->created_at = Carbon::createFromTimestamp(time());

        $this->assertEquals(3, $throttle->globalDelay(), '', 3);
    }

    public function testGlobalDelayWithArrayThresholds2()
    {
        $throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]');
        $throttle->setGlobalInterval(10);
        $throttle->setGlobalThresholds([5 => 3, 10 => 10]);

        $throttle->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle[newQuery]'));
        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('where')->with('type', 'global')->andReturn($query);

        $query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEquals(time() - 10, $interval->getTimestamp(), '', 3);
            return true;
        }))->andReturn($query);
        $query->shouldReceive('get')->andReturn($models = m::mock('Illuminate\Database\Eloquent\Collection'));

        $models->shouldReceive('count')->andReturn(11);
        $models->shouldReceive('last')->andReturn($last = new EloquentThrottle);

        $last->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $last->created_at = Carbon::createFromTimestamp(time());

        $this->assertEquals(10, $throttle->globalDelay(), '', 3);
    }

    public function testGlobalDelayWithArrayThresholds3()
    {
        $throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]');
        $throttle->setGlobalInterval(10);
        $throttle->setGlobalThresholds([5 => 33, 10 => 100]);

        $throttle->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle[newQuery]'));
        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('where')->with('type', 'global')->andReturn($query);

        $query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEquals(time() - 10, $interval->getTimestamp(), '', 3);
            return true;
        }))->andReturn($query);
        $query->shouldReceive('get')->andReturn($models = m::mock('Illuminate\Database\Eloquent\Collection'));

        $models->shouldReceive('count')->andReturn(11);
        $models->shouldReceive('last')->andReturn($last = new EloquentThrottle);

        $last->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $last->created_at = Carbon::createFromTimestamp(time() - 200);

        $this->assertEquals(0, $throttle->globalDelay(), '', 3);
    }

    public function testIpDelayWithIntegerThreshold()
    {
        $throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]');
        $throttle->setIpInterval(10);
        $throttle->setIpThresholds(5);

        $throttle->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle[newQuery]'));
        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('where')->with('type', 'ip')->andReturn($query);
        $query->shouldReceive('where')->with('ip', '127.0.0.1')->andReturn($query);

        $query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEquals(time() - 10, $interval->getTimestamp(), '', 3);
            return true;
        }))->andReturn($query);
        $query->shouldReceive('get')->andReturn($models = m::mock('Illuminate\Database\Eloquent\Collection'));

        $models->shouldReceive('count')->andReturn(6);
        $models->shouldReceive('first')->andReturn($first = new EloquentThrottle);

        $first->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $first->created_at = Carbon::createFromTimestamp(time());

        $this->assertEquals(10, $throttle->ipDelay('127.0.0.1'), '', 3);
    }

    public function testIpDelayWithArrayThresholds1()
    {
        $throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]');
        $throttle->setIpInterval(10);
        $throttle->setIpThresholds([5 => 3, 10 => 10]);

        $throttle->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle[newQuery]'));
        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('where')->with('type', 'ip')->andReturn($query);
        $query->shouldReceive('where')->with('ip', '127.0.0.1')->andReturn($query);

        $query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEquals(time() - 10, $interval->getTimestamp(), '', 3);
            return true;
        }))->andReturn($query);
        $query->shouldReceive('get')->andReturn($models = m::mock('Illuminate\Database\Eloquent\Collection'));

        $models->shouldReceive('count')->andReturn(6);
        $models->shouldReceive('last')->andReturn($last = new EloquentThrottle);

        $last->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $last->created_at = Carbon::createFromTimestamp(time());

        $this->assertEquals(3, $throttle->ipDelay('127.0.0.1'), '', 3);
    }

    public function testIpDelayWithArrayThresholds2()
    {
        $throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]');
        $throttle->setIpInterval(10);
        $throttle->setIpThresholds([5 => 3, 10 => 10]);

        $throttle->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle[newQuery]'));
        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('where')->with('type', 'ip')->andReturn($query);
        $query->shouldReceive('where')->with('ip', '127.0.0.1')->andReturn($query);

        $query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEquals(time() - 10, $interval->getTimestamp(), '', 3);
            return true;
        }))->andReturn($query);
        $query->shouldReceive('get')->andReturn($models = m::mock('Illuminate\Database\Eloquent\Collection'));

        $models->shouldReceive('count')->andReturn(11);
        $models->shouldReceive('last')->andReturn($last = new EloquentThrottle);

        $last->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $last->created_at = Carbon::createFromTimestamp(time());

        $this->assertEquals(10, $throttle->ipDelay('127.0.0.1'), '', 3);
    }

    public function testUserDelayWithIntegerThreshold()
    {
        $throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]');
        $throttle->setUserInterval(10);
        $throttle->setUserThresholds(5);

        $throttle->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle[newQuery]'));
        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('where')->with('type', 'user')->andReturn($query);
        $query->shouldReceive('where')->with('user_id', 1)->andReturn($query);

        $query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEquals(time() - 10, $interval->getTimestamp(), '', 3);
            return true;
        }))->andReturn($query);
        $query->shouldReceive('get')->andReturn($models = m::mock('Illuminate\Database\Eloquent\Collection'));

        $models->shouldReceive('count')->andReturn(6);
        $models->shouldReceive('first')->andReturn($first = new EloquentThrottle);

        $first->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $first->created_at = Carbon::createFromTimestamp(time());

        $user = m::mock('Cartalyst\Sentinel\Users\UserInterface');
        $user->shouldReceive('getUserId')->andReturn(1);

        $this->assertEquals(10, $throttle->userDelay($user), '', 3);
    }

    public function testUserDelayWithArrayThresholds1()
    {
        $throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]');
        $throttle->setUserInterval(10);
        $throttle->setUserThresholds([5 => 3, 10 => 10]);

        $throttle->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle[newQuery]'));
        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('where')->with('type', 'user')->andReturn($query);
        $query->shouldReceive('where')->with('user_id', 1)->andReturn($query);

        $query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEquals(time() - 10, $interval->getTimestamp(), '', 3);
            return true;
        }))->andReturn($query);
        $query->shouldReceive('get')->andReturn($models = m::mock('Illuminate\Database\Eloquent\Collection'));

        $models->shouldReceive('count')->andReturn(6);
        $models->shouldReceive('last')->andReturn($last = new EloquentThrottle);

        $last->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $last->created_at = Carbon::createFromTimestamp(time());

        $user = m::mock('Cartalyst\Sentinel\Users\UserInterface');
        $user->shouldReceive('getUserId')->andReturn(1);

        $this->assertEquals(3, $throttle->userDelay($user), '', 3);
    }

    public function testUserDelayWithArrayThresholds2()
    {
        $throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]');
        $throttle->setUserInterval(10);
        $throttle->setUserThresholds([5 => 3, 10 => 10]);

        $throttle->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle[newQuery]'));
        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('where')->with('type', 'user')->andReturn($query);
        $query->shouldReceive('where')->with('user_id', 1)->andReturn($query);

        $query->shouldReceive('where')->with('created_at', '>', m::on(function ($interval) {
            $this->assertEquals(time() - 10, $interval->getTimestamp(), '', 3);
            return true;
        }))->andReturn($query);
        $query->shouldReceive('get')->andReturn($models = m::mock('Illuminate\Database\Eloquent\Collection'));

        $models->shouldReceive('count')->andReturn(11);
        $models->shouldReceive('last')->andReturn($last = new EloquentThrottle);

        $last->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $last->created_at = Carbon::createFromTimestamp(time());

        $user = m::mock('Cartalyst\Sentinel\Users\UserInterface');
        $user->shouldReceive('getUserId')->andReturn(1);

        $this->assertEquals(10, $throttle->userDelay($user), '', 3);
    }

    public function testLog()
    {
        $throttle = m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository[createModel]');

        $throttle->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Throttling\EloquentThrottle[newQuery]'));

        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

        $query->shouldReceive('where')->with('type', 'global')->andReturn($query);
        $query->shouldReceive('where')->with('id', '=', '');

        $model->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId')->once();
        $model->getConnection()->getQueryGrammar()->shouldReceive('compileUpdate')->once();
        $model->getConnection()->getQueryGrammar()->shouldReceive('prepareBindingsForUpdate')->once()->andReturn([]);
        $model->getConnection()->shouldReceive('update')->once();

        $user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');
        $user->shouldReceive('getUserId')->once()->andReturn(1);

        $throttle->log('127.0.0.1', $user);
    }
}
