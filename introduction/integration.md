# Integration

## Laravel 4

The Sentinel package has optional support for Laravel 4 and it comes bundled with a Service Provider and a Facade for easy integration.

After installing the package, open your Laravel config file located at `app/config/app.php` and add the following lines.

In the `$providers` array add the following service provider for this package.

	'Cartalyst\Sentinel\Laravel\SentinelServiceProvider',

In the `$aliases` array add the following facades for this package.

	'Activation'    => 'Cartalyst\Sentinel\Laravel\Facades\Activation',
	'Reminder'      => 'Cartalyst\Sentinel\Laravel\Facades\Reminder',
	'Sentinel'      => 'Cartalyst\Sentinel\Laravel\Facades\Sentinel',
	'SwiftIdentity' => 'Cartalyst\Sentinel\Laravel\Facades\SwiftIdentity',

### Migrations

Run the following command to migrate Sentinel.

`php artisan migrate --package=cartalyst/sentinel`

### Configuration

After installing, you can publish the package configuration file into your application by running the following command on your terminal:

	php artisan config:publish cartalyst/sentinel

This will publish the config file to `app/config/packages/cartalyst/sentinel/config.php` where you can modify the package configuration.

## Native

..
