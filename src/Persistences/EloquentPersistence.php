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
 * @version    7.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2023, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Sentinel\Persistences;

use Illuminate\Database\Eloquent\Model;
use Cartalyst\Sentinel\Users\EloquentUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EloquentPersistence extends Model implements PersistenceInterface
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'persistences';

    /**
     * The Users model FQCN.
     *
     * @var string
     */
    protected static $usersModel = EloquentUser::class;

    /**
     * {@inheritdoc}
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(static::$usersModel);
    }

    /**
     * Get the Users model FQCN.
     *
     * @return string
     */
    public static function getUsersModel(): string
    {
        return static::$usersModel;
    }

    /**
     * Set the Users model FQCN.
     *
     * @param string $usersModel
     *
     * @return void
     */
    public static function setUsersModel(string $usersModel): void
    {
        static::$usersModel = $usersModel;
    }
}
