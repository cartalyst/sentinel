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
     * The Permissions instance FQCN.
     *
     * @var string
     */
    protected static $permissionsClass = StrictPermissions::class;

    /**
     * Returns the permissions.
     *
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions ?? [];
    }

    /**
     * Sets permissions.
     *
     * @param array $permissions
     *
     * @return $this
     */
    public function setPermissions(array $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Returns the permissions class name.
     *
     * @return string
     */
    public static function getPermissionsClass(): string
    {
        return static::$permissionsClass;
    }

    /**
     * Sets the permissions class name.
     *
     * @param string $permissionsClass
     *
     * @return void
     */
    public static function setPermissionsClass(string $permissionsClass): void
    {
        static::$permissionsClass = $permissionsClass;
    }

    /**
     * Creates the permissions object.
     *
     * @return $this
     */
    abstract protected function createPermissions(): PermissionsInterface;

    /**
     * {@inheritdoc}
     */
    public function getPermissionsInstance(): PermissionsInterface
    {
        if ($this->permissionsInstance === null) {
            $this->permissionsInstance = $this->createPermissions();
        }

        return $this->permissionsInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function addPermission(string $permission, bool $value = true): PermissibleInterface
    {
        if (! array_key_exists($permission, $this->getPermissions())) {
            $this->permissions = array_merge($this->getPermissions(), [$permission => $value]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function updatePermission(string $permission, bool $value = true, bool $create = false): PermissibleInterface
    {
        if (array_key_exists($permission, $this->getPermissions())) {
            $permissions = $this->getPermissions();

            $permissions[$permission] = $value;

            $this->permissions = $permissions;
        } elseif ($create) {
            $this->addPermission($permission, $value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removePermission(string $permission): PermissibleInterface
    {
        if (array_key_exists($permission, $this->getPermissions())) {
            $permissions = $this->getPermissions();

            unset($permissions[$permission]);

            $this->permissions = $permissions;
        }

        return $this;
    }
}
