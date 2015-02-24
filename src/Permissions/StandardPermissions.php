<?php namespace Cartalyst\Sentinel\Permissions;
/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Sentinel
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

class StandardPermissions implements PermissionsInterface {

	use PermissionsTrait;

	/**
	 * {@inheritDoc}
	 */
	protected function createPreparedPermissions()
	{
		$prepared = [];

		if ( ! empty($this->secondaryPermissions))
		{
			foreach ($this->secondaryPermissions as $permissions)
			{
				$this->preparePermissions($prepared, $permissions);
			}
		}

		if ( ! empty($this->permissions))
		{
			$permissions = [];

			$this->preparePermissions($permissions, $this->permissions);

			$prepared = array_merge($prepared, $permissions);
		}

		return $prepared;
	}

}
