### Reminder

Reminder allows you to manage reminders through Sentinel.

#### Reminder::create($user)

Creates a new reminder record for the user.

Returns the reminder object.

```php
$user = Sentinel::findById(1);

Reminder::create($user);
```

#### Reminder::exists($user)

Check if a reminder record exists for the user.

Returns the reminder object or bool.

```php
$user = Sentinel::findById(1);

Reminder::exists($user);
```

#### Reminder::complete($user, $code, $password)

Attempt to complete the password reset for the user using the code passed and the new password.

Returns bool.

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

```php
Reminder::removeExpired();
```

#### Reminder::createModel()

Creates a new reminder model instance.

```php
$reminder = Activate::createModel();
```

#### Reminder::setModel($model)

Sets the reminder model.

```php
Reminder::setModel('Your\Reminder\Model');
```
