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

use Cartalyst\Sentinel\Users\UserInterface;
use RuntimeException;
use SpiExpressSecondFactor;

class SwipeIdentityException extends RuntimeException {

	/**
	 * The user which caused the exception.
	 *
	 * @var \Cartalyst\Sentinel\Users\UserInterface
	 */
	protected $user;

	/**
	 * The user which caused the exception.
	 *
	 * @var \Cartalyst\Sentinel\Users\UserInterface
	 */
	protected $response;

	/**
	 * Returns the user.
	 *
	 * @return \Cartalyst\Sentinel\Users\UserInterface
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Set the user associated with Sentinel (does not log in).
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
	 * @return void
	 */
	public function setUser(UserInterface $user)
	{
		$this->user = $user;
	}

	/**
	 * Returns the response.
	 *
	 * @return \SpiExpressSecondFactor
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Set the response.
	 *
	 * @param  \SpiExpressSecondFactor  $response
	 * @return void
	 */
	public function setResponse(SpiExpressSecondFactor $response)
	{
		$this->response = $response;
	}

}
