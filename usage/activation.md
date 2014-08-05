### Activation

Activation allows you to manage activations through Sentinel.

#### Activation::create()

Creates a new activation record for the user.

Returns the activation object.

##### Arguments

Key   | Required | Type                                   | Default | Description
----- | -------- | -------------------------------------- | ------- | ---------------------------
$user | true     | Cartalyst\Sentinel\Users\UserInterface | null    | The Sentinel user object.

##### Example

```php
$user = Sentinel::findById(1);

$activation = Activation::create($user);
```

#### Activation::exists()

Check if an activation record exists for the user.

Returns the activation object or boolean `false`.

##### Arguments

Key   | Required | Type                                   | Default | Description
----- | -------- | -------------------------------------- | ------- | ---------------------------
$user | true     | Cartalyst\Sentinel\Users\UserInterface | null    | The Sentinel user object.

##### Example

```php
$user = Sentinel::findById(1);

Activation::exists($user);
```

#### Activation::complete()

Attempt to complete activation for the user using the code passed.

Returns bool.

##### Arguments

Key   | Required | Type                                   | Default | Description
----- | -------- | -------------------------------------- | ------- | ---------------------------
$user | true     | Cartalyst\Sentinel\Users\UserInterface | null    | The Sentinel user object.
$code | true     | string                                 | null    | The activation code.

##### Example

```php
$user = Sentinel::findById(1);

if (Activation::complete($user, 'activation_code_here'))
{
	// Activation was successfull
}
else
{
	// Activation not found or not completed.
}
```

#### Activation::completed()

Check if activation has been completed for the user.

Returns the activation object or bool.

##### Arguments

Key   | Required | Type                                   | Default | Description
----- | -------- | -------------------------------------- | ------- | ---------------------------
$user | true     | Cartalyst\Sentinel\Users\UserInterface | null    | The Sentinel user object.

##### Example

```php
$user = Sentinel::findById(1);

if ($activation = Activation::completed($user))
{
	// User has completed the activation process
}
else
{
	// Activation not found or not completed
}
```

#### Activation::remove()

Remove the activation for the user.

Returns true or null.

##### Arguments

Key   | Required | Type                                   | Default | Description
----- | -------- | -------------------------------------- | ------- | ---------------------------
$user | true     | Cartalyst\Sentinel\Users\UserInterface | null    | The Sentinel user object.

##### Example

```php
$user = Sentinel::findById(1);

Activation::remove($user);
```

#### Activation::deleteExpired()

Remove all expired activations.

```php
Activation::deleteExpired();
```

#### Activation::createModel()

Creates a new activation model instance.

```php
$activation = Activate::createModel();
```

#### Activation::setModel()

Sets the activation model.

##### Arguments

Key    | Required | Type   | Default | Description
------ | -------- | ------ | ------- | -----------------------------------------
$model | true     | string | null    | The new activation model.

##### Example

```php
Activation::setModel('Your\Activation\Model');
```

#### Exceptions

- `Cartalyst\Sentinel\Checkpoints\NotActivatedException`

Methods | Parameters                             | Description
------- | -------------------------------------- | -----------------------------
setUser | Cartalyst\Sentinel\Users\UserInterface | Sets a user object on the exception.
getUser | ..                                     | Retrieves the user object that caused the exception.
