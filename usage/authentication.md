### Authentication

In this section, we will cover authentication methods.

#### Sentinel::authenticate()

This method uthenticates a user against the given `$credentials`, you pass a `boolean` on the second argument to set the remember me flag on / off.

##### Arguments

Key          | Required | Type  | Default | Description
------------ | -------- | ----- | ------- | ------------------------------------
$credentials | true     | array | null    | The user credentials.
$remember    | false    | bool  | false   | The remember me flag.

##### Example

```php
$credentials = [
	'email'    => 'john.doe@example.com',
	'password' => 'password',
];

Sentinel::authenticate($credentials);
```

#### Sentinel::authenticateAndRemember()

This method authenticates and remembers the user, it acts more like an alias to the `authenticate()` but it sets the `$remember` flag to `true`.

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

#### Sentinel::forceAuthenticate($credentials)

Authenticates a user bypassing all checkpoints.

##### Arguments

Key          | Required | Type  | Default | Description
------------ | -------- | ----- | ------- | ------------------------------------
$credentials | true     | array | null    | The user credentials.
$remember    | false    | bool  | false   | The remember me flag.

##### Example

```php
$credentials = [
	'email'    => 'john.doe@example.com',
	'password' => 'password',
];

Sentinel::forceAuthenticate($credentials);
```

#### Sentinel::forceAuthenticateAndRemember($credentials)

Authenticates and remembers a user bypassing all checkpoints.

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

#### Sentinel::stateless($credentials)

Performs stateless authentication.

Returns the user object or false.

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

#### Sentinel::basic()

Authenticate using `HTTP` basic auth.

Returns the auth response.

##### Example

```php
return Sentinel::basic();
```
