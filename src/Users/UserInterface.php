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

namespace Cartalyst\Sentinel\Users;

interface UserInterface
{
    /**
     * Returns the user primary key.
     *
     * @return int
     */
    public function getUserId(): int;

    /**
     * Returns the user login.
     *
     * @return string
     */
    public function getUserLogin(): string;

    /**
     * Returns the user login attribute name.
     *
     * @return string
     */
    public function getUserLoginName(): string;

    /**
     * Returns the user password.
     *
     * @return string
     */
    public function getUserPassword(): string;
}
