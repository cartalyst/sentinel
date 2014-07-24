### Unique Passwords

Sentinel Unique Passwords is a Sentinel addon that prevents users from setting the same password more than once.

#### Installation

The best and easiest way to install Sentinel Unique Passwords is with [Composer](http://getcomposer.org).

##### Preparation

Open your `composer.json` file and add the following to the `require` array:

	"cartalyst/sentinel-unique-passwords": "dev-master"

> **Note:** This version is still in development, make sure that you set `min-stability` to `dev` on your `composer.json` file.

> **Note:** Make sure that after the required changes your `composer.json` file is valid by running `composer validate`.

##### Install the dependencies

Run Composer to install or update the new requirement.

	php composer install

or

	php composer update

Now you are able to require the `vendor/autoload.php` file to autoload the package.

#### Integration

##### Laravel 4

The Sentinel Unique Passwords package has optional support for Laravel 4 and it comes bundled with a Service Provider for easy integration.

After installing the package, open your Laravel config file located at `app/config/app.php` and add the following.

In the `$providers` array add the following service provider for this package.

	'Cartalyst\SentinelUniquePasswords\Laravel\SentinelUniquePasswordsServiceProvider',

##### Migrations

Run the following command to migrate Sentinel Unique Passwords.

`php artisan migrate --package=cartalyst/sentinel-unique-passwords`

#### Native

```php
use Cartalyst\SentinelUniquePasswords\UniquePasswords;

$uniquePasswords = new UniquePasswords(Sentinel::getUserRepository());

Sentinel::getEventDispatcher()->listen("eloquent.created: Cartalyst\Sentinel\Users\EloquentUser", function($user, $credentials) use ($uniquePasswords)
{
	$uniquePasswords->created($user, $credentials);
});

Sentinel::getEventDispatcher()->listen('sentinel.user.filled', function($user, $credentials) use ($uniquePasswords)
{
	$uniquePasswords->filled($user, $credentials);
});

$user = Sentinel::findById(1);

try
{
	Sentinel::update($user, ['password' => 'foobar']);
}
catch (Cartalyst\SentinelUniquePasswords\Exceptions\NotUniquePasswordException $e)
{
	// Generate your error here
}
```
