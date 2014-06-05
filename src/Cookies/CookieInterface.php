<?php namespace Cartalyst\Sentinel\Cookies;
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

interface CookieInterface {

	/**
	 * Put a value in the Sentinel cookie (to be stored until it's cleared).
	 *
	 * @param  mixed  $value
	 * @return void
	 */
	public function put($value);

	/**
	 * Returns the Sentinel cookie value.
	 *
	 * @return mixed
	 */
	public function get();

	/**
	 * Remove the Sentinel cookie.
	 *
	 * @return void
	 */
	public function forget();

}
