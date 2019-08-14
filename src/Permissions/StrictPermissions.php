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
 * @version    2.0.18
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2019, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Permissions;

class StrictPermissions implements PermissionsInterface
{
    use PermissionsTrait;

    /**
     * {@inheritDoc}
     */
    protected function createPreparedPermissions()
    {
        $prepared = [];

        if (! empty($this->secondaryPermissions)) {
            foreach ($this->secondaryPermissions as $permissions) {
                $this->preparePermissions($prepared, $permissions);
            }
        }

        if (! empty($this->permissions)) {
            $this->preparePermissions($prepared, $this->permissions);
        }

        return $prepared;
    }
}
