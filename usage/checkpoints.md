## Checkpoints

Checkpoints can be referred to as security gates, the authentication process has to successfully pass through every single gate defined in order to be granted access.

By default, when logging in, checking for existing sessions and failed logins occur, you may configure an indefinite number of "checkpoints".

These are classes which may respond to each event and handle accordingly. We ship with three, an `activation` checkpoint, a `throttle` checkpoint, and a `swipe` two-factor authentication checkpoint.

Feel free to add, remove or re-order these.

### Activation

The `activation` checkpoint is responsible for validating the login attempt against the activation to make sure the user is activated prior to granting him access to a protected area.

### Throttle

The `throttle` checkpoint is responsible for validating the login attempt against the defined throttling rules.

### Swipe Identity

The `swipe` checkpoint is responsible for validating the login using Swipe Identity's two factor authentication mechanism.

### Functions

#### Sentinel::addCheckpoint($checkpoint)

Add a new checkpoint.

> **Note** Checkpoints must implement `Cartalyst\Sentinel\Checkpoints\CheckpointInterface`.

```php
$checkpoint = new Your\Custom\Checkpoint;

Sentinel::addCheckpoint($checkpoint);
```

#### Sentinel::enableCheckpoints()

Enable checkpoints.

```php
Sentinel::enableCheckpoints();
```

#### Sentinel::disableCheckpoints()

Disable checkpoints.

```php
Sentinel::disableCheckpoints();
```

#### Sentinel::checkpointsEnabled()

Check whether checkpoints are enabled or disabled.

```php
$checkpoints = Sentinel::checkpointsEnabled();
```

#### Sentinel::bypassCheckpoints($callback)

Execute a closure that bypasses all checkpoints.

```php
$callback = function($sentinel)
{
	return $sentinel->check();
};

return Sentinel::bypassCheckpoints($callback);
```
