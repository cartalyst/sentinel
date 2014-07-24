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
