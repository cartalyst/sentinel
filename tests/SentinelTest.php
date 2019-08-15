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
use Cartalyst\Sentinel\Roles\IlluminateRoleRepository;
use Cartalyst\Sentinel\Users\IlluminateUserRepository;
use Cartalyst\Sentinel\Activations\ActivationInterface;
use Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Cartalyst\Sentinel\Activations\IlluminateActivationRepository;
use Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository;

class SentinelTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_can_register_a_valid_user()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = m::mock(EloquentUser::class);

        $users->shouldReceive('validForCreation')->once()->andReturn(true);
        $users->shouldReceive('create')->once()->andReturn($user);

        $dispatcher->shouldReceive('dispatch')->twice();

        $user = $sentinel->register([
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_register_and_activate_a_valid_user()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = m::mock(EloquentUser::class);

        $users->shouldReceive('validForCreation')->once()->andReturn(true);
        $users->shouldReceive('create')->once()->andReturn($user);

        $activation = m::mock(ActivationInterface::class);
        $activation->shouldReceive('getCode')->once()->andReturn('a_random_code');

        $activations->shouldReceive('create')->once()->andReturn($activation);
        $activations->shouldReceive('complete')->once()->andReturn(true);

        $dispatcher->shouldReceive('dispatch')->times(4);

        $user = $sentinel->registerAndActivate([
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_will_not_register_an_invalid_user()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $users->shouldReceive('validForCreation')->once()->andReturn(false);

        $dispatcher->shouldReceive('dispatch')->once();

        $user = $sentinel->register([
            'email' => 'foo@example.com',
        ]);

        $this->assertFalse($user);
    }

    /** @test */
    public function it_can_activate_a_user_using_its_id()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser();

        $activation = m::mock(ActivationInterface::class);
        $activation->shouldReceive('getCode')->once()->andReturn('a_random_code');

        $users->shouldReceive('findById')->with('1')->once()->andReturn($user);

        $activations->shouldReceive('create')->once()->andReturn($activation);
        $activations->shouldReceive('complete')->once()->andReturn(true);

        $dispatcher->shouldReceive('dispatch')->twice();

        $this->assertTrue($sentinel->activate('1'));
    }

    /** @test */
    public function it_can_activate_a_user_using_its_instance()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser();

        $activation = m::mock(ActivationInterface::class);
        $activation->shouldReceive('getCode')->once()->andReturn('a_random_code');

        $activations->shouldReceive('create')->once()->andReturn($activation);
        $activations->shouldReceive('complete')->once()->andReturn(true);

        $dispatcher->shouldReceive('dispatch')->twice();

        $this->assertTrue($sentinel->activate($user));
    }

    /** @test */
    public function it_can_activate_a_user_using_its_credentials()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $credentials = [
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $user = new EloquentUser();

        $activation = m::mock(ActivationInterface::class);
        $activation->shouldReceive('getCode')->once()->andReturn('a_random_code');

        $users->shouldReceive('findByCredentials')->with($credentials)->once()->andReturn($user);

        $activations->shouldReceive('create')->once()->andReturn($activation);
        $activations->shouldReceive('complete')->once()->andReturn(true);

        $dispatcher->shouldReceive('dispatch')->twice();

        $this->assertTrue($sentinel->activate($credentials));
    }

    /** @test */
    public function it_can_check_if_the_user_is_logged_in()
    {
        list($sentinel, $persistences) = $this->createSentinel();

        $user = new EloquentUser();

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn($user);

        $this->assertInstanceOf(EloquentUser::class, $sentinel->check());
    }

    /** @test */
    public function it_can_check_if_the_user_is_logged_in_when_it_is_not()
    {
        list($sentinel, $persistences) = $this->createSentinel();

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(null);

        $this->assertFalse($sentinel->check());
    }

    /** @test */
    public function it_can_force_the_check_if_the_user_is_logged_in()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $persistences->shouldReceive('check')->once();
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser());

        $checkpoint = new ActivationCheckpoint($activations);

        $sentinel->addCheckpoint('activation', $checkpoint);

        $valid = $sentinel->forceCheck();

        $this->assertFalse($valid);
    }

    public function testGuest1()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $persistences->shouldReceive('check')->once();

        $this->assertTrue($sentinel->guest());
    }

    public function testGuest2()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser();

        $sentinel->setUser($user);

        $this->assertFalse($sentinel->guest());
    }

    /** @test */
    public function it_can_authenticate_a_user_using_its_credentials()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser();

        $credentials = [
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $persistences->shouldReceive('persist')->once();

        $users->shouldReceive('findByCredentials')->with($credentials)->once()->andReturn($user);
        $users->shouldReceive('validateCredentials')->once()->andReturn(true);
        $users->shouldReceive('recordLogin')->once();

        $dispatcher->shouldReceive('until')->once();

        $dispatcher->shouldReceive('dispatch')->times(3);

        $this->assertInstanceOf(EloquentUser::class, $sentinel->authenticate($credentials));
    }

    /** @test */
    public function it_can_authenticate_a_user_using_its_user_instance()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser();

        $persistences->shouldReceive('persist')->once();

        $users->shouldReceive('recordLogin')->once();

        $dispatcher->shouldReceive('until')->once();
        $dispatcher->shouldReceive('dispatch')->times(3);

        $this->assertInstanceOf(EloquentUser::class, $sentinel->authenticate($user));
    }

    /** @test */
    public function it_will_not_authenticate_a_user_with_invalid_credentials()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $users->shouldReceive('findByCredentials')->once();

        $dispatcher->shouldReceive('until')->once();

        $this->assertFalse($sentinel->authenticate([]));
    }

    /** @test */
    public function it_can_authenticate_and_remember()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser();

        $credentials = [
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $persistences->shouldReceive('persistAndRemember')->once();

        $users->shouldReceive('findByCredentials')->with($credentials)->once()->andReturn($user);
        $users->shouldReceive('validateCredentials')->once()->andReturn(true);
        $users->shouldReceive('recordLogin')->once();

        $dispatcher->shouldReceive('until')->once();
        $dispatcher->shouldReceive('dispatch')->times(3);

        $this->assertInstanceOf(EloquentUser::class, $sentinel->authenticateAndRemember($credentials));
    }

    /** @test */
    public function it_can_set_the_user_instance_on_the_sentinel_class()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser();

        $sentinel->setUser($user);

        $this->assertInstanceOf(EloquentUser::class, $sentinel->getUser());
    }

    /** @test */
    public function it_can_get_the_checkpoint_status()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $sentinel->disableCheckpoints();

        $this->assertFalse($sentinel->checkpointsStatus());

        $sentinel->enableCheckpoints();

        $this->assertTrue($sentinel->checkpointsStatus());
    }

    /** @test */
    public function it_can_login_with_a_valid_user()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser();

        $persistences->shouldReceive('persist')->once();

        $dispatcher->shouldReceive('dispatch')->twice();

        $users->shouldReceive('recordLogin')->once();

        $this->assertInstanceOf(EloquentUser::class, $sentinel->login($user));
    }

    /** @test */
    public function it_will_not_login_with_an_invalid_user()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser();

        $persistences->shouldReceive('persist')->once();

        $dispatcher->shouldReceive('dispatch')->once();

        $users->shouldReceive('recordLogin')->once()->andReturn(false);

        $this->assertFalse($sentinel->login($user));
    }

    public function it_will_ensure_the_user_is_not_defined_when_logging_out()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser();

        $persistences->shouldReceive('persist')->once();
        $persistences->shouldReceive('forget')->once();

        $users->shouldReceive('recordLogin')->once();
        $users->shouldReceive('recordLogout')->once();

        $sentinel->login($user);
        $sentinel->logout($user);

        $this->assertNull($sentinel->getUser(false));
    }

    /** @test */
    public function it_can_logout_the_current_user()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser();

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->once()->andReturn($user);
        $persistences->shouldReceive('forget')->once();

        $users->shouldReceive('recordLogout')->once()->andReturn(true);

        $dispatcher->shouldReceive('dispatch')->twice();

        $this->assertTrue($sentinel->logout($user));
    }

    /** @test */
    public function it_can_logout_the_user_on_the_other_devices()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser();

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->once()->andReturn($user);
        $persistences->shouldReceive('flush')->once();

        $dispatcher->shouldReceive('dispatch')->twice();

        $users->shouldReceive('recordLogout')->once()->andReturn(true);

        $this->assertTrue($sentinel->logout($user, true));
    }

    /** @test */
    public function it_can_maintain_a_user_session_after_logging_out_another_user()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user        = new EloquentUser();
        $currentUser = new EloquentUser();

        $persistences->shouldReceive('persist')->once();
        $persistences->shouldReceive('flush')->once()->with($user, false);

        $dispatcher->shouldReceive('dispatch')->times(4);

        $users->shouldReceive('recordLogin')->once();

        $sentinel->login($currentUser);

        $sentinel->logout($user);

        $this->assertSame($currentUser, $sentinel->getUser(false));
    }

    /** @test */
    public function it_can_logout_an_invalid_user()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = null;

        $persistences->shouldReceive('check')->once();

        $dispatcher->shouldReceive('dispatch')->twice();

        $this->assertTrue($sentinel->logout($user, true));
    }

    /** @test */
    public function it_can_bypass_the_checkpoints()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = m::mock(EloquentUser::class);

        $checkpoint = new ActivationCheckpoint($activations);

        $sentinel->addCheckpoint('activation', $checkpoint);

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser());

        $sentinel->bypassCheckpoints(function () use ($sentinel) {
            $this->assertInstanceOf(EloquentUser::class, $sentinel->check());
        });
    }

    /** @test */
    public function it_can_create_a_basic_response()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $response = json_encode(['response']);

        $sentinel->creatingBasicResponse(function () use ($response) {
            return $response;
        });

        $this->assertSame($response, $sentinel->getBasicResponse());
    }

    /** @test */
    public function it_can_set_and_get_the_various_repositories()
    {
        list($sentinel) = $this->createSentinel();

        $sentinel->setPersistenceRepository(m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository'));
        $sentinel->setUserRepository(m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository'));
        $sentinel->setRoleRepository(m::mock('Cartalyst\Sentinel\Roles\IlluminateRoleRepository'));
        $sentinel->setActivationRepository(m::mock('Cartalyst\Sentinel\Activations\IlluminateActivationRepository'));
        $sentinel->setReminderRepository(m::mock('Cartalyst\Sentinel\Reminders\IlluminateReminderRepository'));
        $sentinel->setThrottleRepository(m::mock('Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository'));

        $this->assertInstanceOf('Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface', $sentinel->getPersistenceRepository());
        $this->assertInstanceOf('Cartalyst\Sentinel\Users\UserRepositoryInterface', $sentinel->getUserRepository());
        $this->assertInstanceOf('Cartalyst\Sentinel\Roles\RoleRepositoryInterface', $sentinel->getRoleRepository());
        $this->assertInstanceOf('Cartalyst\Sentinel\Activations\ActivationRepositoryInterface', $sentinel->getActivationRepository());
        $this->assertInstanceOf('Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface', $sentinel->getReminderRepository());
        $this->assertInstanceOf('Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface', $sentinel->getThrottleRepository());
    }

    /** @test */
    public function it_can_pass_method_calls_to_a_user_repository_directly()
    {
        list($sentinel, $persistences, $users) = $this->createSentinel();

        $users->shouldReceive('findById')->once()->andReturn(m::mock(EloquentUser::class));

        $user = $sentinel->findById(1);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_pass_method_calls_to_a_user_repository_via_findUserBy()
    {
        list($sentinel, $persistences, $users) = $this->createSentinel();

        $users->shouldReceive('findById')->once()->andReturn(m::mock(EloquentUser::class));

        $user = $sentinel->findUserById(1);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_pass_method_calls_to_a_role_repository_via_findRoleBy()
    {
        list($sentinel, $persistences, $users, $roles) = $this->createSentinel();

        $roles->shouldReceive('findById')->once()->andReturn(m::mock(EloquentRole::class));

        $user = $sentinel->findRoleById(1);

        $this->assertInstanceOf(EloquentRole::class, $user);
    }

    /** @test */
    public function it_can_pass_methods_via_the_user_repository_when_a_user_is_logged_in()
    {
        list($sentinel, $persistences, $users, $roles) = $this->createSentinel();

        $user = m::mock(EloquentUser::class);
        $user->shouldReceive('hasAccess')->andReturn(true);

        $persistences->shouldReceive('check')->andReturn(true);
        $persistences->shouldReceive('findUserByPersistenceCode')->andReturn($user);

        $this->assertTrue($sentinel->hasAccess());
    }

    /** @test */
    public function an_exception_will_be_thrown_when_activating_an_invalid_user()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No valid user was provided.');

        list($sentinel) = $this->createSentinel();

        $sentinel->activate(20.00);
    }

    /** @test */
    public function an_exception_will_be_thrown_when_logging_in_with_a_non_activated_user_while_using_the_activation_checkpoint()
    {
        $this->expectException(NotActivatedException::class);
        $this->expectExceptionMessage('Your account has not been activated yet.');

        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser();

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn($user);

        $activations->shouldReceive('completed')->once()->andReturn(false);

        $checkpoint = new ActivationCheckpoint($activations);

        $sentinel->addCheckpoint('activation', $checkpoint);

        $sentinel->check();
    }

    /** @test */
    public function an_exception_will_be_thrown_when_registering_with_an_invalid_closure()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must provide a closure or a boolean.');

        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $sentinel->register([
            'email' => 'foo@example.com',
        ], 'invalid_closure');
    }

    /** @test */
    public function an_exception_will_be_thrown_when_trying_to_get_the_basic_response()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Attempting basic auth after headers have already been sent.');

        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $sentinel->getBasicResponse();
    }

    /** @test */
    public function an_exception_will_be_thrown_when_calling_methods_which_are_only_available_when_a_user_is_logged_in()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method Cartalyst\Sentinel\Sentinel::getRoles() can only be called if a user is logged in.');

        list($sentinel, $persistences) = $this->createSentinel();
        $persistences->shouldReceive('check')->once()->andReturn(null);

        $sentinel->getRoles();
    }

    /** @test */
    public function an_exception_will_be_thrown_when_calling_invalid_methods()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method Cartalyst\Sentinel\Sentinel::methodThatDoesntExist()');

        list($sentinel) = $this->createSentinel();

        $sentinel->methodThatDoesntExist();
    }

    protected function createSentinel()
    {
        $sentinel = new Sentinel(
            $persistences = m::mock(IlluminatePersistenceRepository::class),
            $users = m::mock(IlluminateUserRepository::class),
            $roles = m::mock(IlluminateRoleRepository::class),
            $activations = m::mock(IlluminateActivationRepository::class),
            $dispatcher = m::mock(Dispatcher::class)
        );

        return [$sentinel, $persistences, $users, $roles, $activations, $dispatcher];
    }
}
