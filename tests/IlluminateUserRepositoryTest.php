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

use Cartalyst\Sentinel\Users\EloquentUser;
use Mockery as m;
use PHPUnit_Framework_TestCase;

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
        $users = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository[createModel,findById]', [
            $hasher = m::mock('Cartalyst\Sentinel\Hashing\NativeHasher'),
            null,
            'UserMock'
        ]);

        $this->assertEquals('UserMock', $users->getModel());
    }

    public function testFindById()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $query->shouldReceive('find')->with(1)->once()->andReturn($model);

        $users->findById(1);
    }

    public function testFindByCredentials1()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $query->shouldReceive('where')->once()->andReturn($model);
        $query->shouldReceive('first')->once()->andReturn($model);

        $users->findByCredentials([
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ]);
    }

    public function testFindByCredentials2()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $query->shouldReceive('whereNested')->once()->andReturn($model);
        $query->shouldReceive('first')->once()->andReturn($model);

        $users->findByCredentials([
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ]);
    }

    public function testFindByCredentials3()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $query->shouldReceive('whereNested')->once()->andReturn($model);
        $query->shouldReceive('first')->once()->andReturn($model);

        $users->findByCredentials([
            'login' => 'foo@example.com',
        ]);
    }

    public function testFindByCredentials4()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $user = $users->findByCredentials([
            'password' => 'secret',
        ]);

        $this->assertEquals(null, $user);
    }

    public function testFindByCredentials5()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $user = $users->findByCredentials([
            'username' => 'foo',
        ]);

        $this->assertEquals(null, $user);
    }

    public function testFindByCredentials6()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $this->assertNull($users->findByCredentials([]));
    }

    public function testFindByPersistenceCode()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $query->shouldReceive('whereHas')->once()->andReturn($model);
        $model->shouldReceive('first')->once()->andReturn($model);

        $users->findByPersistenceCode('foobar');
    }

    public function testRecordLogin()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $model->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId')->once();

        $users->recordLogin($model);
    }

    public function testRecordLogout()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $model->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId')->once();

        $users->recordLogout($model);
    }

    public function testValidateCredentials()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->password = 'secret';

        $hasher->shouldReceive('check')->with('secret', 'secret')->once()->andReturn(true);

        $valid = $users->validateCredentials($model, [
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertTrue($valid);
    }

    public function testValidateUserForCreation()
    {
        $user = new EloquentUser;

        list($users, $hasher, $model, $query) = $this->getUsersMock();

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
        $user = new EloquentUser;

        list($users, $hasher, $model, $query) = $this->getUsersMock();

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
        $user = new EloquentUser;

        list($users, $hasher, $model, $query) = $this->getUsersMock();

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
        $user = new EloquentUser;

        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $credetials = [
            'email'    => 'foo@example.com',
            'password' => null,
        ];

        $users->validForCreation($credetials);
    }

    public function testValidateUserForUpdate()
    {
        $user = $this->fakeUser();

        list($users, $hasher, $model, $query) = $this->getUsersMock();

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

        $model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $model->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId')->once();

        $hasher->shouldReceive('hash')->once()->with('secret')->andReturn(password_hash('secret', PASSWORD_DEFAULT));

        $user = $users->create([
            'email' => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf('Cartalyst\Sentinel\Users\EloquentUser', $user);
    }

    public function testCreateWithValidCallback()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $model->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId')->once();

        $hasher->shouldReceive('hash')->once()->with('secret')->andReturn(password_hash('secret', PASSWORD_DEFAULT));

        $user = $users->create([
            'email' => 'foo@example.com',
            'password' => 'secret',
        ], function ($user) {
            return true;
        });

        $this->assertInstanceOf('Cartalyst\Sentinel\Users\EloquentUser', $user);
    }

    public function testCreateWithInvalidCallback()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');

        $hasher->shouldReceive('hash')->once()->with('secret')->andReturn(password_hash('secret', PASSWORD_DEFAULT));

        $user = $users->create([
            'email' => 'foo@example.com',
            'password' => 'secret',
        ], function ($user) {
            return false;
        });

        $this->assertFalse($user);
    }

    public function testUpdate1()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));
        $resolver->shouldReceive('connection')->andReturn(m::mock('Illuminate\Database\Connection')->makePartial());
        $model->getConnection()->shouldReceive('getName');
        $model->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock('Illuminate\Database\Query\Grammars\Grammar'));
        $model->getConnection()->shouldReceive('getPostProcessor')->andReturn($processor = m::mock('Illuminate\Database\Query\Processors\Processor'));

        $model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $model->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $processor->shouldReceive('processInsertGetId');

        $user = $this->fakeUser();

        $updated = $users->update($user, [
            'email' => 'foo1@example.com',
        ]);

        $this->assertInstanceOf('Cartalyst\Sentinel\Users\EloquentUser', $user);
    }

    public function testUpdate2()
    {
        $users = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository[createModel,findById]', [
            $hasher = m::mock('Cartalyst\Sentinel\Hashing\NativeHasher')
        ]);

        $users->shouldReceive('findById')->once()->andReturn($user = $this->fakeUser());
        $users->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Users\EloquentUser[newQuery]'));

        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

        $updated = $users->update(1, [
            'email' => 'foo1@example.com',
        ]);

        $this->assertInstanceOf('Cartalyst\Sentinel\Users\EloquentUser', $user);
    }

    public function testHasherSetterAndGetter()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $this->assertInstanceOf('Cartalyst\Sentinel\Hashing\NativeHasher', $users->getHasher());

        $users->setHasher(m::mock('Cartalyst\Sentinel\Hashing\HasherInterface'));

        $this->assertInstanceOf('Cartalyst\Sentinel\Hashing\HasherInterface', $users->getHasher());
    }

    protected function fakeUser()
    {
        $user = new EloquentUser;

        $user->password = 'foobar';
        $user->email = 'foo@example.com';

        return $user;
    }

    protected function getUsersMock()
    {
        $users = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository[createModel]', [
            $hasher = m::mock('Cartalyst\Sentinel\Hashing\NativeHasher')
        ]);

        $users->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Users\EloquentUser[newQuery]'));

        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

        return [$users, $hasher, $model, $query];
    }
}
