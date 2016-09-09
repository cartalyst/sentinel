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
 * @version    2.0.13
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2016, Cartalyst LLC
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
            'Cartalyst\Sentinel\Roles\EloquentRole', 1, 2, 3, 4, 5, 6,
        ]);
    }

    public function testFindById()
    {
        $roles = m::mock('Cartalyst\Sentinel\Roles\IlluminateRoleRepository[createModel]');

        $roles->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Roles\EloquentRole[newQuery]'));

        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('find')->with(1)->andReturn($query);

        $roles->findById(1);
    }

    public function testFindBySlug()
    {
        $roles = m::mock('Cartalyst\Sentinel\Roles\IlluminateRoleRepository[createModel]');

        $roles->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Roles\EloquentRole[newQuery]'));

        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('where')->with('slug', 'foo')->andReturn($query);
        $query->shouldReceive('first')->once();

        $roles->findBySlug('foo');
    }

    public function testFindByName()
    {
        $roles = m::mock('Cartalyst\Sentinel\Roles\IlluminateRoleRepository[createModel]');

        $roles->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Roles\EloquentRole[newQuery]'));

        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('where')->with('name', 'foo')->andReturn($query);
        $query->shouldReceive('first')->once();

        $roles->findByName('foo');
    }
}
