<?php namespace Cartalyst\Sentinel\Native;
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

use Cartalyst\Sentinel\Activations\IlluminateActivationRepository;
use Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use Cartalyst\Sentinel\Checkpoints\SwipeIdentityCheckpoint;
use Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint;
use Cartalyst\Sentinel\Cookies\NativeCookie;
use Cartalyst\Sentinel\Groups\IlluminateGroupRepository;
use Cartalyst\Sentinel\Hashing\NativeHasher;
use Cartalyst\Sentinel\Persistence\SentinelPersistence;
use Cartalyst\Sentinel\Reminders\IlluminateReminderRepository;
use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Sessions\NativeSession;
use Cartalyst\Sentinel\Swipe\SentinelSwipe;
use Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository;
use Cartalyst\Sentinel\Users\IlluminateUserRepository;
use Illuminate\Events\Dispatcher;

class SentinelBootstrapper {

	protected $config;

	public function __construct($config = null)
	{
		if (is_string($config))
		{
			$this->config = new ConfigRepository($config);
		}
		else
		{
			$this->config = $config ?: new ConfigRepository;
		}
	}

	public function createSentinel()
	{
		$persistence = $this->createPersistence();
		$users       = $this->createUsers();
		$groups      = $this->createGroups();
		$dispatcher  = $this->createEventsDispatcher();

		$sentinel = new Sentinel(
			$persistence,
			$users,
			$groups,
			$dispatcher
		);

		$activations = $this->createActivations();
		$ipAddress   = $this->guessIpAddress();
		$checkpoints = $this->createCheckpoints($activations, $ipAddress);

		foreach ($checkpoints as $checkpoint)
		{
			$sentinel->addCheckpoint($checkpoint);
		}

		$reminders = $this->createReminders($users);

		$sentinel->setActivationsRepository($activations);
		$sentinel->setRemindersRepository($reminders);

		return $sentinel;
	}

	protected function createPersistence()
	{
		$session = $this->createSession();
		$cookie = $this->createCookie();

		return new SentinelPersistence($session, $cookie);
	}

	protected function createSession()
	{
		return new NativeSession($this->config['session']);
	}

	protected function createCookie()
	{
		return new NativeCookie($this->config['cookie']);
	}

	protected function createUsers()
	{
		$hasher = $this->createHasher();

		$model = $this->config['users']['model'];

		$groups = $this->config['groups']['model'];
		if (class_exists($groups) && method_exists($groups, 'setUsersModel'))
		{
			forward_static_call_array([$groups, 'setUsersModel'], [$model]);
		}

		return new IlluminateUserRepository($hasher, $model);
	}

	protected function createHasher()
	{
		return new NativeHasher;
	}

	protected function createGroups()
	{
		$model = $this->config['groups']['model'];

		$users = $this->config['users']['model'];
		if (class_exists($users) && method_exists($users, 'setGroupsModel'))
		{
			forward_static_call_array([$users, 'setGroupsModel'], [$model]);
		}

		return new IlluminateGroupRepository($model);
	}

	protected function createActivations()
	{
		$model = $this->config['activations']['model'];
		$expires = $this->config['activations']['expires'];

		return new IlluminateActivationRepository($model, $expires);
	}

	protected function guessIpAddress()
	{
		foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $key)
		{
			if (array_key_exists($key, $_SERVER) === true)
			{
				foreach (explode(',', $_SERVER[$key]) as $ipAddress)
				{
					$ipAddress = trim($ipAddress);

					if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)
					{
						return $ipAddress;
					}
				}
			}
		}
	}

	protected function createActivationCheckpoint(IlluminateActivationRepository $activations)
	{
		return new ActivationCheckpoint($activations);
	}

	protected function createCheckpoints(IlluminateActivationRepository $activations, $ipAddress)
	{
		$checkpoints = $this->config['checkpoints'];

		$activation = $this->createActivationCheckpoint($activations);
		$swipe = $this->createSwipeCheckpoint($ipAddress);
		$throttle = $this->createThrottleCheckpoint($ipAddress);

		foreach ($checkpoints as $index => $checkpoint)
		{
			if ( ! isset($$checkpoint))
			{
				throw new \InvalidArgumentException("Invalid checkpoint [$checkpoint] given.");
			}

			$checkpoints[$index] = $$checkpoint;
		}

		return $checkpoints;
	}

	protected function createSwipeCheckpoint($ipAddress)
	{
		$swipe = $this->createSwipe($ipAddress);

		return new SwipeIdentityCheckpoint($swipe);
	}

	protected function createSwipe($ipAddress)
	{
		$email = $this->config['swipe']['email'];
		$password = $this->config['swipe']['password'];
		$apiKey = $this->config['swipe']['api_key'];
		$appCode = $this->config['swipe']['app_code'];
		$method = $this->config['swipe']['method'];

		return new SentinelSwipe(
			$email,
			$password,
			$apiKey,
			$appCode,
			$ipAddress,
			$method
		);
	}

	protected function createThrottleCheckpoint($ipAddress)
	{
		$throttling = $this->createThrottling();

		return new ThrottleCheckpoint($throttling, $ipAddress);
	}

	protected function createThrottling()
	{
		$model = $this->config['throttling']['model'];

		foreach (['global', 'ip', 'user'] as $type)
		{
			${"{$type}Interval"} = $this->config['throttling'][$type]['interval'];
			${"{$type}Thresholds"} = $this->config['throttling'][$type]['thresholds'];
		}

		return new IlluminateThrottleRepository(
			$model,
			$globalInterval,
			$globalThresholds,
			$ipInterval,
			$ipThresholds,
			$userInterval,
			$userThresholds
		);
	}

	protected function createEventsDispatcher()
	{
		return new Dispatcher;
	}

	protected function createReminders($users)
	{
		$model = $this->config['reminders']['model'];
		$expires = $this->config['reminders']['expires'];

		return new IlluminateReminderRepository($users, $model, $expires);
	}

}
