<?php namespace Cartalyst\Sentinel\Checkpoints;
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

use Carbon\Carbon;
use RuntimeException;

class ThrottlingException extends RuntimeException {

	/**
	 * Delay, in seconds.
	 *
	 * @var string
	 */
	protected $delay;

	/**
	 * Throttling type which caused the exception.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Returns the delay.
	 *
	 * @return int
	 */
	public function getDelay()
	{
		return $this->delay;
	}

	/**
	 * Set the delay.
	 *
	 * @param  int  $delay
	 * @return void
	 */
	public function setDelay($delay)
	{
		$this->delay = $delay;
	}

	/**
	 * Returns the type.
	 *
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Set the type.
	 *
	 * @param  int  $type
	 * @return void
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * Returns a Carbon object representing the time which the throttle is lifted.
	 *
	 * @return \Carbon\Carbon
	 */
	public function getFree()
	{
		return Carbon::now()->addSeconds($this->delay);
	}

}
