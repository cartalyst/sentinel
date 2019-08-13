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

namespace Cartalyst\Sentinel\Tests\Reminders;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Grammars\Grammar;
use Cartalyst\Sentinel\Reminders\EloquentReminder;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\ConnectionResolverInterface;
use Cartalyst\Sentinel\Users\IlluminateUserRepository;
use Cartalyst\Sentinel\Reminders\IlluminateReminderRepository;

class IlluminateReminderRepositoryTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $users = m::mock(IlluminateUserRepository::class);

        $reminders = new IlluminateReminderRepository($users, 'ReminderModelMock', 259200);

        $this->assertSame('ReminderModelMock', $reminders->getModel());
    }

    /** @test */
    public function it_can_create_a_reminder_code()
    {
        list($reminders, $users, $model, $query) = $this->getReminderMock();

        $this->addMockConnection($model);

        $model->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $model->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId')->once();

        $user = $this->getUserMock();

        $reminder = $reminders->create($user);

        $this->assertInstanceOf(EloquentReminder::class, $reminder);
    }

    /** @test */
    public function it_can_determine_if_a_reminder_exists()
    {
        list($reminders, $users, $model, $query) = $this->getReminderMock();

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('completed', false)->andReturn($query);
        $query->shouldReceive('first')->once();

        $this->shouldReceiveExpires($query);

        $user = $this->getUserMock();

        $status = $reminders->exists($user);

        $this->assertFalse($status);
    }

    /** @test */
    public function it_can_complete_a_reminder()
    {
        list($reminders, $users, $model, $query) = $this->getReminderMock();

        $reminder = m::mock(EloquentReminder::class);
        $reminder->shouldReceive('fill')->once();
        $reminder->shouldReceive('save')->once();

        $users->shouldReceive('validForUpdate')->once()->andReturn(true);
        $users->shouldReceive('update')->once()->andReturn(true);

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
        $query->shouldReceive('where')->with('completed', false)->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn($reminder);

        $this->shouldReceiveExpires($query);

        $user = $this->getUserMock();

        $status = $reminders->complete($user, 'foobar', 'secret');

        $this->assertTrue($status);
    }

    /** @test */
    public function it_cannot_complete_a_reminder_that_has_expired()
    {
        list($reminders, $users, $model, $query) = $this->getReminderMock();

        $reminder = m::mock(EloquentReminder::class);

        $users->shouldReceive('validForUpdate')->once()->andReturn(false);

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
        $query->shouldReceive('where')->with('completed', false)->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn($reminder);

        $this->shouldReceiveExpires($query);

        $user = $this->getUserMock();

        $status = $reminders->complete($user, 'foobar', 'secret');

        $this->assertFalse($status);
    }

    /** @test */
    public function it_can_remove_expired_reminders()
    {
        list($reminders, $users, $model, $query) = $this->getReminderMock();

        $query->shouldReceive('where')->with('completed', false)->andReturn($query);

        $this->shouldReceiveExpires($query, '<');

        $query->shouldReceive('delete')->once()->andReturn(true);

        $status = $reminders->removeExpired();

        $this->assertTrue($status);
    }

    protected function getReminderMock()
    {
        $users = m::mock(IlluminateUserRepository::class);

        $query = m::mock('Illuminate\Database\Eloquent\Builder');

        $model = m::mock('Cartalyst\Sentinel\Reminders\EloquentReminder[newQuery]');
        $model->shouldReceive('newQuery')->andReturn($query);

        $reminders = m::mock('Cartalyst\Sentinel\Reminders\IlluminateReminderRepository[createModel]', [$users]);
        $reminders->shouldReceive('createModel')->andReturn($model);

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
        $query->shouldReceive('where')->with('created_at', $operator, m::on(function () {
            return true;
        }))->andReturn($query);
    }

    protected function addMockConnection($model)
    {
        $resolver = m::mock(ConnectionResolverInterface::class);
        $resolver->shouldReceive('connection')->andReturn(m::mock(Connection::class)->makePartial());

        $model->setConnectionResolver($resolver);
        $model->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock(Grammar::class));
        $model->getConnection()->shouldReceive('getPostProcessor')->andReturn(m::mock(Processor::class));
    }
}
