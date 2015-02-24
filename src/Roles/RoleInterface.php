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

namespace Cartalyst\Sentinel\Roles;

interface RoleInterface
{
    /**
     * Returns the role's primary key.
     *
     * @return int
     */
    public function getRoleId();

    /**
     * Returns the role's slug.
     *
     * @return string
     */
    public function getRoleSlug();

    /**
     * Returns all users for the role.
     *
     * @return \IteratorAggregate
     */
    public function getUsers();

    /**
     * Returns the users model.
     *
     * @return string
     */
    public static function getUsersModel();

    /**
     * Sets the users model.
     *
     * @param  string  $usersModel
     * @return void
     */
    public static function setUsersModel($usersModel);
}
