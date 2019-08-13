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
 * @version    2.0.17
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2017, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Tests\Users;

use Mockery as m;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Cartalyst\Sentinel\Users\EloquentUser;
use Cartalyst\Sentinel\Hashing\NativeHasher;
use Cartalyst\Sentinel\Hashing\HasherInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\ConnectionResolverInterface;

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
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $query->shouldReceive('find')->with(1)->once()->andReturn($model);

        $user = $users->findById(1);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_find_by_a_user_by_its_credentials_1()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

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
    public function it_can_find_by_a_user_by_its_credentials_2()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $query->shouldReceive('whereNested')->once()->andReturn($model);
        $query->shouldReceive('first')->once()->andReturn($model);

        $user = $users->findByCredentials([
            'login'    => 'foo@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_find_by_a_user_by_its_credentials_3()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $query->shouldReceive('whereNested')->once()->andReturn($model);
        $query->shouldReceive('first')->once()->andReturn($model);

        $user = $users->findByCredentials([
            'login' => 'foo@example.com',
        ]);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_find_by_a_user_by_its_credentials_4()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $user = $users->findByCredentials([
            'password' => 'secret',
        ]);

        $this->assertNull($user);
    }

    /** @test */
    public function it_can_find_by_a_user_by_its_credentials_5()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $user = $users->findByCredentials([
            'username' => 'foo',
        ]);

        $this->assertNull($user);
    }

    /** @test */
    public function it_can_find_by_a_user_by_its_credentials_6()
    {
        list($users, $hasher, $model) = $this->getUsersMock();

        $model->shouldReceive('getLoginNames')->andReturn(['email']);

        $this->assertNull($users->findByCredentials([]));
    }

    /** @test */
    public function it_can_find_a_user_by_its_persistence_code()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $query->shouldReceive('whereHas')->once()->andReturn($model);
        $model->shouldReceive('first')->once()->andReturn($model);

        $user = $users->findByPersistenceCode('foobar');

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_record_the_login()
    {
        list($users, $hasher, $model, $query) = $this->getUsersMock();

        $this->addMockConnection($model);
        $model->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $model->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId')->once();

        $user = $users->recordLogin($model);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_record_the_logout()
    {
        list($users, $hasher, $model) = $this->getUsersMock();

        $this->addMockConnection($model);
        $model->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $model->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId')->once();

        $user = $users->recordLogout($model);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_validate_the_credentials()
    {
        list($users, $hasher, $model) = $this->getUsersMock();

        $model->password = 'secret';

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

        list($users) = $this->getUsersMock();

        $credetials = [
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $valid = $users->validForCreation($credetials);

        $this->assertTrue($valid);
    }

    /** @test */
    public function it_can_check_if_the_user_is_valid_for_being_updated()
    {
        $user = $this->fakeUser();

        list($users) = $this->getUsersMock();

        $credetials = [
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ];

        $valid = $users->validForUpdate($user, $credetials);

        $this->assertTrue($valid);
    }

    /** @test */
    public function it_can_create_a_user()
    {
        list($users, $hasher, $model) = $this->getUsersMock();

        $this->addMockConnection($model);
        $model->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $model->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId')->once();

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
        list($users, $hasher, $model) = $this->getUsersMock();

        $this->addMockConnection($model);
        $model->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $model->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId')->once();

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
        list($users, $hasher, $model) = $this->getUsersMock();

        $this->addMockConnection($model);
        $model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');

        $hasher->shouldReceive('hash')->once()->with('secret')->andReturn(password_hash('secret', PASSWORD_DEFAULT));

        $user = $users->create([
            'email'    => 'foo@example.com',
            'password' => 'secret',
        ], function ($user) {
            return false;
        });

        $this->assertFalse($user);
    }

    /** @test */
    public function it_can_update_a_user_1()
    {
        list($users, $hasher, $model) = $this->getUsersMock();

        $this->addMockConnection($model);
        $model->getConnection()->shouldReceive('getName');
        $model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $model->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $model->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId');

        $user = $this->fakeUser();

        $user = $users->update($user, [
            'email' => 'foo1@example.com',
        ]);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_update_a_user_2()
    {
        $hasher = m::mock(NativeHasher::class);

        $users = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository[createModel,findById]', [$hasher]);

        $model = m::mock('Cartalyst\Sentinel\Users\EloquentUser[newQuery]');
        $this->addMockConnection($model);
        $model->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $model->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $model->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId')->once();
        $model->shouldReceive('newQuery')->andReturn($query = m::mock(Builder::class));

        $users->shouldReceive('findById')->once()->andReturn($user = $this->fakeUser());
        $users->shouldReceive('createModel')->andReturn($model);

        $user = $users->update(1, [
            'email' => 'foo1@example.com',
        ]);

        $this->assertInstanceOf(EloquentUser::class, $user);
    }

    /** @test */
    public function it_can_set_and_get_the_hashing_strategy()
    {
        list($users) = $this->getUsersMock();

        $this->assertInstanceOf(NativeHasher::class, $users->getHasher());

        $users->setHasher(m::mock(HasherInterface::class));

        $this->assertInstanceOf(HasherInterface::class, $users->getHasher());
    }

    /** @test */
    public function an_exception_will_be_thrown_when_checking_if_the_user_is_valid_for_being_created_without_a_login()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No [login] credential was passed.');

        list($users) = $this->getUsersMock();

        $credetials = [
            'password' => 'secret',
        ];

        $users->validForCreation($credetials);
    }

    /** @test */
    public function an_exception_will_be_thrown_when_checking_if_the_user_is_valid_for_being_created_without_a_password()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have not passed a [password].');

        list($users) = $this->getUsersMock();

        $credetials = [
            'email' => 'foo@example.com',
        ];

        $users->validForCreation($credetials);
    }

    /** @test */
    public function an_exception_will_be_thrown_when_checking_if_the_user_is_valid_for_being_created_with_an_empty_password()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have not passed a [password].');

        list($users) = $this->getUsersMock();

        $credetials = [
            'email'    => 'foo@example.com',
            'password' => null,
        ];

        $users->validForCreation($credetials);
    }

    protected function fakeUser()
    {
        $user = new EloquentUser();

        $user->password = 'foobar';
        $user->email    = 'foo@example.com';

        return $user;
    }

    protected function getUsersMock()
    {
        $users = m::mock('Cartalyst\Sentinel\Users\IlluminateUserRepository[createModel]', [
            $hasher = m::mock(NativeHasher::class),
        ]);

        $users->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Users\EloquentUser[newQuery]'));

        $model->shouldReceive('newQuery')->andReturn($query = m::mock(Builder::class));

        return [$users, $hasher, $model, $query];
    }

    protected function addMockConnection($model)
    {
        $resolver = m::mock(ConnectionResolverInterface::class);
        $resolver->shouldReceive('connection')->andReturn(m::mock(Connection::class)->makePartial());

        $model->setConnectionResolver($resolver);
        $model->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock(Grammar::class));
        $model->getConnection()->shouldReceive('getPostProcessor')->andReturn(m::mock(Processor::class));
    }
}
