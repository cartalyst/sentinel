### Throttle

There are three types of throttling.

- `global` throttling will monitor the overall failed login attempts across your site and can limit the affects of an attempted DDoS attack.
- `ip` throttling allows you to throttle the failed login attempts (across any account) of a given IP address.
- `user` throttling allows you to throttle the login attempts on an individual user account.

Each type of throttling has the same options. The first is the interval, this is the time (in seconds) for which we check for failed logins. Any logins outside this time are no longer assessed when throttling.

The second option is thresholds, this may be approached using one of two ways.

- The first way, is by providing a key/value array, the key is the number of failed login attempts, and the value is the delay in seconds before the next attempt can occur.
- The second way is by providing an integer, if the number of failed login attempts outweigh the thresholds integer, that throttle is locked until there are no more failed login attempts within the specified interval.

On this premise, we encourage you to use array thresholds for global throttling (and perhaps IP throttling as well), so as to not lock your whole site out for minutes on end because it's being DDoS'd. However, for user throttling, locking a single account out because somebody is attempting to breach it could be an appropriate response.

You may use any type of throttling for any scenario, and the specific configurations are designed to be customized as your site grows.

#### Exceptions

- `Cartalyst\Sentinel\Checkpoints\ThrottlingException`

Methods               | Parameters                                   | Description
--------------------- | -------------------------------------------- | -----------
setDelay              | Cartalyst\Sentinel\Users\UserInterface $user | Sets a user object on the exception.
getDelay              | ..                                           | Retrieves the user object that caused the exception.
setType               | string $type                                 | Sets a user object on the exception.
getType               | ..                                           | Retrieves the user object that caused the exception.
getFree               | ..                                           | Retrieves time the throttle is lifted.

