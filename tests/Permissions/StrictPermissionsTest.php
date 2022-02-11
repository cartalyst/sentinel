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
use Cartalyst\Sentinel\Permissions\StrictPermissions;

class StrictPermissionsTest extends TestCase
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
        $permissions = new StrictPermissions(
            ['foo' => true, 'bar' => false, 'fred' => true],
            [
                ['bar' => true],
                ['qux'  => true],
                ['fred' => false],
            ]
        );

        $this->assertTrue($permissions->hasAccess('foo'));
        $this->assertTrue($permissions->hasAccess('qux'));
        $this->assertFalse($permissions->hasAccess('bar'));
        $this->assertFalse($permissions->hasAccess('fred'));
        $this->assertFalse($permissions->hasAccess(['foo', 'bar']));

        $this->assertTrue($permissions->hasAnyAccess(['foo', 'bar']));
        $this->assertFalse($permissions->hasAnyAccess(['bar', 'fred']));
    }

    /** @test */
    public function permissions_with_wildcards_can_be_used()
    {
        $permissions = new StrictPermissions(['foo.bar' => true, 'foo.qux' => false]);

        $this->assertTrue($permissions->hasAccess('foo*'));
        $this->assertFalse($permissions->hasAccess('foo'));

        $permissions = new StrictPermissions(['foo.*' => true]);

        $this->assertTrue($permissions->hasAccess('foo.bar'));
        $this->assertTrue($permissions->hasAccess('foo.qux'));
    }

    /** @test */
    public function permissions_as_class_names_can_be_used()
    {
        $permissions = new StrictPermissions(['Class@method1,method2' => true]);

        $this->assertTrue($permissions->hasAccess('Class@method1'));
        $this->assertTrue($permissions->hasAccess('Class@method2'));
    }
}
