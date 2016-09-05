# Change Log

This project follows [Semantic Versioning](CONTRIBUTING.md).

## Proposals

We do not give estimated times for completion on `Accepted` Proposals.

- [Accepted](https://github.com/cartalyst/sentinel/labels/Accepted)
- [Rejected](https://github.com/cartalyst/sentinel/labels/Rejected)

---

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
