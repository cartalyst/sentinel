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

use Mockery as m;
use PHPUnit_Framework_TestCase;

class IlluminateRoleRepositoryTest extends PHPUnit_Framework_TestCase
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
        $roles = m::mock('Cartalyst\Sentinel\Roles\IlluminateRoleRepository[createModel]', [
            'RoleMock'
        ]);

        $this->assertEquals('RoleMock', $roles->getModel());
    }

    public function testFindById()
    {
        list($roles, $model, $query) = $this->getRolesMock();

        $query->shouldReceive('find')->with(1)->andReturn($model);

        $role = $roles->findById(1);

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Roles\RoleInterface',
            $role
        );
    }

    public function testFindBySlug()
    {
        list($roles, $model, $query) = $this->getRolesMock();

        $query->shouldReceive('where')->with('slug', 'foo')->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn($model);

        $role = $roles->findBySlug('foo');

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Roles\RoleInterface',
            $role
        );
    }

    public function testFindByName()
    {
        list($roles, $model, $query) = $this->getRolesMock();

        $query->shouldReceive('where')->with('name', 'foo')->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn($model);

        $role = $roles->findByName('foo');

        $this->assertInstanceOf(
            'Cartalyst\Sentinel\Roles\RoleInterface',
            $role
        );
    }


    protected function getRolesMock()
    {
        $model = m::mock('Cartalyst\Sentinel\Roles\EloquentRole');
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $roles = m::mock('Cartalyst\Sentinel\Roles\IlluminateRoleRepository[createModel]');

        $roles->shouldReceive('createModel')->andReturn($model);
        $model->shouldReceive('newQuery')->andReturn($query);

        return [$roles, $model, $query];
    }
}
