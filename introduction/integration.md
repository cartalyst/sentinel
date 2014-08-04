## Integration

Cartalyst packages are framework agnostic and as such can be integrated easily natively or with your favorite framework.

### Laravel 4

The Sentinel package has optional support for Laravel 4 and it comes bundled with a Service Provider and a Facade for easy integration.

After installing the package, open your Laravel config file located at `app/config/app.php` and add the following lines.

In the `$providers` array add the following service provider for this package.

```php
'Cartalyst\Sentinel\Laravel\SentinelServiceProvider',
```

In the `$aliases` array add the following facades for this package.

```php
'Activation' => 'Cartalyst\Sentinel\Laravel\Facades\Activation',
'Reminder'   => 'Cartalyst\Sentinel\Laravel\Facades\Reminder',
'Sentinel'   => 'Cartalyst\Sentinel\Laravel\Facades\Sentinel',
```

#### Migrations

Run the following command to migrate Sentinel.

`php artisan migrate --package=cartalyst/sentinel`

#### Configuration

After installing, you can publish the package configuration file into your application by running the following command on your terminal:

`php artisan config:publish cartalyst/sentinel`

This will publish the config file to `app/config/packages/cartalyst/sentinel/config.php` where you can modify the package configuration.

### Native

Sentinel ships with default implementations for `illuminate/database`, in order to use it, make sure you require it on your `composer.json` file.

```php
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;

require 'vendor/autoload.php';

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

Sentinel::register([
	'email'    => 'test@example.com',
	'password' => 'foobar',
]);
```
