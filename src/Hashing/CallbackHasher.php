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

use Closure;

class CallbackHasher implements HasherInterface {

	/**
	 * The closure used for hashing a value.
	 *
	 * @var \Closure
	 */
	protected $hash;

	/**
	 * The closure used for checking a hashed value.
	 *
	 * @var \Closure
	 */
	protected $check;

	/**
	 * Create a new callback hasher instance.
	 *
	 * @param  \Closure  $hash
	 * @param  \Closure  $check
	 * @return void
	 */
	public function __construct(Closure $hash, Closure $check)
	{
		$this->hash = $hash;

		$this->check = $check;
	}

	/**
	 * {@inheritDoc}
	 */
	public function hash($value)
	{
		$callback = $this->hash;

		return $callback($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function check($value, $hashedValue)
	{
		$callback = $this->check;

		return $callback($value, $hashedValue);
	}

}
