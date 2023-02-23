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

namespace Cartalyst\Sentinel\Roles;

use IteratorAggregate;

interface RoleInterface
{
    /**
     * Returns the role's primary key.
     *
     * @return int
     */
    public function getRoleId(): int;

    /**
     * Returns the role's slug.
     *
     * @return string
     */
    public function getRoleSlug(): string;

    /**
     * Returns all users for the role.
     *
     * @return \IteratorAggregate
     */
    public function getUsers(): IteratorAggregate;

    /**
     * Returns the users model.
     *
     * @return string
     */
    public static function getUsersModel(): string;

    /**
     * Sets the users model.
     *
     * @param string $usersModel
     *
     * @return void
     */
    public static function setUsersModel(string $usersModel): void;
}
