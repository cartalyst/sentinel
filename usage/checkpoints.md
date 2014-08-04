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

##### Sentinel::addCheckpoint($key, $checkpoint)

Add a new checkpoint.

```php
$checkpoint = new Your\Custom\Checkpoint;

Sentinel::addCheckpoint('your_checkpoint', $checkpoint);
```

##### Sentinel::removeCheckpoint($key);

```php
Sentinel::removeCheckpoint('activation');
```

##### Sentinel::enableCheckpoints()

Enable checkpoints.

```php
Sentinel::enableCheckpoints();
```

##### Sentinel::disableCheckpoints()

Disable checkpoints.

```php
Sentinel::disableCheckpoints();
```

##### Sentinel::checkpointsStatus()

Check whether checkpoints are enabled or disabled.

```php
$checkpoints = Sentinel::checkpointsStatus();
```

##### Sentinel::bypassCheckpoints($callback, $checkpoints)

Execute a closure that bypasses all checkpoints.

Bypass all checkpoints.

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
