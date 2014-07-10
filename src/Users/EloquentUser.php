<?php namespace Cartalyst\Sentinel\Users;
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

use Cartalyst\Sentinel\Roles\RoleableInterface;
use Cartalyst\Sentinel\Roles\RoleInterface;
use Cartalyst\Sentinel\Permissions\PermissibleInterface;
use Cartalyst\Sentinel\Persistences\PersistableInterface;
use Cartalyst\Sentinel\Permissions\SentinelPermissions;
use Illuminate\Database\Eloquent\Model;

class EloquentUser extends Model implements RoleableInterface, PermissibleInterface, PersistableInterface, UserInterface {

	/**
	 * {@inheritDoc}
	 */
	protected $table = 'users';

	/**
	 * {@inheritDoc}
	 */
	protected $fillable = [
		'email',
		'password',
		'permissions',
		'first_name',
		'last_name',
	];

	/**
	 * {@inheritDoc}
	 */
	protected $persistableKey = 'user_id';

	/**
	 * {@inheritDoc}
	 */
	protected $persistableRelationship = 'persistences';

	/**
	 * Cached permissions instance for the given user.
	 *
	 * @var \Cartalyst\Sentinel\Permissions\PermissionsInterface
	 */
	protected $permissionsInstance;

	/**
	 * Array of login column names.
	 *
	 * @var array
	 */
	protected $loginNames = ['email'];

	/**
	 * The roles model name.
	 *
	 * @var string
	 */
	protected static $rolesModel = 'Cartalyst\Sentinel\Roles\EloquentRole';

	/**
	 * The persistences model name.
	 *
	 * @var string
	 */
	protected static $persistencesModel = 'Cartalyst\Sentinel\Persistences\EloquentPersistence';

	/**
	 * Returns an array of login column names.
	 *
	 * @return array
	 */
	public function getLoginNames()
	{
		return $this->loginNames;
	}

	/**
	 * Roles relationship.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function roles()
	{
		return $this->belongsToMany(static::$rolesModel, 'roles_users', 'user_id', 'role_id')->withTimestamps();
	}

	/**
	 * Persistences relationship.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function persistences()
	{
		return $this->hasMany(static::$persistencesModel, 'user_id');
	}

	/**
	 * Get mutator for the "permissions" attribute.
	 *
	 * @param  mixed  $permissions
	 * @return array
	 */
	public function getPermissionsAttribute($permissions)
	{
		return $permissions ? json_decode($permissions, true) : [];
	}

	/**
	 * Set mutator for the "permissions" attribute.
	 *
	 * @param  mixed  $permissions
	 * @return void
	 */
	public function setPermissionsAttribute(array $permissions)
	{
		$this->attributes['permissions'] = $permissions ? json_encode($permissions) : '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRoles()
	{
		return $this->roles;
	}

	/**
	 * {@inheritDoc}
	 */
	public function inRole($role)
	{
		$role = array_first($this->roles, function($index, $instance) use ($role)
		{
			if ($role instanceof RoleInterface)
			{
				return ($instance->getRoleId() === $role->getRoleId());
			}

			if ($instance->getRoleId() == $role)
			{
				return true;
			}

			if ($instance->getRoleSlug() == $role)
			{
				return true;
			}

			return false;
		});

		return $role !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPermissions()
	{
		if ($this->permissionsInstance === null)
		{
			$this->permissionsInstance = $this->createPermissions();
		}

		return $this->permissionsInstance;
	}

	/**
	 * {@inheritDoc}
	 */
	public function generatePersistenceCode()
	{
		return str_random(32);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUserId()
	{
		return $this->getKey();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPersistableId()
	{
		return $this->getKey();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPersistableKey()
	{
		return $this->persistableKey;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPersistableRelationship()
	{
		return $this->persistableRelationship;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPersistableKey($key)
	{
		$this->persistableKey = $key;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUserLogin()
	{
		return $this->getAttribute($this->getUserLoginName());
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUserLoginName()
	{
		return reset($this->loginNames);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUserPassword()
	{
		return $this->password;
	}

	/**
	 * Creates a permissions object.
	 *
	 * @return \Cartalyst\Sentinel\Permissions\PermissionsInterface
	 */
	protected function createPermissions()
	{
		$userPermissions = $this->permissions;

		$rolePermissions = [];

		foreach ($this->roles as $role)
		{
			$rolePermissions[] = $role->permissions;
		}

		return new SentinelPermissions($userPermissions, $rolePermissions);
	}

	/**
	 * Get the roles model.
	 *
	 * @return string
	 */
	public static function getRolesModel()
	{
		return static::$rolesModel;
	}

	/**
	 * Set the roles model.
	 *
	 * @param  string  $rolesModel
	 * @return void
	 */
	public static function setRolesModel($rolesModel)
	{
		static::$rolesModel = $rolesModel;
	}

	/**
	 * Get the persistences model.
	 *
	 * @return string
	 */
	public static function getPersistencesModel()
	{
		return static::$persistencesModel;
	}

	/**
	 * Set the persistences model.
	 *
	 * @param  string  $persistencesModel
	 * @return void
	 */
	public static function setPersistencesModel($persistencesModel)
	{
		static::$persistencesModel = $persistencesModel;
	}

	/**
	 * Dynamically pass missing methods to the user.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		$methods = ['hasAccess', 'hasAnyAccess'];

		if (in_array($method, $methods))
		{
			$permissions = $this->getPermissions();

			return call_user_func_array([$permissions, $method], $parameters);
		}

		return parent::__call($method, $parameters);
	}

}
