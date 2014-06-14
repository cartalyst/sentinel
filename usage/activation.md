## Activation

Activation allows you to manage activations through Sentinel.

### Activation::create($user)

Creates a new activation record for the user.

Returns the activation object.

```php
$user = Sentinel::findById(1);

Activation::create($user);
```

### Activation::exists($user)

Check if an activation record exists for the user.

Returns the activation object or bool.

```php
$user = Sentinel::findById(1);

Activation::exists($user);
```

### Activation::complete($user, $code)

Attempt to complete activation for the user using the code passed.

Returns bool.

```php
$user = Sentinel::findById(1);

if ($activation = Activation::complete($user, 'activation_code_here'))
{
	// Activation was successfull
}
else
{
	// Activation not found or not completed.
}
```

### Activation::completed($user)

Check if activation has been completed for the user.

Returns the activation object or bool.

```php
$user = Sentinel::findById(1);

if ($activation = Activation::completed($user))
{
	// User has completed the activation.
}
else
{
	// Activation not found or not completed.
}
```

### Activation::remove($user)

Remove the activation for the user.

Returns true or null.

```php
$user = Sentinel::findById(1);

Activation::remove($user);
```

### Activation::deleteExpired()

Remove all expired activations.

```php
Activation::deleteExpired();
```

### Activation::createModel()

Creates a new activation model instance.

```php
$activation = Activate::createModel();
```

### Activation::setModel($model)

Sets the activation model.

```php
Activation::setModel('Your\Activation\Model');
```

### Exceptions

- `Cartalyst\Sentinel\Checkpoints\NotActivatedException`

Methods             | Parameters                               | Description
------------------- | ---------------------------------------- | -----------
setUser             | Cartalyst\Sentinel\Users\UserInterface   | Sets a user object on the exception.
getUser             | ..                                       | Retrieves the user object that caused the exception.
