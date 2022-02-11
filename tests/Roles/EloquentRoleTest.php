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
 * @version    6.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2022, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Sentinel\Tests\Roles;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Connection;
use Cartalyst\Sentinel\Roles\EloquentRole;
use Cartalyst\Sentinel\Users\EloquentUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EloquentRoleTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->role = new EloquentRole();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->role = null;

        m::close();
    }

    /** @test */
    public function it_can_set_and_get_the_users_model_fqcn()
    {
        EloquentRole::setUsersModel(EloquentUser::class);

        $this->assertSame(EloquentUser::class, EloquentRole::getUsersModel());
    }

    /** @test */
    public function it_can_get_the_permissions_using_the_accessor()
    {
        $this->role->slug = 'foo';

        $permissions = ['foo' => true];

        $this->role->permissions = $permissions;

        $this->assertSame($permissions, $this->role->permissions);
    }

    /** @test */
    public function it_can_get_the_users_for_the_role()
    {
        $this->addMockConnection($this->role);

        $this->assertInstanceOf(Collection::class, $this->role->getUsers());
    }

    /** @test */
    public function it_can_pass_methods_to_the_permissions_instance()
    {
        $this->addMockConnection($this->role);

        $permissions = [];

        $this->assertFalse($this->role->hasAnyAccess($permissions));
    }

    /** @test */
    public function it_can_get_the_users_relationship()
    {
        $this->addMockConnection($this->role);

        $this->assertInstanceOf(BelongsToMany::class, $this->role->users());
    }

    /** @test */
    public function it_can_delete_a_role()
    {
        $users = m::mock(BelongsToMany::class);
        $users->shouldReceive('detach')->once();

        $role         = m::mock('Cartalyst\Sentinel\Roles\EloquentRole[users]');
        $role->exists = true;

        $this->addMockConnection($role);

        $role->getConnection()->getQueryGrammar()->shouldReceive('compileDelete');
        $role->getConnection()->getQueryGrammar()->shouldReceive('prepareBindingsForDelete')->andReturn([]);
        $role->getConnection()->shouldReceive('delete')->once()->andReturn(true);
        $role->shouldReceive('users')->once()->andReturn($users);

        $this->assertTrue($role->delete());
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
