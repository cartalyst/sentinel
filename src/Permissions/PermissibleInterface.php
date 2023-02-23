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
 * @version    7.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2023, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Sentinel\Permissions;

interface PermissibleInterface
{
    /**
     * Returns the Permissions instance.
     *
     * @return \Cartalyst\Sentinel\Permissions\PermissionsInterface
     */
    public function getPermissionsInstance(): PermissionsInterface;

    /**
     * Adds a permission.
     *
     * @param string $permission
     * @param bool   $value
     *
     * @return \Cartalyst\Sentinel\Permissions\PermissibleInterface
     */
    public function addPermission(string $permission, bool $value = true): PermissibleInterface;

    /**
     * Updates a permission.
     *
     * @param string $permission
     * @param bool   $value
     * @param bool   $create
     *
     * @return \Cartalyst\Sentinel\Permissions\PermissibleInterface
     */
    public function updatePermission(string $permission, bool $value = true, bool $create = false): PermissibleInterface;

    /**
     * Removes a permission.
     *
     * @param string $permission
     *
     * @return \Cartalyst\Sentinel\Permissions\PermissibleInterface
     */
    public function removePermission(string $permission): PermissibleInterface;
}
