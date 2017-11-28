<?php

/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Sentinel
 * @version    2.0.17
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2017, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel;

use BadMethodCallException;
use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Checkpoints\CheckpointInterface;
use Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface;
use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Cartalyst\Sentinel\Roles\RoleRepositoryInterface;
use Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface;
use Cartalyst\Sentinel\Users\UserInterface;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Cartalyst\Support\Traits\EventTrait;
use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;
use RuntimeException;

class Sentinel
{
    use EventTrait;

    /**
     * The current cached, logged in user.
     *
     * @var \Cartalyst\Sentinel\Users\UserInterface
     */
    protected $user;

    /**
     * The Persistence repository.
     *
     * @var \Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface
     */
    protected $persistences;

    /**
     * The User repository.
     *
     * @var \Cartalyst\Sentinel\Users\UserRepositoryInterface
     */
    protected $users;

    /**
     * The Role repository.
     *
     * @var \Cartalyst\Sentinel\Roles\RoleRepositoryInterface
     */
    protected $roles;

    /**
     * The Activations repository.
     *
     * @var \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface
     */
    protected $activations;

    /**
     * Cached, available methods on the user repository, used for dynamic calls.
     *
     * @var array
     */
    protected $userMethods = [];

    /**
     * Array that holds all the enabled checkpoints.
     *
     * @var array
     */
    protected $checkpoints = [];

    /**
     * Flag for the checkpoint status.
     *
     * @var bool
     */
    protected $checkpointsStatus = true;

    /**
     * The Reminders repository.
     *
     * @var \Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface
     */
    protected $reminders;

    /**
     * The closure to retrieve the request credentials.
     *
     * @var \Closure
     */
    protected $requestCredentials;

    /**
     * The closure used to create a basic response for failed HTTP auth.
     *
     * @var \Closure
     */
    protected $basicResponse;

    /**
     * The Throttle repository.
     *
     * @var \Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface
     */
    protected $throttle;

    /**
     * Create a new Sentinel instance.
     *
     * @param  \Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface  $persistence
     * @param  \Cartalyst\Sentinel\Users\UserRepositoryInterface  $users
     * @param  \Cartalyst\Sentinel\Roles\RoleRepositoryInterface  $roles
     * @param  \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface  $activations
     * @param  \Illuminate\Contracts\Events\Dispatcher  $dispatcher
     * @return void
     */
    public function __construct(
        PersistenceRepositoryInterface $persistences,
        UserRepositoryInterface $users,
        RoleRepositoryInterface $roles,
        ActivationRepositoryInterface $activations,
        Dispatcher $dispatcher
    ) {
        $this->persistences = $persistences;

        $this->users = $users;

        $this->roles = $roles;

        $this->activations = $activations;

        $this->dispatcher = $dispatcher;
    }

    /**
     * Registers a user. You may provide a callback to occur before the user
     * is saved, or provide a true boolean as a shortcut to activation.
     *
     * @param  array  $credentials
     * @param  \Closure|bool  $callback
     * @return \Cartalyst\Sentinel\Users\UserInteface|bool
     * @throws \InvalidArgumentException
     */
    public function register(array $credentials, $callback = null)
    {
        if ($callback !== null && ! $callback instanceof Closure && ! is_bool($callback)) {
            throw new InvalidArgumentException('You must provide a closure or a boolean.');
        }

        $this->fireEvent('sentinel.registering', $credentials);

        $valid = $this->users->validForCreation($credentials);

        if ($valid === false) {
            return false;
        }

        $argument = $callback instanceof Closure ? $callback : null;

        $user = $this->users->create($credentials, $argument);

        if ($callback === true) {
            $this->activate($user);
        }

        $this->fireEvent('sentinel.registered', $user);

        return $user;
    }

    /**
     * Registers and activates the user.
     *
     * @param  array  $credentials
     * @return \Cartalyst\Sentinel\Users\UserInteface|bool
     */
    public function registerAndActivate(array $credentials)
    {
        return $this->register($credentials, true);
    }

    /**
     * Activates the given user.
     *
     * @param  mixed  $user
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function activate($user)
    {
        if (is_string($user) || is_array($user)) {
            $users = $this->getUserRepository();

            $method = 'findBy'.(is_string($user) ? 'Id' : 'Credentials');

            $user = $users->{$method}($user);
        }

        if (! $user instanceof UserInterface) {
            throw new InvalidArgumentException('No valid user was provided.');
        }

        $this->fireEvent('sentinel.activating', $user);

        $activations = $this->getActivationRepository();

        $activation = $activations->create($user);

        $this->fireEvent('sentinel.activated', [ $user, $activation ]);

        return $activations->complete($user, $activation->getCode());
    }

    /**
     * Checks to see if a user is logged in.
     *
     * @return \Cartalyst\Sentinel\Users\UserInterface|bool
     */
    public function check()
    {
        if ($this->user !== null) {
            return $this->user;
        }

        if (! $code = $this->persistences->check()) {
            return false;
        }

        if (! $user = $this->persistences->findUserByPersistenceCode($code)) {
            return false;
        }

        if (! $this->cycleCheckpoints('check', $user)) {
            return false;
        }

        return $this->user = $user;
    }

    /**
     * Checks to see if a user is logged in, bypassing checkpoints
     *
     * @return \Cartalyst\Sentinel\Users\UserInterface|bool
     */
    public function forceCheck()
    {
        return $this->bypassCheckpoints(function ($sentinel) {
            return $sentinel->check();
        });
    }

    /**
     * Checks if we are currently a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return ! $this->check();
    }

    /**
     * Authenticates a user, with "remember" flag.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface|array  $credentials
     * @param  bool  $remember
     * @param  bool  $login
     * @return \Cartalyst\Sentinel\Users\UserInterface|bool
     */
    public function authenticate($credentials, $remember = false, $login = true)
    {
        $response = $this->fireEvent('sentinel.authenticating', $credentials, true);

        if ($response === false) {
            return false;
        }

        if ($credentials instanceof UserInterface) {
            $user = $credentials;
        } else {
            $user = $this->users->findByCredentials($credentials);

            $valid = $user !== null ? $this->users->validateCredentials($user, $credentials) : false;

            if ($user === null || $valid === false) {
                $this->cycleCheckpoints('fail', $user, false);

                return false;
            }
        }

        if (! $this->cycleCheckpoints('login', $user)) {
            return false;
        }

        if ($login === true) {
            $method = $remember === true ? 'loginAndRemember' : 'login';

            if (! $user = $this->{$method}($user)) {
                return false;
            }
        }

        $this->fireEvent('sentinel.authenticated', $user);

        return $this->user = $user;
    }

    /**
     * Authenticates a user, with the "remember" flag.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface|array  $credentials
     * @return \Cartalyst\Sentinel\Users\UserInterface|bool
     */
    public function authenticateAndRemember($credentials)
    {
        return $this->authenticate($credentials, true);
    }

    /**
     * Forces an authentication to bypass checkpoints.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface|array  $credentials
     * @param  bool  $remember
     * @return \Cartalyst\Sentinel\Users\UserInterface|bool
     */
    public function forceAuthenticate($credentials, $remember = false)
    {
        return $this->bypassCheckpoints(function ($sentinel) use ($credentials, $remember) {
            return $sentinel->authenticate($credentials, $remember);
        });
    }

    /**
     * Forces an authentication to bypass checkpoints, with the "remember" flag.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface|array  $credentials
     * @return \Cartalyst\Sentinel\Users\UserInterface|bool
     */
    public function forceAuthenticateAndRemember($credentials)
    {
        return $this->forceAuthenticate($credentials, true);
    }

    /**
     * Attempt a stateless authentication.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface|array  $credentials
     * @return \Cartalyst\Sentinel\Users\UserInterface|bool
     */
    public function stateless($credentials)
    {
        return $this->authenticate($credentials, false, false);
    }

    /**
     * Attempt to authenticate using HTTP Basic Auth.
     *
     * @return mixed
     */
    public function basic()
    {
        $credentials = $this->getRequestCredentials();

        // We don't really want to add a throttling record for the
        // first failed login attempt, which actually occurs when
        // the user first hits a protected route.
        if ($credentials === null) {
            return $this->getBasicResponse();
        }

        $user = $this->stateless($credentials);

        if ($user) {
            return;
        }

        return $this->getBasicResponse();
    }

    /**
     * Returns the request credentials.
     *
     * @return array
     */
    public function getRequestCredentials()
    {
        if ($this->requestCredentials === null) {
            $this->requestCredentials = function () {
                $credentials = [];

                if (isset($_SERVER['PHP_AUTH_USER'])) {
                    $credentials['login'] = $_SERVER['PHP_AUTH_USER'];
                }

                if (isset($_SERVER['PHP_AUTH_PW'])) {
                    $credentials['password'] = $_SERVER['PHP_AUTH_PW'];
                }

                if (count($credentials) > 0) {
                    return $credentials;
                }
            };
        }

        $credentials = $this->requestCredentials;

        return $credentials();
    }

    /**
     * Sets the closure which resolves the request credentials.
     *
     * @param  \Closure  $requestCredentials
     * @return void
     */
    public function setRequestCredentials(Closure $requestCredentials)
    {
        $this->requestCredentials = $requestCredentials;
    }

    /**
     * Sends a response when HTTP basic authentication fails.
     *
     * @return mixed
     * @throws \RuntimeException
     */
    public function getBasicResponse()
    {
        // Default the basic response
        if ($this->basicResponse === null) {
            $this->basicResponse = function () {
                if (headers_sent()) {
                    throw new RuntimeException('Attempting basic auth after headers have already been sent.');
                }

                header('WWW-Authenticate: Basic');
                header('HTTP/1.0 401 Unauthorized');

                echo 'Invalid credentials.';
                exit;
            };
        }

        $response = $this->basicResponse;

        return $response();
    }

    /**
     * Sets the callback which creates a basic response.
     *
     * @param  \Closure  $basicResonse
     * @return void
     */
    public function creatingBasicResponse(Closure $basicResponse)
    {
        $this->basicResponse = $basicResponse;
    }

    /**
     * Persists a login for the given user.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @param  bool  $remember
     * @return \Cartalyst\Sentinel\Users\UserInterface|bool
     */
    public function login(UserInterface $user, $remember = false)
    {
        $method = $remember === true ? 'persistAndRemember' : 'persist';

        $this->persistences->{$method}($user);

        $response = $this->users->recordLogin($user);

        if ($response === false) {
            return false;
        }

        return $this->user = $user;
    }

    /**
     * Persists a login for the given user, with the "remember" flag.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return \Cartalyst\Sentinel\Users\UserInterface|bool
     */
    public function loginAndRemember(UserInterface $user)
    {
        return $this->login($user, true);
    }

    /**
     * Logs the current user out.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @param  bool  $everywhere
     * @return bool
     */
    public function logout(UserInterface $user = null, $everywhere = false)
    {
        $currentUser = $this->check();

        if ($user && $user !== $currentUser) {
            $this->persistences->flush($user, false);

            return true;
        }

        $user = $user ?: $currentUser;

        if ($user === false) {
            return true;
        }

        $method = $everywhere === true ? 'flush' : 'forget';

        $this->persistences->{$method}($user);

        $this->user = null;

        return $this->users->recordLogout($user);
    }

    /**
     * Pass a closure to Sentinel to bypass checkpoints.
     *
     * @param  \Closure  $callback
     * @param  array  $checkpoints
     * @return mixed
     */
    public function bypassCheckpoints(Closure $callback, $checkpoints = [])
    {
        $originalCheckpoints = $this->checkpoints;

        $activeCheckpoints = [];

        foreach (array_keys($originalCheckpoints) as $checkpoint) {
            if ($checkpoints && ! in_array($checkpoint, $checkpoints)) {
                $activeCheckpoints[$checkpoint] = $originalCheckpoints[$checkpoint];
            }
        }

        // Temporarily replace the registered checkpoints
        $this->checkpoints = $activeCheckpoints;

        // Fire the callback
        $result = $callback($this);

        // Reset checkpoints
        $this->checkpoints = $originalCheckpoints;

        return $result;
    }

    /**
     * Checks if checkpoints are enabled.
     *
     * @return bool
     */
    public function checkpointsStatus()
    {
        return $this->checkpointsStatus;
    }

    /**
     * Enables checkpoints.
     *
     * @return void
     */
    public function enableCheckpoints()
    {
        $this->checkpointsStatus = true;
    }

    /**
     * Disables checkpoints.
     *
     * @return void
     */
    public function disableCheckpoints()
    {
        $this->checkpointsStatus = false;
    }

    /**
     * Add a new checkpoint to Sentinel.
     *
     * @param  string  $key
     * @param  \Cartalyst\Sentinel\Checkpoints\CheckpointInterface  $checkpoint
     * @return void
     */
    public function addCheckpoint($key, CheckpointInterface $checkpoint)
    {
        $this->checkpoints[$key] = $checkpoint;
    }

    /**
     * Removes a checkpoint.
     *
     * @param  string  $key
     * @return void
     */
    public function removeCheckpoint($key)
    {
        if (isset($this->checkpoints[$key])) {
            unset($this->checkpoints[$key]);
        }
    }

    /**
     * Removes the given checkpoints.
     *
     * @param  array  $checkpoints
     * @return void
     */
    public function removeCheckpoints(array $checkpoints = [])
    {
        foreach ($checkpoints as $checkpoint) {
            $this->removeCheckpoint($checkpoint);
        }
    }

    /**
     * Cycles through all the registered checkpoints for a user. Checkpoints
     * may throw their own exceptions, however, if just one returns false,
     * the cycle fails.
     *
     * @param  string  $method
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @param  bool  $halt
     * @return bool
     */
    protected function cycleCheckpoints($method, UserInterface $user = null, $halt = true)
    {
        if (! $this->checkpointsStatus) {
            return true;
        }

        foreach ($this->checkpoints as $checkpoint) {
            $response = $checkpoint->{$method}($user);

            if ($response === false && $halt === true) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the currently logged in user, lazily checking for it.
     *
     * @param  bool  $check
     * @return \Cartalyst\Sentinel\Users\UserInterface
     */
    public function getUser($check = true)
    {
        if ($check === true && $this->user === null) {
            $this->check();
        }

        return $this->user;
    }

    /**
     * Sets the user associated with Sentinel (does not log in).
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return void
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Returns the user repository.
     *
     * @return \Cartalyst\Sentinel\Users\UserRepositoryInterface
     */
    public function getUserRepository()
    {
        return $this->users;
    }

    /**
     * Sets the user repository.
     *
     * @param  \Cartalyst\Sentinel\Users\UserRepositoryInterface  $users
     * @return void
     */
    public function setUserRepository(UserRepositoryInterface $users)
    {
        $this->users = $users;

        $this->userMethods = [];
    }

    /**
     * Returns the role repository.
     *
     * @return \Cartalyst\Sentinel\Roles\RoleRepositoryInterface
     */
    public function getRoleRepository()
    {
        return $this->roles;
    }

    /**
     * Sets the role repository.
     *
     * @param  \Cartalyst\Sentinel\Roles\RoleRepositoryInterface  $roles
     * @return void
     */
    public function setRoleRepository(RoleRepositoryInterface $roles)
    {
        $this->roles = $roles;
    }

    /**
     * Returns the persistences repository.
     *
     * @return \Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface
     */
    public function getPersistenceRepository()
    {
        return $this->persistences;
    }

    /**
     * Sets the persistences repository.
     *
     * @param  \Cartalyst\Sentinel\Persistences\PersistenceRepositoryInterface  $persistences
     * @return void
     */
    public function setPersistenceRepository(PersistenceRepositoryInterface $persistences)
    {
        $this->persistences = $persistences;
    }

    /**
     * Returns the activations repository.
     *
     * @return \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface
     */
    public function getActivationRepository()
    {
        return $this->activations;
    }

    /**
     * Sets the activations repository.
     *
     * @param  \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface  $activations
     * @return void
     */
    public function setActivationRepository(ActivationRepositoryInterface $activations)
    {
        $this->activations = $activations;
    }

    /**
     * Returns the reminders repository.
     *
     * @return \Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface
     */
    public function getReminderRepository()
    {
        return $this->reminders;
    }

    /**
     * Sets the reminders repository.
     *
     * @param  \Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface  $reminders
     * @return void
     */
    public function setReminderRepository(ReminderRepositoryInterface $reminders)
    {
        $this->reminders = $reminders;
    }

    /**
     * Returns the throttle repository.
     *
     * @return \Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface
     */
    public function getThrottleRepository()
    {
        return $this->throttle;
    }

    /**
     * Sets the throttle repository.
     *
     * @param  \Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface  $throttle
     * @return void
     */
    public function setThrottleRepository(ThrottleRepositoryInterface $throttle)
    {
        $this->throttle = $throttle;
    }

    /**
     * Returns all accessible methods on the associated user repository.
     *
     * @return array
     */
    protected function getUserMethods()
    {
        if (empty($this->userMethods)) {
            $users = $this->getUserRepository();

            $methods = get_class_methods($users);

            $this->userMethods = array_diff($methods, ['__construct']);
        }

        return $this->userMethods;
    }

    /**
     * Dynamically pass missing methods to Sentinel.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        $methods = $this->getUserMethods();

        if (in_array($method, $methods)) {
            $users = $this->getUserRepository();

            return call_user_func_array([$users, $method], $parameters);
        }

        if (starts_with($method, 'findUserBy')) {
            $user = $this->getUserRepository();

            $method = 'findBy'.substr($method, 10);

            return call_user_func_array([$user, $method], $parameters);
        }

        if (starts_with($method, 'findRoleBy')) {
            $roles = $this->getRoleRepository();

            $method = 'findBy'.substr($method, 10);

            return call_user_func_array([$roles, $method], $parameters);
        }

        $methods = ['getRoles', 'inRole', 'hasAccess', 'hasAnyAccess'];

        $className = get_class($this);

        if (in_array($method, $methods)) {
            $user = $this->getUser();

            if ($user === null) {
                throw new BadMethodCallException("Method {$className}::{$method}() can only be called if a user is logged in.");
            }

            return call_user_func_array([$user, $method], $parameters);
        }

        throw new BadMethodCallException("Call to undefined method {$className}::{$method}()");
    }
}
