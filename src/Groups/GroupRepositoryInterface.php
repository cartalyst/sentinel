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

interface GroupRepositoryInterface {

	/**
	 * Finds a group by the given primary key.
	 *
	 * @param  int  $id
	 * @return \Cartalyst\Sentinel\Groups\GroupInterface
	 */
	public function findById($id);

	/**
	 * Finds a group by the given slug.
	 *
	 * @param  string  $slug
	 * @return \Cartalyst\Sentinel\Groups\GroupInterface
	 */
	public function findBySlug($slug);

	/**
	 * Finds a group by the given name.
	 *
	 * @param  string  $name
	 * @return \Cartalyst\Sentinel\Groups\GroupInterface
	 */
	public function findByName($name);

}
