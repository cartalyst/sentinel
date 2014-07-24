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
