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
 * @version    6.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2022, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Sentinel\Users;

use Closure;

interface UserRepositoryInterface
{
    /**
     * Finds a user by the given primary key.
     *
     * @param int $id
     *
     * @return \Cartalyst\Sentinel\Users\UserInterface|null
     */
    public function findById(int $id): ?UserInterface;

    /**
     * Finds a user by the given credentials.
     *
     * @param array $credentials
     *
     * @return \Cartalyst\Sentinel\Users\UserInterface|null
     */
    public function findByCredentials(array $credentials): ?UserInterface;

    /**
     * Finds a user by the given persistence code.
     *
     * @param string $code
     *
     * @return \Cartalyst\Sentinel\Users\UserInterface|null
     */
    public function findByPersistenceCode(string $code): ?UserInterface;

    /**
     * Records a login for the given user.
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface $user
     *
     * @return bool
     */
    public function recordLogin(UserInterface $user): bool;

    /**
     * Records a logout for the given user.
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface $user
     *
     * @return bool
     */
    public function recordLogout(UserInterface $user): bool;

    /**
     * Validate the password of the given user.
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface $user
     * @param array                                   $credentials
     *
     * @return bool
     */
    public function validateCredentials(UserInterface $user, array $credentials): bool;

    /**
     * Validate if the given user is valid for creation.
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function validForCreation(array $credentials): bool;

    /**
     * Validate if the given user is valid for updating.
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface|int $user
     * @param array                                       $credentials
     *
     * @return bool
     */
    public function validForUpdate($user, array $credentials): bool;

    /**
     * Creates a user.
     *
     * @param array         $credentials
     * @param \Closure|null $callback
     *
     * @return \Cartalyst\Sentinel\Users\UserInterface|null
     */
    public function create(array $credentials, Closure $callback = null): ?UserInterface;

    /**
     * Updates a user.
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface|int $user
     * @param array                                       $credentials
     *
     * @return \Cartalyst\Sentinel\Users\UserInterface
     */
    public function update($user, array $credentials): UserInterface;
}
