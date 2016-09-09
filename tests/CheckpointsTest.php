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

use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Users\EloquentUser;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class CheckpointsTest extends PHPUnit_Framework_TestCase
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

    public function testAddCheckpoint()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser);

        $activationCheckpoint = m::mock('Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint', [$activations]);
        $activationCheckpoint->shouldReceive('check')->once();

        $sentinel->addCheckpoint('activation', $activationCheckpoint);

        $sentinel->check();
    }

    public function testRemoveCheckpoint()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher, $throttle) = $this->createSentinel();

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser);

        $activationCheckpoint = m::mock('Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint', [$activations]);

        $throttleCheckpoint = m::mock('Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint', [$throttle]);
        $throttleCheckpoint->shouldReceive('check')->once();

        $sentinel->addCheckpoint('activation', $activationCheckpoint);
        $sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $sentinel->removeCheckpoint('activation');

        $sentinel->check();
    }

    public function testBypassCheckpoint()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher, $throttle) = $this->createSentinel();

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser);

        $activationCheckpoint = m::mock('Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint', [$activations]);

        $throttleCheckpoint = m::mock('Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint', [$throttle]);

        $sentinel->addCheckpoint('activation', $activationCheckpoint);
        $sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $sentinel->bypassCheckpoints(function ($sentinel) {
            $sentinel->check();
        });
    }

    public function testBypassSpecificCheckpoint()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher, $throttle) = $this->createSentinel();

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser);

        $activationCheckpoint = m::mock('Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint', [$activations]);

        $throttleCheckpoint = m::mock('Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint', [$throttle]);
        $throttleCheckpoint->shouldReceive('check')->once();

        $sentinel->addCheckpoint('activation', $activationCheckpoint);
        $sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $sentinel->bypassCheckpoints(function ($s) {
            $this->assertNotNull($s->check());
        }, ['activation']);
    }

    public function testDisableCheckpoints()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher, $throttle) = $this->createSentinel();

        $this->assertTrue($sentinel->checkpointsStatus());

        $sentinel->disableCheckpoints();

        $this->assertFalse($sentinel->checkpointsStatus());

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser);

        $activationCheckpoint = m::mock('Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint', [$activations]);
        $throttleCheckpoint   = m::mock('Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint', [$throttle]);

        $sentinel->addCheckpoint('activation', $activationCheckpoint);
        $sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $this->assertNotNull($sentinel->check());
    }

    public function testEnableCheckpoints()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher, $throttle) = $this->createSentinel();

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser);

        $this->assertTrue($sentinel->checkpointsStatus());

        $sentinel->disableCheckpoints();

        $this->assertFalse($sentinel->checkpointsStatus());

        $sentinel->enableCheckpoints();

        $this->assertTrue($sentinel->checkpointsStatus());

        $activationCheckpoint = m::mock('Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint', [$activations]);
        $throttleCheckpoint   = m::mock('Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint', [$throttle]);

        $activationCheckpoint->shouldReceive('check')->once();
        $throttleCheckpoint->shouldReceive('check')->once();

        $sentinel->addCheckpoint('activation', $activationCheckpoint);
        $sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $this->assertNotNull($sentinel->check());
    }

    public function testCheckCheckpoint()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher, $throttle) = $this->createSentinel();

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser);

        $throttleCheckpoint = m::mock('Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint', [$throttle]);
        $throttleCheckpoint->shouldReceive('check')->once()->andReturn(false);

        $sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $this->assertFalse($sentinel->check());
    }

    public function testLoginCheckpoint()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher, $throttle) = $this->createSentinel();

        $dispatcher->shouldReceive('until')->once();

        $throttleCheckpoint = m::mock('Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint', [$throttle]);
        $throttleCheckpoint->shouldReceive('login')->once()->andReturn(false);

        $sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $this->assertFalse($sentinel->authenticate(new EloquentUser));
    }

    public function testFailCheckpoint()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher, $throttle) = $this->createSentinel();

        $dispatcher->shouldReceive('until')->once();

        $credentials = [
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $users->shouldReceive('findByCredentials')->with($credentials)->once();

        $throttleCheckpoint = m::mock('Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint', [$throttle]);
        $throttleCheckpoint->shouldReceive('fail')->once()->andReturn(false);

        $sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $this->assertFalse($sentinel->authenticate($credentials));
    }

    protected function createSentinel()
    {
        $sentinel = new Sentinel(
            $persistences = m::mock('Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface'),
            $users        = m::mock('Cartalyst\Sentinel\Users\UserRepositoryInterface'),
            $roles        = m::mock('Cartalyst\Sentinel\Roles\RoleRepositoryInterface'),
            $activations  = m::mock('Cartalyst\Sentinel\Activations\ActivationRepositoryInterface'),
            $dispatcher   = m::mock('Illuminate\Events\Dispatcher')
        );

        $throttle = m::mock('Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface');

        return [$sentinel, $persistences, $users, $roles, $activations, $dispatcher, $throttle];
    }
}
