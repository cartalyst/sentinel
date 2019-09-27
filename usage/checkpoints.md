### Checkpoints

Checkpoints can be referred to as security gates, the authentication process has to successfully pass through every single gate defined in order to be granted access.

By default, when logging in, checks for `existing sessions` and `failed logins` occur, you may configure an indefinite number of "checkpoints".

These are classes which may respond to each event and handle accordingly. We ship with two, an `activation` checkpoint and a `throttle` checkpoint.

> **Note** Checkpoints must implement `Cartalyst\Sentinel\Checkpoints\CheckpointInterface`.

Feel free to add, remove or re-order these.

#### Activation

The `activation` checkpoint is responsible for validating the login attempt against the activation checkpoint to make sure the user is activated prior to granting access to a specific area.

#### Throttle

The `throttle` checkpoint is responsible for validating the login attempts against the defined throttling rules.

#### Usage


#### Functions

##### Sentinel::addCheckpoint()

Add a new checkpoint.

##### Arguments

Key           | Required | Type                                               | Default | Description
------------- | -------- | -------------------------------------------------- | ------- | -----------------------------------------
$key          | true     | string                                             | null    | The name of the checkpoint..
$checkpoint   | true     | Cartalyst\Sentinel\Checkpoints\CheckpointInterface | null    | The name of the checkpoint..

##### Example

```php
$checkpoint = new Your\Custom\Checkpoint;

Sentinel::addCheckpoint('your_checkpoint', $checkpoint);
```

##### Sentinel::removeCheckpoint();

##### Arguments

Key           | Required | Type                                               | Default | Description
------------- | -------- | ------- | ------- | -----------------------------------------
$key          | true     | string  | null    | The name of the checkpoint.

##### Example

```php
Sentinel::removeCheckpoint('activation');
```

##### Sentinel::enableCheckpoints()

Enable checkpoints.

##### Example

```php
Sentinel::enableCheckpoints();
```

##### Sentinel::disableCheckpoints()

Disable checkpoints.

##### Example

```php
Sentinel::disableCheckpoints();
```

##### Sentinel::checkpointsStatus()

Check whether checkpoints are enabled or disabled.

##### Example

```php
$checkpoints = Sentinel::checkpointsStatus();
```

##### Sentinel::bypassCheckpoints($callback, $checkpoints)

Execute a closure that bypasses all checkpoints.

Bypass all checkpoints.

Returns: result of `$callback`.

##### Arguments

Key           | Required | Type                                               | Default | Description
------------- | -------- | ------- | ------- | -----------------------------------------
$callback     | true     | closure | null    | Closure to use when bypassing checkpoints.
checkpoints   | false    | array   | []      | Array of checkpoints to bypass.

##### Example

```php
$callback = function($sentinel)
{
	return $sentinel->check();
};

return Sentinel::bypassCheckpoints($callback);
```

Bypass specific checkpoints.

```php
$callback = function($sentinel)
{
	return $sentinel->check();
};

return Sentinel::bypassCheckpoints($callback, ['activation']);
```
