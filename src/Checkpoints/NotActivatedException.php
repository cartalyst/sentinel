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
 * @version    2.0.18
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2019, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Checkpoints;

use Cartalyst\Sentinel\Users\UserInterface;
use RuntimeException;

class NotActivatedException extends RuntimeException
{
    /**
     * The user which caused the exception.
     *
     * @var \Cartalyst\Sentinel\Users\UserInterface
     */
    protected $user;

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
     * Sets the user associated with Sentinel (does not log in).
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface
     * @return void
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }
}
