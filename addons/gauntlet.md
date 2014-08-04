### Sentinel Gauntlet

Sentinel Gauntlet is a Sentinel addon that prevents users from setting the same password more than once.

#### Installation

The best and easiest way to install Sentinel Unique Passwords is with [Composer](http://getcomposer.org).

##### Preparation

Open your `composer.json` file and add the following to the `require` array:

	"cartalyst/sentinel-gauntlet": "1.0.*"

> **Note:** Make sure that after the required changes your `composer.json` file is valid by running `composer validate`.

##### Install the dependencies

Run Composer to install or update the new requirement.

	php composer install

or

	php composer update

Now you are able to require the `vendor/autoload.php` file to autoload the addon.

#### Integration

##### Laravel 4

The Sentinel Unique Passwords addon has optional support for Laravel 4 and it comes bundled with a Service Provider for easy integration.

After installing the addon, open your Laravel config file located at `app/config/app.php` and add the following.

In the `$providers` array add the following service provider for this addon.

	'Cartalyst\SentinelGauntlet\Laravel\SentinelGauntletServiceProvider',

##### Migrations

Run the following command to migrate Sentinel Unique Passwords.

`php artisan migrate --package=cartalyst/sentinel-gauntlet`

#### Native

```php
use Cartalyst\SentinelGauntlet\UniquePasswords;

$uniquePasswords = new UniquePasswords(Sentinel::getUserRepository());

Sentinel::getEventDispatcher()->listen("eloquent.created: Cartalyst\Sentinel\Users\EloquentUser", function($user, $credentials) use ($uniquePasswords)
{
	$uniquePasswords->created($user, $credentials);
});

Sentinel::getEventDispatcher()->listen('sentinel.user.filled', function($user, $credentials) use ($uniquePasswords)
{
	$uniquePasswords->filled($user, $credentials);
});


#### Usage

$user = Sentinel::findById(1);

try
{
	Sentinel::update($user, ['password' => 'foobar']);
}
catch (Cartalyst\SentinelGauntlet\Exceptions\NotUniquePasswordException $e)
{
	// Generate your error here
}
```
