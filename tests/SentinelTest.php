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
 * @version    2.0.17
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2017, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\tests;

use Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Users\EloquentUser;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class SentinelTest extends PHPUnit_Framework_TestCase
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

    public function testValidRegister()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $users->shouldReceive('validForCreation')->once()->andReturn(true);
        $users->shouldReceive('create')->once();

        $method = method_exists($dispatcher, 'fire') ? 'fire' : 'dispatch';
        $dispatcher->shouldReceive($method)->twice();

        $sentinel->register([
            'email' => 'foo@example.com',
            'password' => 'secret',
        ]);
    }

    public function testRegisterInvalidUser()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $users->shouldReceive('validForCreation')->once()->andReturn(false);

        $method = method_exists($dispatcher, 'fire') ? 'fire' : 'dispatch';
        $dispatcher->shouldReceive($method)->once();

        $user = $sentinel->register([
            'email' => 'foo@example.com',
        ]);

        $this->assertFalse($user);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRegisterWithInvalidClosure()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $sentinel->register([
            'email' => 'foo@example.com',
        ], 'invalid_closure');
    }

    public function testRegisterAndActivate()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $users->shouldReceive('validForCreation')->once()->andReturn(true);
        $users->shouldReceive('create')->once()->andReturn(m::mock('Cartalyst\Sentinel\Users\EloquentUser'));

        $activations->shouldReceive('create')->once()->andReturn($activation = m::mock('Cartalyst\Sentinel\Activations\ActivationInterface'));
        $activations->shouldReceive('complete')->once()->andReturn(true);


        $activation->shouldReceive('getCode')->once()->andReturn('a_random_code');

        $method = method_exists($dispatcher, 'fire') ? 'fire' : 'dispatch';
        $dispatcher->shouldReceive($method)->times(4);

        $sentinel->registerAndActivate([
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ]);
    }

    public function testActivateUserByInstance()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser;

        $activations->shouldReceive('create')->once()->andReturn($activation = m::mock('Cartalyst\Sentinel\Activations\ActivationInterface'));
        $activations->shouldReceive('complete')->once()->andReturn(true);
        $activation->shouldReceive('getCode')->once()->andReturn('a_random_code');

        $method = method_exists($dispatcher, 'fire') ? 'fire' : 'dispatch';
        $dispatcher->shouldReceive($method)->twice();

        $sentinel->activate($user);
    }

    public function testActivateUserById()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser;

        $users->shouldReceive('findById')->with('1')->once()->andReturn(new EloquentUser);

        $activations->shouldReceive('create')->once()->andReturn($activation = m::mock('Cartalyst\Sentinel\Activations\ActivationInterface'));
        $activations->shouldReceive('complete')->once()->andReturn(true);
        $activation->shouldReceive('getCode')->once()->andReturn('a_random_code');

        $method = method_exists($dispatcher, 'fire') ? 'fire' : 'dispatch';
        $dispatcher->shouldReceive($method)->twice();

        $sentinel->activate('1');
    }

    public function testActivateUserByCredentials()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $credentials = [
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $users->shouldReceive('findByCredentials')->with($credentials)->once()->andReturn(new EloquentUser);

        $activations->shouldReceive('create')->once()->andReturn($activation = m::mock('Cartalyst\Sentinel\Activations\ActivationInterface'));
        $activations->shouldReceive('complete')->once()->andReturn(true);
        $activation->shouldReceive('getCode')->once()->andReturn('a_random_code');

        $method = method_exists($dispatcher, 'fire') ? 'fire' : 'dispatch';
        $dispatcher->shouldReceive($method)->twice();

        $sentinel->activate($credentials);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testActivateInvalidUserType()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $sentinel->activate(20.00);
    }

    public function testCheck1()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser);

        $sentinel->check();
    }

    public function testCheck2()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(false);

        $sentinel->check();
    }

    public function testCheck3()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $persistences->shouldReceive('check')->once();
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(false);

        $sentinel->check();
    }

    public function testCheck4()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser;

        $sentinel->setUser($user);

        $this->assertInstanceOf('Cartalyst\Sentinel\Users\EloquentUser', $sentinel->check());
    }

    public function testForceCheck()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $persistences->shouldReceive('check')->once();
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser);

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

        $user = new EloquentUser;

        $sentinel->setUser($user);

        $this->assertFalse($sentinel->guest());
    }

    public function testAuthenticate1()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser;

        $credentials = [
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $persistences->shouldReceive('persist')->once();

        $users->shouldReceive('findByCredentials')->with($credentials)->once()->andReturn($user);
        $users->shouldReceive('validateCredentials')->once()->andReturn(true);
        $users->shouldReceive('recordLogin')->once();

        $dispatcher->shouldReceive('until')->once();
        $method = method_exists($dispatcher, 'fire') ? 'fire' : 'dispatch';
        $dispatcher->shouldReceive($method)->once();

        $sentinel->authenticate($credentials);
    }

    public function testAuthenticate2()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser;

        $persistences->shouldReceive('persist')->once();

        $users->shouldReceive('recordLogin')->once();

        $dispatcher->shouldReceive('until')->once();
        $method = method_exists($dispatcher, 'fire') ? 'fire' : 'dispatch';
        $dispatcher->shouldReceive($method)->once();

        $sentinel->authenticate($user);
    }

    public function testAuthenticate3()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser;

        $credentials = [
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $users->shouldReceive('findByCredentials')->once();

        $dispatcher->shouldReceive('until')->once();

        $sentinel->authenticate($credentials);
    }

    public function testAuthenticateAndRemember()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser;

        $credentials = [
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $persistences->shouldReceive('persistAndRemember')->once();

        $users->shouldReceive('findByCredentials')->with($credentials)->once()->andReturn($user);
        $users->shouldReceive('validateCredentials')->once()->andReturn(true);
        $users->shouldReceive('recordLogin')->once();

        $dispatcher->shouldReceive('until')->once();
        $method = method_exists($dispatcher, 'fire') ? 'fire' : 'dispatch';
        $dispatcher->shouldReceive($method)->once();

        $sentinel->authenticateAndRemember($credentials);
    }

    public function testCheckpoints()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $sentinel->disableCheckpoints();

        $this->assertFalse($sentinel->checkpointsStatus());

        $sentinel->enableCheckpoints();

        $this->assertTrue($sentinel->checkpointsStatus());
    }

    public function testSetAndGetRepositories()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

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

    public function testLogin()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser;

        $persistences->shouldReceive('persist')->once();

        $users->shouldReceive('recordLogin')->once();

        $this->assertInstanceOf('Cartalyst\Sentinel\Users\EloquentUser', $sentinel->login($user));
    }

    public function testLoginFailure()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser;

        $persistences->shouldReceive('persist')->once();

        $users->shouldReceive('recordLogin')->once()->andReturn(false);

        $this->assertFalse($sentinel->login($user));
    }

    public function testLogout()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser;

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->once()->andReturn($user);
        $persistences->shouldReceive('forget')->once();

        $users->shouldReceive('recordLogout')->once();

        $sentinel->logout($user);
    }

    public function testLogoutEverywhere()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser;

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->once()->andReturn($user);
        $persistences->shouldReceive('flush')->once();

        $users->shouldReceive('recordLogout')->once();

        $sentinel->logout($user, true);
    }

    public function testUserIsNullAfterLogout()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = new EloquentUser;

        $persistences->shouldReceive('persist')->once();
        $persistences->shouldReceive('forget')->once();

        $users->shouldReceive('recordLogin')->once();
        $users->shouldReceive('recordLogout')->once();

        $sentinel->login($user);
        $sentinel->logout($user);

        $this->assertNull($sentinel->getUser(false));
    }

    public function testUserIsMaintainedAfterLoggingOutAnotherUser()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user        = new EloquentUser();
        $currentUser = new EloquentUser();

        $persistences->shouldReceive('persist')->once();
        $persistences->shouldReceive('flush')->once()->with($user, false);

        $users->shouldReceive('recordLogin')->once();

        $sentinel->login($currentUser);

        $sentinel->logout($user);

        $this->assertSame($currentUser, $sentinel->getUser(false));
    }

    public function testLogoutInvalidUser()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = null;

        $persistences->shouldReceive('check')->once();

        $sentinel->logout($user, true);
    }

    /**
     * @expectedException \Cartalyst\Sentinel\Checkpoints\NotActivatedException
     */
    public function testAddCheckpoint()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser);

        $activations->shouldReceive('completed')->once()->andReturn(false);

        $user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');

        $checkpoint = new ActivationCheckpoint($activations);

        $sentinel->addCheckpoint('activation', $checkpoint);

        $sentinel->check();
    }

    public function testBypassCheckpoints()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $user = m::mock('Cartalyst\Sentinel\Users\EloquentUser');

        $checkpoint = new ActivationCheckpoint($activations);

        $sentinel->addCheckpoint('activation', $checkpoint);

        $persistences->shouldReceive('check')->once()->andReturn('foobar');
        $persistences->shouldReceive('findUserByPersistenceCode')->with('foobar')->andReturn(new EloquentUser);

        $sentinel->bypassCheckpoints(function () use ($sentinel) {
            $this->assertInstanceOf('Cartalyst\Sentinel\Users\EloquentUser', $sentinel->check());
        });
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetBasicResponse()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $sentinel->getBasicResponse();
    }

    public function testCreateGetBasicResponse()
    {
        list($sentinel, $persistences, $users, $roles, $activations, $dispatcher) = $this->createSentinel();

        $response = json_encode(['response']);

        $sentinel->creatingBasicResponse(function () use ($response) {
            return $response;
        });

        $this->assertEquals($response, $sentinel->getBasicResponse());
    }

    protected function createSentinel()
    {
        $sentinel = new Sentinel(
            $persistences = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository'),
            $users        = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository'),
            $roles        = m::mock('Cartalyst\Sentinel\Roles\IlluminateRoleRepository'),
            $activations  = m::mock('Cartalyst\Sentinel\Activations\IlluminateActivationRepository'),
            $dispatcher   = m::mock('Illuminate\Contracts\Events\Dispatcher')
        );

        return [$sentinel, $persistences, $users, $roles, $activations, $dispatcher];
    }
}
