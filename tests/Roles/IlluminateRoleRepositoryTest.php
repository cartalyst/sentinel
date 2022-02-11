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
use Illuminate\Database\Eloquent\Builder;
use Cartalyst\Sentinel\Roles\EloquentRole;

class IlluminateRoleRepositoryTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->query = m::mock(Builder::class);
        $this->model = m::mock(EloquentRole::class);
        $this->roles = m::mock('Cartalyst\Sentinel\Roles\IlluminateRoleRepository[createModel]');
        $this->roles->shouldReceive('createModel')->andReturn($this->model);
    }

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
        $this->model->shouldReceive('newQuery')->andReturn($this->query);

        $this->query->shouldReceive('find')->with(1)->andReturn($this->model);

        $role = $this->roles->findById(1);

        $this->assertInstanceOf(EloquentRole::class, $role);
    }

    /** @test */
    public function it_can_find_a_role_using_its_slug()
    {
        $this->model->shouldReceive('newQuery')->andReturn($this->query);

        $this->query->shouldReceive('where')->with('slug', 'foo')->andReturnSelf();
        $this->query->shouldReceive('first')->once()->andReturn($this->model);

        $this->assertInstanceOf(EloquentRole::class, $this->roles->findBySlug('foo'));
    }

    /** @test */
    public function it_can_find_a_role_using_its_name()
    {
        $this->model->shouldReceive('newQuery')->andReturn($this->query);

        $this->query->shouldReceive('where')->with('name', 'foo')->andReturnSelf();
        $this->query->shouldReceive('first')->once()->andReturn($this->model);

        $this->assertInstanceOf(EloquentRole::class, $this->roles->findByName('foo'));
    }
}
