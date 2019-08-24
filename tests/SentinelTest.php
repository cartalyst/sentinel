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

namespace Cartalyst\Sentinel\Tests;

use Mockery as m;
use RuntimeException;
use BadMethodCallException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Roles\EloquentRole;
use Cartalyst\Sentinel\Users\EloquentUser;
use Illuminate\Contracts\Events\Dispatcher;
use Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Cartalyst\Sentinel\Activations\ActivationInterface;
use Cartalyst\Sentinel\Checkpoints\CheckpointInterface;
use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface;
use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface;

class SentinelTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->sentinel = new Sentinel(
            $this->persistences = m::mock(PersistenceRepositoryInterface::class),
            $this->users = m::mock(UserRepositoryInterface::class),
            $this->roles = m::mock(RoleRepositoryInterface::class),
            $this->activations = m::mock(ActivationRepositoryInterface::class),
            $this->dispatcher = m::mock(Dispatcher::class)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->sentinel     = null;
        $this->persistences = null;
        $this->users        = null;
        $this->roles        = null;
        $this->activations  = null;
        $this->dispatcher   = null;
        m::close();
    }

    /** @test */
    public function it_can_register_a_valid_user()
    {
        $this->users->shouldReceive('validForCreation')->once()->andReturn(true);
        $this->users->shouldReceive('create')->once()->andReturn(m::mock(EloquentUser::class));

        $this->dispatcher->shouldReceive('dispatch')->twice();

        $user = $this->sentinel->register([
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_register_and_activate_a_valid_user()
    {
        $this->users->shouldReceive('validForCreation')->once()->andReturn(true);
        $this->users->shouldReceive('create')->once()->andReturn(m::mock(EloquentUser::class));

        $activation = m::mock(ActivationInterface::class);
        $activation->shouldReceive('getCode')->once()->andReturn('a_random_code');

        $this->activations->shouldReceive('create')->once()->andReturn($activation);
        $this->activations->shouldReceive('complete')->once()->andReturn(true);

        $this->dispatcher->shouldReceive('dispatch')->times(4);

        $user = $this->sentinel->registerAndActivate([
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_will_not_register_an_invalid_user()
    {
        $this->users->shouldReceive('validForCreation')->once()->andReturn(false);

        $this->dispatcher->shouldReceive('dispatch')->once();

        $user = $this->sentinel->register([
            'email' => 'foo@example.com',
        ]);

        $this->assertFalse($user);
    }

    /** @test */
    public function it_can_activate_a_user_using_its_id()
    {
        $activation = m::mock(ActivationInterface::class);
        $activation->shouldReceive('getCode')->once()->andReturn('a_random_code');

        $this->users->shouldReceive('findById')->with('1')->once()->andReturn(m::mock(EloquentUser::class));

        $this->activations->shouldReceive('create')->once()->andReturn($activation);
        $this->activations->shouldReceive('complete')->once()->andReturn(true);

        $this->dispatcher->shouldReceive('dispatch')->twice();

        $this->assertTrue($this->sentinel->activate('1'));
    }

    /** @test */
    public function it_can_activate_a_user_using_its_instance()
    {
        $user = m::mock(EloquentUser::class);

        $activation = m::mock(ActivationInterface::class);
        $activation->shouldReceive('getCode')->once()->andReturn('a_random_code');

        $this->activations->shouldReceive('create')->once()->andReturn($activation);
        $this->activations->shouldReceive('complete')->once()->andReturn(true);

        $this->dispatcher->shouldReceive('dispatch')->twice();

        $this->assertTrue($this->sentinel->activate($user));
    }

    /** @test */
    public function it_can_activate_a_user_using_its_credentials()
    {
        $credentials = [
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $user = m::mock(EloquentUser::class);

        $activation = m::mock(ActivationInterface::class);
        $activation->shouldReceive('getCode')->once()->andReturn('a_random_code');

        $this->users->shouldReceive('findByCredentials')->with($credentials)->once()->andReturn($user);

        $this->activations->shouldReceive('create')->once()->andReturn($activation);
        $this->activations->shouldReceive('complete')->once()->andReturn(true);

        $this->dispatcher->shouldReceive('dispatch')->twice();

        $this->assertTrue($this->sentinel->activate($credentials));
    }

    /** @test */
    public function it_can_check_if_the_user_is_logged_in()
    {
        $user = m::mock(EloquentUser::class);

        $this->persistences->shouldReceive('check')->once()->andReturn('foobar');
        $this->persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn($user);

        $this->assertInstanceOf(EloquentUser::class, $this->sentinel->check());
    }

    /** @test */
    public function it_can_check_if_the_user_is_logged_in_when_it_is_not()
    {
        $this->persistences->shouldReceive('check')->once()->andReturn('foobar');
        $this->persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(null);

        $this->assertFalse($this->sentinel->check());
    }

    /** @test */
    public function it_can_force_the_check_if_the_user_is_logged_in()
    {
        $this->persistences->shouldReceive('check')->once();
        $this->persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(m::mock(EloquentUser::class));

        $checkpoint = m::mock(CheckpointInterface::class);

        $this->sentinel->addCheckpoint('activation', $checkpoint);

        $valid = $this->sentinel->forceCheck();

        $this->assertFalse($valid);
    }

    /** @test */
    public function it_can_check_checkpoints()
    {
        $checkpoint = m::mock(CheckpointInterface::class);
        $checkpoint->shouldReceive('check')->andReturn(false);
        $this->sentinel->addCheckpoint('foobar', $checkpoint);

        $this->persistences->shouldReceive('check')->once()->andReturn(true);
        $this->persistences->shouldReceive('findUserByPersistenceCode')->andReturn(m::mock(EloquentUser::class));

        $this->assertFalse($this->sentinel->check());
    }

    public function testGuest1()
    {
        $this->persistences->shouldReceive('check')->once();

        $this->assertTrue($this->sentinel->guest());
    }

    public function testGuest2()
    {
        $user = m::mock(EloquentUser::class);

        $this->sentinel->setUser($user);

        $this->assertFalse($this->sentinel->guest());
    }

    /** @test */
    public function it_can_authenticate_a_user_using_its_credentials()
    {
        $user = m::mock(EloquentUser::class);

        $credentials = [
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $this->persistences->shouldReceive('persist')->once();

        $this->users->shouldReceive('findByCredentials')->with($credentials)->once()->andReturn($user);
        $this->users->shouldReceive('validateCredentials')->once()->andReturn(true);
        $this->users->shouldReceive('recordLogin')->once()->andReturn(true);

        $this->dispatcher->shouldReceive('until')->once();

        $this->dispatcher->shouldReceive('dispatch')->times(3);

        $this->assertInstanceOf(EloquentUser::class, $this->sentinel->authenticate($credentials));
    }

    /** @test */
    public function it_can_authenticate_a_user_using_its_user_instance()
    {
        $user = m::mock(EloquentUser::class);

        $this->persistences->shouldReceive('persist')->once();

        $this->users->shouldReceive('recordLogin')->once()->andReturn(true);

        $this->dispatcher->shouldReceive('until')->once();
        $this->dispatcher->shouldReceive('dispatch')->times(3);

        $this->assertInstanceOf(EloquentUser::class, $this->sentinel->authenticate($user));
    }

    /** @test */
    public function it_will_not_authenticate_a_user_with_invalid_credentials()
    {
        $this->users->shouldReceive('findByCredentials')->once();

        $this->dispatcher->shouldReceive('until')->once();

        $this->assertFalse($this->sentinel->authenticate([]));
    }

    /** @test */
    public function it_can_authenticate_and_remember()
    {
        $user = m::mock(EloquentUser::class);

        $credentials = [
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $this->persistences->shouldReceive('persistAndRemember')->once();

        $this->users->shouldReceive('findByCredentials')->with($credentials)->once()->andReturn($user);
        $this->users->shouldReceive('validateCredentials')->once()->andReturn(true);
        $this->users->shouldReceive('recordLogin')->once()->andReturn(true);

        $this->dispatcher->shouldReceive('until')->once();
        $this->dispatcher->shouldReceive('dispatch')->times(3);

        $this->assertInstanceOf(EloquentUser::class, $this->sentinel->authenticateAndRemember($credentials));
    }

    /** @test */
    public function it_can_authenticate_when_checkpoints_are_disabled()
    {
        $this->sentinel->disableCheckpoints();
        $user = m::mock(EloquentUser::class);

        $this->persistences->shouldReceive('persist')->once();
        $this->users->shouldReceive('recordLogin')->once()->andReturn(true);

        $this->dispatcher->shouldReceive('until');
        $this->dispatcher->shouldReceive('dispatch');

        $this->assertInstanceOf(EloquentUser::class, $this->sentinel->authenticate($user));
    }

    /** @test */
    public function it_cannot_authenticate_when_firing_an_event_fails()
    {
        $credentials = [
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $this->dispatcher->shouldReceive('until')->once()->andReturn(false);

        $this->assertFalse($this->sentinel->authenticate($credentials));
    }

    /** @test */
    public function it_cannot_authenticate_when_a_checkpoint_fails()
    {
        $user = m::mock(EloquentUser::class);

        $checkpoint = m::mock(CheckpointInterface::class);
        $checkpoint->shouldReceive('login')->andReturn(false);
        $this->sentinel->addCheckpoint('foobar', $checkpoint);

        $this->dispatcher->shouldReceive('until');
        $this->dispatcher->shouldReceive('dispatch');

        $this->assertFalse($this->sentinel->authenticate($user));
    }

    /** @test */
    public function it_cannot_authenticate_when_a_login_fails()
    {
        $user = m::mock(EloquentUser::class);

        $this->persistences->shouldReceive('persist')->once();

        $this->users->shouldReceive('recordLogin')->once()->andReturn(false);

        $this->dispatcher->shouldReceive('until');
        $this->dispatcher->shouldReceive('dispatch');

        $this->assertFalse($this->sentinel->authenticate($user));
    }

    /** @test */
    public function it_can_set_the_user_instance_on_the_sentinel_class()
    {
        $user = m::mock(EloquentUser::class);

        $this->sentinel->setUser($user);

        $this->assertInstanceOf(EloquentUser::class, $this->sentinel->getUser());
    }

    /** @test */
    public function it_can_get_the_checkpoint_status()
    {
        $this->sentinel->disableCheckpoints();

        $this->assertFalse($this->sentinel->checkpointsStatus());

        $this->sentinel->enableCheckpoints();

        $this->assertTrue($this->sentinel->checkpointsStatus());
    }

    /** @test */
    public function it_can_login_with_a_valid_user()
    {
        $user = m::mock(EloquentUser::class);

        $this->persistences->shouldReceive('persist')->once();

        $this->dispatcher->shouldReceive('dispatch')->twice();

        $this->users->shouldReceive('recordLogin')->once()->andReturn(true);

        $this->assertInstanceOf(EloquentUser::class, $this->sentinel->login($user));
    }

    /** @test */
    public function it_will_not_login_with_an_invalid_user()
    {
        $user = m::mock(EloquentUser::class);

        $this->persistences->shouldReceive('persist')->once();

        $this->dispatcher->shouldReceive('dispatch')->once();

        $this->users->shouldReceive('recordLogin')->once()->andReturn(false);

        $this->assertFalse($this->sentinel->login($user));
    }

    public function it_will_ensure_the_user_is_not_defined_when_logging_out()
    {
        $user = m::mock(EloquentUser::class);

        $this->persistences->shouldReceive('persist')->once();
        $this->persistences->shouldReceive('forget')->once();

        $this->users->shouldReceive('recordLogin')->once();
        $this->users->shouldReceive('recordLogout')->once();

        $this->sentinel->login($user);
        $this->sentinel->logout($user);

        $this->assertNull($this->sentinel->getUser(false));
    }

    /** @test */
    public function it_can_logout_the_current_user()
    {
        $user = m::mock(EloquentUser::class);

        $this->persistences->shouldReceive('check')->once()->andReturn('foobar');
        $this->persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->once()->andReturn($user);
        $this->persistences->shouldReceive('forget')->once();

        $this->users->shouldReceive('recordLogout')->once()->andReturn(true);

        $this->dispatcher->shouldReceive('dispatch')->twice();

        $this->assertTrue($this->sentinel->logout($user));
    }

    /** @test */
    public function it_can_logout_the_user_on_the_other_devices()
    {
        $user = m::mock(EloquentUser::class);

        $this->persistences->shouldReceive('check')->once()->andReturn('foobar');
        $this->persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->once()->andReturn($user);
        $this->persistences->shouldReceive('flush')->once();

        $this->dispatcher->shouldReceive('dispatch')->twice();

        $this->users->shouldReceive('recordLogout')->once()->andReturn(true);

        $this->assertTrue($this->sentinel->logout($user, true));
    }

    /** @test */
    public function it_can_maintain_a_user_session_after_logging_out_another_user()
    {
        $user        = m::mock(EloquentUser::class);
        $currentUser = m::mock(EloquentUser::class);

        $this->persistences->shouldReceive('persist')->once();
        $this->persistences->shouldReceive('flush')->once()->with($user, false);

        $this->dispatcher->shouldReceive('dispatch')->times(4);

        $this->users->shouldReceive('recordLogin')->once()->andReturn(true);

        $this->sentinel->login($currentUser);

        $this->sentinel->logout($user);

        $this->assertSame($currentUser, $this->sentinel->getUser(false));
    }

    /** @test */
    public function it_can_logout_an_invalid_user()
    {
        $user = null;

        $this->persistences->shouldReceive('check')->once();

        $this->dispatcher->shouldReceive('dispatch')->twice();

        $this->assertTrue($this->sentinel->logout($user, true));
    }

    /** @test */
    public function it_can_bypass_the_checkpoints()
    {
        $user = m::mock(EloquentUser::class);

        $foo = m::mock(CheckpointInterface::class);
        $foo->shouldReceive('check')->never();
        $bar = m::mock(CheckpointInterface::class);
        $bar->shouldReceive('check')->once()->andReturn(true);

        $this->sentinel->addCheckpoint('foo', $foo);
        $this->sentinel->addCheckpoint('bar', $bar);

        $this->persistences->shouldReceive('check')->once()->andReturn('foobar');
        $this->persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser());

        $this->sentinel->bypassCheckpoints(function ($args) {
            $this->assertSame($this->sentinel, $args);

            return $args->check();
        }, ['foo']);
        $this->assertSame($this->sentinel->getCheckpoints()['foo'], $foo);
        $this->assertSame($this->sentinel->getCheckpoints()['bar'], $bar);
    }

    /** @test */
    public function it_can_remove_individual_checkpoints()
    {
        $this->sentinel->addCheckpoint('foo', m::mock(CheckpointInterface::class));
        $this->sentinel->addCheckpoint('bar', m::mock(CheckpointInterface::class));

        $this->assertTrue(array_key_exists('foo', $this->sentinel->getCheckpoints()));
        $this->assertTrue(array_key_exists('bar', $this->sentinel->getCheckpoints()));

        $this->sentinel->removeCheckpoint('foo');
        $this->assertFalse(array_key_exists('foo', $this->sentinel->getCheckpoints()));
        $this->assertTrue(array_key_exists('bar', $this->sentinel->getCheckpoints()));
    }

    /** @test */
    public function it_can_remove_multiple_checkpoints()
    {
        $this->sentinel->addCheckpoint('foo', m::mock(CheckpointInterface::class));
        $this->sentinel->addCheckpoint('bar', m::mock(CheckpointInterface::class));
        $this->sentinel->addCheckpoint('baz', m::mock(CheckpointInterface::class));

        $this->assertTrue(array_key_exists('foo', $this->sentinel->getCheckpoints()));
        $this->assertTrue(array_key_exists('bar', $this->sentinel->getCheckpoints()));
        $this->assertTrue(array_key_exists('baz', $this->sentinel->getCheckpoints()));

        $this->sentinel->removeCheckpoints(['foo', 'bar']);
        $this->assertFalse(array_key_exists('foo', $this->sentinel->getCheckpoints()));
        $this->assertFalse(array_key_exists('bar', $this->sentinel->getCheckpoints()));
        $this->assertTrue(array_key_exists('baz', $this->sentinel->getCheckpoints()));
    }

    /** @test */
    public function it_can_create_a_basic_response()
    {
        $response = json_encode(['response']);

        $this->sentinel->creatingBasicResponse(function () use ($response) {
            return $response;
        });

        $this->assertSame($response, $this->sentinel->getBasicResponse());
    }

    /** @test */
    public function it_can_set_and_get_the_various_repositories()
    {
        $this->sentinel->setPersistenceRepository($persistence = m::mock(PersistenceRepositoryInterface::class));
        $this->sentinel->setUserRepository($users = m::mock(UserRepositoryInterface::class));
        $this->sentinel->setRoleRepository($roles = m::mock(RoleRepositoryInterface::class));
        $this->sentinel->setActivationRepository($activations = m::mock(ActivationRepositoryInterface::class));
        $this->sentinel->setReminderRepository($reminders = m::mock(ReminderRepositoryInterface::class));
        $this->sentinel->setThrottleRepository($throttling = m::mock(ThrottleRepositoryInterface::class));

        $this->assertSame($persistence, $this->sentinel->getPersistenceRepository());
        $this->assertSame($users, $this->sentinel->getUserRepository());
        $this->assertSame($roles, $this->sentinel->getRoleRepository());
        $this->assertSame($activations, $this->sentinel->getActivationRepository());
        $this->assertSame($reminders, $this->sentinel->getReminderRepository());
        $this->assertSame($throttling, $this->sentinel->getThrottleRepository());
    }

    /** @test */
    public function it_can_pass_method_calls_to_a_user_repository_directly()
    {
        $this->users->shouldReceive('findById')->once()->andReturn(m::mock(EloquentUser::class));

        $user = $this->sentinel->findById(1);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_pass_method_calls_to_a_user_repository_via_findUserBy()
    {
        $this->users->shouldReceive('findById')->once()->andReturn(m::mock(EloquentUser::class));

        $user = $this->sentinel->findUserById(1);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_pass_method_calls_to_a_role_repository_via_findRoleBy()
    {
        $this->roles->shouldReceive('findById')->once()->andReturn(m::mock(EloquentRole::class));

        $user = $this->sentinel->findRoleById(1);

        $this->assertInstanceOf(EloquentRole::class, $user);
    }

    /** @test */
    public function it_can_pass_methods_via_the_user_repository_when_a_user_is_logged_in()
    {
        $user = m::mock(EloquentUser::class);
        $user->shouldReceive('hasAccess')->andReturn(true);

        $this->persistences->shouldReceive('check')->andReturn(true);
        $this->persistences->shouldReceive('findUserByPersistenceCode')->andReturn($user);

        $this->assertTrue($this->sentinel->hasAccess());
    }

    /** @test */
    public function an_exception_will_be_thrown_when_activating_an_invalid_user()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No valid user was provided.');

        $this->sentinel->activate(20.00);
    }

    /** @test */
    public function an_exception_will_be_thrown_when_registering_with_an_invalid_closure()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must provide a closure or a boolean.');

        $this->sentinel->register([
            'email' => 'foo@example.com',
        ], 'invalid_closure');
    }

    /** @test */
    public function an_exception_will_be_thrown_when_trying_to_get_the_basic_response()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Attempting basic auth after headers have already been sent.');

        $this->sentinel->getBasicResponse();
    }

    /** @test */
    public function an_exception_will_be_thrown_when_calling_methods_which_are_only_available_when_a_user_is_logged_in()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method Cartalyst\Sentinel\Sentinel::getRoles() can only be called if a user is logged in.');

        $this->persistences->shouldReceive('check')->once()->andReturn(null);

        $this->sentinel->getRoles();
    }

    /** @test */
    public function an_exception_will_be_thrown_when_calling_invalid_methods()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method Cartalyst\Sentinel\Sentinel::methodThatDoesntExist()');

        $this->sentinel->methodThatDoesntExist();
    }
}
