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

use Cartalyst\Sentinel\Swipe\SwipeInterface;
use Cartalyst\Sentinel\Users\UserInterface;
use SpiExpressSecondFactor;

class SwipeIdentityCheckpoint implements CheckpointInterface {

	use AuthenticatedCheckpoint;

	/**
	 * The Swipe repository
	 *
	 * @var \Cartalyst\Sentinel\Swipe\SwipeInterface
	 */
	protected $swipe;

	/**
	 * Constructor.
	 *
	 * @param  \Cartalyst\Sentinel\Swipe\SwipeInterface  $swipe
	 * @return void
	 */
	public function __construct(SwipeInterface $swipe)
	{
		$this->swipe = $swipe;
	}

	/**
	 * {@inheritDoc}
	 */
	public function login(UserInterface $user)
	{
		if ($this->swipe->isAnswering())
		{
			return true;
		}

		list($response, $code) = $this->swipe->response($user);

		switch ($code)
		{
			case NEED_REGISTER_SMS:
				$message = 'User needs to register SMS.';
				break;

			case NEED_REGISTER_SWIPE:
				$message = 'User needs to register their swipe application.';
				break;

			case RC_SWIPE_TIMEOUT:
				return false;

			case RC_SWIPE_ACCEPTED:
				return true;

			case RC_SWIPE_REJECTED:
				$message = 'User has rejected swipe request.';
				break;

			case RC_SMS_DELIVERED:
				$message = 'SMS was delivered to user.';
				break;

			case RC_ERROR:
				$message = 'An error occured with Swipe Identity.';
				break;

			case RC_APP_DOES_NOT_EXIST:
				$message = 'Your Swipe Identity app is misconfigured.';
				break;
		}

		$this->throwException($message, $code, $user, $response);
	}

	/**
	 * {@inheritDoc}
	 */
	public function check(UserInterface $user)
	{
		return true;
	}

	/**
	 * Throws an exception due to an unsuccessful Swipe Identity authentication.
	 *
	 * @param  string  $message
	 * @param  int  $code
	 * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
	 * @param  \SpiExpressSecondFactor  $response
	 * @throws \Cartalyst\Sentinel\Checkpoints\SwipeIdentityException
	 */
	protected function throwException($message, $code, UserInterface $user, SpiExpressSecondFactor $response)
	{
		$exception = new SwipeIdentityException($message, $code);

		$exception->setUser($user);

		$exception->setResponse($response);

		throw $exception;
	}

}
