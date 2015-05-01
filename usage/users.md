### Users

The user repository can be accessed using `Sentinel::getUserRepository()` and allows you to manage users using Sentinel.

> **Note 1** You can use the methods below directly on the Sentinel facade without the `getUserRepository` part. Example `Sentinel::findById(1)` instead of `Sentinel::getUserRepository()->findById(1)`.

> **Note 2** You can add the word `User` between `find` and the method name and drop the `getUserRepository` call. Example `Sentinel::findUserByCredentials($credentials)` instead of `Sentinel::getUserRepository()->findByCredentials($credentials)`.

#### Sentinel::findById()

Finds a user using it's id.

Returns: `Cartalyst\Sentinel\Users\UserInterface` or `null`.

##### Arguments

Key | Required | Type  | Default | Description
--- | -------- | ----- | ------- | ---------------------------------------------
$id | true     | int   | null    | The user unique identifier.

##### Example

```php
$user = Sentinel::findById(1);
```

##### Example Response

```
{
	id: "1",
	email: "john.doe@example.com",
	permissions: {
		admin: true
	},
	last_login: {
		date: "2014-02-17 03:44:31",
		timezone_type: 3,
		timezone: "UTC"
	},
	first_name: "John",
	last_name: "Doe",
	created_at: "2014-02-17 02:43:01",
	updated_at: "2014-02-17 02:43:37"
}
```

#### Sentinel::findByCredentials()

Finds a user by it's credentials.

Returns: `Cartalyst\Sentinel\Users\UserInterface` or `null`.

##### Arguments

Key          | Required | Type  | Default | Description
------------ | -------- | ----- | ------- | ------------------------------------
$credentials | true     | array | null    | The user credentials.

##### Example

```php
$credentials = [
	'login' => 'john.doe@example.com',
];

$user = Sentinel::findByCredentials($credentials);
```

##### Example Response

```
{
	id: "1",
	email: "john.doe@example.com",
	permissions: {
		admin: true
	},
	last_login: {
		date: "2014-02-17 03:44:31",
		timezone_type: 3,
		timezone: "UTC"
	},
	first_name: "John",
	last_name: "Doe",
	created_at: "2014-02-17 02:43:01",
	updated_at: "2014-02-17 02:43:37"
}
```

#### Sentinel::findByPersistenceCode()

Finds a user by persistence code.

Returns: `Cartalyst\Sentinel\Users\UserInterface` or `null`.

##### Arguments

Key   | Required | Type  | Default | Description
----- | -------- | ----- | ------- | -------------------------------------------
$code | true     | string | null   | The persistence code.

##### Example

```php
$user = Sentinel::findByPersistenceCode('persistence_code_here');
```

##### Example Response

```
{
	id: "1",
	email: "john.doe@example.com",
	permissions: {
		admin: true
	},
	last_login: {
		date: "2014-02-17 03:44:31",
		timezone_type: 3,
		timezone: "UTC"
	},
	first_name: "John",
	last_name: "Doe",
	created_at: "2014-02-17 02:43:01",
	updated_at: "2014-02-17 02:43:37"
}
```

#### Sentinel::validateCredentials()

Validates the user credentials.

This is useful when you want to verify if the current user password matches the given password.

##### Arguments

Key          | Required | Type  | Default | Description
------------ | -------- | ----- | ------- | ------------------------------------
$credentials | true     | array | null    | The user credentials.

##### Example

```php
$credentials = [
	'email'    => 'john.doe@example.com',
	'password' => 'password',
];

$user = Sentinel::findUserById(1);

$user = Sentinel::validateCredentials($user, $credentials);
```

###### Example Response

```
true
```

#### Sentinel::validForCreation()

Validates a user for creation.

##### Arguments

Key          | Required | Type  | Default | Description
------------ | -------- | ----- | ------- | ------------------------------------
$credentials | true     | array | null    | The user credentials.

##### Example

```php
$credentials = [
	'email'    => 'john.doe@example.com',
	'password' => 'password',
];

$user = Sentinel::validForCreation($credentials);
```

###### Example Response

```
true
```

#### Sentinel::validForUpdate()

Validates a user for update.

##### Arguments

Key          | Required | Type                                   | Default | Description
------------ | -------- | -------------------------------------- | ------- | ------------------------------------
$user        | true     | Cartalyst\Sentinel\Users\UserInterface | null    | The Sentinel user object.
$credentials | true     | array                                  | null    | The user credentials.

##### Example

```php
$user = Sentinel::findById(1);

$credentials = [
	'email' => 'johnathan.doe@example.com',
];

$user = Sentinel::validForUpdate($user, $credentials);
```

###### Example Response

```
true
```

#### Sentinel::create()

Creates a new user.

##### Arguments

Key          | Required | Type           | Default | Description
------------ | -------- | -------------- | ------- | ------------------------------------
$credentials | true     | array          | null    | The user credentials.
$callback    | false    | Closure        | null    | A `Closure` that would be executed before the user is created and can prevent user creation if it returns false.

##### Example

```php
$credentials = [
	'email'    => 'john.doe@example.com',
	'password' => 'password',
];

$user = Sentinel::create($credentials);
```

##### Example Response

```
{
	id: "1",
	email: "john.doe@example.com",
	permissions: {
		admin: true
	},
	last_login: {
		date: "2014-02-17 03:44:31",
		timezone_type: 3,
		timezone: "UTC"
	},
	first_name: "John",
	last_name: "Doe",
	created_at: "2014-02-17 02:43:01",
	updated_at: "2014-02-17 02:43:37"
}
```

#### Sentinel::update()

Updates an existing user.

##### Arguments

Key          | Required | Type                                   | Default | Description
------------ | -------- | -------------------------------------- | ------- | ------------------------------------
$user        | true     | Cartalyst\Sentinel\Users\UserInterface | null    | The Sentinel user object.
$credentials | true     | array                                  | null    | The user credentials.

##### Example

```php
$user = Sentinel::findById(1);

$credentials = [
	'email' => 'new.john.doe@example.com',
];

$user = Sentinel::update($user, $credentials);
```

##### Example Response

```
{
	id: "1",
	email: "john.doe@example.com",
	permissions: {
		admin: true
	},
	last_login: {
		date: "2014-02-17 03:44:31",
		timezone_type: 3,
		timezone: "UTC"
	},
	first_name: "John",
	last_name: "Doe",
	created_at: "2014-02-17 02:43:01",
	updated_at: "2014-02-17 02:43:37"
}
```

#### $user->delete()

A user object can be deleted by calling eloquent's `delete` method on the user object. All related records for that specific user will be deleted as well.

##### Example

```php
$user = Sentinel::findById(1);

$user->delete();
```

#### Sentinel::getHasher()

Returns the current hasher.

##### Example

```php
$hasher = Sentinel::getHasher();
```

#### Sentinel::setHasher()

Sets the hasher.

##### Arguments

Key     | Required | Type                                        | Default | Description
------- | -------- | ------------------------------------------- | ------- | -------------------
$hasher | true     | Cartalyst\Sentinel\Hashing\HasherInterface  | null    | The hasher object.

##### Example

```php
Sentinel::setHasher(new Cartalyst\Sentinel\Hashing\WhirlpoolHasher);
```

#### Sentinel::inRole($role)

Check if the current user belongs to the given role.

##### Arguments

Key          | Required | Type   | Default | Description
------------ | -------- | ------ | ------- | ------------------------------------
$role        | true     | string | null    | The role to check against.

##### Example

```php
$admin = Sentinel::inRole('admin');
```

#### Sentinel::createModel()

Creates a new user model instance.

```php
$user = Sentinel::createModel();
```

#### Sentinel::setModel()

Sets the user model.

Your new model needs to extend the `Cartalyst\Sentinel\Users\EloquentUser` class.

##### Arguments

Key    | Required | Type   | Default | Description
------ | -------- | ------ | ------- | -----------------------------------------
$model | true     | string | null    | The users model class name.

##### Example

```php
Sentinel::setModel('Acme\Models\User');
```
