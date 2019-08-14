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

use Cartalyst\Sentinel\Activations\IlluminateActivationRepository;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class IlluminateActivationRepositoryTest extends PHPUnit_Framework_TestCase
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
        $activations = new IlluminateActivationRepository('ActivationMock', 259200);

        $this->assertEquals('ActivationMock', $activations->getModel());
    }

    public function testCreate()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $model->shouldReceive('fill');
        $model->shouldReceive('setAttribute');
        $model->shouldReceive('save');

        $user = $this->getUserMock();

        $activation = $activations->create($user);

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Activations\EloquentActivation',
            $activation
        );
    }

    public function testExists()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('completed', false)->andReturn($query);

        $this->shouldReceiveExpires($query);

        $query->shouldReceive('first')->once();

        $user = $this->getUserMock();

        $activations->exists($user);
    }

    public function testCompleteValidActivation()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
        $query->shouldReceive('where')->with('completed', false)->andReturn($query);

        $this->shouldReceiveExpires($query);

        $query->shouldReceive('first')->once()->andReturn($activation = m::mock('Cartalyst\Sentinel\Activations\EloquentActivation'));

        $activation->shouldReceive('fill')->once();
        $activation->shouldReceive('save')->once();

        $user = $this->getUserMock();

        $activations->complete($user, 'foobar');
    }

    public function testCompleteInvalidActivation()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
        $query->shouldReceive('where')->with('completed', false)->andReturn($query);

        $this->shouldReceiveExpires($query);

        $query->shouldReceive('first')->once();

        $user = $this->getUserMock();

        $activations->complete($user, 'foobar');
    }

    public function testCompleted()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('completed', true)->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn($model);

        $user = $this->getUserMock();

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Activations\ActivationInterface',
            $activations->completed($user)
        );
    }

    public function testRemoveReturnsFalseIfNoCompleteActivationIsFound()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('completed', true)->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn(false);

        $user = $this->getUserMock();

        $this->assertFalse($activations->remove($user));
    }

    public function testRemoveValidActivation()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('completed', true)->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn($activation = m::mock('Cartalyst\Sentinel\Activations\EloquentActivation'));

        $activation->shouldReceive('delete')->once()->andReturn(true);

        $user = $this->getUserMock();

        $this->assertTrue($activations->remove($user));
    }

    public function testRemoveExpired()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $query->shouldReceive('where')->with('completed', false)->andReturn($query);

        $this->shouldReceiveExpires($query, '<');

        $query->shouldReceive('delete')->once();

        $activations->removeExpired();
    }

    protected function getActivationMock()
    {
        $activations = m::mock('Cartalyst\Sentinel\Activations\IlluminateActivationRepository[createModel]');
        $model = m::mock('Cartalyst\Sentinel\Activations\EloquentActivation');
        $query = m::mock('Illuminate\Database\Eloquent\Builder');

        $activations->shouldReceive('createModel')->andReturn($model);
        $model->shouldReceive('newQuery')->andReturn($query);

        return [$activations, $model, $query];
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
