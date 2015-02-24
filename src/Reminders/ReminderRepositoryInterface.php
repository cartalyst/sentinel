<?php namespace Cartalyst\Sentinel\Reminders;
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

use Cartalyst\Sentinel\Users\UserInterface;

interface ReminderRepositoryInterface {

	/**
	 * Create a new reminder record and code.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
	 * @return string
	 */
	public function create(UserInterface $user);

	/**
	 * Check if a valid reminder exists.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
	 * @param  string  $code
	 * @return bool
	 */
	public function exists(UserInterface $user, $code = null);

	/**
	 * Complete reminder for the given user.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
	 * @param  string  $code
	 * @param  string  $password
	 * @return bool
	 */
	public function complete(UserInterface $user, $code, $password);

	/**
	 * Remove expired reminder codes.
	 *
	 * @return int
	 */
	public function removeExpired();

}
