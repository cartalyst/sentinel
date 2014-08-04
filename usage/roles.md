## Roles

The role repository can be accessed using `Sentinel::getRoleRepository()` and allows you to manage roles using Sentinel.

> **Note** You can add the word `Role` between `find` and the method name and drop the `getRoleRepository` call. Example `Sentinel::findRoleBySlug` instead of `Sentinel::getRoleRepository()->findBySlug`.

### Sentinel::findRoleById($id)

Find a role by id.

```php
$role = Sentinel::findRoleById(1);
```

### Sentinel::findRoleBySlug($slug)

Find a role by slug.

```php
$role = Sentinel::findRoleBySlug('the-role-slug');
```

### Sentinel::findRoleByName($name)

Find a role by name.

```php
$role = Sentinel::findRoleByName('The Role Name');
```

### Sentinel::getRoleRepository()->createModel()

Creates a new role model instance.

```php
$role = Sentinel::getRoleRepository()->createModel();
```

### Sentinel::getRoleRepository()->setModel($model)

Sets the role model.

```php
Sentinel::getRoleRepository()->setModel('Your\Role\Model');
```

### Examples

The `$roles` variable throughout the examples refers to the role repository.

```php
$roles = Sentinel::getRoleRepository();
```

#### Create a new role.

```php
$roles->createModel()->create([
	'name' => 'Subscribers',
	'slug' => 'subscribers',
]);
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
