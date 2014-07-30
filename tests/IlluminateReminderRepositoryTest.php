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

use ArrayIterator;
use Carbon\Carbon;
use Cartalyst\Sentinel\Reminders\IlluminateReminderRepository;
use Mockery as m;
use PHPUnit_Framework_TestCase;
use StdClass;

class IlluminateReminderRepositoryTest extends PHPUnit_Framework_TestCase {

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
		$reminders = m::mock('Cartalyst\Sentinel\Reminders\IlluminateReminderRepository[createModel]', [$users = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository')]);

		$reminders->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Reminders\EloquentReminder[newQuery]'));

		$this->addMockConnection($model);

		$model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
		$model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
		$query->shouldReceive('insertGetId')->once();

		$user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');
		$user->shouldReceive('getUserId')->once()->andReturn(1);

		$activation = $reminders->create($user);

		$this->assertInstanceOf('Cartalyst\Sentinel\Reminders\EloquentReminder', $activation);
	}

	public function testExists()
	{
		$reminders = m::mock('Cartalyst\Sentinel\Reminders\IlluminateReminderRepository[createModel]', [$users = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository')]);

		$reminders->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Reminders\EloquentReminder[newQuery]'));

		$model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

		$query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
		$query->shouldReceive('where')->with('completed', false)->andReturn($query);
		$query->shouldReceive('first')->once();

		$user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');
		$user->shouldReceive('getUserId')->once()->andReturn(1);

		$reminders->exists($user);
	}

	public function testCompleteValidReminder()
	{
		$reminders = m::mock('Cartalyst\Sentinel\Reminders\IlluminateReminderRepository[createModel]', [$users = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository')]);

		$users->shouldReceive('validForUpdate')->once()->andReturn(true);
		$users->shouldReceive('update')->once()->andReturn(true);

		$reminders->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Reminders\EloquentReminder[newQuery]'));

		$model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

		$query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
		$query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
		$query->shouldReceive('where')->with('completed', false)->andReturn($query);
		$query->shouldReceive('first')->once()->andReturn($activation = m::mock('Cartalyst\Sentinel\Reminders\EloquentReminder'));

		$activation->shouldReceive('fill')->once();
		$activation->shouldReceive('save')->once();

		$user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');
		$user->shouldReceive('getUserId')->once()->andReturn(1);

		$reminders->complete($user, 'foobar', 'secret');
	}

	public function testCompleteInValidReminder1()
	{
		$reminders = m::mock('Cartalyst\Sentinel\Reminders\IlluminateReminderRepository[createModel]', [$users = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository')]);

		$users->shouldReceive('validForUpdate')->once()->andReturn(false);

		$reminders->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Reminders\EloquentReminder[newQuery]'));

		$model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

		$query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
		$query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
		$query->shouldReceive('where')->with('completed', false)->andReturn($query);
		$query->shouldReceive('first')->once()->andReturn($activation = m::mock('Cartalyst\Sentinel\Reminders\EloquentReminder'));

		$user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');
		$user->shouldReceive('getUserId')->once()->andReturn(1);

		$reminders->complete($user, 'foobar', 'secret');
	}

	public function testCompleteInValidReminder2()
	{
		$reminders = m::mock('Cartalyst\Sentinel\Reminders\IlluminateReminderRepository[createModel]', [$users = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository')]);

		$reminders->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Reminders\EloquentReminder[newQuery]'));

		$model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

		$query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
		$query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
		$query->shouldReceive('where')->with('completed', false)->andReturn($query);
		$query->shouldReceive('first')->once();

		$user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');
		$user->shouldReceive('getUserId')->once()->andReturn(1);

		$reminders->complete($user, 'foobar', 'secret');
	}

	public function testRemoveExpired()
	{
		$reminders = m::mock('Cartalyst\Sentinel\Reminders\IlluminateReminderRepository[createModel]', [$users = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository')]);

		$reminders->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Reminders\EloquentReminder[newQuery]'));

		$model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

		$query->shouldReceive('where')->with('completed', false)->andReturn($query);
		$query->shouldReceive('where')->andReturn($query);
		$query->shouldReceive('delete')->once();

		$reminders->removeExpired();
	}

	protected function addMockConnection($model)
	{
		$model->setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));
		$resolver->shouldReceive('connection')->andReturn(m::mock('Illuminate\Database\Connection'));
		$model->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock('Illuminate\Database\Query\Grammars\Grammar'));
		$model->getConnection()->shouldReceive('getPostProcessor')->andReturn(m::mock('Illuminate\Database\Query\Processors\Processor'));
	}

}
