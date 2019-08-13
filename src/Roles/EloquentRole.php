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
 * @version    3.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2019, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Roles;

use IteratorAggregate;
use Illuminate\Database\Eloquent\Model;
use Cartalyst\Sentinel\Users\EloquentUser;
use Cartalyst\Sentinel\Permissions\PermissibleTrait;
use Cartalyst\Sentinel\Permissions\PermissibleInterface;
use Cartalyst\Sentinel\Permissions\PermissionsInterface;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EloquentRole extends Model implements PermissibleInterface, RoleInterface
{
    use PermissibleTrait;

    /**
     * {@inheritdoc}
     */
    protected $table = 'roles';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name',
        'slug',
        'permissions',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'permissions' => 'json',
    ];

    /**
     * The Eloquent users model name.
     *
     * @var string
     */
    protected static $usersModel = EloquentUser::class;

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        if ($this->exists && (! method_exists(static::class, 'isForceDeleting') || $this->isForceDeleting())) {
            $this->users()->detach();
        }

        return parent::delete();
    }

    /**
     * The Users relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(static::$usersModel, 'role_users', 'role_id', 'user_id')->withTimestamps();
    }

    // /**
    //  * Get mutator for the "permissions" attribute.
    //  *
    //  * @param mixed $permissions
    //  *
    //  * @return array
    //  */
    // public function getPermissionsAttribute($permissions)
    // {
    //     return $permissions ? json_decode($permissions, true) : [];
    // }

    // /**
    //  * Set mutator for the "permissions" attribute.
    //  *
    //  * @param mixed $permissions
    //  *
    //  * @return void
    //  */
    // public function setPermissionsAttribute(array $permissions)
    // {
    //     $this->attributes['permissions'] = $permissions ? json_encode($permissions) : '';
    // }

    /**
     * {@inheritdoc}
     */
    public function getRoleId(): int
    {
        return $this->getKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleSlug(): string
    {
        return $this->slug;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsers(): IteratorAggregate
    {
        return $this->users;
    }

    /**
     * {@inheritdoc}
     */
    public static function getUsersModel(): string
    {
        return static::$usersModel;
    }

    /**
     * {@inheritdoc}
     */
    public static function setUsersModel($usersModel): void
    {
        static::$usersModel = $usersModel;
    }

    /**
     * Dynamically pass missing methods to the permissions.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $methods = ['hasAccess', 'hasAnyAccess'];

        if (in_array($method, $methods)) {
            $permissions = $this->getPermissionsInstance();

            return call_user_func_array([$permissions, $method], $parameters);
        }

        return parent::__call($method, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function createPermissions(): PermissionsInterface
    {
        return new static::$permissionsClass($this->permissions);
    }
}
