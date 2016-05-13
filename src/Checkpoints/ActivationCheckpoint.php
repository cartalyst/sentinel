<?php

/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Sentinel
 * @version    2.0.12
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Checkpoints;

use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Users\UserInterface;

class ActivationCheckpoint implements CheckpointInterface
{
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

        if (! $completed) {
            $exception = new NotActivatedException('Your account has not been activated yet.');

            $exception->setUser($user);

            throw $exception;
        }
    }
}
