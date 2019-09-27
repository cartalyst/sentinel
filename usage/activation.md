### Activation

Activation allows you to manage activations through Sentinel.

#### Activation::create()

Creates a new activation record for the user.

Returns: `Cartalyst\Sentinel\Activations\EloquentActivation`.

##### Arguments

Key   | Required | Type                                   | Default | Description
----- | -------- | -------------------------------------- | ------- | ---------------------------
$user | true     | Cartalyst\Sentinel\Users\UserInterface | null    | The Sentinel user object.

##### Example

```php
$user = Sentinel::findById(1);

$activation = Activation::create($user);
```

##### Example Response

```
{
	code: "HNjOSGWoVHCNx70UAnbphnAJVIttFvot",
	user_id: "1",
	created_at: "2014-02-17 02:43:01",
	updated_at: "2014-02-17 02:43:37",
	id: 1
}
```

#### Activation::exists()

Check if an activation record exists for the user.

Returns: `bool`.

##### Arguments

Key   | Required | Type                                   | Default | Description
----- | -------- | -------------------------------------- | ------- | ---------------------------
$user | true     | Cartalyst\Sentinel\Users\UserInterface | null    | The Sentinel user object.
$code | false    | string                                 | null    | The activation code.

##### Example

```php
$user = Sentinel::findById(1);

$activation = Activation::exists($user);
```

###### Example Response

```
{
	id: "1",
	user_id: "1",
	code: "HNjOSGWoVHCNx70UAnbphnAJVIttFvot",
	completed: false,
	completed_at: null,
	created_at: "2014-02-17 02:43:01",
	updated_at: "2014-02-17 02:43:37"
}
```

#### Activation::complete()

Attempt to complete activation for the user using the code passed.

Returns: `bool`

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

##### Example Response

```
true
```

#### Activation::completed()

Check if activation has been completed for the user.

Returns: `bool`.

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

##### Example Response

```
{
	id: "1",
	user_id: "1",
	code: "HiaVCzyLb6XFeZcVFpfUlCoLGZfhddHs",
	completed: true,
	completed_at: "2014-02-17 02:44:13",
	created_at: "2014-02-17 02:43:01",
	updated_at: "2014-02-17 02:43:37"
}
```

#### Activation::remove()

Remove the activation for the user.

Returns: `bool`.

##### Arguments

Key   | Required | Type                                   | Default | Description
----- | -------- | -------------------------------------- | ------- | ---------------------------
$user | true     | Cartalyst\Sentinel\Users\UserInterface | null    | The Sentinel user object.

##### Example

```php
$user = Sentinel::findById(1);

Activation::remove($user);
```

##### Example Response

```
true
```

#### Activation::removeExpired()

Removes all the expired activations.

Returns: `bool`.

```php
Activation::removeExpired();
```

#### Activation::createModel()

Creates a new activation model instance.

Returns: `Cartalyst\Sentinel\Activations\EloquentActivation`

```php
$activation = Activation::createModel();
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
