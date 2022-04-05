# Changelog

### v6.0.0 - 2022-04-06

- Add Laravel 9 support

### v5.1.0 - 2020-12-22

- Add PHP 8 support

### v5.0.0 - 2020-09-12

- BC Break: PHP 7.3 is the minimum required PHP version
- BC Break: Laravel 8.0 is the minimum supported Laravel version

### v4.0.0 - 2020-03-05

- BC Break: PHP 7.2 is the minimum required PHP version
- BC Break: Laravel 7.0 is the minimum supported Laravel version

### v3.0.4 - 2020-02-07

`ADDED`

- The `inAnyRole` to be called statically, `Sentinel::inAnyRole($role)`

### v3.0.3 - 2019-09-26

`FIXED`

- A few permissions type retrieval bugs.

### v3.0.2 - 2019-09-25

`FIXED`

- A bug on the `addPermission` method of the `PermissibleTrait`.

### v3.0.1 - 2019-09-24

`FIXED`

- A few return types.

### v3.0.0 - 2019-09-11

- BC Break: PHP 7.2 is the minimum required PHP version
- BC Break: Laravel 6.0 is the minimum supported Laravel version
- Added PHP 7 Scalar type hints
- Added PHP 7 Return type hints
- Added `Sentinel::getCheckpoints()` method to retrieve all the added Checkpoints
- Added `Sentinel::getActivationRepository()->get()` method to retrieve the valid Activation
- Added `Sentinel::getReminderRepository()->get()` method to retrieve the valid Reminder
- Updated the `Sentinel::getActivationRepository()->exists();` method to always return a boolean
- Updated the `Sentinel::getActivationRepository()->completed();` method to always return a boolean
- Updated the `Sentinel::getReminderRepository()->exists();` method to always return a boolean
- Updated the `findByPersistenceCode()` to return a `PersistenceInterface` or `null` instead of `PersistenceInterface` or `bool`
- Updated the `findUserByPersistenceCode()` to return `UserInterface` or `null` instead of `UserInterface` or `bool`
- Fixed an issue where a call for EloquentUser::setPersistences was missing during bootstrap
- Fixed an issue where personal permissions were not taking priority over pattern matching
- Fixed an issue where the Throttling repository was not being set properly
- Fixed an issue with the Native Cookie forget method
- Fixed an issue where the events didn't had the payload passed correctly
- Fixed an issue where the global throttles cache was not being cleared
- Removed unnecessary dependencies
- Removed integrations for both CodeIgniter and FuelPHP
- Removed strict comparisons in favour of type hinting

### v2.0.18 - 2019-08-14

`ADDED`

- Added support for Larastan
- Added events for logging in and logging out

`UPDATED`

- Updated the `orWhere` query builder loop to lead with correct boolean constraint
- Updated several tests for better coverage and overall quality

`FIXED`

- Fixed an issue with the throttling threshold not behaving as expected
- Fixed an issue where a model with soft deletes was causing issues when force deleting
- Fixed an issue where the default global threshold value was incorrect
- Fixed an issue with the order of overrides registration on the Laravel service provider
- Fixed an issue with the `checkPermission()` method not behaving not casting some permissions as `string`s
- Fixed an issue where the `checkActivation` method on the Activations Checkpoint was not returning the completion status
- Fixed a few docblock typos

### v2.0.17 - 2017-11-28

`FIXED`

- Incorrect docblock on the Illuminate Reminder Repository.

`ADDED`

- Null Cookie implementation.

### v2.0.16 - 2017-10-09

`FIXED`

- Issue on `get` method on `IlluminateCookie` returning incorrect type.

`ADDED`

- Support for Laravel 5.5 Package Discovery.

### v2.0.15 - 2017-02-23

`REVISED`

- Loosened `cartalyst/support` version.

### v2.0.14 - 2017-01-30

`FIXED`

- Specify engine on the reminders table.
- Single option on the native bootstrapper.
- A bug causing `inRole` to return false after one iteration.
- Use the event dispatcher contract.

### v2.0.13 - 2016-09-05

`ADDED`

- Missing Throttle repository getter/setter.
- Laravel 5.3 support.

`UPDATED`

- Detach the role from users when the role is deleted.
- Pass persistence model from the configuration to the native bootstrapper.

`REMOVED`

- Removed usage of Laravel helpers.

### v2.0.12 - 2016-05-13

`UPDATED`

- Bump `paragonie/random_compat` version.
- Use CSPRNG for salts.

### v2.0.11 - 2016-04-28

`UPDATED`

- User model to check if the model is being soft deleted.
- Native Session to better check if session is open or closed.

### v2.0.10 - 2016-04-28

`FIXED`

- Delete method on the user model not returning parent.
- Removed unused imports on some tests.
- Reference to the stdClass on tests.
- Various docblocks.

`UPDATED`

- User model to hide the hashed password by default.

### v2.0.9 - 2016-02-11

`FIXED`

- Bypassing specific checkpoints.

`ADDED`

- A new database schema file for MySQL 5.6+.

### v2.0.8 - 2015-11-25

`REVISED`

- Use json methods for various cookie implementations.

### v2.0.7 - 2015-08-26

`REVISED`

- Added additional check to prevent the first user from being returned when skipping login columns.

### v2.0.6 - 2015-07-21

`UPDATED`

- `composer.json` to fix Composer warnings about migrations.

### v2.0.5 - 2015-07-09

`REVISED`

- Using `singleton` in favor of `bindShared` being deprecated on Laravel 5.1 and removed on Laravel 5.2.

### v2.0.4 - 2015-06-25

`UPDATED`

- `.travis.yml` file contents.

`FIXED`

- Method visibility on the `PermissibleTrait` to avoid warnings on some IDE's.

### v2.0.3 - 2015-06-24

`UPDATED`

- License to 3-clause BSD.
- Some other minor tweaks.

### v2.0.2 - 2015-05-22

`FIXED`

- Throw an exception when registering with a blank password.

### v2.0.1 - 2015-03-13

`FIXED`

- Set the user object on the `Sentinel` class to null after logout.
- Detect client ip using symfony's `Request` class on the native bootstrapper.

### v2.0.0 - 2015-02-24

- Updated for Laravel 5.

`REVISED`

- Switched to PSR-2.

### v1.0.15 - 2015-11-25

`REVISED`

- Use json methods for various cookie implementations.

### v1.0.14 - 2015-08-26

`REVISED`

- Added additional check to prevent the first user from being returned when skipping login columns.

### v1.0.13 - 2015-07-21

`UPDATED`

- `composer.json` to fix Composer warnings about migrations.

### v1.0.12 - 2015-06-25

`UPDATED`

- `.travis.yml` file contents.

`FIXED`

- Method visibility on the `PermissibleTrait` to avoid warnings on some IDE's.

### v1.0.11 - 2015-06-24

`UPDATED`

- License to 3-clause BSD.
- Some other minor tweaks.

### v1.0.10 - 2015-05-22

`FIXED`

- Throw an exception when registering with a blank password.

### v1.0.9 - 2015-03-13

`FIXED`

- Set the user object on the `Sentinel` class to null after logout.
- Detect client ip using symfony's `Request` class on the native bootstrapper.

### v1.0.8 - 2015-01-23

`FIXED`

- Fixed a bug on the `findByCredentials` method that caused the first user to be returned when an empty array is passed.

`ADDED`

- Added mysql database schema.

### v1.0.7 - 2014-10-21

`FIXED`

- Added the `$hidden` property to the user model with the password field being hidden by default.

### v1.0.6 - 2014-09-24

`FIXED`

- Wrap garbageCollect into a try catch block to prevent an exception from being thrown if the database is not setup.

### v1.0.5 - 2014-09-16

`FIXED`

- Fixed a minor issue when deleting a user, the method wasn't returning the expected boolean only null.

### v1.0.4 - 2014-09-15

`REVISED`

- Improved the requirements to allow the installation on Laravel 5.0.

### v1.0.3 - 2014-09-13

`FIXED`

- Updated the updatePermission method signature on the PermissibleInterface due to a PHP bug on older versions.

### v1.0.2 - 2014-09-10

`FIXED`

- Fixed some doc blocks typos

`REVISED`

- Loosened the requirements on the composer.json

`ADDED`

- Added an IoC Container alias for the Sentinel class.

### v1.0.1 - 2014-08-07

`FIXED`

- Addresses a bug where user model overriding was ignored.

### v1.0.0 - 2014-08-05

- Authentication.
- Authorization.
- Registration.
- Driver based permission system.
- Flexible activation scenarios.
- Reminders. (password reset)
- Inter-account throttling with DDoS protection.
- Roles and role permissions.
- Remember me.
- Interface driven. (your own implementations at will)
