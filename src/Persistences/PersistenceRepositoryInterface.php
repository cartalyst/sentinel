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
 * @version    2.0.17
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2017, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Persistences;

interface PersistenceRepositoryInterface
{
    /**
     * Checks for a persistence code in the current session.
     *
     * @return string
     */
    public function check();

    /**
     * Finds a persistence by persistence code.
     *
     * @param  string  $code
     * @return \Cartalyst\Sentinel\Persistences\PersistenceInterface|false
     */
    public function findByPersistenceCode($code);

    /**
     * Finds a user by persistence code.
     *
     * @param  string  $code
     * @return \Cartalyst\Sentinel\Users\UserInterface|false
     */
    public function findUserByPersistenceCode($code);

    /**
     * Adds a new user persistence to the current session and attaches the user.
     *
     * @param  \Cartalyst\Sentinel\Persistences\PersistenceInterface  $persistable
     * @param  bool  $remember
     * @return bool
     */
    public function persist(PersistableInterface $persistable, $remember = false);

    /**
     * Adds a new user persistence, to remember.
     *
     * @param  \Cartalyst\Sentinel\Persistences\PersistableInterface  $persistable
     * @return bool
     */
    public function persistAndRemember(PersistableInterface $persistable);

    /**
     * Removes the persistence bound to the current session.
     *
     * @param  \Cartalyst\Sentinel\Persistences\PersistableInterface  $persistable
     * @return bool|null
     */
    public function forget();

    /**
     * Removes the given persistence code.
     *
     * @param  string  $code
     * @return bool|null
     */
    public function remove($code);

    /**
     * Flushes persistences for the given user.
     *
     * @param  \Cartalyst\Sentinel\Persistence\PersistableInterface  $persistable
     * @param  bool  $forget
     * @return void
     */
    public function flush(PersistableInterface $persistable, $forget = true);
}
