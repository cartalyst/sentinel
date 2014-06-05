<?php namespace Cartalyst\Sentinel\Swipe;
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

use Cartalyst\Sentinel\Users\UserInterface;
use Closure;

interface SwipeInterface {

	/**
	 * Return the Swipe Identity authentication response object and code.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
	 * @return array
	 */
	public function response(UserInterface $user);

	/**
	 * Set the SMS number for the given user.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
	 * @param  string  $number
	 * @return bool
	 */
	public function saveNumber(UserInterface $user, $number);

	/**
	 * Checks the SMS answer for the given user. Pass an optional callback to be
	 * executed on successful verification of the answer to be executed while
	 * the object is in an answering state. If you choose to pass a callback,
	 * it's return value should be cascaded out of this method. If not, the
	 * boolean result of the SMS answer should be returned instead.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
	 * @param  string  $answer
	 * @param  \Closure  $callback
	 * @return mixed
	 */
	public function checkAnswer(UserInterface $user, $answer, Closure $callback = null);

}
