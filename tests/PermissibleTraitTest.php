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

namespace Cartalyst\Sentinel\Tests;

use Cartalyst\Sentinel\Permissions\PermissibleTrait;
use Cartalyst\Sentinel\Permissions\PermissibleInterface;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class PermissibleTraitTest extends PHPUnit_Framework_TestCase
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

    public function testPermissionsClassSetterAndGetter()
    {
        $permissible = new PermissibleStub;

        $permissible::setPermissionsClass('Cartalyst\Sentinel\Permissions\StandardPermissions');

        $this->assertEquals('Cartalyst\Sentinel\Permissions\StandardPermissions', $permissible::getPermissionsClass());
    }

    public function testGetPermissionsInstance()
    {
        $permissible = new PermissibleStub;

        $this->assertInstanceOf('Cartalyst\Sentinel\Permissions\StandardPermissions', $permissible->getPermissionsInstance());
    }

    public function testAddPermission()
    {
        $permissible = new PermissibleStub;

        $permissible->addPermission('test');
        $permissible->addPermission('test1');

        $permissions = [
            'test'  => true,
            'test1' => true,
        ];

        $this->assertEquals($permissions, $permissible->getPermissions());
    }

    public function testUpdatePermission()
    {
        $permissible = new PermissibleStub;

        $permissible->addPermission('test');
        $permissible->addPermission('test1');
        $permissible->updatePermission('test1', false);

        $permissions = [
            'test'  => true,
            'test1' => false,
        ];

        $this->assertEquals($permissions, $permissible->getPermissions());
    }

    public function testUpdateOrCreatePermission()
    {
        $permissible = new PermissibleStub;

        $permissible->addPermission('test1');
        $permissible->updatePermission('test2', false);

        $permissions = [
            'test1' => true,
        ];

        $this->assertEquals($permissions, $permissible->getPermissions());

        $permissible = new PermissibleStub;

        $permissible->addPermission('test1');
        $permissible->updatePermission('test2', false, true);

        $permissions = [
            'test1' => true,
            'test2' => false,
        ];

        $this->assertEquals($permissions, $permissible->getPermissions());
    }

    public function testRemovePermission()
    {
        $permissible = new PermissibleStub;

        $permissible->addPermission('test');
        $permissible->addPermission('test1');
        $permissible->removePermission('test1');

        $permissions = [
            'test'  => true,
        ];

        $this->assertEquals($permissions, $permissible->getPermissions());
    }

    public function testPermissionsSetterAndGetter()
    {
        $permissible = new PermissibleStub;

        $permissions = [
            'test' => true
        ];

        $permissible->setPermissions($permissions);

        $this->assertEquals($permissions, $permissible->getPermissions());
    }
}

class PermissibleStub implements PermissibleInterface
{
    use PermissibleTrait;

    protected $permissions = [];

    protected function createPermissions()
    {
        return new static::$permissionsClass($this->permissions);
    }
}
