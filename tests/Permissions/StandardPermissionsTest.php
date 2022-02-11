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
use Cartalyst\Sentinel\Permissions\StandardPermissions;

class StandardPermissionsTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function permissions_can_inherit_from_secondary_permissions()
    {
        $primaryPermissions = ['user.create' => true, 'user.update' => false, 'user.delete' => true];

        $secondaryPermissions = [
            ['user.update' => true],
            ['user.view'   => true],
            ['user.delete' => false],
        ];

        $permissions = new StandardPermissions($primaryPermissions, $secondaryPermissions);

        $this->assertTrue($permissions->hasAccess('user.create'));
        $this->assertTrue($permissions->hasAccess('user.view'));
        $this->assertTrue($permissions->hasAccess('user.delete'));
        $this->assertFalse($permissions->hasAccess('user.update'));
        $this->assertFalse($permissions->hasAccess(['user.create', 'user.update']));

        $this->assertTrue($permissions->hasAnyAccess(['user.create', 'user.update']));
        $this->assertTrue($permissions->hasAnyAccess(['user.update', 'user.delete']));
    }

    /** @test */
    public function permissions_with_wildcards_can_be_used()
    {
        $permissions = new StandardPermissions(['user.create' => true, 'user.update' => false]);

        $this->assertTrue($permissions->hasAccess('user*'));
        $this->assertFalse($permissions->hasAccess('user'));

        $permissions = new StandardPermissions(['user.*' => true]);

        $this->assertTrue($permissions->hasAccess('user.create'));
        $this->assertTrue($permissions->hasAccess('user.update'));
    }

    /** @test */
    public function permissions_as_class_names_can_be_used()
    {
        $permissions = new StandardPermissions(['Class@method1,method2' => true]);

        $this->assertTrue($permissions->hasAccess('Class@method1'));
        $this->assertTrue($permissions->hasAccess('Class@method2'));
    }
}
