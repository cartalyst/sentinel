## Permissions

Permissions can be broken down into two types and two implementations.

- Group-based Permissions
- User-based Permissions

Depending on the used implementation, these permissions will behave differently.

### Permissions implementations

##### SentinelPermissions

This implementation will reject a permission as soon as one rejected permission is found on either the user or any of the assigned groups, granting a user a permission that is rejected on a group he is assigned to will not grant that user this permission.

##### StrictPermissions

This implementation will give the user-based permissions a higher priority and will override group-based permissions, any permissions granted/rejected on the user will always take precendece over any group-based permissions assigned.

Group-based permissions that define the same permission with different access rights will be rejected incase of any rejections on any group.

If a user is not assigned a permission, the user will inherit permissions from the group. If a user is assigned a permission of false or true, then the user's permission will override the group permission.

> **Note** The permission type is seto to `SentinelPermissions` by default, it can be changed on the `config` file.

##### Example

###### Administrator Group

	{
		"name" : "Administrator",
		"permissions" : {
			"user.create" : true,
			"user.delete" : true,
			"user.view"   : true,
			"user.update" : true
		}
	}

###### Moderator Group

	{
		"name" : "Moderator",
		"permissions" : {
			"user.create" : false,
			"user.delete" : false,
			"user.view"   : true,
			"user.update" : true
		}
	}

And you have these three users, one as an Administrator, one as a Moderator and the last one has both the Administrator and Moderator groups assigned.

##### User - John Doe

	{
		"id" : 1,
		"first_name" : "John",
		"last_name" : "Doe",
		"groups" : ["administrator"],
		"permissions" : null
	}

###### Actions he can execute

This user has access to everything and can execute every action on your application.

##### User - Jane Smith

	{
		"id" : 2,
		"first_name" : "Jane",
		"last_name" : "Smith",
		"groups" : ["moderator"],
		"permissions" : {
			"user.update" : false
		}
	}

###### Actions she can execute

View users.

###### Actions she cannot execute

Create, Update or Delete users.

> **Note:** We are using `Permission Inheritance` here, hence the `user.update : false` which means whatever you define on your group permission this user permission will inherit that permission, which means that in this case the user is denied access to update users.

##### User - Bruce Wayne

	{
		"id" : 3,
		"first_name" : "Bruce",
		"last_name" : "Wayne",
		"groups" : ["administrator", "moderator"],
		"permissions" : {
			"user.create" : true
		}
	}

###### Actions he can execute

Create, Update and View users.

###### Actions he cannot execute

Delete users.

Since this is a special user, mainly because this user has two assigned groups, there are some things that you should know when assigning multiple groups to a user.

When a user has two or more groups assigned, if those groups have the same permissions but different permission access's are assigned, once any of those group permissions are denied, the user will be denied access to that permission no matter what the other groups have as a permission value and no matter with permission type is being used.

Which means for you to allow a permission to this specific user, you have to be using `strict` permissions and you have to change the user permission to grant access.

### Usage

Permissions live on permissible models, users and groups.

You can add, modify, update or delete permissions right on the objects.

#### Storing Permissions

```php
$user = Sentinel::findById(1);

$user->permissions = [
	'user.create' => true,
	'user.delete' => false,
];

$user->save();
```

```php
$group = Sentinel::findGroupById(1);

$group->permissions = [
	'user.update' => true,
	'user.view' => true,
];
```

#### Checking for Permissions

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

#### Wildcard Checks

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

#### Advanced Usage Scenarios

##### Controller Based Permissions

You can easily implement permission checks based on controller methods, consider the following example implemented as a Laravel filter.

Permissions can be stored as action names on users and groups, then simply perform checks on the action before executing it and redirect on failure with an error message.

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
