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

namespace Cartalyst\Sentinel\Tests\Reminders;

use Mockery as m;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Cartalyst\Sentinel\Reminders\EloquentReminder;
use Cartalyst\Sentinel\Users\IlluminateUserRepository;
use Cartalyst\Sentinel\Reminders\IlluminateReminderRepository;

class IlluminateReminderRepositoryTest extends TestCase
{
    /**
     * The User Repository instance.
     *
     * @var \Cartalyst\Sentinel\Users\IlluminateUserRepository
     */
    protected $users;

    /**
     * The Eloquent Builder instance.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * The Eloquent Reminder instance.
     *
     * @var \Cartalyst\Sentinel\Reminders\EloquentReminder
     */
    protected $model;

    /**
     * The Reminder Repository instance.
     *
     * @var \Cartalyst\Sentinel\Reminders\IlluminateReminderRepository
     */
    protected $reminders;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->users = m::mock(IlluminateUserRepository::class);

        $this->query = m::mock(Builder::class);

        $this->model = m::mock(EloquentReminder::class);
        $this->model->shouldReceive('newQuery')->andReturn($this->query);

        $this->reminders = m::mock('Cartalyst\Sentinel\Reminders\IlluminateReminderRepository[createModel]', [$this->users]);
        $this->reminders->shouldReceive('createModel')->andReturn($this->model);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->users     = null;
        $this->query     = null;
        $this->model     = null;
        $this->reminders = null;

        m::close();
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $reminders = new IlluminateReminderRepository($this->users, 'ReminderModelMock', 259200);

        $this->assertSame('ReminderModelMock', $reminders->getModel());
    }

    /** @test */
    public function it_can_create_a_reminder_code()
    {
        $this->model->shouldReceive('fill');
        $this->model->shouldReceive('setAttribute');
        $this->model->shouldReceive('save')->once();

        $user = $this->getUserMock();

        $reminder = $this->reminders->create($user);

        $this->assertInstanceOf(EloquentReminder::class, $reminder);
    }

    /** @test */
    public function it_can_determine_if_a_reminder_exists()
    {
        $this->query->shouldReceive('where')->with('user_id', '1')->andReturnSelf();
        $this->query->shouldReceive('where')->with('completed', false)->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::type(Carbon::class))->andReturnSelf();
        $this->query->shouldReceive('first')->once();

        $user = $this->getUserMock();

        $status = $this->reminders->exists($user);

        $this->assertFalse($status);
    }

    /** @test */
    public function it_can_determine_if_a_reminder_exists_with_a_code()
    {
        $this->query->shouldReceive('where')->with('user_id', '1')->andReturnSelf();
        $this->query->shouldReceive('where')->with('completed', false)->andReturnSelf();
        $this->query->shouldReceive('where')->with('code', 'foobar')->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::type(Carbon::class))->andReturnSelf();
        $this->query->shouldReceive('first')->once();

        $user = $this->getUserMock();

        $status = $this->reminders->exists($user, 'foobar');

        $this->assertFalse($status);
    }

    /** @test */
    public function it_can_complete_a_reminder()
    {
        $user = $this->getUserMock();

        $this->model->shouldReceive('fill')->once();
        $this->model->shouldReceive('save')->once();

        $this->users->shouldReceive('validForUpdate')->once()->andReturn(true);
        $this->users->shouldReceive('update')->once()->andReturn($user);

        $this->query->shouldReceive('where')->with('user_id', '1')->andReturnSelf();
        $this->query->shouldReceive('where')->with('code', 'foobar')->andReturnSelf();
        $this->query->shouldReceive('where')->with('completed', false)->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::type(Carbon::class))->andReturnSelf();
        $this->query->shouldReceive('first')->once()->andReturn($this->model);

        $status = $this->reminders->complete($user, 'foobar', 'secret');

        $this->assertTrue($status);
    }

    /** @test */
    public function it_cannot_complete_a_reminder_that_does_not_exist()
    {
        $this->query->shouldReceive('where')->with('user_id', '1')->andReturnSelf();
        $this->query->shouldReceive('where')->with('code', 'foobar')->andReturnSelf();
        $this->query->shouldReceive('where')->with('completed', false)->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::type(Carbon::class))->andReturnSelf();
        $this->query->shouldReceive('first')->once()->andReturn(null);

        $user = $this->getUserMock();

        $status = $this->reminders->complete($user, 'foobar', 'secret');

        $this->assertFalse($status);
    }

    /** @test */
    public function it_cannot_complete_a_reminder_that_has_expired()
    {
        $this->users->shouldReceive('validForUpdate')->once()->andReturn(false);

        $this->query->shouldReceive('where')->with('user_id', '1')->andReturnSelf();
        $this->query->shouldReceive('where')->with('code', 'foobar')->andReturnSelf();
        $this->query->shouldReceive('where')->with('completed', false)->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '>', m::type(Carbon::class))->andReturnSelf();
        $this->query->shouldReceive('first')->once()->andReturn($this->model);

        $user = $this->getUserMock();

        $status = $this->reminders->complete($user, 'foobar', 'secret');

        $this->assertFalse($status);
    }

    /** @test */
    public function it_can_remove_expired_reminders()
    {
        $this->query->shouldReceive('where')->with('completed', false)->andReturnSelf();
        $this->query->shouldReceive('where')->with('created_at', '<', m::type(Carbon::class))->andReturnSelf();

        $this->query->shouldReceive('delete')->once()->andReturn(true);

        $status = $this->reminders->removeExpired();

        $this->assertTrue($status);
    }

    protected function getUserMock()
    {
        $user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');

        $user->shouldReceive('getUserId')->once()->andReturn(1);

        return $user;
    }
}
