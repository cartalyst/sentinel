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

use Cartalyst\Sentinel\Groups\GroupableInterface;
use Cartalyst\Sentinel\Groups\GroupInterface;
use Cartalyst\Sentinel\Permissions\PermissibleInterface;
use Cartalyst\Sentinel\Persistence\PersistableInterface;
use Cartalyst\Sentinel\Permissions\SentinelPermissions;
use Illuminate\Database\Eloquent\Model;

class EloquentUser extends Model implements GroupableInterface, PermissibleInterface, PersistableInterface, UserInterface {

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
	 * The groups model name.
	 *
	 * @var string
	 */
	protected static $groupsModel = 'Cartalyst\Sentinel\Groups\EloquentGroup';

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
	 * Groups relationship.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function groups()
	{
		return $this->belongsToMany(static::$groupsModel, 'groups_users', 'user_id', 'group_id')->withTimestamps();
	}

	/**
	 * Get mutator for the "persistence codes" attribute.
	 *
	 * @param  mixed  $codes
	 * @return array
	 */
	public function getPersistenceCodesAttribute($codes)
	{
		return $codes ? json_decode($codes, true) : [];
	}

	/**
	 * Set mutator for the "persistence codes" attribute.
	 *
	 * @param  mixed  $codes
	 * @return void
	 */
	public function setPersistenceCodesAttribute(array $codes)
	{
		$this->attributes['persistence_codes'] = $codes ? json_encode(array_values($codes)) : '';
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
	public function getGroups()
	{
		return $this->groups;
	}

	/**
	 * {@inheritDoc}
	 */
	public function inGroup($group)
	{
		$group = array_first($this->groups, function($index, $instance) use ($group)
		{
			if ($group instanceof GroupInterface)
			{
				return ($instance->getGroupId() === $group->getGroupId());
			}

			if ($instance->getGroupId() == $group)
			{
				return true;
			}

			if ($instance->getGroupSlug() == $group)
			{
				return true;
			}

			return false;
		});

		return $group !== null;
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
	public function getPersistenceCodes()
	{
		return $this->persistence_codes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addPersistenceCode($code)
	{
		$codes = $this->persistence_codes;

		$codes[] = $code;

		$this->persistence_codes = $codes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function removePersistenceCode($code)
	{
		$codes = $this->persistence_codes;

		$index = array_search($code, $codes);

		if ($index !== false)
		{
			unset($codes[$index]);
		}

		$this->persistence_codes = $codes;
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

		$groupPermissions = [];

		foreach ($this->groups as $group)
		{
			$groupPermissions[] = $group->permissions;
		}

		return new SentinelPermissions($userPermissions, $groupPermissions);
	}

	/**
	 * Get the groups model.
	 *
	 * @return string
	 */
	public static function getGroupsModel()
	{
		return static::$groupsModel;
	}

	/**
	 * Set the groups model.
	 *
	 * @param  string  $groupsModel
	 * @return void
	 */
	public static function setGroupsModel($groupsModel)
	{
		static::$groupsModel = $groupsModel;
	}

	/**
	 * Dynamically pass missing methods to the user.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 * @throws \BadMethodCallException
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
