### Registration

In this section, we will cover registration methods.

#### Sentinel::register()

With this method you'll be able to register new users onto your application.

The first argument is a `key/value` pair which should contain the user login column name, the password and other attributes you see fit.

The second argument is a `bool` or a `Closure`, that when set to `true` will automatically activate the user account or when a `Closure` is passed the `UserRepositoryInterface` for any aditional checks before creating the user, if the `Closure` returns `true` the user will be saved to the DB otherwise it will not be saved.

Returns: `Cartalyst\Sentinel\Users\UserInterface` or `false`.

##### Arguments

Key          | Required | Type           | Default | Description
------------ | -------- | -------------- | ------- | ---------------------------
$credentials | true     | array          | null    | The user credentials.
$callback    | false    | bool ; Closure | false   | This argument is used for two things, either pass in `true` to activate the user or a `Closure` that would be executed before the user is created and can prevent user creation if it returns false.

##### Example

```php
$credentials = [
	'email'    => 'john.doe@example.com',
	'password' => 'password',
];

$user = Sentinel::register($credentials);
```

##### Example Response

```
{
	email: "john.doe@example.com",
	created_at: "2014-02-17 02:43:01",
	updated_at: "2014-02-17 02:43:01"
	id: 2
}
```

#### Sentinel::registerAndActivate()

This method registers and activates the user, it's an alias for the `register()` method but it sets the `$callback` flag to `true`.

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

$user = Sentinel::registerAndActivate($credentials);
```

##### Example Response

```
{
	email: "john.doe@example.com",
	created_at: "2014-02-17 02:43:01",
	updated_at: "2014-02-17 02:43:01"
	id: 2
}
```
