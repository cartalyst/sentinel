<?php namespace Cartalyst\Sentinel\Persistence;
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

interface PersistableInterface {

	/**
	 * Generates a random persist code.
	 *
	 * @return string
	 */
	public function generatePersistenceCode();

	/**
	 * Returns an array of assigned persist codes.
	 *
	 * @return array
	 */
	public function getPersistenceCodes();

	/**
	 * Adds a new persist code.
	 *
	 * @param  string  $code
	 * @return void
	 */
	public function addPersistenceCode($code);

	/**
	 * Removes a persist code.
	 *
	 * @param  string  $code
	 * @return void
	 */
	public function removePersistenceCode($code);

}
