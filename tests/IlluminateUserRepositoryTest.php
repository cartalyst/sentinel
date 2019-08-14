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

use Cartalyst\Sentinel\Users\EloquentUser;
use PHPUnit_Framework_TestCase;
use Mockery as m;

class IlluminateUserRepositoryTest extends PHPUnit_Framework_TestCase
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

    public function testConstructor()
    {
        $users = m::mock(
            'Cartalyst\Sentinel\Users\IlluminateUserRepository[createModel]',
            [
                $hasher = m::mock('Cartalyst\Sentinel\Hashing\NativeHasher'),
                null,
                'UserMock'
            ]
        );

        $this->assertEquals('UserMock', $users->getModel());
    }

    public function testFindById()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $query->shouldReceive('find')->with(1)->once()->andReturn($model);

        $user = $users->findById(1);
        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Users\UserInterface',
            $user
        );
    }

    public function testFindByCredentials1()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);
        $query->shouldReceive('where')
              ->with('email', 'foo@example.com')
              ->once()
              ->andReturn($model);
        $query->shouldReceive('first')->once()->andReturn($model);

        $user = $users->findByCredentials([
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Users\UserInterface',
            $user
        );
    }

    public function testFindByCredentials2()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);
        $query->shouldReceive('whereNested')
              ->once()
              ->andReturn($model);
        $query->shouldReceive('first')->once()->andReturn($model);

        $user = $users->findByCredentials([
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Users\UserInterface',
            $user
        );
    }

    public function testFindByCredentials3()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);
        $query->shouldReceive('whereNested')
              ->once()
              ->andReturn($model);
        $query->shouldReceive('first')->once()->andReturn($model);

        $user = $users->findByCredentials([
            'login' => 'foo@example.com',
        ]);

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Users\UserInterface',
            $user
        );
    }

    public function testFindByCredentials4()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);
        $user = $users->findByCredentials([
            'password' => 'secret',
        ]);

        $this->assertEquals(null, $user);
    }

    public function testFindByCredentials5()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);
        $user = $users->findByCredentials([
            'username' => 'foo',
        ]);

        $this->assertEquals(null, $user);
    }

    public function testFindByCredentials6()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $this->assertNull($users->findByCredentials([]));
    }

    public function testFindByPersistenceCode()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $query->shouldReceive('whereHas')->once()->andReturn($model);
        $model->shouldReceive('first')->once()->andReturn($model);

        $user = $users->findByPersistenceCode('foobar');

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Users\UserInterface',
            $user
        );
    }

    public function testRecordLogin()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('setAttribute');
        $model->shouldReceive('save')->andReturn(true);

        $user = $users->recordLogin($model);

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Users\UserInterface',
            $user
        );
    }

    public function testRecordLogout()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('save')->once()->andReturn(true);

        $user = $users->recordLogout($model);

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Users\UserInterface',
            $user
        );
    }

    public function testValidateCredentials()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getAttribute')->andReturn('secret');
        $hasher->shouldReceive('check')
               ->with('secret', 'secret')
               ->once()
               ->andReturn(true);

        $valid = $users->validateCredentials($model, [
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertTrue($valid);
    }

    public function testValidateUserForCreation()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $credetials = [
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $valid = $users->validForCreation($credetials);

        $this->assertTrue($valid);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateUserForCreationWithoutLogin()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $credetials = [
            'password' => 'secret',
        ];

        $users->validForCreation($credetials);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateUserForCreationWithoutPassword()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $credetials = [
            'email' => 'foo@example.com',
        ];

        $users->validForCreation($credetials);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateUserForCreationWithEmptyPassword()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $credetials = [
            'email'    => 'foo@example.com',
            'password' => null,
        ];

        $users->validForCreation($credetials);
    }

    public function testValidateUserForUpdate()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $user = $this->fakeUser();
        $user->shouldReceive('getUserId');

        $credetials = [
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $valid = $users->validForUpdate($user, $credetials);

        $this->assertTrue($valid);
    }

    public function testCreate()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);
        $model->shouldReceive('fill');
        $model->shouldReceive('save');
        $hasher->shouldReceive('hash')
               ->once()
               ->with('secret')
               ->andReturn(password_hash('secret', PASSWORD_DEFAULT));

        $user = $users->create([
            'email' => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Users\UserInterface',
            $user
        );
    }

    public function testCreateWithValidCallback()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);
        $model->shouldReceive('fill');
        $model->shouldReceive('save');

        $hasher->shouldReceive('hash')->once()->with('secret')->andReturn(password_hash('secret', PASSWORD_DEFAULT));

        $user = $users->create([
            'email' => 'foo@example.com',
            'password' => 'secret',
        ], function ($user) {
            return true;
        });

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Users\UserInterface',
            $user
        );
    }

    public function testCreateWithInvalidCallback()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);
        $model->shouldReceive('fill');
        $model->shouldReceive('save');

        $hasher->shouldReceive('hash')->once()->with('secret')->andReturn(password_hash('secret', PASSWORD_DEFAULT));

        $credentials = [
            'email' => 'foo@example.com',
            'password' => 'secret',
        ];

        $user = $users->create($credentials, function ($user) {
            return false;
        });

        $this->assertFalse($user);
    }

    public function testUpdate1()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $user = $this->fakeUser();
        $user->shouldReceive('getLoginNames')->andReturn(['email']);
        $user->shouldReceive('fill');
        $user->shouldReceive('save');

        $updated = $users->update($user, [
            'email' => 'foo1@example.com',
        ]);

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Users\UserInterface',
            $user
        );
    }

    public function testUpdate2()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $query->shouldReceive('find')->andReturn($user = $this->fakeUser());

        $user->shouldReceive('getLoginNames')->andReturn(['email']);
        $user->shouldReceive('fill');
        $user->shouldReceive('save');

        $updated = $users->update(1, [
            'email' => 'foo1@example.com',
        ]);

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Users\UserInterface',
            $user
        );
    }

    public function testHasherSetterAndGetter()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Hashing\NativeHasher',
            $users->getHasher()
        );

        $users->setHasher(m::mock('Cartalyst\Sentinel\Hashing\HasherInterface'));

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Hashing\HasherInterface',
            $users->getHasher()
        );
    }

    protected function fakeUser()
    {
        return m::mock('Cartalyst\Sentinel\Users\EloquentUser');
    }

    protected function getUsersMock()
    {
        $hasher = m::mock('Cartalyst\Sentinel\Hashing\NativeHasher');
        $model = m::mock('Cartalyst\Sentinel\Users\EloquentUser');
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $users = m::mock(
            'Cartalyst\Sentinel\Users\IlluminateUserRepository[createModel]',
            [$hasher]
        );

        $users->shouldReceive('createModel')->andReturn($model);
        $model->shouldReceive('newQuery')->andReturn($query);

        return [$users, $hasher, $model, $query];
    }

}
