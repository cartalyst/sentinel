<?php namespace Cartalyst\Sentinel\Groups;
/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the license.txt file.
 *
 * @package    Sentinel
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

interface GroupableInterface {

	/**
	 * Returns all associated groups.
	 *
	 * @return \IteratorAggregate
	 */
	public function getGroups();

	/**
	 * Returns if the user is in the given group.
	 *
	 * @param  mixed  $group
	 * @return bool
	 */
	public function inGroup($group);

}
