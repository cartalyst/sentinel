### Reminder

Reminder allows you to manage reminders through Sentinel.

#### Reminder::create()

Creates a new reminder record for the user.

Returns: `Cartalyst\Sentinel\Reminders\EloquentReminder`

##### Arguments

Key   | Required | Type                                   | Default | Description
----- | -------- | -------------------------------------- | ------- | -------------------------
$user | true     | Cartalyst\Sentinel\Users\UserInterface | null    | The Sentinel user object.

```php
$user = Sentinel::findById(1);

Reminder::create($user);
```

#### Reminder::exists()

Check if a reminder record exists for the user.

Returns: `bool`

##### Arguments

Key   | Required | Type                                   | Default | Description
----- | -------- | -------------------------------------- | ------- | -------------------------
$user | true     | Cartalyst\Sentinel\Users\UserInterface | null    | The user credentials.
$code | false    | string                                 | null    | The user credentials.

```php
$user = Sentinel::findById(1);

Reminder::exists($user);
```

#### Reminder::complete()

Attempt to complete the password reset for the user using the code passed and the new password.

Returns: `bool`.

##### Arguments

Key       | Required | Type                                   | Default | Description
--------- | -------- | -------------------------------------- | ------- | -------------------------
$user     | true     | Cartalyst\Sentinel\Users\UserInterface | null    | The user credentials.
$code     | true     | string                                 | null    | The user credentials.
$password | true     | string                                 | null    | The user credentials.

```php
$user = Sentinel::findById(1);

if ($reminder = Reminder::complete($user, 'reminder_code_here', 'new_password_here'))
{
	// Reminder was successfull
}
else
{
	// Reminder not found or not completed.
}
```

#### Reminder::removeExpired()

Remove all expired reminders.

Returns: `bool`.

```php
Reminder::removeExpired();
```

#### Reminder::createModel()

Creates a new reminder model instance.

Returns: `Cartalyst\Sentinel\Reminders\EloquentReminder`

```php
$reminder = Reminder::createModel();
```

#### Reminder::setModel()

Sets the reminder model.

##### Arguments

Key    | Required | Type   | Default | Description
------ | -------- | ------ | ------- | -----------------------------------------
$model | true     | string | null    | The new reminders model.

```php
Reminder::setModel('Your\Reminder\Model');
```
