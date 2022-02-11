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
    protected function setUp(): void
    {
        $this->permissible = new PermissibleStub();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->permissible = null;

        m::close();
    }

    /** @test */
    public function it_can_set_and_get_the_permissions_class()
    {
        $this->permissible::setPermissionsClass(StandardPermissions::class);

        $this->assertSame(StandardPermissions::class, $this->permissible::getPermissionsClass());
    }

    /** @test */
    public function it_can_get_the_permissions_intance()
    {
        $this->permissible::setPermissionsClass(StandardPermissions::class);

        $this->assertInstanceOf(StandardPermissions::class, $this->permissible->getPermissionsInstance());
    }

    /** @test */
    public function it_can_add_permissions()
    {
        $this->permissible->addPermission('test');
        $this->permissible->addPermission('test1');

        $permissions = [
            'test'  => true,
            'test1' => true,
        ];

        $this->assertSame($permissions, $this->permissible->getPermissions());
    }

    /** @test */
    public function it_can_update_permissions()
    {
        $this->permissible->addPermission('test');
        $this->permissible->addPermission('test1');
        $this->permissible->updatePermission('test1', false);

        $permissions = [
            'test'  => true,
            'test1' => false,
        ];

        $this->assertSame($permissions, $this->permissible->getPermissions());
    }

    /** @test */
    public function it_can_create_or_update_permissions()
    {
        $this->permissible->addPermission('test1');
        $this->permissible->updatePermission('test2', false);

        $permissions = [
            'test1' => true,
        ];

        $this->assertSame($permissions, $this->permissible->getPermissions());

        $this->permissible = new PermissibleStub();

        $this->permissible->addPermission('test1');
        $this->permissible->updatePermission('test2', false, true);

        $permissions = [
            'test1' => true,
            'test2' => false,
        ];

        $this->assertSame($permissions, $this->permissible->getPermissions());
    }

    /** @test */
    public function it_can_remove_permissions()
    {
        $this->permissible->addPermission('test');
        $this->permissible->addPermission('test1');
        $this->permissible->removePermission('test1');

        $permissions = [
            'test' => true,
        ];

        $this->assertSame($permissions, $this->permissible->getPermissions());
    }

    /** @test */
    public function it_can_use_the_setter_and_getter()
    {
        $permissions = [
            'test' => true,
        ];

        $this->permissible->setPermissions($permissions);

        $this->assertSame($permissions, $this->permissible->getPermissions());
    }
}

class PermissibleStub implements PermissibleInterface
{
    use PermissibleTrait;

    protected $permissions = [];

    protected function createPermissions(): PermissionsInterface
    {
        return new static::$permissionsClass($this->getPermissions());
    }
}
