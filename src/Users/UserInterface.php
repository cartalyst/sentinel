<?php namespace Cartalyst\Sentinel\Users;
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

interface UserInterface {

	/**
	 * Get the user's primary key.
	 *
	 * @return int
	 */
	public function getUserId();

	/**
	 * Get the user's login.
	 *
	 * @return string
	 */
	public function getUserLogin();

	/**
	 * Get the user's login attribute name.
	 *
	 * @return string
	 */
	public function getUserLoginName();

	/**
	 * Get the user's passwrd.
	 *
	 * @return string
	 */
	public function getUserPassword();

}
