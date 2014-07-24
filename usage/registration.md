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
