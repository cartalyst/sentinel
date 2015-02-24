<?php namespace Cartalyst\Sentinel\Persistences;
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

interface PersistableInterface {

	/**
	 * Returns the persistable key name.
	 *
	 * @return string
	 */
	public function getPersistableKey();

	/**
	 * Returns the persistable key value.
	 *
	 * @return string
	 */
	public function getPersistableId();

	/**
	 * Returns the persistable relationship name.
	 *
	 * @return string
	 */
	public function getPersistableRelationship();

	/**
	 * Generates a random persist code.
	 *
	 * @return string
	 */
	public function generatePersistenceCode();

}
