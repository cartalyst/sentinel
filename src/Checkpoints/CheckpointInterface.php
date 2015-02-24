<?php

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

namespace Cartalyst\Sentinel\Checkpoints;

use Cartalyst\Sentinel\Users\UserInterface;

interface CheckpointInterface
{
    /**
     * Checkpoint after a user is logged in. Return false to deny persistence.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return bool
     */
    public function login(UserInterface $user);

    /**
     * Checkpoint for when a user is currently stored in the session.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return bool
     */
    public function check(UserInterface $user);

    /**
     * Checkpoint for when a failed login attempt is logged. User is not always
     * passed and the result of the method will not affect anything, as the
     * login failed.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return void
     */
    public function fail(UserInterface $user = null);
}
