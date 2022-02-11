<?php

/*
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
 * @version    6.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2022, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Sentinel\Laravel;

use Exception;
use InvalidArgumentException;
use Cartalyst\Sentinel\Sentinel;
use Illuminate\Support\ServiceProvider;
use Cartalyst\Sentinel\Hashing\NativeHasher;
use Symfony\Component\HttpFoundation\Response;
use Cartalyst\Sentinel\Cookies\IlluminateCookie;
use Cartalyst\Sentinel\Sessions\IlluminateSession;
use Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint;
use Cartalyst\Sentinel\Roles\IlluminateRoleRepository;
use Cartalyst\Sentinel\Users\IlluminateUserRepository;
use Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use Cartalyst\Sentinel\Reminders\IlluminateReminderRepository;
use Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository;
use Cartalyst\Sentinel\Activations\IlluminateActivationRepository;
use Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository;

class SentinelServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->setOverrides();
        $this->garbageCollect();
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->prepareResources();
        $this->registerPersistences();
        $this->registerUsers();
        $this->registerRoles();
        $this->registerCheckpoints();
        $this->registerReminders();
        $this->registerSentinel();
        $this->setUserResolver();
    }

    /**
     * Prepare the package resources.
     *
     * @return void
     */
    protected function prepareResources()
    {
        // Publish config
        $config = realpath(__DIR__.'/../config/config.php');

        $this->mergeConfigFrom($config, 'cartalyst.sentinel');

        $this->publishes([
            $config => config_path('cartalyst.sentinel.php'),
        ], 'config');

        // Publish migrations
        $migrations = realpath(__DIR__.'/../migrations');

        $this->publishes([
            $migrations => $this->app->databasePath().'/migrations',
        ], 'migrations');
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

        $this->app->singleton('sentinel.persistence', function ($app) {
            $config = $app['config']->get('cartalyst.sentinel.persistences');

            return new IlluminatePersistenceRepository(
                $app['sentinel.session'],
                $app['sentinel.cookie'],
                $config['model'],
                $config['single']
            );
        });
    }

    /**
     * Registers the session.
     *
     * @return void
     */
    protected function registerSession()
    {
        $this->app->singleton('sentinel.session', function ($app) {
            return new IlluminateSession(
                $app['session.store'],
                $app['config']->get('cartalyst.sentinel.session')
            );
        });
    }

    /**
     * Registers the cookie.
     *
     * @return void
     */
    protected function registerCookie()
    {
        $this->app->singleton('sentinel.cookie', function ($app) {
            return new IlluminateCookie(
                $app['request'],
                $app['cookie'],
                $app['config']->get('cartalyst.sentinel.cookie')
            );
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

        $this->app->singleton('sentinel.users', function ($app) {
            $config = $app['config']->get('cartalyst.sentinel.users');

            return new IlluminateUserRepository(
                $app['sentinel.hasher'],
                $app['events'],
                $config['model']
            );
        });
    }

    /**
     * Registers the hahser.
     *
     * @return void
     */
    protected function registerHasher()
    {
        $this->app->singleton('sentinel.hasher', function () {
            return new NativeHasher();
        });
    }

    /**
     * Registers the roles.
     *
     * @return void
     */
    protected function registerRoles()
    {
        $this->app->singleton('sentinel.roles', function ($app) {
            $config = $app['config']->get('cartalyst.sentinel.roles');

            return new IlluminateRoleRepository($config['model']);
        });
    }

    /**
     * Registers the checkpoints.
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    protected function registerCheckpoints()
    {
        $this->registerActivationCheckpoint();

        $this->registerThrottleCheckpoint();

        $this->app->singleton('sentinel.checkpoints', function ($app) {
            $activeCheckpoints = $app['config']->get('cartalyst.sentinel.checkpoints');

            $checkpoints = [];

            foreach ($activeCheckpoints as $checkpoint) {
                if (! $app->offsetExists("sentinel.checkpoint.{$checkpoint}")) {
                    throw new InvalidArgumentException("Invalid checkpoint [${checkpoint}] given.");
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

        $this->app->singleton('sentinel.checkpoint.activation', function ($app) {
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
        $this->app->singleton('sentinel.activations', function ($app) {
            $config = $app['config']->get('cartalyst.sentinel.activations');

            return new IlluminateActivationRepository($config['model'], $config['expires']);
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

        $this->app->singleton('sentinel.checkpoint.throttle', function ($app) {
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
        $this->app->singleton('sentinel.throttling', function ($app) {
            $model = $app['config']->get('cartalyst.sentinel.throttling.model');

            $throttling = $app['config']->get('cartalyst.sentinel.throttling');

            foreach (['global', 'ip', 'user'] as $type) {
                ${"{$type}Interval"} = $throttling[$type]['interval'];
                ${"{$type}Thresholds"} = $throttling[$type]['thresholds'];
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
        $this->app->singleton('sentinel.reminders', function ($app) {
            $config = $app['config']->get('cartalyst.sentinel.reminders');

            return new IlluminateReminderRepository(
                $app['sentinel.users'],
                $config['model'],
                $config['expires']
            );
        });
    }

    /**
     * Registers sentinel.
     *
     * @return void
     */
    protected function registerSentinel()
    {
        $this->app->singleton('sentinel', function ($app) {
            $sentinel = new Sentinel(
                $app['sentinel.persistence'],
                $app['sentinel.users'],
                $app['sentinel.roles'],
                $app['sentinel.activations'],
                $app['events']
            );

            if (isset($app['sentinel.checkpoints'])) {
                foreach ($app['sentinel.checkpoints'] as $key => $checkpoint) {
                    $sentinel->addCheckpoint($key, $checkpoint);
                }
            }

            $sentinel->setActivationRepository($app['sentinel.activations']);
            $sentinel->setReminderRepository($app['sentinel.reminders']);
            $sentinel->setThrottleRepository($app['sentinel.throttling']);

            $sentinel->setRequestCredentials(function () use ($app) {
                $request = $app['request'];

                $login = $request->getUser();
                $password = $request->getPassword();

                if ($login === null && $password === null) {
                    return;
                }

                return compact('login', 'password');
            });

            $sentinel->creatingBasicResponse(function () {
                $headers = ['WWW-Authenticate' => 'Basic'];

                return new Response('Invalid credentials.', 401, $headers);
            });

            return $sentinel;
        });

        $this->app->alias('sentinel', 'Cartalyst\Sentinel\Sentinel');
    }

    /**
     * {@inheritdoc}
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
        $config = $this->app['config']->get('cartalyst.sentinel');

        $this->sweep(
            $this->app['sentinel.activations'],
            $config['activations']['lottery']
        );

        $this->sweep(
            $this->app['sentinel.reminders'],
            $config['reminders']['lottery']
        );
    }

    /**
     * Sweep expired codes.
     *
     * @param mixed $repository
     * @param array $lottery
     *
     * @return void
     */
    protected function sweep($repository, array $lottery)
    {
        if ($this->configHitsLottery($lottery)) {
            try {
                $repository->removeExpired();
            } catch (Exception $e) {
            }
        }
    }

    /**
     * Determine if the configuration odds hit the lottery.
     *
     * @param array $lottery
     *
     * @return bool
     */
    protected function configHitsLottery(array $lottery)
    {
        return mt_rand(1, $lottery[1]) <= $lottery[0];
    }

    /**
     * Sets the user resolver on the request class.
     *
     * @return void
     */
    protected function setUserResolver()
    {
        $this->app->rebinding('request', function ($app, $request) {
            $request->setUserResolver(function () use ($app) {
                return $app['sentinel']->getUser();
            });
        });
    }

    /**
     * Performs the necessary overrides.
     *
     * @return void
     */
    protected function setOverrides()
    {
        $config = $this->app['config']->get('cartalyst.sentinel');

        $users = $config['users']['model'];

        $roles = $config['roles']['model'];

        $persistences = $config['persistences']['model'];

        if (class_exists($users)) {
            if (method_exists($users, 'setRolesModel')) {
                forward_static_call_array([$users, 'setRolesModel'], [$roles]);
            }

            if (method_exists($users, 'setPersistencesModel')) {
                forward_static_call_array([$users, 'setPersistencesModel'], [$persistences]);
            }

            if (method_exists($users, 'setPermissionsClass')) {
                forward_static_call_array([$users, 'setPermissionsClass'], [$config['permissions']['class']]);
            }
        }

        if (class_exists($roles) && method_exists($roles, 'setUsersModel')) {
            forward_static_call_array([$roles, 'setUsersModel'], [$users]);
        }

        if (class_exists($persistences) && method_exists($persistences, 'setUsersModel')) {
            forward_static_call_array([$persistences, 'setUsersModel'], [$users]);
        }
    }
}
