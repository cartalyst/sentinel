<?php namespace Cartalyst\Sentinel\Hashing;
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

use RuntimeException;

class NativeHasher implements HasherInterface {

	/**
	 * {@inheritDoc}
	 */
	public function hash($value)
	{
		if ( ! $hash = password_hash($value, PASSWORD_DEFAULT))
		{
			throw new RuntimeException('Error hashing value. Check system compatibility with password_hash().');
		}

		return $hash;
	}

	/**
	 * {@inheritDoc}
	 */
	public function check($value, $hashedValue)
	{
		return password_verify($value, $hashedValue);
	}

}
