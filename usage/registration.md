### Registration

In this section, we will cover registration methods.

#### Sentinel::register()

With this method you'll be able to register new users onto your application.

The first argument is a `key/value` pair which should contain the user login column name, the password and other attributes you see fit.

The second argument is a boolean, that when set to `true` will automatically activate the user account.

##### Arguments

Key          | Required | Type           | Default | Description
------------ | -------- | -------------- | ------- | ---------------------------
$credentials | true     | array          | null    | The user credentials.
$callback    | false    | bool, Closure  | false   | This argument is used for two things, activation and custom registration. If set to true, it will automatically activate the user account.

##### Example

```php
$credentials = [
	'email'    => 'john.doe@example.com',
	'password' => 'password',
];

$user = Sentinel::register($credentials);
```

#### Sentinel::registerAndActivate()

This method registers and activates the user, it acts more like an alias to the `register()` but it sets the `$callback` flag to `true`.

##### Arguments

Key          | Required | Type           | Default | Description
------------ | -------- | -------------- | ------- | ---------------------------
$credentials | true     | array          | null    | The user credentials.

##### Example

```php
$credentials = [
	'email'    => 'john.doe@example.com',
	'password' => 'password',
];

$user = Sentinel::registerAndActivate($credentials);
```
