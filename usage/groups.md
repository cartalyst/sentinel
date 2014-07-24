### Groups

The group repository can be accessed using `Sentinel::getGroupRepository()` and allows you to manage groups using Sentinel.

> **Note** You can add the word `Group` between `find` and the method name and drop the `getGroupRepository` call. Example `Sentinel::findGroupBySlug` instead of `Sentinel::getGroupRepository()->findBySlug`.

#### Sentinel::findGroupById($id)

Find a group by id.

```php
$group = Sentinel::findGroupById(1);
```

#### Sentinel::findGroupBySlug($slug)

Find a group by slug.

```php
$group = Sentinel::findGroupBySlug('the-group-slug');
```

#### Sentinel::findGroupByName($name)

Find a group by name.

```php
$group = Sentinel::findGroupByName('The Group Name');
```

#### Sentinel::getGroupRepository()->createModel()

Creates a new group model instance.

```php
$group = Sentinel::getGroupRepository()->createModel();
```

#### Sentinel::getGroupRepository()->setModel($model)

Sets the group model.

```php
Sentinel::getGroupRepository()->setModel('Your\Group\Model');
```

#### Examples

The `$groups` variable throughout the examples refers to the group repository.

```php
$groups = Sentinel::getGroupRepository();
```

##### Create a new group.

```php
$groups->createModel()->create([
	'name' => 'Subscribers',
	'slug' => 'subscribers',
]);
```

##### Assign a user to a group.

```php
$user = Sentinel::findById(1);

$group = Sentinel::findGroupByName('Subscribers');

$group->users()->attach($user);
```

##### Remove a user from a group.

```php
$user = Sentinel::findById(1);

$group = Sentinel::findGroupByName('Subscribers');

$group->users()->detach($user);
```
