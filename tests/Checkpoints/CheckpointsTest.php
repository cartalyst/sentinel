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

namespace Cartalyst\Sentinel\Tests\Checkpoints;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Users\EloquentUser;
use Illuminate\Contracts\Events\Dispatcher;
use Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint;
use Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface;
use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface;

class CheckpointsTest extends TestCase
{
    /**
     * The Illuminate Events Dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * The Sentinel instance.
     *
     * @var \Cartalyst\Sentinel\Sentinel
     */
    protected $sentinel;

    /**
     * The Users repository instance.
     *
     * @var \Cartalyst\Sentinel\Users\UserRepositoryInterface
     */
    protected $users;

    /**
     * The Roles repository instance.
     *
     * @var \Cartalyst\Sentinel\Roles\RoleRepositoryInterface
     */
    protected $roles;

    /**
     * The Activations repository instance.
     *
     * @var \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface
     */
    protected $activations;

    /**
     * The Persistences repository instance.
     *
     * @var \Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface
     */
    protected $persistences;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->dispatcher   = m::mock(Dispatcher::class);
        $this->users        = m::mock(UserRepositoryInterface::class);
        $this->roles        = m::mock(RoleRepositoryInterface::class);
        $this->throttle     = m::mock(ThrottleRepositoryInterface::class);
        $this->activations  = m::mock(ActivationRepositoryInterface::class);
        $this->persistences = m::mock(PersistenceRepositoryInterface::class);

        $this->sentinel = new Sentinel($this->persistences, $this->users, $this->roles, $this->activations, $this->dispatcher);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_can_add_checkpoint_at_runtime()
    {
        $activationCheckpoint = m::mock(ActivationCheckpoint::class, [$this->activations]);

        $this->sentinel->addCheckpoint('activation', $activationCheckpoint);

        $this->assertCount(1, $this->sentinel->getCheckpoints());
        $this->assertArrayHasKey('activation', $this->sentinel->getCheckpoints());
    }

    /** @test */
    public function it_can_remove_checkpoint_at_runtime()
    {
        $activationCheckpoint = m::mock(ActivationCheckpoint::class, [$this->activations]);
        $throttleCheckpoint   = m::mock(ThrottleCheckpoint::class, [$this->throttle]);

        $this->sentinel->addCheckpoint('activation', $activationCheckpoint);
        $this->sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $this->sentinel->removeCheckpoint('activation');

        $this->assertCount(1, $this->sentinel->getCheckpoints());
        $this->assertArrayNotHasKey('activation', $this->sentinel->getCheckpoints());
    }

    /** @test */
    public function it_can_bypass_all_checkpoints()
    {
        $this->persistences->shouldReceive('check')->once()->andReturn('foobar');
        $this->persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser());

        $activationCheckpoint = m::mock(ActivationCheckpoint::class, [$this->activations]);

        $throttleCheckpoint = m::mock(ThrottleCheckpoint::class, [$this->throttle]);

        $this->sentinel->addCheckpoint('activation', $activationCheckpoint);
        $this->sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $this->sentinel->bypassCheckpoints(function ($sentinel) {
            $this->assertNotNull($this->sentinel->check());
        });
    }

    /** @test */
    public function it_can_bypass_a_specific_endpoint()
    {
        $this->persistences->shouldReceive('check')->once()->andReturn('foobar');
        $this->persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser());

        $activationCheckpoint = m::mock(ActivationCheckpoint::class, [$this->activations]);

        $throttleCheckpoint = m::mock(ThrottleCheckpoint::class, [$this->throttle]);
        $throttleCheckpoint->shouldReceive('check')->once();

        $this->sentinel->addCheckpoint('activation', $activationCheckpoint);
        $this->sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $this->sentinel->bypassCheckpoints(function ($s) {
            $this->assertNotNull($s->check());
        }, ['activation']);
    }

    /** @test */
    public function it_can_disable_all_checkpoints()
    {
        $this->assertTrue($this->sentinel->checkpointsStatus());

        $this->sentinel->disableCheckpoints();

        $this->assertFalse($this->sentinel->checkpointsStatus());

        $this->persistences->shouldReceive('check')->once()->andReturn('foobar');
        $this->persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser());

        $activationCheckpoint = m::mock(ActivationCheckpoint::class, [$this->activations]);
        $throttleCheckpoint   = m::mock(ThrottleCheckpoint::class, [$this->throttle]);

        $this->sentinel->addCheckpoint('activation', $activationCheckpoint);
        $this->sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $this->assertNotNull($this->sentinel->check());
    }

    /** @test */
    public function it_can_enable_all_checkpoints()
    {
        $this->persistences->shouldReceive('check')->once()->andReturn('foobar');
        $this->persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser());

        $this->assertTrue($this->sentinel->checkpointsStatus());

        $this->sentinel->disableCheckpoints();

        $this->assertFalse($this->sentinel->checkpointsStatus());

        $this->sentinel->enableCheckpoints();

        $this->assertTrue($this->sentinel->checkpointsStatus());

        $activationCheckpoint = m::mock(ActivationCheckpoint::class, [$this->activations]);
        $throttleCheckpoint   = m::mock(ThrottleCheckpoint::class, [$this->throttle]);

        $activationCheckpoint->shouldReceive('check')->once();

        $this->sentinel->addCheckpoint('activation', $activationCheckpoint);
        $this->sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $this->assertNotNull($this->sentinel->check());
    }

    /** @test */
    public function the_check_checkpoint_will_be_invoked()
    {
        $this->persistences->shouldReceive('check')->once()->andReturn('foobar');
        $this->persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser());

        $throttleCheckpoint = m::mock(ThrottleCheckpoint::class, [$this->throttle]);
        $throttleCheckpoint->shouldReceive('check')->once()->andReturn(false);

        $this->sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $this->assertFalse($this->sentinel->check());
    }

    /** @test */
    public function the_login_checkpoint_will_be_invoked()
    {
        $this->dispatcher->shouldReceive('until')->once();

        $throttleCheckpoint = m::mock(ThrottleCheckpoint::class, [$this->throttle]);
        $throttleCheckpoint->shouldReceive('login')->once()->andReturn(false);

        $this->sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $this->assertFalse($this->sentinel->authenticate(new EloquentUser()));
    }

    /** @test */
    public function the_fail_checkpoint_will_be_invoked()
    {
        $credentials = [
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $this->dispatcher->shouldReceive('until')->once();

        $this->users->shouldReceive('findByCredentials')->with($credentials)->once();

        $throttleCheckpoint = m::mock(ThrottleCheckpoint::class, [$this->throttle]);
        $throttleCheckpoint->shouldReceive('fail')->once()->andReturn(false);

        $this->sentinel->addCheckpoint('throttle', $throttleCheckpoint);

        $this->assertFalse($this->sentinel->authenticate($credentials));
    }
}
