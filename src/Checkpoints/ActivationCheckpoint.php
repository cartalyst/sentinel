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

use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Users\UserInterface;

class ActivationCheckpoint implements CheckpointInterface {

	use AuthenticatedCheckpoint;

	/**
	 * The activation repository.
	 *
	 * @var \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface
	 */
	protected $activations;

	/**
	 * Create a new activation checkpoint.
	 *
	 * @param  \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface  $activations
	 * @return void
	 */
	public function __construct(ActivationRepositoryInterface $activations)
	{
		$this->activations = $activations;
	}

	/**
	 * {@inheritDoc}
	 */
	public function login(UserInterface $user)
	{
		return $this->checkActivation($user);
	}

	/**
	 * {@inheritDoc}
	 */
	public function check(UserInterface $user)
	{
		return $this->checkActivation($user);
	}

	/**
	 * Checks the activation status of the given user.
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
	 * @return bool
	 * @throws \Cartalyst\Sentinel\Checkpoints\NotActivatedException
	 */
	protected function checkActivation(UserInterface $user)
	{
		$completed = $this->activations->completed($user);

		if ( ! $completed)
		{
			$exception = new NotActivatedException('Your account has not been activated yet.');

			$exception->setUser($user);

			throw $exception;
		}
	}

}
