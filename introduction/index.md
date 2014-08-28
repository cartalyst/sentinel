## Introduction

A modern and framework agnostic authorization and authentication package featuring roles, permissions, custom hashing algorithms and additional security features.

The package follows the FIG standard PSR-4 to ensure a high level of interoperability between shared PHP code.

The package requires PHP 5.4+ and comes bundled with a Laravel 4 Facade and a Service Provider to simplify the optional framework integration.

Have a [read through the Installation Guide](#installation) and on how to [Integrate it with Laravel 4](#laravel-4).

###### Create a user

```php
Sentinel::register(array(
	'email'    => 'john.doe@example.com',
	'password' => 'foobar',
));
```

###### Authenticate a user

```php
Sentinel::authenticate(array(
	'email'    => 'john.doe@example.com',
	'password' => 'foobar',
));
```

### Features

Sentinel is a complete refactor of our popular Sentry authentication & authorization library. Everything you admired plus a whole lot more.

- Authentication.
- Authorization.
- Registration.
- Users & Roles Management.
- Driver based permission system.
- Flexible activation scenarios.
- Reminders (password reset).
- Inter-account throttling with DDoS protection.
- Custom hashing strategies.
- Multiple sessions.
- Multiple login columns.
- Integration with Laravel.
- Allow use of multiple ORM implementations.
- Native facade for easy usage outside Laravel.
- Interface driven (your own implementations at will).

### Sentry vs Sentinel

Feature                                       | Sentry               | Sentinel
--------------------------------------------- | -------------------- | -------------------------------------------
Persistences                                  | Single               | Single/Multiple
Store additional data on persistences         | No                   | Yes
Login attributes                              | Single               | Multiple (ex. email, username)
Custom checkpoints                            | No                   | Yes
Custom hashing strategies                     | No                   | Yes
Driver-based permissions                      | No                   | Yes
Inter-account throttling with DDoS protection | Basic                | Advanced
