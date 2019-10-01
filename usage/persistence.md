### Persistence

Persistence allows you to manage persistences through Sentinel.

> **Note** The `$persistence` variable below is the PersistenceRepository, you can get it using `Sentinel::getPersistenceRepository()`

#### $persistence->check()

Checks for an active persistence record.

Returns: the persistence code or `null`.

```php
$code = $persistence->check();
```

#### $persistence->findByPersistenceCode($code)

Find a persistence record by code.

Returns: the persistence object or `false`.

```php
$persistence = $persistence->findByPersistenceCode('foobar');
```

#### $persistence->findUserByPersistenceCode($code)

Find the user that is associated with the given persistence code.

Returns: the user object or `false`.

```php
$user = $persistence->findUserByPersistenceCode('foobar');
```

#### $persistence->persist($user, $remember)

Persist a user.

Returns: `bool`.

```php
$user = Sentinel::findById(1);

$persistence->persist($user);
```

#### $persistence->persistAndRemember($user, $remember)

Persist and remember a user.

Returns: `bool`.

```php
$user = Sentinel::findById(1);

$persistence->persistAndRemember($user);
```

#### $persistence->forget()

Forget the current persistence cookie and session.

Returns: `bool` or `null`.

```php
$persistence->forget();
```

#### $persistence->remove($code)

Remove the persistence record that matches the given code.

Returns: `bool` or `null`.

```php
$persistence->remove('foobar');
```

#### $persistence->flush($user, $forget)

Flushes all persistence records for the given user including the current session by default, passing in false a second parameter will keep current session active.

```php
$persistence->flush($user);
```

#### $persistence->createModel()

Creates a new persistence model instance.

```php
$persistence = $persistence->createModel();
```

#### $persistence->setModel($model)

Sets the persistence model.

```php
$persistence->setModel('Your\Persistence\Model');
```

#### Usage Scenarios

Storing additional information along sessions.

In the example below, we will store the browser name, version and platform on every session it was created on.

> **Note** This example uses `cbschuld/browser.php`, make sure you add it to your composer.json to use the browser methods.

- Migration

Create a new migration that alters the `persistences` table to add three new fields, `browser`, `version` and `platform`.

- Model

Create a new `Persistence` model that extends the default Persistence model.

- Override the constructor.

Override the constructor to populate the new attributes.

- Config

Update your sentinel config file to reference your new `Persistence` model.

```php
<?php

use Cartalyst\Sentinel\Persistences\EloquentPersistence;

class Persistence extends EloquentPersistence {

	/**
	 * {@inheritDoc}
	 */
	protected $guarded = [];

	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $attributes = array())
	{
		$browser = new Browser;

		$attributes['version']  = $browser->getVersion();
		$attributes['platform'] = $browser->getPlatform();
		$attributes['browser']  = $browser->getBrowser();

		parent::__construct($attributes);
	}

}
```
