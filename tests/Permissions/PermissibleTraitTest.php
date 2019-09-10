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

namespace Cartalyst\Sentinel\Tests\Permissions;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Cartalyst\Sentinel\Permissions\PermissibleTrait;
use Cartalyst\Sentinel\Permissions\StandardPermissions;
use Cartalyst\Sentinel\Permissions\PermissibleInterface;
use Cartalyst\Sentinel\Permissions\PermissionsInterface;

class PermissibleTraitTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_can_set_and_get_the_permissions_class()
    {
        $permissible = new PermissibleStub();

        $permissible::setPermissionsClass(StandardPermissions::class);

        $this->assertSame(StandardPermissions::class, $permissible::getPermissionsClass());
    }

    /** @test */
    public function it_can_get_the_permissions_intance()
    {
        $permissible = new PermissibleStub();

        $permissible::setPermissionsClass(StandardPermissions::class);

        $this->assertInstanceOf(StandardPermissions::class, $permissible->getPermissionsInstance());
    }

    /** @test */
    public function it_can_add_permissions()
    {
        $permissible = new PermissibleStub();

        $permissible->addPermission('test');
        $permissible->addPermission('test1');

        $permissions = [
            'test'  => true,
            'test1' => true,
        ];

        $this->assertSame($permissions, $permissible->getPermissions());
    }

    /** @test */
    public function it_can_update_permissions()
    {
        $permissible = new PermissibleStub();

        $permissible->addPermission('test');
        $permissible->addPermission('test1');
        $permissible->updatePermission('test1', false);

        $permissions = [
            'test'  => true,
            'test1' => false,
        ];

        $this->assertSame($permissions, $permissible->getPermissions());
    }

    /** @test */
    public function it_can_create_or_update_permissions()
    {
        $permissible = new PermissibleStub();

        $permissible->addPermission('test1');
        $permissible->updatePermission('test2', false);

        $permissions = [
            'test1' => true,
        ];

        $this->assertSame($permissions, $permissible->getPermissions());

        $permissible = new PermissibleStub();

        $permissible->addPermission('test1');
        $permissible->updatePermission('test2', false, true);

        $permissions = [
            'test1' => true,
            'test2' => false,
        ];

        $this->assertSame($permissions, $permissible->getPermissions());
    }

    /** @test */
    public function it_can_remove_permissions()
    {
        $permissible = new PermissibleStub();

        $permissible->addPermission('test');
        $permissible->addPermission('test1');
        $permissible->removePermission('test1');

        $permissions = [
            'test' => true,
        ];

        $this->assertSame($permissions, $permissible->getPermissions());
    }

    /** @test */
    public function it_can_use_the_setter_and_getter()
    {
        $permissible = new PermissibleStub();

        $permissions = [
            'test' => true,
        ];

        $permissible->setPermissions($permissions);

        $this->assertSame($permissions, $permissible->getPermissions());
    }
}

class PermissibleStub implements PermissibleInterface
{
    use PermissibleTrait;

    protected $permissions = [];

    protected function createPermissions(): PermissionsInterface
    {
        return new static::$permissionsClass($this->permissions);
    }
}
