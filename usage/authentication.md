### Authentication

In this section, we will cover the Sentinel authentication methods.

#### Sentinel::authenticate()

This method authenticates a user against the given `$credentials`, additionally a second `bool` argument of `true` can be passed to set the remember state on the user and a third `bool` argument of `false` can be passed to disable automatic login.

Returns: `Cartalyst\Sentinel\Users\UserInterface` or `false`.

##### Arguments

Key          | Required | Type  | Default | Description
------------ | -------- | ----- | ------- | ------------------------------------
$credentials | true     | array | null    | The user credentials.
$remember    | false    | bool  | false   | Flag to set the remember cookie.
$login       | false    | bool  | true    | Flag to disable automatic login.

##### Example

```php
$credentials = [
	'email'    => 'john.doe@example.com',
	'password' => 'password',
];

Sentinel::authenticate($credentials);
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

#### Sentinel::authenticateAndRemember()

This method authenticates and remembers the user, it's an alias fore the `authenticate()` method but it sets the `$remember` flag to `true`.

Returns: `Cartalyst\Sentinel\Users\UserInterface` or `false`.

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

Sentinel::authenticateAndRemember($credentials);
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

#### Sentinel::forceAuthenticate()

Authenticates a user bypassing all checkpoints.

Returns: `Cartalyst\Sentinel\Users\UserInterface` or `false`.

##### Arguments

Key          | Required | Type  | Default | Description
------------ | -------- | ----- | ------- | ------------------------------------
$credentials | true     | array | null    | The user credentials.
$remember    | false    | bool  | false   | Flag to set the remember cookie.

##### Example

```php
$credentials = [
	'email'    => 'john.doe@example.com',
	'password' => 'password',
];

Sentinel::forceAuthenticate($credentials);
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

#### Sentinel::forceAuthenticateAndRemember()

Authenticates and remembers a user bypassing all checkpoints.

Returns: `Cartalyst\Sentinel\Users\UserInterface` or `false`.

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

Sentinel::forceAuthenticateAndRemember($credentials);
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

#### Sentinel::stateless()

Performs stateless authentication.

Returns: `Cartalyst\Sentinel\Users\UserInterface` or `false`.

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

if ($user = Sentinel::stateless($credentials))
{
	// Authentication successful and the user is assigned to the `$user` variable.
}
else
{
	// Authentication failed.
}
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

#### Sentinel::basic()

Authenticates using the `HTTP` basic auth.

Returns: The auth response.

##### Example

```php
return Sentinel::basic();
```
