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
 * @version    2.0.18
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2019, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\tests;

use Cartalyst\Sentinel\Reminders\IlluminateReminderRepository;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class IlluminateReminderRepositoryTest extends PHPUnit_Framework_TestCase
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

    public function testConstructorModel()
    {
        $users = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository');

        $reminders = new IlluminateReminderRepository($users, 'ActivationMock', 259200);

        $this->assertEquals('ActivationMock', $reminders->getModel());
    }

    public function testCreate()
    {
        list($reminders, $users, $model, $query) = $this->getReminderMock();

        $model->shouldReceive('fill')->once();
        $model->shouldReceive('setAttribute')->once();
        $model->shouldReceive('save')->once();

        $user = $this->getUserMock();

        $activation = $reminders->create($user);

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Reminders\EloquentReminder',
            $activation
        );
    }

    public function testExists()
    {
        list($reminders, $users, $model, $query) = $this->getReminderMock();

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('completed', false)->andReturn($query);

        $this->shouldReceiveExpires($query);

        $query->shouldReceive('first')->once();

        $user = $this->getUserMock();

        $reminders->exists($user);
    }

    public function testCompleteValidReminder()
    {
        list($reminders, $users, $model, $query) = $this->getReminderMock();

        $users->shouldReceive('validForUpdate')->once()->andReturn(true);
        $users->shouldReceive('update')->once()->andReturn(true);

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
        $query->shouldReceive('where')->with('completed', false)->andReturn($query);

        $this->shouldReceiveExpires($query);

        $query->shouldReceive('first')->once()->andReturn($activation = m::mock('Cartalyst\Sentinel\Reminders\EloquentReminder'));

        $activation->shouldReceive('fill')->once();
        $activation->shouldReceive('save')->once();

        $user = $this->getUserMock();

        $reminders->complete($user, 'foobar', 'secret');
    }

    public function testCompleteInValidReminder1()
    {
        list($reminders, $users, $model, $query) = $this->getReminderMock();

        $users->shouldReceive('validForUpdate')->once()->andReturn(false);

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
        $query->shouldReceive('where')->with('completed', false)->andReturn($query);

        $this->shouldReceiveExpires($query);

        $query->shouldReceive('first')->once()->andReturn($activation = m::mock('Cartalyst\Sentinel\Reminders\EloquentReminder'));

        $user = $this->getUserMock();

        $reminders->complete($user, 'foobar', 'secret');
    }

    public function testCompleteInValidReminder2()
    {
        list($reminders, $users, $model, $query) = $this->getReminderMock();

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
        $query->shouldReceive('where')->with('completed', false)->andReturn($query);

        $this->shouldReceiveExpires($query);

        $query->shouldReceive('first')->once();

        $user = $this->getUserMock();

        $reminders->complete($user, 'foobar', 'secret');
    }

    public function testRemoveExpired()
    {
        list($reminders, $users, $model, $query) = $this->getReminderMock();

        $query->shouldReceive('where')->with('completed', false)->andReturn($query);

        $this->shouldReceiveExpires($query, '<');

        $query->shouldReceive('delete')->once();

        $reminders->removeExpired();
    }

    protected function getReminderMock()
    {
        $model = m::mock('Cartalyst\Sentinel\Reminders\EloquentReminder');
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $users     = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository');
        $reminders = m::mock(
            'Cartalyst\Sentinel\Reminders\IlluminateReminderRepository[createModel]',
            [$users]
        );

        $reminders->shouldReceive('createModel')->andReturn($model);
        $model->shouldReceive('newQuery')->andReturn($query);

        return [$reminders, $users, $model, $query];
    }

    protected function getUserMock()
    {
        $user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');

        $user->shouldReceive('getUserId')->once()->andReturn(1);

        return $user;
    }

    protected function shouldReceiveExpires($query, $operator = '>')
    {
        $query->shouldReceive('where')->with('created_at', $operator, m::on(function ($timestamp) {
            $expires = 259200;
            $this->assertEquals(time() - $expires, $timestamp->getTimestamp(), '', 3);
            return true;
        }))->andReturn($query);
    }

}
