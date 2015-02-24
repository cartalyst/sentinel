<?php namespace Cartalyst\Sentinel\Throttling;
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

interface ThrottleRepositoryInterface {

	/**
	 * Returns the global throttling delay, in seconds.
	 *
	 * @return int
	 */
	public function globalDelay();

	/**
	 * Returns the IP address throttling delay, in seconds.
	 *
	 * @param  string  $ipAddress
	 * @return int
	 */
	public function ipDelay($ipAddress);

	/**
	 * Returns the throttling delay for the given user, in seconds.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
	 * @return int
	 */
	public function userDelay(UserInterface $user);

	/**
	 * Logs a new throttling entry.
	 *
	 * @param  string  $ipAddress
	 * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
	 * @return void
	 */
	public function log($ipAddress = null, UserInterface $user = null);

}
