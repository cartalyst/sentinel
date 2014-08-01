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
	 * Returns the user primary key.
	 *
	 * @return int
	 */
	public function getUserId();

	/**
	 * Returns the user login.
	 *
	 * @return string
	 */
	public function getUserLogin();

	/**
	 * Returns the user login attribute name.
	 *
	 * @return string
	 */
	public function getUserLoginName();

	/**
	 * Returns the user passwrd.
	 *
	 * @return string
	 */
	public function getUserPassword();

}
