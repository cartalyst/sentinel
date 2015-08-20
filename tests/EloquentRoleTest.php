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
 * @version    2.1.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\tests;

use Cartalyst\Sentinel\Roles\EloquentRole;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class EloquentRoleTest extends PHPUnit_Framework_TestCase
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

    public function testUsersModelGetterAndSetter()
    {
        EloquentRole::setUsersModel('Cartalyst\Sentinel\Users\EloquentUser');

        $this->assertEquals('Cartalyst\Sentinel\Users\EloquentUser', EloquentRole::getUsersModel());
    }

    public function testPermissionsAccessAndMutator()
    {
        $role = new EloquentRole;

        $role->slug = 'foo';

        $permissions = ['foo' => true];

        $role->permissions = $permissions;

        $this->assertEquals($permissions, $role->permissions);
    }

    public function testUserRelationship()
    {
        $role = new EloquentRole;

        $this->addMockConnection($role);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\BelongsToMany', $role->users());
    }

    protected function addMockConnection($model)
    {
        $model->setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));
        $resolver->shouldReceive('connection')->andReturn(m::mock('Illuminate\Database\Connection'));
        $model->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock('Illuminate\Database\Query\Grammars\Grammar'));
        $model->getConnection()->shouldReceive('getPostProcessor')->andReturn(m::mock('Illuminate\Database\Query\Processors\Processor'));
    }
}
