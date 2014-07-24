### Hashing

By default, Sentinel encourages the sole use of the native PHP 5.5 hashing standard, `password_hash()`. Sentinel requires no configuration to use this method.

While it is not encouraged for security reasons, we provide functionality to override the hashing strategy used by Sentinel so as to accomodate for legacy applications moving forward.

There are 5 built in hashers:

- [Native hasher](#native-hasher)
- [Bcrypt hasher](#bcrypt-hasher)
- [Callback hasher](#callback-hasher)
- [Whirlpool hasher](#other-hashers)
- [SHA256 hasher](#other-hashers)

<div name="native-hasher" data-unique="native-hasher"></div>

#### Native Hasher

The encouraged hasher to use in Sentinel is the native hasher. It will use PHP 5.5's `password_hash()` function and is setup to use the most secure hashing strategy of the day (which is current bcrypt). There is no setup required for this hasher.

<div name="bcrypt-hasher" data-unique="bcrypt-hasher"></div>

#### Bcrypt Hasher

The Bcrypt hasher uses the Bcrypt hashing algorithm. It is a safe algorithm to use, however this hasher has been deprecated in favor of the native hasher as it provides a uniform API to whatever the chosen hashing strategy of the day is.

To use the Bcrypt hasher:

```php
// Native PHP
$sentinel->setHasher(new Cartalyst\Sentinel\Hashing\BcryptHasher);

// In Laravel
Sentinel::setHasher(new Cartalyst\Sentinel\Hashing\BcryptHasher);
```

<div name="callback-hasher" data-unique="callback-hasher"></div>

#### Callback Hasher

The callback hasher is a strategy which allows you to define the methods used to hash a value and in-turn check the hashed value. This is particularly useful when upgrading from legacy systems, which may use one or more hashing strategies. It will allow you to write logic that accounts for old strategies and new strategies, as seen in the example below.

Be **extremely** careful that you don't expose vulnerabilities in your system by designing a hashing strategy that is unsafe to use.

To use the callback hasher:

```php
$hasher = function($value)
{
	return password_hash($value, PASSWORD_DEFAULT);
};

$checker = function($value, $hashedValue)
{
	// Try use the safe password_hash() function first, as all newly hashed passwords will use this
	if (password_verify($value, $hashedValue))
	{
		return true;
	}

	// Because we're upgrading from a legacy system, we'll check if the hash is an old one and therefore allow us to log the person in anyway
	return some_method_to_check_a_hash($value, $hashedValue);
}

// Native PHP
$sentinel->setHasher(new Cartalyst\Sentinel\Hashing\NativeHasher($hasher, $checker));

// In Laravel
Sentinel::setHasher(new Cartalyst\Sentinel\Hashing\NativeHasher($hasher, $checker));
```

<div name="other-hashers" data-unique="other-hashers"></div>

#### Other Hashers

Other hashers, such as the **whirlpool hasher** and the **SHA256 hasher** are supported by Sentinel, however we do not encourage their use as these algorithms are open to vulnerabilities. We would encourage people to use the [callback hahser](#callback-hasher) and implement their own logic for moving away from such systems.

We understand that not every system needs to move away from these strategies however. Telling Sentinel to use these strategies is straight forward:

```php
// Native PHP
$sentinel->setHasher(new Cartalyst\Sentinel\Hashing\WhirlpoolHasher);
$sentinel->setHasher(new Cartalyst\Sentinel\Hashing\Sha256Hasher);

// In Laravel
Sentinel::setHasher(new Cartalyst\Sentinel\Hashing\WhirlpoolHasher);
Sentinel::setHasher(new Cartalyst\Sentinel\Hashing\Sha256Hasher);
```
