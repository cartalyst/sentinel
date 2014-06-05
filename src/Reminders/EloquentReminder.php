<?php namespace Cartalyst\Sentinel\Reminders;
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

use Illuminate\Database\Eloquent\Model;

class EloquentReminder extends Model {

	/**
	 * {@inheritDoc}
	 */
	protected $table = 'reminders';

	/**
	 * {@inheritDoc}
	 */
	protected $fillable = [
		'code',
		'completed',
		'completed_at',
	];

	/**
	 * Get mutator for the "completed" attribute.
	 *
	 * @param  mixed  $completed
	 * @return bool
	 */
	public function getCompletedAttribute($completed)
	{
		return (bool) $completed;
	}

	/**
	 * Set mutator for the "completed" attribute.
	 *
	 * @param  mixed  $completed
	 * @return void
	 */
	public function setCompletedAttribute($completed)
	{
		$this->attributes['completed'] = (int) (bool) $completed;
	}

}
