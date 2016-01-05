### Roles

The role repository can be accessed using `Sentinel::getRoleRepository()` and allows you to manage roles using Sentinel.

> **Note** You can add the word `Role` between `find` and the method name and drop the `getRoleRepository` call. Example `Sentinel::findRoleBySlug($slug)` instead of `Sentinel::getRoleRepository()->findBySlug($slug)`.

#### Sentinel::findRoleById()

Finds a role by its ID.

Returns: `Cartalyst\Sentinel\Roles\RoleInterface` or `null`.

##### Arguments

Key | Required | Type  | Default | Description
--- | -------- | ----- | ------- | ---------------------------------------------
$id | true     | int   | null    | The role unique identifier.

##### Example

```php
$role = Sentinel::findRoleById(1);
```

##### Example Response

```
{
	id: "1",
	slug: "admin",
	name: "Admin",
	permissions: {
		admin: true
	},
	created_at: "2014-02-17 02:43:01",
	updated_at: "2014-02-17 02:43:37"
}
```

#### Sentinel::findRoleBySlug()

Finds a role by its slug.

Returns: `Cartalyst\Sentinel\Roles\RoleInterface` or `null`.

##### Arguments

Key   | Required | Type     | Default | Description
----- | -------- | -------- | ------- | ----------------------------------------
$slug | true     | string   | null    | The role slug.

##### Example

```php
$role = Sentinel::findRoleBySlug('admin');
```

##### Example Response

```
{
	id: "1",
	slug: "admin",
	name: "Admin",
	permissions: {
		admin: true
	},
	created_at: "2014-02-17 02:43:01",
	updated_at: "2014-02-17 02:43:37"
}
```

#### Sentinel::findRoleByName()

Finds a role by its name.

Returns: `Cartalyst\Sentinel\Roles\RoleInterface` or `null`.

##### Arguments

Key   | Required | Type     | Default | Description
----- | -------- | -------- | ------- | ----------------------------------------
$name | true     | string   | null    | The role name.

##### Example

```php
$role = Sentinel::findRoleByName('Admin');
```

##### Example Response

```
{
	id: "1",
	slug: "admin",
	name: "Admin",
	permissions: {
		admin: true
	},
	created_at: "2014-02-17 02:43:01",
	updated_at: "2014-02-17 02:43:37"
}
```

#### Sentinel::getRoleRepository()->createModel()

Creates a new role model instance.

```php
$role = Sentinel::getRoleRepository()->createModel();
```

#### Sentinel::getRoleRepository()->setModel()

Sets the role model.

Your new model needs to extend the `Cartalyst\Sentinel\Roles\EloquentRole` class.

##### Arguments

Key    | Required | Type   | Default | Description
------ | -------- | ------ | ------- | -----------------------------------------
$model | true     | string | null    | The roles model class name.

##### Example

```php
Sentinel::getRoleRepository()->setModel('Acme\Models\Role');
```

#### Create a new role.

Create a new role.

##### Arguments

Key         | Required | Type  | Default | Description
----------- | -------- | ----- | ------- | -------------------------------------
$attributes | true     | array | null    | The role attributes.

##### Example

```php
$role = Sentinel::getRoleRepository()->createModel()->create([
	'name' => 'Subscribers',
	'slug' => 'subscribers',
]);
```

##### Example Response

```
{
	name: "Subscribers",
	slug: "subscribers",
	created_at: "2014-02-17 02:43:01",
	updated_at: "2014-02-17 02:43:37",
	id: 2
}
```

#### Assign a user to a role.

```php
$user = Sentinel::findById(1);

$role = Sentinel::findRoleByName('Subscribers');

$role->users()->attach($user);
```

#### Remove a user from a role.

```php
$user = Sentinel::findById(1);

$role = Sentinel::findRoleByName('Subscribers');

$role->users()->detach($user);
```
