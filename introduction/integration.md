## Integration

Cartalyst packages are framework agnostic and as such can be integrated easily natively or with your favorite framework.

### Laravel 8

The Sentinel package has optional support for Laravel 8 and it comes bundled with a Service Provider and a Facade for easy integration.

After installing the package, open your Laravel config file located at `config/app.php` and add the following lines.

In the `$providers` array add the following service provider for this package.

```php
Cartalyst\Sentinel\Laravel\SentinelServiceProvider::class,
```

In the `$aliases` array add the following facades for this package.

```php
'Activation' => Cartalyst\Sentinel\Laravel\Facades\Activation::class,
'Reminder'   => Cartalyst\Sentinel\Laravel\Facades\Reminder::class,
'Sentinel'   => Cartalyst\Sentinel\Laravel\Facades\Sentinel::class,
```

#### Assets

Run the following command to publish the migrations and config file.

`php artisan vendor:publish --provider="Cartalyst\Sentinel\Laravel\SentinelServiceProvider"`

#### Migrations

Run the following command to migrate Sentinel after publishing the assets.

> **Note:** Before running the following command, please remove the default Laravel migrations to avoid table collision.

`php artisan migrate`

#### Configuration

After publishing, the sentinel config file can be found under `config/cartalyst.sentinel.php` where you can modify the package configuration.

### Native

Sentinel being framework agnostic, by default, ships with the implementation for the Illuminate Database component.

In order to use it, make sure you require the necessary dependencies by running:

```
composer require illuminate/database illuminate/events symfony/http-foundation
```

```php
// Import the necessary classes
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;

// Include the composer autoload file
require 'vendor/autoload.php';

// Setup a new Eloquent Capsule instance
$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'sentinel',
    'username'  => 'user',
    'password'  => 'secret',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
]);

$capsule->bootEloquent();
```

The integration is done and you can now use all the available methods, here's an example:

```php
// Register a new user
Sentinel::register([
	'email'    => 'test@example.com',
	'password' => 'foobar',
]);
```
