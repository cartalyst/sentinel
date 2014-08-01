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

use Fuel\Core\Cookie;

class FuelPHPCookie implements CookieInterface {

	/**
	 * The cookie key.
	 *
	 * @var string
	 */
	protected $key = 'cartalyst_sentinel';

	/**
	 * Create a new FuelPHP cookie driver.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function __construct($key = null)
	{
		if (isset($key))
		{
			$this->key = $key;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function put($value)
	{
		Cookie::set($this->key, serialize($value), 2628000);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get()
	{
		$value = Cookie::get($this->key);

		if ($value)
		{
			return unserialize($value);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function forget()
	{
		Cookie::delete($this->key);
	}

}
