<?php namespace Cartalyst\Sentinel\Persistences;
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

interface PersistenceRepositoryInterface {

	/**
	 * Checks for a persistence code in the current session.
	 *
	 * @return string
	 */
	public function check();

	/**
	 * Adds a new user persistence to the current session and attaches the user.
	 *
	 * @param  \Cartalyst\Sentinel\Persistence\PersistableInterface  $persistable
	 * @return void
	 */
	public function add(PersistableInterface $persistable);

	/**
	 * Adds a new user persistence, to remember.
	 *
	 * @param  \Cartalyst\Sentinel\Persistence\PersistableInterface  $persistable
	 * @return void
	 */
	public function addAndRemember(PersistableInterface $persistable);

	/**
	 * Removes the persistence bound to the current session.
	 *
	 * @param  \Cartalyst\Sentinel\Persistence\PersistableInterface  $persistable
	 * @return void
	 */
	public function remove();

	/**
	 * Flushes persistences for the given user.
	 *
	 * @param  \Cartalyst\Sentinel\Persistence\PersistableInterface  $persistable
	 * @param  bool  $current
	 * @return void
	 */
	public function flush(PersistableInterface $persistable, $current = true);

}
