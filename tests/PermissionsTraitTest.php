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

use Cartalyst\Sentinel\Permissions\PermissionsTrait;
use Cartalyst\Sentinel\Permissions\PermissionsInterface;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class PermissionsTraitTest extends PHPUnit_Framework_TestCase
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
        $secondaryPermissions = [
            [
                'test1' => true,
            ],
            [
                'test1' => false,
            ]
        ];

        $permissions = new PermissionsStub([
            'test' => true,
            'test2' => false,
        ], $secondaryPermissions);

        $secondaryPermissions[] = [
            'test2' => true,
        ];

        $permissions->setSecondaryPermissions($secondaryPermissions);

        $this->assertTrue($permissions->hasAccess('test'));
        $this->assertFalse($permissions->hasAccess('test', 'test1'));
        $this->assertFalse($permissions->hasAccess(['test', 'test1']));

        $this->assertTrue($permissions->hasAnyAccess('test'));
        $this->assertTrue($permissions->hasAnyAccess('test', 'test1'));
        $this->assertTrue($permissions->hasAnyAccess(['test', 'test1']));
        $this->assertFalse($permissions->hasAnyAccess(['test4', 'test5']));

        $this->assertEquals($secondaryPermissions, $permissions->getSecondaryPermissions());
    }

    public function testWildCardPermissions()
    {
        $permissions = new PermissionsStub([
            'user.add'    => true,
            'user.remove' => false,
        ]);

        $this->assertTrue($permissions->hasAccess('user.*'));
    }

    public function testClassPermissions()
    {
        $permissions = new PermissionsStub([
            'Foo\Bar\Baz@add,view'      => true,
            'Foo\Bar\Baz@update,remove' => false,
        ]);

        $this->assertTrue($permissions->hasAccess('Foo\Bar\Baz@add'));
        $this->assertTrue($permissions->hasAccess('Foo\Bar\Baz@view'));

        $this->assertFalse($permissions->hasAccess('Foo\Bar\Baz@update'));
        $this->assertFalse($permissions->hasAccess('Foo\Bar\Baz@remove'));
    }
}

class PermissionsStub implements PermissionsInterface
{
    use PermissionsTrait;

    protected function createPreparedPermissions()
    {
        $prepared = [];

        if (! empty($this->secondaryPermissions)) {
            foreach ($this->secondaryPermissions as $permissions) {
                $this->preparePermissions($prepared, $permissions);
            }
        }

        if (! empty($this->permissions)) {
            $permissions = [];
            $this->preparePermissions($permissions, $this->permissions);
            $prepared = array_merge($prepared, $permissions);
        }

        return $prepared;
    }
}
