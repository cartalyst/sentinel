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

class Sha256Hasher implements HasherInterface {

	use Hasher;

	/**
	 * {@inheritDoc}
	 */
	public function hash($value)
	{
		$salt = $this->createSalt();

		return $salt.hash('sha256', $salt.$value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function check($value, $hashedValue)
	{
		$salt = substr($hashedValue, 0, $this->saltLength);

		return $this->slowEquals($salt.hash('sha256', $salt.$value), $hashedValue);
	}

}
