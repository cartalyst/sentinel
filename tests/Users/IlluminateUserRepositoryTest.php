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

namespace Cartalyst\Sentinel\Tests\Users;

use Mockery as m;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Cartalyst\Sentinel\Users\EloquentUser;
use Cartalyst\Sentinel\Hashing\NativeHasher;
use Cartalyst\Sentinel\Hashing\HasherInterface;

class IlluminateUserRepositoryTest extends TestCase
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
        $hasher = m::mock(NativeHasher::class);

        $users = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository[createModel,findById]', [
            $hasher, null, 'UserMock',
        ]);

        $this->assertSame('UserMock', $users->getModel());
    }

    /** @test */
    public function it_can_find_a_user_by_its_id()
    {
        [$users, $hasher, $model, $query] = $this->getUsersMock();

        $query->shouldReceive('find')->with(1)->once()->andReturn($model);

        $user = $users->findById(1);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_find_a_user_by_its_credentials_1()
    {
        [$users, $hasher, $model, $query] = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $query->shouldReceive('where')->with('email', 'foo@example.com')->once()->andReturn($model);
        $query->shouldReceive('first')->once()->andReturn($model);

        $user = $users->findByCredentials([
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_find_a_user_by_its_credentials_2()
    {
        [$users, $hasher, $model, $query] = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email', 'username']);

        $query->shouldReceive('whereNested')->with(m::on(function ($argument) use ($query) {
            $query->shouldReceive('where')->with('email', 'foo@example.com');
            $query->shouldReceive('orWhere')->with('username', 'foo@example.com');

            return null === $argument($query);
        }))->andReturn($model);
        $query->shouldReceive('first')->once()->andReturn($model);

        $user = $users->findByCredentials([
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_find_a_user_by_its_credentials_3()
    {
        [$users, $hasher, $model, $query] = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $query->shouldReceive('whereNested')->with(m::on(function ($argument) use ($query) {
            $query->shouldReceive('where')->with('email', 'foo@example.com');

            return null === $argument($query);
        }))->andReturn($model);
        $query->shouldReceive('first')->once()->andReturn($model);

        $user = $users->findByCredentials([
            'login' => 'foo@example.com',
        ]);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_cannot_find_a_user_by_invalid_credentials_1()
    {
        [$users, $hasher, $model] = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $user = $users->findByCredentials([
            'password' => 'secret',
        ]);

        $this->assertNull($user);
    }

    /** @test */
    public function it_cannot_find_a_user_by_invalid_credentials_2()
    {
        [$users, $hasher, $model] = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $user = $users->findByCredentials([
            'username' => 'foo',
        ]);

        $this->assertNull($user);
    }

    /** @test */
    public function it_cannot_find_a_user_by_invalid_credentials_3()
    {
        [$users, $hasher, $model] = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $this->assertNull($users->findByCredentials([]));
    }

    /** @test */
    public function it_can_find_a_user_by_its_persistence_code()
    {
        [$users, $hasher, $model, $query] = $this->getUsersMock();

        $query->shouldReceive('whereHas')->with('persistences', m::on(function ($argument) use ($query) {
            $query->shouldReceive('where')->with('code', 'foobar');

            return null === $argument($query);
        }))->andReturn($model);
        $model->shouldReceive('first')->once()->andReturn($model);

        $user = $users->findByPersistenceCode('foobar');

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_record_the_login()
    {
        [$users, $hasher, $model] = $this->getUsersMock();

        $model->shouldReceive('setAttribute');
        $model->shouldReceive('save')->once()->andReturn(true);

        $this->assertTrue($users->recordLogin($model));
    }

    /** @test */
    public function it_can_record_the_logout()
    {
        [$users, $hasher, $model] = $this->getUsersMock();

        $model->shouldReceive('save')->once()->andReturn(true);

        $this->assertTrue($users->recordLogout($model));
    }

    /** @test */
    public function it_can_validate_the_credentials()
    {
        [$users, $hasher, $model] = $this->getUsersMock();

        $model->shouldReceive('getAttribute')->andReturn('secret');

        $hasher->shouldReceive('check')->with('secret', 'secret')->once()->andReturn(true);

        $valid = $users->validateCredentials($model, [
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertTrue($valid);
    }

    /** @test */
    public function it_can_check_if_the_user_is_valid_for_being_created()
    {
        $user = new EloquentUser();

        [$users, $hasher, $model] = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $credentials = [
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $valid = $users->validForCreation($credentials);

        $this->assertTrue($valid);
    }

    /** @test */
    public function it_can_check_if_the_user_is_valid_for_being_updated()
    {
        $user = $this->fakeUser();

        [$users, $hasher, $model] = $this->getUsersMock();

        $user->shouldReceive('getUserId')->once()->andReturn(1);

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $credentials = [
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $valid = $users->validForUpdate($user, $credentials);

        $this->assertTrue($valid);
    }

    /** @test */
    public function it_can_create_a_user_using_login()
    {
        [$users, $hasher, $model] = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);
        $model->shouldReceive('fill');
        $model->shouldReceive('save')->once();

        $hasher->shouldReceive('hash')->once()->with('secret')->andReturn(password_hash('secret', PASSWORD_DEFAULT));

        $user = $users->create([
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_create_a_user()
    {
        [$users, $hasher, $model] = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);
        $model->shouldReceive('fill');
        $model->shouldReceive('save')->once();

        $hasher->shouldReceive('hash')->once()->with('secret')->andReturn(password_hash('secret', PASSWORD_DEFAULT));

        $user = $users->create([
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_create_a_user_with_valid_callback()
    {
        [$users, $hasher, $model] = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);
        $model->shouldReceive('fill');
        $model->shouldReceive('save')->once();

        $hasher->shouldReceive('hash')->once()->with('secret')->andReturn(password_hash('secret', PASSWORD_DEFAULT));

        $user = $users->create([
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ], function ($user) {
            return true;
        });

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_will_not_create_a_user_with_an_invalid_callback()
    {
        [$users, $hasher, $model] = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);
        $model->shouldReceive('fill');

        $hasher->shouldReceive('hash')->once()->with('secret')->andReturn(password_hash('secret', PASSWORD_DEFAULT));

        $user = $users->create([
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ], function ($user) {
            return false;
        });

        $this->assertNull($user);
    }

    /** @test */
    public function it_can_update_a_user_by_instance()
    {
        [$users, $hasher, $model] = $this->getUsersMock();

        $user = $this->fakeUser();
        $user->shouldReceive('getLoginNames')->andReturn(['email']);
        $user->shouldReceive('fill');
        $user->shouldReceive('save')->once();

        $user = $users->update($user, [
            'email' => 'foo1@example.com',
        ]);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_update_a_user_by_id()
    {
        [$users, $hasher, $model, $query] = $this->getUsersMock();

        $user = $this->fakeUser();
        $user->shouldReceive('getLoginNames')->andReturn(['email']);
        $user->shouldReceive('fill');
        $user->shouldReceive('save')->once();

        $query->shouldReceive('find')->once()->with(1)->andReturn($user);

        $user = $users->update(1, [
            'email' => 'foo1@example.com',
        ]);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_set_and_get_the_hashing_strategy()
    {
        [$users] = $this->getUsersMock();

        $this->assertInstanceOf(NativeHasher::class, $users->getHasher());

        $users->setHasher(m::mock(HasherInterface::class));

        $this->assertInstanceOf(HasherInterface::class, $users->getHasher());
    }

    /** @test */
    public function an_exception_will_be_thrown_when_checking_if_the_user_is_valid_for_being_created_without_a_login()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No [login] credential was passed.');

        [$users, $hasher, $model] = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $credentials = [
            'password' => 'secret',
        ];

        $users->validForCreation($credentials);
    }

    /** @test */
    public function an_exception_will_be_thrown_when_checking_if_the_user_is_valid_for_being_created_without_a_password()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have not passed a [password].');

        [$users, $hasher, $model] = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $credentials = [
            'email' => 'foo@example.com',
        ];

        $users->validForCreation($credentials);
    }

    /** @test */
    public function an_exception_will_be_thrown_when_checking_if_the_user_is_valid_for_being_created_with_an_empty_password()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have not passed a [password].');

        [$users, $hasher, $model] = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $credentials = [
            'email'    => 'foo@example.com',
            'password' => null,
        ];

        $users->validForCreation($credentials);
    }

    protected function fakeUser()
    {
        return m::mock('Cartalyst\Sentinel\Users\EloquentUser');
    }

    protected function getUsersMock()
    {
        $hasher = m::mock(NativeHasher::class);

        $query = m::mock(Builder::class);

        $model = m::mock('Cartalyst\Sentinel\Users\EloquentUser');
        $model->shouldReceive('newQuery')->andReturn($query);

        $users = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository[createModel]', [
            $hasher,
        ]);
        $users->shouldReceive('createModel')->andReturn($model);

        return [$users, $hasher, $model, $query];
    }
}
