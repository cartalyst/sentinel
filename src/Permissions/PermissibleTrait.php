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
 * @version    2.0.17
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2017, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Permissions;

trait PermissibleTrait
{
    /**
     * The cached permissions instance for the given user.
     *
     * @var \Cartalyst\Sentinel\Permissions\PermissionsInterface
     */
    protected $permissionsInstance;

    /**
     * The permissions instance class name.
     *
     * @var string
     */
    protected static $permissionsClass = 'Cartalyst\Sentinel\Permissions\StrictPermissions';

    /**
     * Returns the permissions.
     *
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Sets permissions.
     *
     * @param  array  $permissions
     * @return void
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * Returns the permissions class name.
     *
     * @return string
     */
    public static function getPermissionsClass()
    {
        return static::$permissionsClass;
    }

    /**
     * Sets the permissions class name.
     *
     * @param  string  $permissionsClass
     * @return void
     */
    public static function setPermissionsClass($permissionsClass)
    {
        static::$permissionsClass = $permissionsClass;
    }

    /**
     * Creates the permissions object.
     *
     * @return \Cartalyst\Sentinel\Permissions\PermissionsInterface
     */
    abstract protected function createPermissions();

    /**
     * {@inheritDoc}
     */
    public function getPermissionsInstance()
    {
        if ($this->permissionsInstance === null) {
            $this->permissionsInstance = $this->createPermissions();
        }

        return $this->permissionsInstance;
    }

    /**
     * {@inheritDoc}
     */
    public function addPermission($permission, $value = true)
    {
        if (! array_key_exists($permission, $this->permissions)) {
            $this->permissions = array_merge($this->permissions, [$permission => $value]);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function updatePermission($permission, $value = true, $create = false)
    {
        if (array_key_exists($permission, $this->permissions)) {
            $permissions = $this->permissions;

            $permissions[$permission] = $value;

            $this->permissions = $permissions;
        } elseif ($create) {
            $this->addPermission($permission, $value);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removePermission($permission)
    {
        if (array_key_exists($permission, $this->permissions)) {
            $permissions = $this->permissions;

            unset($permissions[$permission]);

            $this->permissions = $permissions;
        }

        return $this;
    }
}
