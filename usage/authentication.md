## Auth & Login

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


### Authorization

In this section, we will cover authorization methods.

#### Sentinel::check()

Check if a user is logged in.

Returns the user object or false.

```php
if ($user = Sentinel::check())
{
	// User is logged in and assigned to the `$user` variable.
}
else
{
	// No user is logged in.
}
```

#### Sentinel::forceCheck()

Check if a user is logged in, bypassing all checkpoints.

Returns the user object or false.

```php
if ($user = Sentinel::forceCheck())
{
	// Authentication and login successful and the user is assigned to the `$user` variable.
}
else
{
	// No user is logged in.
}
```

#### Sentinel::guest()

Check if no user is currently logged in.

Returns bool.

```php
if (Sentinel::guest())
{
	// No user is logged in.
}
```


### Registration

In this section, we will cover registration methods.

#### Sentinel::register($credentials)

Register a new user.

```php
$credentials = [
	'email'    => 'john.doe@example.com',
	'password' => 'password',
];

$user = Sentinel::register($credentials);
```

#### Sentinel::registerAndActivate($credentials)

Register and activate a new user.

```php
$credentials = [
	'email'    => 'john.doe@example.com',
	'password' => 'password',
];

$user = Sentinel::registerAndActivate($credentials);
```


### Login

In this section, we will cover login methods.

#### Sentinel::login($user)

Logs a user in, additionally can be passed a second bool param to set the remember state on the user.

Returns the user object or false.

```php
$user = Sentinel::findById(1);

Sentinel::login($user);
```

#### Sentinel::loginAndRemember($user)

Logs a user in and sets a remember cookie.

Returns the user object or false.

```php
$user = Sentinel::findById(1);

Sentinel::loginAndRemember($user);
```

#### Sentinel::logout($everywhere)

Logs a user out, optionally can be passed a bool parameter `true` that will flush all active sessions for the user.

Returns bool.

```php
// Destroy current session
Sentinel::logout();

// Destroy all sessions
Sentinel::logout(true);
```
