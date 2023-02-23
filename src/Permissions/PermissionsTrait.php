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

use Illuminate\Support\Str;

trait PermissionsTrait
{
    /**
     * The main permissions.
     *
     * @var array
     */
    protected $permissions = [];

    /**
     * The secondary permissions.
     *
     * @var array
     */
    protected $secondaryPermissions = [];

    /**
     * An array of cached, prepared permissions.
     *
     * @var array
     */
    protected $preparedPermissions;

    /**
     * Constructor.
     *
     * @param array|null $permissions
     * @param array|null $secondaryPermissions
     *
     * @return void
     */
    public function __construct(array $permissions = null, array $secondaryPermissions = null)
    {
        $this->permissions = $permissions;

        $this->secondaryPermissions = $secondaryPermissions;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAccess($permissions): bool
    {
        if (is_string($permissions)) {
            $permissions = func_get_args();
        }

        $prepared = $this->getPreparedPermissions();

        foreach ($permissions as $permission) {
            if (! $this->checkPermission($prepared, $permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAnyAccess($permissions): bool
    {
        if (is_string($permissions)) {
            $permissions = func_get_args();
        }

        $prepared = $this->getPreparedPermissions();

        foreach ($permissions as $permission) {
            if ($this->checkPermission($prepared, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the main permissions.
     *
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions ?? [];
    }

    /**
     * Returns the secondary permissions.
     *
     * @return array
     */
    public function getSecondaryPermissions(): array
    {
        return $this->secondaryPermissions ?? [];
    }

    /**
     * Sets secondary permissions.
     *
     * @param array $secondaryPermissions
     *
     * @return $this
     */
    public function setSecondaryPermissions(array $secondaryPermissions): self
    {
        $this->secondaryPermissions = $secondaryPermissions;

        $this->preparedPermissions = null;

        return $this;
    }

    /**
     * Lazily grab the prepared permissions.
     *
     * @return array
     */
    protected function getPreparedPermissions(): array
    {
        if ($this->preparedPermissions === null) {
            $this->preparedPermissions = $this->createPreparedPermissions();
        }

        return $this->preparedPermissions;
    }

    /**
     * Does the heavy lifting of preparing permissions.
     *
     * @param array $prepared
     * @param array $permissions
     *
     * @return void
     */
    protected function preparePermissions(array &$prepared, array $permissions): void
    {
        foreach ($permissions as $keys => $value) {
            foreach ($this->extractClassPermissions($keys) as $key) {
                // If the value is not in the array, we're opting in
                if (! array_key_exists($key, $prepared)) {
                    $prepared[$key] = $value;

                    continue;
                }

                // If our value is in the array and equals false, it will override
                if ($value === false) {
                    $prepared[$key] = $value;
                }
            }
        }
    }

    /**
     * Takes the given permission key and inspects it for a class & method. If
     * it exists, methods may be comma-separated, e.g. Class@method1,method2.
     *
     * @param string $key
     *
     * @return array
     */
    protected function extractClassPermissions(string $key): array
    {
        if (! Str::contains($key, '@')) {
            return (array) $key;
        }

        $keys = [];

        list($class, $methods) = explode('@', $key);

        foreach (explode(',', $methods) as $method) {
            $keys[] = "{$class}@{$method}";
        }

        return $keys;
    }

    /**
     * Checks a permission in the prepared array, including wildcard checks and permissions.
     *
     * @param array  $prepared
     * @param string $permission
     *
     * @return bool
     */
    protected function checkPermission(array $prepared, string $permission): bool
    {
        if (array_key_exists($permission, $prepared)) {
            return $prepared[$permission] === true;
        }

        foreach ($prepared as $key => $value) {
            $key = (string) $key;

            if ((Str::is($permission, $key) || Str::is($key, $permission)) && $value === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the prepared permissions.
     *
     * @return array
     */
    abstract protected function createPreparedPermissions(): array;
}
