## Introduction

A modern and framework agnostic authorization and authentication package featuring groups, permissions, custom hashing algorithms and additional security features.

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


- Allow for custom hashing strategies.
- Refactor permissions out into a driver-based system.
- Refactor *Provider and *Interface implementations into single *Repository classes.
- Multiple sessions.
- Multiple login columns.
- Inter-account throttling and improved DDoS protection.
- Improved integration with Laravel (Sentinel::basic(), easy email integration with queues).
- Improved speed - make use of eager loading.
- Allow use of implementations (such as Eloquent and Kohana ORM) to take place on the ORM level.
- Allow more flexible activation scenarios.
- Groups renamed to roles bringing us in-line with RBAC terminology.

