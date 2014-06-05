<?php namespace Cartalyst\Sentinel\Laravel\Facades;
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

use Illuminate\Support\Facades\Facade;

class Reminder extends Facade {

	/**
	 * {@inheritDoc}
	 */
	protected static function getFacadeAccessor()
	{
		return 'sentinel.reminders';
	}

}
