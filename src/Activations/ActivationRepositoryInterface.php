<?php

/*
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
 * @version    7.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2023, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Sentinel\Activations;

use Cartalyst\Sentinel\Users\UserInterface;

interface ActivationRepositoryInterface
{
    /**
     * Create a new activation record and code.
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface $user
     *
     * @return \Cartalyst\Sentinel\Activations\ActivationInterface
     */
    public function create(UserInterface $user): ActivationInterface;

    /**
     * Gets the activation for the given user.
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface $user
     * @param string|null                             $code
     *
     * @return \Cartalyst\Sentinel\Activations\ActivationInterface|null
     */
    public function get(UserInterface $user, string $code = null): ?ActivationInterface;

    /**
     * Checks if a valid activation for the given user exists.
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface $user
     * @param string|null                             $code
     *
     * @return bool
     */
    public function exists(UserInterface $user, string $code = null): bool;

    /**
     * Completes the activation for the given user.
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface $user
     * @param string                                  $code
     *
     * @return bool
     */
    public function complete(UserInterface $user, string $code): bool;

    /**
     * Checks if a valid activation has been completed.
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface $user
     *
     * @return bool
     */
    public function completed(UserInterface $user): bool;

    /**
     * Remove an existing activation (deactivate).
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface $user
     *
     * @return bool|null
     */
    public function remove(UserInterface $user): ?bool;

    /**
     * Remove expired activation codes.
     *
     * @return bool
     */
    public function removeExpired(): bool;
}
