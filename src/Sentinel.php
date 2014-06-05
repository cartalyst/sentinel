<?php namespace Cartalyst\Sentinel;
/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the license.txt file.
 *
 * @package    Sentinel
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use BadMethodCallException;
use Cartalyst\Sentinel\Activations\ActivationRepositoryInterface;
use Cartalyst\Sentinel\Activations\IlluminateActivationRepository;
use Cartalyst\Sentinel\Checkpoints\CheckpointInterface;
use Cartalyst\Sentinel\Cookies\NativeCookie;
use Cartalyst\Sentinel\Groups\GroupRepositoryInterface;
use Cartalyst\Sentinel\Groups\IlluminateGroupRepository;
use Cartalyst\Sentinel\Hashing\NativeHasher;
use Cartalyst\Sentinel\Persistence\PersistenceInterface;
use Cartalyst\Sentinel\Persistence\SentinelPersistence;
use Cartalyst\Sentinel\Reminders\IlluminateReminderRepository;
use Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface;
use Cartalyst\Sentinel\Sessions\NativeSession;
use Cartalyst\Sentinel\Users\IlluminateUserRepository;
use Cartalyst\Sentinel\Users\UserInterface;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Closure;
use Illuminate\Events\Dispatcher;
use InvalidArgumentException;

class Sentinel {

	/**
	 * The current cached, logged in user.
	 *
	 * @var \Cartalyst\Sentinel\Users\UserInterface
	 */
	protected $user;

	/**
	 * The persistence driver (the class which actually manages sessions).
	 *
	 * @var \Cartalyst\Sentinel\Persistence\PersistenceInterface
	 */
	protected $persistence;

	/**
	 * The User repository.
	 *
	 * @var \Cartalyst\Sentinel\Users\UserRepositoryInterface
	 */
	protected $users;

	/**
	 * The Group repository.
	 *
	 * @var \Cartalyst\Sentinel\Groups\GroupRepositoryInterface
	 */
	protected $groups;

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
	 * Event dispatcher.
	 *
	 * @var \Illuminate\Events\Dispatcher
	 */
	protected $dispatcher;

	/**
	 * Array that holds all the enabled checkpoints.
	 *
	 * @var array
	 */
	protected $checkpoints = [];

	/**
	 * Bool that holds checkpoint status.
	 *
	 * @var bool
	 */
	protected $checkpointsEnabled = true;

	/**
	 * Reminders repository.
	 *
	 * @var \Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface
	 */
	protected $reminders;

	/**
	 * The closure to retrieve request credentials.
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
	 * Create a new Sentinel instance.
	 *
	 * @param  \Cartalyst\Sentinel\Persistence\PersistenceInterface  $persistence
	 * @param  \Cartalyst\Sentinel\Users\UserRepositoryInterface  $users
	 * @param  \Cartalyst\Sentinel\Groups\GroupRepositoryInterface  $groups
	 * @param  \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface  $activations
	 * @param  \Illuminate\Events\Dispatcher  $dispatcher
	 * @return void
	 */
	public function __construct(
		PersistenceInterface $persistence,
		UserRepositoryInterface $users,
		GroupRepositoryInterface $groups,
		ActivationRepositoryInterface $activations,
		Dispatcher $dispatcher
	)
	{
		$this->persistence = $persistence;

		$this->users = $users;

		$this->groups = $groups;

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
		if ($callback !== null && ! $callback instanceof Closure && ! is_bool($callback))
		{
			throw new InvalidArgumentException('You must provide a closure or a boolean.');
		}

		$valid = $this->users->validForCreation($credentials);

		if ($valid === false)
		{
			return false;
		}

		$argument = $callback instanceof Closure ? $callback : null;

		$user = $this->users->create($credentials, $argument);

		if ($callback === true)
		{
			$this->activate($user);
		}

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
	 * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function activate($user)
	{
		if (is_string($user))
		{
			$users = $this->getUserRepository();

			$user = $users->findById($user);
		}
		elseif (is_array($user))
		{
			$users = $this->getUserRepository();

			$user = $users->findByCredentials($user);
		}

		if ( ! $user instanceof UserInterface)
		{
			throw new InvalidArgumentException('No valid user was provided.');
		}

		$activations = $this->getActivationsRepository();

		$activation = $activations->create($user);

		return $activations->complete($user, $activation->code);
	}

	/**
	 * Checks to see if a user is logged in.
	 *
	 * @return \Cartalyst\Sentinel\Users\UserInterface|bool
	 * @todo   IS this where we would throw exceptions? (Not Activated etc)
	 */
	public function check()
	{
		if ($this->user !== null)
		{
			return $this->user;
		}

		if ( ! $code = $this->persistence->check())
		{
			return false;
		}

		if ( ! $user = $this->users->findByPersistenceCode($code))
		{
			return false;
		}

		if ( ! $this->cycleCheckpoints('check', $user))
		{
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
		return $this->bypassCheckpoints(function($sentinel)
		{
			return $sentinel->check();
		});
	}

	/**
	 * Returns if we are currently a guest.
	 *
	 * @return \Cartalyst\Sentinel\Users\UserInterface|bool
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
		$response = $this->fireEvent('authenticating', $credentials, true);

		if ($response === false) return false;

		if ($credentials instanceof UserInterface)
		{
			$user = $credentials;
		}
		else
		{
			$user = $this->users->findByCredentials($credentials);

			$valid = $user !== null ? $this->users->validateCredentials($user, $credentials) : false;

			if ($user === null || $valid === false)
			{
				$this->cycleCheckpoints('fail', $user, false);

				return false;
			}
		}

		if ( ! $this->cycleCheckpoints('login', $user))
		{
			return false;
		}

		if ($login === true)
		{
			$method = $remember === true ? 'loginAndRemember' : 'login';

			if ( ! $user = $this->{$method}($user))
			{
				return false;
			}
		}

		$this->fireEvent('authenticated', $user);

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
		return $this->bypassCheckpoints(function($sentinel) use ($credentials, $remember)
		{
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
		if ($credentials === null)
		{
			return $this->getBasicResponse();
		}

		$user = $this->stateless($credentials);

		if ($user) return;

		return $this->getBasicResponse();
	}

	/**
	 * Get the request credentials.
	 *
	 * @return array
	 */
	public function getRequestCredentials()
	{
		if ($this->requestCredentials === null)
		{
			$this->requestCredentials = function()
			{
				$credentials = [];

				if (isset($_SERVER['PHP_AUTH_USER']))
				{
					$credentials['login'] = $_SERVER['PHP_AUTH_USER'];
				}

				if (isset($_SERVER['PHP_AUTH_PW']))
				{
					$credentials['password'] = $_SERVER['PHP_AUTH_PW'];
				}

				if (count($credentials) > 0)
				{
					return $credentials;
				}
			};
		}

		$credentials = $this->requestCredentials;

		return $credentials();
	}

	/**
	 * Set the closure which resolves request credentials.
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
	 */
	public function getBasicResponse()
	{
		// Default the basic response
		if ($this->basicResponse === null)
		{
			$this->basicResponse = function()
			{
				if (headers_sent())
				{
					throw new \RuntimeException('Attempting basic auth after headers have already been sent.');
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
	 * Set the callback which creates a basic response.
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
		$method = $remember === true ? 'addAndRemember' : 'add';

		$this->persistence->{$method}($user);

		$response = $this->users->recordLogin($user);

		if ($response === false)
		{
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
	 * Log the current (or given) user out.
	 *
	 * @param  bool  $everywhere
	 * @return bool
	 */
	public function logout($everywhere = false)
	{
		$user = $this->getUser();

		if ($user === null)
		{
			return true;
		}

		$method = $everywhere === true ? 'flush' : 'remove';

		$this->persistence->{$method}($user);

		return $this->users->recordLogout($user);
	}

	/**
	 * Pass a closure to Sentinel to bypass checkpoints.
	 *
	 * @param  \Closure  $callback
	 * @return mixed
	 */
	public function bypassCheckpoints(Closure $callback)
	{
		// Temporarily remove the array of registered checkpoints
		$checkpoints = $this->checkpoints;

		$this->checkpoints = false;

		// Fire the callback
		$result = $callback($this);

		// Reset checkpoints
		$this->checkpoints = $checkpoints;

		return $result;
	}

	/**
	 * Returns if checkpoints are enabled.
	 *
	 * @return bool
	 */
	public function checkpointsEnabled()
	{
		return $this->checkpoints;
	}

	/**
	 * Enables checkpoints.
	 *
	 * @return void
	 */
	public function enableCheckpoints()
	{
		$this->checkpointsEnabled = true;
	}

	/**
	 * Disables checkpoints.
	 *
	 * @return void
	 */
	public function disableCheckpoints()
	{
		$this->checkpointsEnabled = false;
	}

	/**
	 * Add a new checkpoint to Sentinel.
	 *
	 * @param  \Cartalyst\Sentinel\Checkpoints\CheckpointInterface  $checkpoint
	 * @return void
	 */
	public function addCheckpoint(CheckpointInterface $checkpoint)
	{
		$this->checkpoints[] = $checkpoint;
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
		if ( ! $this->checkpointsEnabled)
		{
			return true;
		}

		foreach ($this->checkpoints as $checkpoint)
		{
			$response = $checkpoint->{$method}($user);

			if ($response === false && $halt === true)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Register a Sentinel event.
	 *
	 * @param  string  $event
	 * @param  \Closure|string  $callback
	 * @param  int  $priority
	 * @return void
	 */
	protected function registerEvent($event, $callback, $priority = 0)
	{
		$dispatcher = $this->getEventDispatcher();

		$dispatcher->listen("sentinel.{$event}", $callback, $priority);
	}

	/**
	 * Call a Sentinel event.
	 *
	 * @param  string  $event
	 * @param  mixed   $payload
	 * @param  bool    $halt
	 * @return mixed
	 */
	protected function fireEvent($event, $payload = [], $halt = false)
	{
		if ( ! $dispatcher = $this->getEventDispatcher()) return;

		$method = $halt ? 'until' : 'fire';

		return $dispatcher->{$method}("sentinel.{$event}", $payload);
	}

	/**
	 * Returns the currently logged in user, lazily checking for it.
	 *
	 * @param  bool  $check
	 * @return \Cartalyst\Sentinel\Users\UserInterface
	 */
	public function getUser($check = true)
	{
		if ($check === true && $this->user === null)
		{
			$this->check();
		}

		return $this->user;
	}

	/**
	 * Set the user associated with Sentinel (does not log in).
	 *
	 * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
	 * @return void
	 */
	public function setUser(UserInterface $user)
	{
		$this->user = $user;
	}

	/**
	 * Returns the persistence instance.
	 *
	 * @return \Cartalyst\Sentinel\Persistence\PersistenceInterface
	 */
	public function getPersistence()
	{
		if ($this->persistence === null)
		{
			$this->persistence = $this->createPersistence();
		}

		return $this->persistence;
	}

	/**
	 * Set the persistence instance.
	 *
	 * @param  \Cartalyst\Sentinel\Persistence\PersistenceInterface  $persistence
	 * @return void
	 */
	public function setPersistence(PersistenceInterface $persistence)
	{
		$this->persistence = $persistence;
	}

	/**
	 * Creates a persistence instance.
	 *
	 * @return \Cartalyst\Sentinel\Users\IlluminateUserRepository
	 */
	protected function createPersistence()
	{
		$session = new NativeSession;

		$cookie = new NativeCookie;

		return new SentinelPersistence($session, $cookie);
	}

	/**
	 * Returns the user repository.
	 *
	 * @return \Cartalyst\Sentinel\Users\UserRepositoryInterface
	 */
	public function getUserRepository()
	{
		if ($this->users === null)
		{
			$this->users = $this->createUserRepository();

			$this->userMethods = [];
		}

		return $this->users;
	}

	/**
	 * Set the user repository.
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
	 * Creates a default user repository if none has been specified.
	 *
	 * @return \Cartalyst\Sentinel\Users\IlluminateUserRepository
	 */
	protected function createUserRepository()
	{
		$hasher = new NativeHasher;

		$model = 'Cartalyst\Sentinel\Users\EloquentUser';

		return new IlluminateUserRepository($hasher, $model);
	}

	/**
	 * Returns the group repository.
	 *
	 * @return \Cartalyst\Sentinel\Groups\GroupRepositoryInterface
	 */
	public function getGroupRepository()
	{
		if ($this->groups === null)
		{
			$this->groups = $this->createGroupRepository();
		}

		return $this->groups;
	}

	/**
	 * Set the group repository.
	 *
	 * @param  \Cartalyst\Sentinel\Groups\GroupRepositoryInterface  $groups
	 * @return void
	 */
	public function setGroupRepository(GroupRepositoryInterface $groups)
	{
		$this->groups = $groups;
	}

	/**
	 * Creates a default group repository if none has been specified.
	 *
	 * @return \Cartalyst\Sentinel\Groups\IlluminateGroupRepository
	 */
	protected function createGroupRepository()
	{
		$model = 'Cartalyst\Sentinel\Groups\EloquentGroup';

		return new IlluminateGroupRepository($model);
	}

	/**
	 * Get the event dispatcher.
	 *
	 * @return \Illuminate\Events\Dispatcher
	 */
	public function getEventDispatcher()
	{
		return $this->dispatcher;
	}

	/**
	 * Set the event dispatcher.
	 *
	 * @param  \Illuminate\Events\Dispatcher  $dispatcher
	 * @return void
	 */
	public function setEventDispatcher(Dispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Returns the activations repository.
	 *
	 * @return \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface
	 */
	public function getActivationsRepository()
	{
		if ($this->activations === null)
		{
			$this->activations = $this->createActivationsRepository();
		}

		return $this->activations;
	}

	/**
	 * Set the activations repository.
	 *
	 * @param  \Cartalyst\Sentinel\Activations\ActivationRepositoryInterface  $activations
	 * @return void
	 */
	public function setActivationsRepository(ActivationRepositoryInterface $activations)
	{
		$this->activations = $activations;
	}

	/**
	 * Creates a default activations repository if none has been specified.
	 *
	 * @return \Cartalyst\Sentinel\Activations\IlluminateActivationRepository
	 */
	protected function createActivationsRepository()
	{
		$model = 'Cartalyst\Sentinel\Activations\EloquentActivation';

		return new IlluminateActivationRepository($model);
	}

	/**
	 * Returns the reminders repository.
	 *
	 * @return \Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface
	 */
	public function getRemindersRepository()
	{
		if ($this->reminders === null)
		{
			$this->reminders = $this->createRemindersRepository();
		}

		return $this->reminders;
	}

	/**
	 * Set the reminders repository.
	 *
	 * @param  \Cartalyst\Sentinel\Reminders\ReminderRepositoryInterface  $reminders
	 * @return void
	 */
	public function setRemindersRepository(ReminderRepositoryInterface $reminders)
	{
		$this->reminders = $reminders;
	}

	/**
	 * Creates a default reminders repository if none has been specified.
	 *
	 * @return \Cartalyst\Sentinel\Reminders\IlluminateReminderRepository
	 */
	protected function createRemindersRepository()
	{
		$model = 'Cartalyst\Sentinel\Reminders\EloquentReminder';

		$users = $this->getUserRepository();

		return new IlluminateReminderRepository($users, $model);
	}

	/**
	 * Returns all accessible methods on the associated user repository.
	 *
	 * @return array
	 */
	protected function getUserMethods()
	{
		if (empty($this->userMethods))
		{
			$users = $this->getUserRepository();

			$methods = get_class_methods($users);
			$banned = ['__construct'];

			foreach ($banned as $method)
			{
				$index = array_search($method, $methods);

				if ($index !== false)
				{
					unset($methods[$index]);
				}
			}

			$this->userMethods = $methods;
		}

		return $this->userMethods;
	}

	/**
	 * Dynamically pass missing methods to Sentinel.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 * @throws \BadMethodCallException
	 */
	public function __call($method, $parameters)
	{
		$methods = $this->getUserMethods();

		if (in_array($method, $methods))
		{
			$users = $this->getUserRepository();

			return call_user_func_array([$users, $method], $parameters);
		}

		if (starts_with($method, 'findUserBy'))
		{
			$user = $this->getUserRepository();

			$method = 'findBy'.substr($method, 10);

			return call_user_func_array([$user, $method], $parameters);
		}

		if (starts_with($method, 'findGroupBy'))
		{
			$groups = $this->getGroupRepository();

			$method = 'findBy'.substr($method, 11);

			return call_user_func_array([$groups, $method], $parameters);
		}

		$methods = ['getGroups', 'inGroup', 'hasAccess', 'hasAnyAccess'];

		$className = get_class($this);

		if (in_array($method, $methods))
		{
			$user = $this->getUser();

			if ($user === null)
			{
				throw new BadMethodCallException("Method {$className}::{$method}() can only be called if a user is logged in.");
			}

			return call_user_func_array([$user, $method], $parameters);
		}

		throw new BadMethodCallException("Call to undefined method {$className}::{$method}()");
	}

}
