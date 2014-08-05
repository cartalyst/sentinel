### Authorization

In this section, we will cover authorization methods.

#### Sentinel::check()

Check if a user is logged in.

Returns: `Cartalyst\Sentinel\Users\UserInterface` or `false`.

##### Example

```php
if ($user = Sentinel::check())
{
	// User is logged in and assigned to the `$user` variable.
}
else
{
	// User is not logged in
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

#### Sentinel::forceCheck()

Check if a user is logged in, bypassing all checkpoints.

Returns: `Cartalyst\Sentinel\Users\UserInterface` or `false`.

##### Example

```php
if ($user = Sentinel::forceCheck())
{
	// User is logged in and assigned to the `$user` variable.
}
else
{
	// User is not logged in
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

#### Sentinel::guest()

Check if no user is currently logged in.

Returns `true` if the user is not logged in and `false` otherwise.

##### Example

```php
if (Sentinel::guest())
{
	// User is not logged in
}
```

##### Example Response

```
true
```
