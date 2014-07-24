### Authentication

In this section, we will cover authentication methods.

#### Sentinel::authenticate($credentials)

Authenticates a user.

```php
$credentials = [
	'email'    => 'john.doe@example.com',
	'password' => 'password',
];

Sentinel::authenticate($credentials);
```

#### Sentinel::authenticateAndRemember($credentials)

Authenticates and remembers a user.

```php
$credentials = [
	'email'    => 'john.doe@example.com',
	'password' => 'password',
];

Sentinel::authenticateAndRemember($credentials);
```

#### Sentinel::forceAuthenticate($credentials)

Authenticates a user bypassing all checkpoints.

```php
$credentials = [
	'email'    => 'john.doe@example.com',
	'password' => 'password',
];

Sentinel::forceAuthenticate($credentials);
```

#### Sentinel::forceAuthenticateAndRemember($credentials)

Authenticates and remembers a user bypassing all checkpoints.

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
