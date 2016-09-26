<?php namespace Cartalyst\Sentinel\Throttling;
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
 * @version    1.0.16
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Database\Eloquent\Model;

class EloquentThrottle extends Model {

	/**
	 * {@inheritDoc}
	 */
	protected $table = 'throttle';

	/**
	 * {@inheritDoc}
	 */
	protected $fillable = [
		'ip',
		'type',
	];

}
