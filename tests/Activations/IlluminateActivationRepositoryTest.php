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

namespace Cartalyst\Sentinel\Tests\Activations;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Cartalyst\Sentinel\Users\UserInterface;
use Cartalyst\Sentinel\Activations\EloquentActivation;
use Cartalyst\Sentinel\Activations\IlluminateActivationRepository;

class IlluminateActivationRepositoryTest extends TestCase
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
        $activations = new IlluminateActivationRepository('ActivationModelMock', 259200);

        $this->assertSame('ActivationModelMock', $activations->getModel());
    }

    /** @test */
    public function it_can_create_an_activation_code()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $model->shouldReceive('fill')->once();
        $model->shouldReceive('setAttribute')->once();
        $model->shouldReceive('save')->once();

        $user = $this->getUserMock();

        $activation = $activations->create($user);

        $this->assertInstanceOf(EloquentActivation::class, $activation);
    }

    /** @test */
    public function it_can_determine_if_an_activation_exists()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('completed', false)->andReturn($query);
        $query->shouldReceive('first')->once();

        $this->shouldReceiveExpires($query);

        $user = $this->getUserMock();

        $status = $activations->exists($user);

        $this->assertFalse($status);
    }

    /** @test */
    public function it_can_complete_an_activation()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $activation = m::mock(EloquentActivation::class);
        $activation->shouldReceive('fill')->once();
        $activation->shouldReceive('save')->once();

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
        $query->shouldReceive('where')->with('completed', false)->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn($activation);

        $this->shouldReceiveExpires($query);

        $user = $this->getUserMock();

        $status = $activations->complete($user, 'foobar');

        $this->assertTrue($status);
    }

    /** @test */
    public function it_cannot_complete_an_activation_that_has_expired()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
        $query->shouldReceive('where')->with('completed', false)->andReturn($query);
        $query->shouldReceive('first')->once();

        $this->shouldReceiveExpires($query);

        $user = $this->getUserMock();

        $status = $activations->complete($user, 'foobar');

        $this->assertFalse($status);
    }

    /** @test */
    public function it_can_determine_if_an_activation_is_completed()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('completed', true)->andReturn($query);
        $query->shouldReceive('exists')->once()->andReturn(true);

        $user = $this->getUserMock();

        $status = $activations->completed($user);

        $this->assertTrue($status);
    }

    /** @test */
    public function it_can_determine_if_an_activation_is_not_completed()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('completed', true)->andReturn($query);
        $query->shouldReceive('exists')->once()->andReturn(false);

        $user = $this->getUserMock();

        $status = $activations->completed($user);

        $this->assertFalse($status);
    }

    /** @test */
    public function it_can_remove_non_completed_activations()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('completed', true)->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn(false);

        $user = $this->getUserMock();

        $status = $activations->remove($user);

        $this->assertFalse($status);
    }

    /** @test */
    public function it_can_remove_completed_activations()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $activation = m::mock(EloquentActivation::class);

        $query->shouldReceive('where')->with('user_id', '1')->andReturn($query);
        $query->shouldReceive('where')->with('completed', true)->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn($activation);

        $activation->shouldReceive('delete')->once()->andReturn(true);

        $user = $this->getUserMock();

        $status = $activations->remove($user);

        $this->assertTrue($status);
    }

    /** @test */
    public function it_can_remove_expired_activations()
    {
        list($activations, $model, $query) = $this->getActivationMock();

        $query->shouldReceive('where')->with('completed', false)->andReturn($query);

        $this->shouldReceiveExpires($query, '<');

        $query->shouldReceive('delete')->once()->andReturn(true);

        $status = $activations->removeExpired();

        $this->assertTrue($status);
    }

    protected function getActivationMock()
    {
        $query = m::mock('Illuminate\Database\Eloquent\Builder');

        $model = m::mock('Cartalyst\Sentinel\Activations\EloquentActivation');
        $model->shouldReceive('newQuery')->andReturn($query);

        $activations = m::mock('Cartalyst\Sentinel\Activations\IlluminateActivationRepository[createModel]', ['ActivationModelMock', 259200]);
        $activations->shouldReceive('createModel')->andReturn($model);

        return [$activations, $model, $query];
    }

    protected function getUserMock()
    {
        $user = m::mock(UserInterface::class);

        $user->shouldReceive('getUserId')->once()->andReturn(1);

        return $user;
    }

    protected function shouldReceiveExpires($query, $operator = '>')
    {
        $query->shouldReceive('where')->with('created_at', $operator, m::on(function () {
            return true;
        }))->andReturn($query);
    }
}
