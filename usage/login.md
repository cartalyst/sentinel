### Login

In this section, we will cover login methods.

#### Sentinel::login()

This method logs the given a user in, additionally a second `bool` argument of `true` can be passed to set the remember state on the user.

Returns: `Cartalyst\Sentinel\Users\UserInterface` or `false`.

##### Arguments

Key       | Required | Type                                   | Default | Description
--------- | -------- | -------------------------------------- | ------- | ---------------------------
$user     | true     | Cartalyst\Sentinel\Users\UserInterface | null    | The Sentinel user object.
$remember | false    | bool                                   | false   | Flag to set the remember cookie.

##### Example

```php
$user = Sentinel::findById(1);

Sentinel::login($user);
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

#### Sentinel::loginAndRemember()

This method logs and remembers the given user, it's an alias fore the `login()` method but it sets the `$remember` flag to `true`.

Returns: `Cartalyst\Sentinel\Users\UserInterface` or `false`.

##### Arguments

Key   | Required | Type                                   | Default | Description
----- | -------- | -------------------------------------- | ------- | ---------------------------
$user | true     | Cartalyst\Sentinel\Users\UserInterface | null    | The Sentinel user object.

##### Example

```php
$user = Sentinel::findById(1);

Sentinel::loginAndRemember($user);
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

#### Sentinel::logout()

Logs a user out, optionally can be passed a bool parameter `true` that will flush all active sessions for the user.

##### Arguments

Key         | Required | Type                                   | Default | Description
----------- | -------- | -------------------------------------- | ------- | ---------------------------
$user       | false    | Cartalyst\Sentinel\Users\UserInterface | null    | The Sentinel user object.
$everywhere | false    | bool                                   | false   | Flag for whether it should terminate all sessions.

##### Examples

Please refer to the examples below for different ways on terminate your users sessions.

###### Destroy the current logged in user session

```php
Sentinel::logout();
```

###### Destroy all sessions for the current logged in user

```php
Sentinel::logout(null, true);
```

###### Destroy the given user session

```php
$user = Sentinel::findUserById(1);

Sentinel::logout($user);
```

###### Destroy all sessions for the given user

```php
$user = Sentinel::findUserById(1);

Sentinel::logout($user, true);
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
