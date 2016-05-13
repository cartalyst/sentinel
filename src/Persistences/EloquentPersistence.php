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
 * @version    2.0.12
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Persistences;

use Illuminate\Database\Eloquent\Model;

class EloquentPersistence extends Model implements PersistenceInterface
{
    /**
     * {@inheritDoc}
     */
    protected $table = 'persistences';

    /**
     * The users model name.
     *
     * @var string
     */
    protected static $usersModel = 'Cartalyst\Sentinel\Users\EloquentUser';

    /**
     * {@inheritDoc}
     */
    public function user()
    {
        return $this->belongsTo(static::$usersModel);
    }

    /**
     * Get the users model.
     *
     * @return string
     */
    public static function getUsersModel()
    {
        return static::$usersModel;
    }

    /**
     * Set the users model.
     *
     * @param  string  $usersModel
     * @return void
     */
    public static function setUsersModel($usersModel)
    {
        static::$usersModel = $usersModel;
    }
}
