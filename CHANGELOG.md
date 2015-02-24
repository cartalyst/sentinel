# Sentinel Change Log

This project follows [Semantic Versioning](CONTRIBUTING.md).

## Proposals

We do not give estimated times for completion on `Accepted` Proposals.

- [Accepted](https://github.com/cartalyst/sentinel/labels/Accepted)
- [Rejected](https://github.com/cartalyst/sentinel/labels/Rejected)

---

### v2.0.0 - 2015-02-24

- Updated for Laravel 5.

`REVISED`

- Switched to PSR-2.

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
