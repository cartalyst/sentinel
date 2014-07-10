<?php namespace Cartalyst\Sentinel\Laravel;
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
use Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint;
use Cartalyst\Sentinel\Cookies\IlluminateCookie;
use Cartalyst\Sentinel\Groups\IlluminateGroupRepository;
use Cartalyst\Sentinel\Hashing\NativeHasher;
use Cartalyst\Sentinel\Persistence\SentinelPersistence;
use Cartalyst\Sentinel\Reminders\IlluminateReminderRepository;
use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Sessions\IlluminateSession;
use Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository;
use Cartalyst\Sentinel\Users\IlluminateUserRepository;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class SentinelServiceProvider extends ServiceProvider {

	/**
	 * {@inheritDoc}
	 */
	protected $defer = true;

	/**
	 * {@inheritDoc}
	 */
	public function boot()
	{
		$this->package('cartalyst/sentinel', 'cartalyst/sentinel', __DIR__.'/..');
	}

	/**
	 * {@inheritDoc}
	 */
	public function register()
	{
		$this->registerPersistence();
		$this->registerUsers();
		$this->registerGroups();
		$this->registerCheckpoints();
		$this->registerReminders();
		$this->registerSentinel();
	}

	protected function registerPersistence()
	{
		$this->registerSession();
		$this->registerCookie();

		$this->app['sentinel.persistence'] = $this->app->share(function($app)
		{
			return new SentinelPersistence($app['sentinel.session'], $app['sentinel.cookie']);
		});
	}

	protected function registerSession()
	{
		$this->app['sentinel.session'] = $this->app->share(function($app)
		{
			$key = $app['config']['cartalyst/sentinel::session'];

			return new IlluminateSession($app['session.store'], $key);
		});
	}

	protected function registerCookie()
	{
		$this->app['sentinel.cookie'] = $this->app->share(function($app)
		{
			$key = $app['config']['cartalyst/sentinel::cookie'];

			return new IlluminateCookie($app['request'], $app['cookie'], $key);
		});
	}

	protected function registerUsers()
	{
		$this->registerHasher();

		$this->app['sentinel.users'] = $this->app->share(function($app)
		{
			$model = $app['config']['cartalyst/sentinel::users.model'];

			$groups = $app['config']['cartalyst/sentinel::groups.model'];
			if (class_exists($groups) && method_exists($groups, 'setUsersModel'))
			{
				forward_static_call_array([$groups, 'setUsersModel'], [$model]);
			}

			return new IlluminateUserRepository($app['sentinel.hasher'], $model, $app['events']);
		});
	}

	protected function registerHasher()
	{
		$this->app['sentinel.hasher'] = $this->app->share(function($app)
		{
			return new NativeHasher;
		});
	}

	protected function registerGroups()
	{
		$this->app['sentinel.groups'] = $this->app->share(function($app)
		{
			$model = $app['config']['cartalyst/sentinel::groups.model'];

			$users = $app['config']['cartalyst/sentinel::users.model'];
			if (class_exists($users) && method_exists($users, 'setGroupsModel'))
			{
				forward_static_call_array([$users, 'setGroupsModel'], [$model]);
			}

			return new IlluminateGroupRepository($model);
		});
	}

	protected function registerCheckpoints()
	{
		$this->registerActivationCheckpoint();
		$this->registerThrottleCheckpoint();

		$this->app['sentinel.checkpoints'] = $this->app->share(function($app)
		{
			$checkpoints = $app['config']['cartalyst/sentinel::checkpoints'];

			$checkpoints = array_map(function($checkpoint) use ($app)
			{
				if ( ! $app->offsetExists("sentinel.checkpoint.{$checkpoint}"))
				{
					throw new \InvalidArgumentException("Invalid checkpoint [$checkpoint] given.");
				}

				return $app["sentinel.checkpoint.{$checkpoint}"];
			}, $checkpoints);

			return $checkpoints;
		});
	}

	protected function registerActivationCheckpoint()
	{
		$this->registerActivations();

		$this->app['sentinel.checkpoint.activation'] = $this->app->share(function($app)
		{
			return new ActivationCheckpoint($app['sentinel.activations']);
		});
	}

	protected function registerActivations()
	{
		$this->app['sentinel.activations'] = $this->app->share(function($app)
		{
			$model = $app['config']['cartalyst/sentinel::activations.model'];
			$expires = $app['config']['cartalyst/sentinel::activations.expires'];

			return new IlluminateActivationRepository($model, $expires);
		});
	}

	protected function registerThrottleCheckpoint()
	{
		$this->registerThrottling();

		$this->app['sentinel.checkpoint.throttle'] = $this->app->share(function($app)
		{
			return new ThrottleCheckpoint(
				$app['sentinel.throttling'],
				$app['request']->getClientIp()
			);
		});
	}

	protected function registerThrottling()
	{
		$this->app['sentinel.throttling'] = $this->app->share(function($app)
		{
			$model = $app['config']['cartalyst/sentinel::throttling.model'];

			foreach (['global', 'ip', 'user'] as $type)
			{
				${"{$type}Interval"} = $app['config']["cartalyst/sentinel::throttling.{$type}.interval"];
				${"{$type}Thresholds"} = $app['config']["cartalyst/sentinel::throttling.{$type}.thresholds"];
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
		});
	}

	protected function registerReminders()
	{
		$this->app['sentinel.reminders'] = $this->app->share(function($app)
		{
			$model = $app['config']['cartalyst/sentinel::reminders.model'];
			$expires = $app['config']['cartalyst/sentinel::reminders.expires'];

			return new IlluminateReminderRepository($app['sentinel.users'], $model, $expires);
		});
	}

	protected function registerSentinel()
	{
		$this->app['sentinel'] = $this->app->share(function($app)
		{
			$sentinel = new Sentinel(
				$app['sentinel.persistence'],
				$app['sentinel.users'],
				$app['sentinel.groups'],
				$app['sentinel.activations'],
				$app['events']
			);

			if (isset($app['sentinel.checkpoints']))
			{
				foreach ($app['sentinel.checkpoints'] as $checkpoint)
				{
					$sentinel->addCheckpoint($checkpoint);
				}
			}

			$sentinel->setActivationsRepository($app['sentinel.activations']);
			$sentinel->setRemindersRepository($app['sentinel.reminders']);

			$sentinel->setRequestCredentials(function() use ($app)
			{
				$request = $app['request'];

				$login = $request->getUser();
				$password = $request->getPassword();

				if ($login === null && $password === null)
				{
					return;
				}

				return compact('login', 'password');
			});

			$sentinel->creatingBasicResponse(function()
			{
				$headers = ['WWW-Authenticate' => 'Basic'];

				return new Response('Invalid credentials.', 401, $headers);
			});

			return $sentinel;
		});
	}

	/**
	 * {@inheritDoc}
	 */
	public function provides()
	{
		return [
			'sentinel.session',
			'sentinel.cookie',
			'sentinel.persistence',
			'sentinel.hasher',
			'sentinel.users',
			'sentinel.groups',
			'sentinel.activations',
			'sentinel.checkpoint.activation',
			'sentinel.throttling',
			'sentinel.checkpoint.throttle',
			'sentinel.checkpoints',
			'sentinel.reminders',
			'sentinel',
		];
	}

}
