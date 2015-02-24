<?php

/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Sentinel
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\tests;

use Cartalyst\Sentinel\Users\EloquentUser;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class EloquentUserTest extends PHPUnit_Framework_TestCase
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

    public function testPermissionsAccessAndMutator()
    {
        $user = new EloquentUser;
        $user->slug = 'foo';

        $permissions = ['foo' => true];

        $user->permissions = $permissions;

        $this->assertEquals($permissions, $user->permissions);
    }

    public function testGetUserPassword()
    {
        $user = new EloquentUser;
        $user->password = 'foobar';

        $this->assertEquals('foobar', $user->getUserPassword());
    }

    public function testSetAndGetPersistableKey()
    {
        $user = new EloquentUser;

        $user->setPersistableKey('foo_id');

        $this->assertEquals('foo_id', $user->getPersistableKey());
    }

    public function testSetAndGetPersistableRelationship()
    {
        $user = new EloquentUser;

        $user->setPersistableRelationship('foo_persistences');

        $this->assertEquals('foo_persistences', $user->getPersistableRelationship());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetAndGetRolesModel()
    {
        $user = new EloquentUser;

        $user->setRolesModel('RoleMock');

        $this->assertEquals('RoleMock', $user->getRolesModel());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetAndGetPersistencesModel()
    {
        $user = new EloquentUser;

        $user->setPersistencesModel('PersistenceMock');

        $this->assertEquals('PersistenceMock', $user->getPersistencesModel());
    }

    public function testGetPersistableIdAndGetUserId()
    {
        $user = new EloquentUser;

        $user->setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));
        $resolver->shouldReceive('connection')->andReturn(m::mock('Illuminate\Database\Connection'));
        $user->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock('Illuminate\Database\Query\Grammars\Grammar'));
        $user->getConnection()->shouldReceive('getPostProcessor')->andReturn($processor = m::mock('Illuminate\Database\Query\Processors\Processor'));
        $user->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $user->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $processor->shouldReceive('processInsertGetId')->andReturn(1);

        $user->save();

        $this->assertEquals('1', $user->getPersistableId());
        $this->assertEquals('1', $user->getUserId());
    }

    public function testGetUserLogin()
    {
        $user = new EloquentUser;
        $user->email = 'foo@example.com';

        $user->setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));
        $resolver->shouldReceive('connection')->andReturn(m::mock('Illuminate\Database\Connection'));
        $user->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock('Illuminate\Database\Query\Grammars\Grammar'));
        $user->getConnection()->shouldReceive('getPostProcessor')->andReturn($processor = m::mock('Illuminate\Database\Query\Processors\Processor'));
        $user->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $user->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $processor->shouldReceive('processInsertGetId')->andReturn(1);

        $user->save();

        $this->assertEquals('foo@example.com', $user->getUserLogin());
    }

    public function testGetLoginNames()
    {
        $user = new EloquentUser;

        $this->assertEquals(['email'], $user->getLoginNames());
    }

    public function testGetUserLoginName()
    {
        $user = new EloquentUser;

        $this->assertEquals('email', $user->getUserLoginName());
    }

    public function testGeneratePersistenceCode()
    {
        $user = new EloquentUser;

        $this->assertEquals(32, strlen($user->generatePersistenceCode()));
    }

    public function testRolesRelationship()
    {
        $user = new EloquentUser;

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\BelongsToMany', $user->roles());
    }

    public function testPersistencesRelationship()
    {
        $user = new EloquentUser;

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\HasMany', $user->persistences());
    }

    public function testInRole()
    {
        $user = new EloquentUser;

        $user->setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));
        $resolver->shouldReceive('connection')->andReturn(m::mock('Illuminate\Database\Connection'));
        $user->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock('Illuminate\Database\Query\Grammars\Grammar'));
        $user->getConnection()->shouldReceive('getPostProcessor')->andReturn($processor = m::mock('Illuminate\Database\Query\Processors\Processor'));
        $user->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $user->getConnection()->getQueryGrammar()->shouldReceive('compileInsertGetId');
        $processor->shouldReceive('processSelect')->andReturn([
            [
                'slug' => 'foobar',
            ],
            [
                'slug' => 'foo',
            ],
            [
                'slug' => 'bar',
            ],
        ]);

        $user->getConnection()->getQueryGrammar()->shouldReceive('compileSelect');
        $user->getConnection()->shouldReceive('select');

        $this->assertTrue($user->inRole('foobar'));
        $this->assertTrue($user->inRole('foo'));
        $this->assertTrue($user->inRole('bar'));
        $this->assertFalse($user->inRole('baz'));
    }

    public function testGetRoles()
    {
        $user = new EloquentUser;

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->getRoles());
    }

    public function testDeleteUser()
    {
        $user = m::mock('Cartalyst\Sentinel\Users\EloquentUser[roles,persistences,activations,reminders,throttle]');
        $user->exists = true;

        $user->getConnection()->getQueryGrammar()->shouldReceive('compileDelete');
        $user->getConnection()->shouldReceive('delete')->once();

        $user->shouldReceive('roles')->once()->andReturn($roles = m::mock('Illuminate\Database\Eloquent\Relations\BelongsToMany'));

        $user->shouldReceive('persistences')->once()->andReturn($persistences = m::mock('Illuminate\Database\Eloquent\Relations\HasMany'));

        $user->shouldReceive('activations')->once()->andReturn($activations = m::mock('Illuminate\Database\Eloquent\Relations\HasMany'));

        $user->shouldReceive('reminders')->once()->andReturn($reminders = m::mock('Illuminate\Database\Eloquent\Relations\HasMany'));

        $user->shouldReceive('throttle')->once()->andReturn($throttle = m::mock('Illuminate\Database\Eloquent\Relations\HasMany'));

        $persistences->shouldReceive('delete')->once();

        $activations->shouldReceive('delete')->once();

        $roles->shouldReceive('detach')->once();

        $reminders->shouldReceive('delete')->once();

        $throttle->shouldReceive('delete')->once();

        $user->delete();
    }
}
