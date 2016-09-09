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

use Cartalyst\Sentinel\Permissions\StandardPermissions;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class StandardPermissionsTest extends PHPUnit_Framework_TestCase
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

    public function testPermissionsInheritence()
    {
        $permissions = new StandardPermissions(
            ['foo' => true, 'bar' => false, 'fred' => true],
            [
                ['bar' => true],
                ['qux' => true],
                ['fred' => false],
            ]
        );

        $this->assertTrue($permissions->hasAccess('foo'));
        $this->assertFalse($permissions->hasAccess('bar'));
        $this->assertTrue($permissions->hasAccess('qux'));
        $this->assertTrue($permissions->hasAccess('fred'));
        $this->assertFalse($permissions->hasAccess(['foo', 'bar']));
        $this->assertTrue($permissions->hasAnyAccess(['foo', 'bar']));
        $this->assertTrue($permissions->hasAnyAccess(['bar', 'fred']));
    }

    public function testWildcardChecks()
    {
        $permissions = new StandardPermissions(['foo.bar' => true, 'foo.qux' => false]);

        $this->assertFalse($permissions->hasAccess('foo'));
        $this->assertTrue($permissions->hasAccess('foo*'));
    }

    public function testWildcardPermissions()
    {
        $permissions = new StandardPermissions(['foo.*' => true]);

        $this->assertTrue($permissions->hasAccess('foo.bar'));
        $this->assertTrue($permissions->hasAccess('foo.qux'));
    }

    public function testClassPermissions()
    {
        $permissions = new StandardPermissions(['Class@method1,method2' => true]);
        $this->assertTrue($permissions->hasAccess('Class@method1'));
        $this->assertTrue($permissions->hasAccess('Class@method2'));
    }
}
