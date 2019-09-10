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
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Sentinel\Tests\Roles;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Cartalyst\Sentinel\Roles\EloquentRole;

class IlluminateRoleRepositoryTest extends TestCase
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
        $roles = m::mock('Cartalyst\Sentinel\Roles\IlluminateRoleRepository[createModel]', [
            EloquentRole::class,
        ]);

        $this->assertSame(EloquentRole::class, $roles->getModel());
    }

    /** @test */
    public function it_can_find_a_role_using_its_id()
    {
        $query = m::mock(Builder::class);

        $model = m::mock(EloquentRole::class);
        $model->shouldReceive('newQuery')->andReturn($query);

        $roles = m::mock('Cartalyst\Sentinel\Roles\IlluminateRoleRepository[createModel]');
        $roles->shouldReceive('createModel')->andReturn($model);

        $query->shouldReceive('find')->with(1)->andReturn($model);

        $role = $roles->findById(1);

        $this->assertInstanceOf(EloquentRole::class, $role);
    }

    /** @test */
    public function it_can_find_a_role_using_its_slug()
    {
        $query = m::mock(Builder::class);

        $model = m::mock(EloquentRole::class);
        $model->shouldReceive('newQuery')->andReturn($query);

        $roles = m::mock('Cartalyst\Sentinel\Roles\IlluminateRoleRepository[createModel]');
        $roles->shouldReceive('createModel')->andReturn($model);

        $query->shouldReceive('where')->with('slug', 'foo')->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn($model);

        $this->assertInstanceOf(EloquentRole::class, $roles->findBySlug('foo'));
    }

    /** @test */
    public function it_can_find_a_role_using_its_name()
    {
        $query = m::mock(Builder::class);

        $model = m::mock(EloquentRole::class);
        $model->shouldReceive('newQuery')->andReturn($query);

        $roles = m::mock('Cartalyst\Sentinel\Roles\IlluminateRoleRepository[createModel]');
        $roles->shouldReceive('createModel')->andReturn($model);

        $query->shouldReceive('where')->with('name', 'foo')->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn($model);

        $this->assertInstanceOf(EloquentRole::class, $roles->findByName('foo'));
    }
}
