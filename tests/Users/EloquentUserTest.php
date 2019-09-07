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
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Cartalyst\Sentinel\Users\EloquentUser;
use Cartalyst\Sentinel\Roles\RoleInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EloquentUserTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_can_get_the_user_permissions_from_the_accessor()
    {
        $user       = new EloquentUser();
        $user->slug = 'foo';

        $permissions = ['foo' => true];

        $user->permissions = $permissions;

        $this->assertSame($permissions, $user->permissions);
    }

    /** @test */
    public function it_can_set_and_get_the_persistable_key()
    {
        $user = new EloquentUser();
        $user->setPersistableKey('foo_id');

        $this->assertSame('foo_id', $user->getPersistableKey());
    }

    /** @test */
    public function it_can_set_and_get_the_persistable_relationship()
    {
        $user = new EloquentUser();

        $user->setPersistableRelationship('foo_persistences');

        $this->assertSame('foo_persistences', $user->getPersistableRelationship());
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_can_set_and_get_the_roles_model()
    {
        $user = new EloquentUser();

        $user->setRolesModel('RoleMock');

        $this->assertSame('RoleMock', $user->getRolesModel());
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_can_set_and_get_the_persistences_model()
    {
        $user = new EloquentUser();

        $user->setPersistencesModel('PersistenceMock');

        $this->assertSame('PersistenceMock', $user->getPersistencesModel());
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_can_set_and_get_the_activations_model()
    {
        $user = new EloquentUser();

        $user->setActivationsModel('ActivationMock');

        $this->assertSame('ActivationMock', $user->getActivationsModel());
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_can_set_and_get_the_reminders_model()
    {
        $user = new EloquentUser();

        $user->setRemindersModel('ReminderMock');

        $this->assertSame('ReminderMock', $user->getRemindersModel());
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_can_set_and_get_the_throttling_model()
    {
        $user = new EloquentUser();

        $user->setThrottlingModel('ThrottleMock');

        $this->assertSame('ThrottleMock', $user->getThrottlingModel());
    }

    /** @test */
    public function it_can_get_the_user_id()
    {
        $user = new EloquentUser();

        $this->addMockConnection($user);

        $user->getConnection()->shouldReceive('getName');
        $user->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $user->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $user->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId')->andReturn(1);

        $user->save();

        $this->assertSame(1, $user->getUserId());
    }

    /** @test */
    public function it_can_get_the_persistable_id()
    {
        $user = new EloquentUser();

        $this->addMockConnection($user);

        $user->getConnection()->shouldReceive('getName');
        $user->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $user->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $user->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId')->andReturn(1);

        $user->save();

        $this->assertSame('1', $user->getPersistableId());
    }

    /** @test */
    public function it_can_get_the_user_login()
    {
        $user        = new EloquentUser();
        $user->email = 'foo@example.com';

        $this->addMockConnection($user);

        $user->getConnection()->shouldReceive('getName');
        $user->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $user->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $user->getConnection()->getPostProcessor()->shouldReceive('processInsertGetId')->andReturn(1);

        $user->save();

        $this->assertSame('foo@example.com', $user->getUserLogin());
    }

    /** @test */
    public function it_can_get_the_user_login_names()
    {
        $user = new EloquentUser();

        $this->assertSame(['email'], $user->getLoginNames());
    }

    /** @test */
    public function it_can_get_the_user_login_name()
    {
        $user = new EloquentUser();

        $this->assertSame('email', $user->getUserLoginName());
    }

    /** @test */
    public function it_can_get_the_user_password()
    {
        $user           = new EloquentUser();
        $user->password = 'foobar';

        $this->assertSame('foobar', $user->getUserPassword());
    }

    /** @test */
    public function it_can_generate_a_persistence_code()
    {
        $user = new EloquentUser();

        $this->assertSame(32, strlen($user->generatePersistenceCode()));
    }

    /** @test */
    public function it_can_get_the_roles_relationship()
    {
        $user = new EloquentUser();

        $this->addMockConnection($user);

        $this->assertInstanceOf(BelongsToMany::class, $user->roles());
    }

    /** @test */
    public function it_can_get_the_persistences_relationship()
    {
        $user = new EloquentUser();

        $this->addMockConnection($user);

        $this->assertInstanceOf(HasMany::class, $user->persistences());
    }

    /** @test */
    public function it_can_get_the_reminders_relationship()
    {
        $user = new EloquentUser();

        $this->addMockConnection($user);

        $this->assertInstanceOf(HasMany::class, $user->reminders());
    }

    /** @test */
    public function it_can_get_the_activations_relationship()
    {
        $user = new EloquentUser();

        $this->addMockConnection($user);

        $this->assertInstanceOf(HasMany::class, $user->activations());
    }

    /** @test */
    public function it_can_get_the_throttles_relationship()
    {
        $user = new EloquentUser();

        $this->addMockConnection($user);

        $this->assertInstanceOf(HasMany::class, $user->throttle());
    }

    /** @test */
    public function it_can_check_if_user_is_in_role_using_role_slugs()
    {
        $user     = new EloquentUser();
        $user->id = 0;

        $this->addMockConnection($user);

        $user->getConnection()->shouldReceive('getName');
        $user->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $user->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $user->getConnection()->getPostProcessor()->shouldReceive('processSelect')->andReturn([
            ['id' => 1, 'slug' => 'role1'],
            ['id' => 2, 'slug' => 'role2'],
            ['id' => 3, 'slug' => 'role3'],
        ]);

        $user->getConnection()->getQueryGrammar()->shouldReceive('compileSelect');
        $user->getConnection()->shouldReceive('select');

        $this->assertTrue($user->inRole('role1'));
        $this->assertTrue($user->inRole('role2'));
        $this->assertTrue($user->inRole('role3'));
        $this->assertFalse($user->inRole('role4'));
    }

    /** @test */
    public function it_can_check_if_user_is_in_role_using_an_array_of_role_slugs()
    {
        $user     = new EloquentUser();
        $user->id = 0;

        $this->addMockConnection($user);

        $user->getConnection()->shouldReceive('getName');
        $user->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $user->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $user->getConnection()->getPostProcessor()->shouldReceive('processSelect')->andReturn([
            ['id' => 1, 'slug' => 'role1'],
            ['id' => 2, 'slug' => 'role2'],
            ['id' => 3, 'slug' => 'role3'],
        ]);

        $user->getConnection()->getQueryGrammar()->shouldReceive('compileSelect');
        $user->getConnection()->shouldReceive('select');

        $this->assertTrue($user->inAnyRole(['role1', 'role2']));
        $this->assertTrue($user->inAnyRole(['role3', 'role4']));
        $this->assertFalse($user->inAnyRole(['role5', 'role6']));
    }

    /** @test */
    public function it_can_check_if_user_is_in_role_using_role_instances()
    {
        $user     = new EloquentUser();
        $user->id = 0;

        $this->addMockConnection($user);
        $user->getConnection()->shouldReceive('getName');
        $user->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $user->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $user->getConnection()->getPostProcessor()->shouldReceive('processSelect')->andReturn([
            ['id' => 1, 'slug' => 'role1'],
            ['id' => 2, 'slug' => 'role2'],
            ['id' => 3, 'slug' => 'role3'],
        ]);

        $user->getConnection()->getQueryGrammar()->shouldReceive('compileSelect');
        $user->getConnection()->shouldReceive('select');

        $role1 = m::mock(RoleInterface::class);
        $role2 = m::mock(RoleInterface::class);
        $role3 = m::mock(RoleInterface::class);
        $role4 = m::mock(RoleInterface::class);

        $role1->shouldReceive('getRoleId')->once()->andReturn(1);
        $role2->shouldReceive('getRoleId')->once()->andReturn(2);
        $role3->shouldReceive('getRoleId')->once()->andReturn(3);
        $role4->shouldReceive('getRoleId')->once()->andReturn(4);

        $this->assertTrue($user->inRole($role1));
        $this->assertTrue($user->inRole($role2));
        $this->assertTrue($user->inRole($role3));
        $this->assertFalse($user->inRole($role4));
    }

    /** @test */
    public function it_can_check_if_user_is_in_role_using_an_array_of_role_instances()
    {
        $user     = new EloquentUser();
        $user->id = 0;

        $this->addMockConnection($user);

        $user->getConnection()->shouldReceive('getName');
        $user->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $user->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $user->getConnection()->getPostProcessor()->shouldReceive('processSelect')->andReturn([
            ['id' => 1, 'slug' => 'foobar'],
            ['id' => 2, 'slug' => 'foo'],
            ['id' => 3, 'slug' => 'bar'],
        ]);
        $user->getConnection()->getQueryGrammar()->shouldReceive('compileSelect');
        $user->getConnection()->shouldReceive('select');

        $foobar = m::mock(RoleInterface::class);
        $foo    = m::mock(RoleInterface::class);
        $bar    = m::mock(RoleInterface::class);
        $baz    = m::mock(RoleInterface::class);
        $abc    = m::mock(RoleInterface::class);
        $def    = m::mock(RoleInterface::class);
        $ghi    = m::mock(RoleInterface::class);

        $foobar->shouldReceive('getRoleId')->once()->andReturn(1);
        $foo->shouldReceive('getRoleId')->once()->andReturn(2);
        $bar->shouldNotReceive('getRoleId');
        $abc->shouldReceive('getRoleId')->once()->andReturn(4);
        $def->shouldReceive('getRoleId')->once()->andReturn(5);
        $ghi->shouldReceive('getRoleId')->once()->andReturn(6);

        $this->assertFalse($user->inAnyRole([$abc, $def]));
        $this->assertTrue($user->inAnyRole([$ghi, $foobar]));
        $this->assertTrue($user->inAnyRole([$foo, $bar]));
    }

    /** @test */
    public function it_can_get_the_roles_of_a_user()
    {
        $user     = new EloquentUser();
        $user->id = 0;

        $this->addMockConnection($user);

        $user->getConnection()->shouldReceive('getName');
        $user->getConnection()->shouldReceive('select');
        $user->getConnection()->getQueryGrammar()->shouldReceive('compileSelect');
        $user->getConnection()->getPostProcessor()->shouldReceive('processSelect')->andReturn([]);

        $this->assertInstanceOf(Collection::class, $user->getRoles());
    }

    /** @test */
    public function it_can_pass_methods_to_parent()
    {
        $user = new EloquentUser();

        $this->addMockConnection($user);
        $user->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $user->getConnection()->getQueryGrammar()->shouldReceive('wrap');
        $user->getConnection()->shouldReceive('raw');
        $user->getConnection()->getQueryGrammar()->shouldReceive('compileUpdate');
        $user->getConnection()->getQueryGrammar()->shouldReceive('prepareBindingsForUpdate')->andReturn([]);
        $user->getConnection()->shouldReceive('update')->andReturn(true);

        $this->assertTrue($user->increment('test'));
    }

    /** @test */
    public function it_can_pass_methods_to_permissions_instance()
    {
        $mockRole              = m::mock(EloquentRole::class);
        $mockRole->permissions = [];

        $user              = new EloquentUser();
        $user->permissions = ['foo' => true, 'bar' => false];
        $user->roles       = [$mockRole];

        $this->assertTrue($user->hasAccess('foo'));
        $this->assertFalse($user->hasAccess('bar'));
    }

    /** @test */
    public function it_can_delete_a_user()
    {
        $user         = m::mock('Cartalyst\Sentinel\Users\EloquentUser[roles,persistences,activations,reminders,throttle]');
        $user->exists = true;

        $this->addMockConnection($user);
        $user->getConnection()->getQueryGrammar()->shouldReceive('compileDelete');
        $user->getConnection()->getQueryGrammar()->shouldReceive('prepareBindingsForDelete')->andReturn([]);
        $user->getConnection()->shouldReceive('delete')->once();

        $user->shouldReceive('persistences')->once()->andReturn($persistences = m::mock(HasMany::class));
        $user->shouldReceive('activations')->once()->andReturn($activations = m::mock(HasMany::class));
        $user->shouldReceive('reminders')->once()->andReturn($reminders = m::mock(HasMany::class));
        $user->shouldReceive('throttle')->once()->andReturn($throttle = m::mock(HasMany::class));
        $user->shouldReceive('roles')->once()->andReturn($roles = m::mock(BelongsToMany::class));

        $persistences->shouldReceive('delete')->once();
        $activations->shouldReceive('delete')->once();
        $reminders->shouldReceive('delete')->once();
        $throttle->shouldReceive('delete')->once();
        $roles->shouldReceive('detach')->once();

        $this->assertTrue($user->delete());
    }

    protected function addMockConnection($model)
    {
        $resolver = m::mock(ConnectionResolverInterface::class);
        $resolver->shouldReceive('connection')->andReturn($connection = m::mock(Connection::class));

        $model->setConnectionResolver($resolver);
        $model->getConnection()->shouldReceive('getQueryGrammar')->andReturn($grammar = m::mock(Grammar::class));
        $model->getConnection()->shouldReceive('getPostProcessor')->andReturn($processor = m::mock(Processor::class));

        $model->getConnection()->shouldReceive('query')->andReturnUsing(function () use ($connection, $grammar, $processor) {
            return new Builder($connection, $grammar, $processor);
        });
    }
}
