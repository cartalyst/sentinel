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
use Cartalyst\Sentinel\Activations\IlluminateActivationRepository;
use Mockery as m;
use PHPUnit_Framework_TestCase;
use StdClass;

class IlluminateActivationRepositoryTest extends PHPUnit_Framework_TestCase {

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
		$activations = new IlluminateActivationRepository('ActivationMock', 259200);

		$this->assertEquals('ActivationMock', $activations->getModel());
	}

	public function testCreate()
	{
		$activations = m::mock('Cartalyst\Sentinel\Activations\IlluminateActivationRepository[createModel]');

		$activations->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Activations\EloquentActivation[newQuery]'));

		$this->addMockConnection($model);

		$model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
		$model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
		$query->shouldReceive('insertGetId')->once();

		$user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');
		$user->shouldReceive('getUserId')->once()->andReturn(1);

		$activation = $activations->create($user);

		$this->assertInstanceOf('Cartalyst\Sentinel\Activations\EloquentActivation', $activation);
	}

	public function testExists()
	{
		$activations = m::mock('Cartalyst\Sentinel\Activations\IlluminateActivationRepository[createModel]');

		$activations->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Activations\EloquentActivation[newQuery]'));

		$model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

		$query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
		$query->shouldReceive('where')->with('completed', false)->andReturn($query);
		$query->shouldReceive('first')->once();

		$user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');
		$user->shouldReceive('getUserId')->once()->andReturn(1);

		$activations->exists($user);
	}

	public function testCompleteValidActivation()
	{
		$activations = m::mock('Cartalyst\Sentinel\Activations\IlluminateActivationRepository[createModel]');

		$activations->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Activations\EloquentActivation[newQuery]'));

		$model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

		$query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
		$query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
		$query->shouldReceive('where')->with('completed', false)->andReturn($query);
		$query->shouldReceive('first')->once()->andReturn($activation = m::mock('Cartalyst\Sentinel\Activations\EloquentActivation'));

		$activation->shouldReceive('fill')->once();
		$activation->shouldReceive('save')->once();

		$user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');
		$user->shouldReceive('getUserId')->once()->andReturn(1);

		$activations->complete($user, 'foobar');
	}

	public function testCompleteInvalidActivation()
	{
		$activations = m::mock('Cartalyst\Sentinel\Activations\IlluminateActivationRepository[createModel]');

		$activations->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Activations\EloquentActivation[newQuery]'));

		$model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

		$query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
		$query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
		$query->shouldReceive('where')->with('completed', false)->andReturn($query);
		$query->shouldReceive('first')->once();

		$user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');
		$user->shouldReceive('getUserId')->once()->andReturn(1);

		$activations->complete($user, 'foobar');
	}

	public function testCompleted()
	{
		$activations = m::mock('Cartalyst\Sentinel\Activations\IlluminateActivationRepository[createModel]');

		$activations->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Activations\EloquentActivation[newQuery]'));

		$model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

		$query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
		$query->shouldReceive('where')->with('completed', true)->andReturn($query);
		$query->shouldReceive('first')->once();

		$user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');
		$user->shouldReceive('getUserId')->once()->andReturn(1);

		$activations->completed($user);
	}

	public function testRemoveReturnsFalseIfNoCompleteActivationIsFound()
	{
		$activations = m::mock('Cartalyst\Sentinel\Activations\IlluminateActivationRepository[createModel]');

		$activations->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Activations\EloquentActivation[newQuery]'));

		$model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

		$query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
		$query->shouldReceive('where')->with('completed', true)->andReturn($query);
		$query->shouldReceive('first')->once()->andReturn(false);

		$user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');
		$user->shouldReceive('getUserId')->once()->andReturn(1);

		$activations->remove($user);
	}

	public function testRemoveValidActivation()
	{
		$activations = m::mock('Cartalyst\Sentinel\Activations\IlluminateActivationRepository[createModel]');

		$activations->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Activations\EloquentActivation[newQuery]'));

		$model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

		$query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
		$query->shouldReceive('where')->with('completed', true)->andReturn($query);
		$query->shouldReceive('first')->once()->andReturn($activation = m::mock('Cartalyst\Sentinel\Activations\EloquentActivation'));

		$activation->shouldReceive('delete')->once();

		$user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');
		$user->shouldReceive('getUserId')->once()->andReturn(1);

		$activations->remove($user);
	}

	public function testRemoveExpired()
	{
		$lifetime = 259200;

		$activations = m::mock('Cartalyst\Sentinel\Activations\IlluminateActivationRepository[createModel]', [null, $lifetime]);

		$activations->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Activations\EloquentActivation[newQuery]'));

		$model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

		$query->shouldReceive('where')->with('completed', false)->andReturn($query);
		$query->shouldReceive('where')->andReturn($query);
		$query->shouldReceive('delete')->once();

		$activations->removeExpired();
	}

	protected function addMockConnection($model)
	{
		$model->setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));
		$resolver->shouldReceive('connection')->andReturn(m::mock('Illuminate\Database\Connection'));
		$model->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock('Illuminate\Database\Query\Grammars\Grammar'));
		$model->getConnection()->shouldReceive('getPostProcessor')->andReturn(m::mock('Illuminate\Database\Query\Processors\Processor'));
	}

}
