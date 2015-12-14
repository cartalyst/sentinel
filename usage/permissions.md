### Permissions

Permissions can be broken down into two types and two implementations. Depending on the used implementation, these permission types will behave differently.

- Role Permissions
- User Permissions

*Standard* - This implementation will give the user-based permissions a higher priority and will override role-based permissions. Any permissions granted/rejected on the user will always take precendece over any role-based permissions assigned.

*Strict* - This implementation will reject a permission as soon as one rejected permission is found on either the user or any of the assigned roles. Granting a user a permission that is rejected on a role he is assigned to will not grant that user this permission.

Role-based permissions that define the same permission with different access rights will be rejected in case of any rejections on any role.

If a user is not assigned a permission, the user will inherit permissions from the role. If a user is assigned a permission of false or true, then the user's permission will override the role permission.

> **Note** The permission type is set to `StandardPermissions` by default; it can be changed on the `config` file.

###### Administrator Role

	{
		"name" : "Administrator",
		"permissions" : {
			"user.create" : true,
			"user.delete" : true,
			"user.view"   : true,
			"user.update" : true
		}
	}

###### Moderator Role

	{
		"name" : "Moderator",
		"permissions" : {
			"user.create" : false,
			"user.delete" : false,
			"user.view"   : true,
			"user.update" : true
		}
	}

And you have these three users, one as an Administrator, one as a Moderator and the last one has both the Administrator and Moderator roles assigned.

###### User - John Doe

	{
		"id" : 1,
		"first_name" : "John",
		"last_name" : "Doe",
		"roles" : ["administrator"],
		"permissions" : null
	}

This user has access to everything and can execute every action on your application.

###### User - Jane Smith

	{
		"id" : 2,
		"first_name" : "Jane",
		"last_name" : "Smith",
		"roles" : ["moderator"],
		"permissions" : {
			"user.update" : false
		}
	}

- Can view users.
- Cannot create, update or delete users.

> **Note:** The use of `user.update : false` demonstrates `Permission Inheritance`, which applies only when using `Standard Mode` (inheritance is disabled, by design, when using `Strict Mode`). When a permission is defined at the user-level, it **overrides** the same permission that is defined on the role. Given the above example, the user will be denied the `user.update` permission, even though the permission is allowed on the role.

###### User - Bruce Wayne

	{
		"id" : 3,
		"first_name" : "Bruce",
		"last_name" : "Wayne",
		"roles" : ["administrator", "moderator"],
		"permissions" : {
			"user.create" : true
		}
	}

- Can create, update and view users.
- Cannot execute delete users.

This is a special user, mainly because this user has two roles assigned. There are some things that you should know when assigning multiple roles to a user.

When a user has two or more roles assigned, if those roles define the same permissions but they have different values (e.g., one role grants the creation of users and the other role denies it), once any of those role permissions are denied, the user will be denied access to that permission, no matter what the other roles have as a permission value and no matter which permission type (`standard` or `strict`) is being used.

This means that for you to allow a permission for this specific user, you have to be using `standard` permissions and you have to change the user permission to grant access.

#### Usage

Permissions live on permissible models, users and roles.

You can add, modify, update or delete permissions directly on the objects.

##### Storing Permissions

Permissions can either be stored as associative arrays on the Eloquent `user` or `role` by assigning it to the `permissions` attribute or using designated permission methods which make the process easier.

**Array**

Grant the user `user.create` and reject `user.delete`.

```php
$user = Sentinel::findById(1);

$user->permissions = [
	'user.create' => true,
	'user.delete' => false,
];

$user->save();
```

Grant the role `user.update` and `user.view` permissions.

```php
$role = Sentinel::findRoleById(1);

$role->permissions = [
	'user.update' => true,
	'user.view' => true,
];

$role->save();
```

**Designated methods**

> **Note** `addPermission` and `updatePermission` will default to true, calling addPermission('x') will grant the user or role that permission, passing false as a second parameter will deny that permission.

Grant the user `user.create` and reject `user.update`.

```php
$user = Sentinel::findById(1);

$user->addPermission('user.create');
$user->addPermission('user.update', false);

$user->save();
```

Remove `user.delete` from the user.

> **Note** Removing a permission does not explicitly mean rejection, it will fallback to permission inheritance.

```php
$user = Sentinel::findById(1);

$user->removePermission('user.delete')->save();
```

Update existing `user.create` and reject `user.update`

```php
$role = Sentinel::findRoleById(1);

$role->updatePermission('user.create');
$role->updatePermission('user.update', false, true)->save();
```

> **Note 1:** `addPermission`, `updatePermission` and `removePermission` are chainable.
> **Note 2:** On `updatePermission`, passing `true` as a third argument will create the permission if it does not already exist.

##### Checking for Permissions

Permissions checks can be conducted using one of two methods.

Both methods can receive an argument of either a single permission passed as a string or an array of permissions.

##### hasAccess

This method will strictly require all passed permissions to be true in order to grant access.

This test will require both `user.create` and `user.update` to be true in order for permissions to be granted.

```php
$user = Sentinel::findById(1);

if ($user->hasAccess(['user.create', 'user.update']))
{
	// Execute this code if the user has permission
}
else
{
	// Execute this code if the permission check failed
}
```

##### hasAnyAccess

This method will grant access if any permission passes the check.

This test will require only one permission of `user.admin` and `user.create` to be true in order for permissions to be granted.

```php
if (Sentinel::hasAnyAccess(['user.admin', 'user.update']))
{
	// Execute this code if the user has permission
}
else
{
	// Execute this code if the permission check failed
}
```

> **Note** You can use `Sentinel::hasAccess()` or `Sentinel::hasAnyAccess()` directly which will call the methods on the currently logged in user, incase there's no user logged in, a `BadMethodCallException` will be thrown.

##### Wildcard Checks

Permissions can be checked based on wildcards using the `*` character to match any of a set of permissions.

```php
$user = Sentinel::findById(1);

if ($user->hasAccess('user.*'))
{
	// Execute this code if the user has permission
}
else
{
	// Execute this code if the permission check failed
}
```

#### Controller Based Permissions

You can easily implement permission checks based on controller methods, consider the following example implemented as a Laravel filter.

Permissions can be stored as action names on users and roles, then simply perform checks on the action before executing it and redirect on failure with an error message.

```php
Route::filter('permissions', function($route, $request)
{
	$action = $route->getActionName();

	if (Sentinel::hasAccess($action))
	{
		return;
	}

	return Redirect::to('/')->withErrors('Permission denied.');
});
```
