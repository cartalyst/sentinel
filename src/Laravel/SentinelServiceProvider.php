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
use Cartalyst\Sentinel\Hashing\NativeHasher;
use Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository;
use Cartalyst\Sentinel\Reminders\IlluminateReminderRepository;
use Cartalyst\Sentinel\Roles\IlluminateRoleRepository;
use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Sessions\IlluminateSession;
use Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository;
use Cartalyst\Sentinel\Users\IlluminateUserRepository;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class SentinelServiceProvider extends ServiceProvider {

	/**
	 * {@inheritDoc}
	 */
	public function boot()
	{
		$this->package('cartalyst/sentinel', 'cartalyst/sentinel', __DIR__.'/..');

		$this->garbageCollect();
	}

	/**
	 * {@inheritDoc}
	 */
	public function register()
	{
		$this->registerPersistences();
		$this->registerUsers();
		$this->registerRoles();
		$this->registerCheckpoints();
		$this->registerReminders();
		$this->registerSentinel();
	}

	/**
	 * Registers the persistences.
	 *
	 * @return void
	 */
	protected function registerPersistences()
	{
		$this->registerSession();
		$this->registerCookie();

		$this->app['sentinel.persistence'] = $this->app->share(function($app)
		{
			$model = $app['config']['cartalyst/sentinel::persistences.model'];
			$single = $app['config']['cartalyst/sentinel::persistences.single'];
			$users = $app['config']['cartalyst/sentinel::users.model'];

			if (class_exists($users) && method_exists($users, 'setPersistencesModel'))
			{
				forward_static_call_array([$users, 'setPersistencesModel'], [$model]);
			}

			return new IlluminatePersistenceRepository($app['sentinel.session'], $app['sentinel.cookie'], $model, $single);
		});
	}

	/**
	 * Registers the session.
	 *
	 * @return void
	 */
	protected function registerSession()
	{
		$this->app['sentinel.session'] = $this->app->share(function($app)
		{
			$key = $app['config']['cartalyst/sentinel::session'];

			return new IlluminateSession($app['session.store'], $key);
		});
	}

	/**
	 * Registers the cookie.
	 *
	 * @return void
	 */
	protected function registerCookie()
	{
		$this->app['sentinel.cookie'] = $this->app->share(function($app)
		{
			$key = $app['config']['cartalyst/sentinel::cookie'];

			return new IlluminateCookie($app['request'], $app['cookie'], $key);
		});
	}

	/**
	 * Registers the users.
	 *
	 * @return void
	 */
	protected function registerUsers()
	{
		$this->registerHasher();

		$this->app['sentinel.users'] = $this->app->share(function($app)
		{
			$users = $app['config']['cartalyst/sentinel::users.model'];
			$roles = $app['config']['cartalyst/sentinel::roles.model'];
			$persistences = $app['config']['cartalyst/sentinel::persistences.model'];
			$permissions = $app['config']['cartalyst/sentinel::permissions.class'];

			if (class_exists($roles) && method_exists($roles, 'setUsersModel'))
			{
				forward_static_call_array([$roles, 'setUsersModel'], [$users]);
			}

			if (class_exists($persistences) && method_exists($persistences, 'setUsersModel'))
			{
				forward_static_call_array([$persistences, 'setUsersModel'], [$users]);
			}

			if (class_exists($users) && method_exists($users, 'setPermissionsClass'))
			{
				forward_static_call_array([$users, 'setPermissionsClass'], [$permissions]);
			}

			return new IlluminateUserRepository($app['sentinel.hasher'], $app['events'], $users);
		});
	}

	/**
	 * Registers the hahser.
	 *
	 * @return void
	 */
	protected function registerHasher()
	{
		$this->app['sentinel.hasher'] = $this->app->share(function($app)
		{
			return new NativeHasher;
		});
	}

	/**
	 * Registers the roles.
	 *
	 * @return void
	 */
	protected function registerRoles()
	{
		$this->app['sentinel.roles'] = $this->app->share(function($app)
		{
			$model = $app['config']['cartalyst/sentinel::roles.model'];
			$users = $app['config']['cartalyst/sentinel::users.model'];

			if (class_exists($users) && method_exists($users, 'setRolesModel'))
			{
				forward_static_call_array([$users, 'setRolesModel'], [$model]);
			}

			return new IlluminateRoleRepository($model);
		});
	}

	/**
	 * Registers the checkpoints.
	 *
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	protected function registerCheckpoints()
	{
		$this->registerActivationCheckpoint();
		$this->registerThrottleCheckpoint();

		$this->app['sentinel.checkpoints'] = $this->app->share(function($app)
		{
			$activeCheckpoints = $app['config']['cartalyst/sentinel::checkpoints'];

			$checkpoints = [];

			foreach ($activeCheckpoints as $checkpoint)
			{
				if ( ! $app->offsetExists("sentinel.checkpoint.{$checkpoint}"))
				{
					throw new InvalidArgumentException("Invalid checkpoint [$checkpoint] given.");
				}

				$checkpoints[$checkpoint] = $app["sentinel.checkpoint.{$checkpoint}"];
			}

			return $checkpoints;
		});
	}

	/**
	 * Registers the activation checkpoint.
	 *
	 * @return void
	 */
	protected function registerActivationCheckpoint()
	{
		$this->registerActivations();

		$this->app['sentinel.checkpoint.activation'] = $this->app->share(function($app)
		{
			return new ActivationCheckpoint($app['sentinel.activations']);
		});
	}

	/**
	 * Registers the activations.
	 *
	 * @return void
	 */
	protected function registerActivations()
	{
		$this->app['sentinel.activations'] = $this->app->share(function($app)
		{
			$model = $app['config']['cartalyst/sentinel::activations.model'];
			$expires = $app['config']['cartalyst/sentinel::activations.expires'];

			return new IlluminateActivationRepository($model, $expires);
		});
	}

	/**
	 * Registers the throttle checkpoint.
	 *
	 * @return void
	 */
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

	/**
	 * Registers the throttle.
	 *
	 * @return void
	 */
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

	/**
	 * Registers the reminders.
	 *
	 * @return void
	 */
	protected function registerReminders()
	{
		$this->app['sentinel.reminders'] = $this->app->share(function($app)
		{
			$model = $app['config']['cartalyst/sentinel::reminders.model'];
			$expires = $app['config']['cartalyst/sentinel::reminders.expires'];

			return new IlluminateReminderRepository($app['sentinel.users'], $model, $expires);
		});
	}

	/**
	 * Registers sentinel.
	 *
	 * @return void
	 */
	protected function registerSentinel()
	{
		$this->app['sentinel'] = $this->app->share(function($app)
		{
			$sentinel = new Sentinel(
				$app['sentinel.persistence'],
				$app['sentinel.users'],
				$app['sentinel.roles'],
				$app['sentinel.activations'],
				$app['events']
			);

			if (isset($app['sentinel.checkpoints']))
			{
				foreach ($app['sentinel.checkpoints'] as $key => $checkpoint)
				{
					$sentinel->addCheckpoint($key, $checkpoint);
				}
			}

			$sentinel->setActivationRepository($app['sentinel.activations']);
			$sentinel->setReminderRepository($app['sentinel.reminders']);

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
			'sentinel.roles',
			'sentinel.activations',
			'sentinel.checkpoint.activation',
			'sentinel.throttling',
			'sentinel.checkpoint.throttle',
			'sentinel.checkpoints',
			'sentinel.reminders',
			'sentinel',
		];
	}

	/**
	 * Garbage collect activations and reminders.
	 *
	 * @return void
	 */
	protected function garbageCollect()
	{
		$config = $this->app['config']['cartalyst/sentinel::activations.lottery'];

		$this->sweep($this->app['sentinel.activations'], $config);

		$config = $this->app['config']['cartalyst/sentinel::reminders.lottery'];

		$this->sweep($this->app['sentinel.reminders'], $config);
	}

	/**
	 * Sweep expired codes.
	 *
	 * @param  mixed  $repository
	 * @param  array  $lottery
	 * @return void
	 */
	protected function sweep($repository, $lottery)
	{
		if ($this->configHitsLottery($lottery))
		{
			$repository->removeExpired();
		}
	}

	/**
	 * Determine if the configuration odds hit the lottery.
	 *
	 * @param  array  $lottery
	 * @return bool
	 */
	protected function configHitsLottery(array $lottery)
	{
		return mt_rand(1, $lottery[1]) <= $lottery[0];
	}

}
