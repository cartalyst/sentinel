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

interface PermissionsInterface {

	/**
	 * Returns if access is available for all given permissions.
	 *
	 * @param  array|string  $permissions
	 * @return bool
	 */
	public function hasAccess($permissions);

	/**
	 * Returns if access is available for any given permissions.
	 *
	 * @param  array|string  $permissions
	 * @return bool
	 */
	public function hasAnyAccess($permissions);

}
