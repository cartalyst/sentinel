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
 * @version    2.0.12
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Native;

use Cartalyst\Sentinel\Activations\IlluminateActivationRepository;
use Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint;
use Cartalyst\Sentinel\Cookies\NativeCookie;
use Cartalyst\Sentinel\Hashing\NativeHasher;
use Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository;
use Cartalyst\Sentinel\Reminders\IlluminateReminderRepository;
use Cartalyst\Sentinel\Roles\IlluminateRoleRepository;
use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Sessions\NativeSession;
use Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository;
use Cartalyst\Sentinel\Users\IlluminateUserRepository;
use Illuminate\Events\Dispatcher;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

class SentinelBootstrapper
{
    /**
     * Configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * The event dispatcher.
     *
     * @var \Illuminate\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * Constructor.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct($config = null)
    {
        if (is_string($config)) {
            $this->config = new ConfigRepository($config);
        } else {
            $this->config = $config ?: new ConfigRepository;
        }
    }

    /**
     * Creates a sentinel instance.
     *
     * @return \Cartalyst\Sentinel\Sentinel
     */
    public function createSentinel()
    {
        $persistence = $this->createPersistence();
        $users       = $this->createUsers();
        $roles       = $this->createRoles();
        $activations = $this->createActivations();
        $dispatcher  = $this->getEventDispatcher();

        $sentinel = new Sentinel(
            $persistence,
            $users,
            $roles,
            $activations,
            $dispatcher
        );

        $ipAddress = $this->getIpAddress();

        $checkpoints = $this->createCheckpoints($activations, $ipAddress);

        foreach ($checkpoints as $key => $checkpoint) {
            $sentinel->addCheckpoint($key, $checkpoint);
        }

        $reminders = $this->createReminders($users);

        $sentinel->setActivationRepository($activations);

        $sentinel->setReminderRepository($reminders);

        return $sentinel;
    }

    /**
     * Creates a persistences repository.
     *
     * @return \Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository
     */
    protected function createPersistence()
    {
        $session = $this->createSession();

        $cookie = $this->createCookie();

        return new IlluminatePersistenceRepository($session, $cookie);
    }

    /**
     * Creates a session.
     *
     * @return \Cartalyst\Sentinel\Sessions\NativeSession
     */
    protected function createSession()
    {
        return new NativeSession($this->config['session']);
    }

    /**
     * Creates a cookie.
     *
     * @return \Cartalyst\Sentinel\Cookies\NativeCookie
     */
    protected function createCookie()
    {
        return new NativeCookie($this->config['cookie']);
    }

    /**
     * Creates a user repository.
     *
     * @return \Cartalyst\Sentinel\Users\IlluminateUserRepository
     */
    protected function createUsers()
    {
        $hasher = $this->createHasher();

        $model = $this->config['users']['model'];

        $roles = $this->config['roles']['model'];

        $persistences = $this->config['persistences']['model'];

        if (class_exists($roles) && method_exists($roles, 'setUsersModel')) {
            forward_static_call_array([$roles, 'setUsersModel'], [$model]);
        }

        if (class_exists($persistences) && method_exists($persistences, 'setUsersModel')) {
            forward_static_call_array([$persistences, 'setUsersModel'], [$model]);
        }

        return new IlluminateUserRepository($hasher, $this->getEventDispatcher(), $model);
    }

    /**
     * Creates a hasher.
     *
     * @return \Cartalyst\Sentinel\Hashing\NativeHasher
     */
    protected function createHasher()
    {
        return new NativeHasher;
    }

    /**
     * Creates a role repository.
     *
     * @return \Cartalyst\Sentinel\Roles\IlluminateRoleRepository
     */
    protected function createRoles()
    {
        $model = $this->config['roles']['model'];

        $users = $this->config['users']['model'];

        if (class_exists($users) && method_exists($users, 'setRolesModel')) {
            forward_static_call_array([$users, 'setRolesModel'], [$model]);
        }

        return new IlluminateRoleRepository($model);
    }

    /**
     * Creates an activation repository.
     *
     * @return \Cartalyst\Sentinel\Activations\IlluminateActivationRepository
     */
    protected function createActivations()
    {
        $model = $this->config['activations']['model'];

        $expires = $this->config['activations']['expires'];

        return new IlluminateActivationRepository($model, $expires);
    }

    /**
     * Returns the client's ip address.
     *
     * @return string
     */
    protected function getIpAddress()
    {
        $request = Request::createFromGlobals();

        return $request->getClientIp();
    }

    /**
     * Create an activation checkpoint.
     *
     * @param  \Cartalyst\Sentinel\Activations\IlluminateActivationRepository  $activations
     * @return \Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint
     */
    protected function createActivationCheckpoint(IlluminateActivationRepository $activations)
    {
        return new ActivationCheckpoint($activations);
    }

    /**
     * Create activation and throttling checkpoints.
     *
     * @param  \Cartalyst\Sentinel\Activations\IlluminateActivationRepository  $activations
     * @param  string  $ipAddress
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function createCheckpoints(IlluminateActivationRepository $activations, $ipAddress)
    {
        $activeCheckpoints = $this->config['checkpoints'];

        $activation = $this->createActivationCheckpoint($activations);

        $throttle = $this->createThrottleCheckpoint($ipAddress);

        $checkpoints = [];

        foreach ($activeCheckpoints as $checkpoint) {
            if (! isset($$checkpoint)) {
                throw new InvalidArgumentException("Invalid checkpoint [{$checkpoint}] given.");
            }

            $checkpoints[$checkpoint] = $$checkpoint;
        }

        return $checkpoints;
    }

    /**
     * Create a throttle checkpoint.
     *
     * @param  string  $ipAddress
     * @return \Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint
     */
    protected function createThrottleCheckpoint($ipAddress)
    {
        $throttling = $this->createThrottling();

        return new ThrottleCheckpoint($throttling, $ipAddress);
    }

    /**
     * Create a throttling repository.
     *
     * @return \Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository
     */
    protected function createThrottling()
    {
        $model = $this->config['throttling']['model'];

        foreach (['global', 'ip', 'user'] as $type) {
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

    /**
     * Returns the event dispatcher.
     *
     * @return \Illuminate\Events\Dispatcher
     */
    protected function getEventDispatcher()
    {
        if (! $this->dispatcher) {
            $this->dispatcher = new Dispatcher;
        }

        return $this->dispatcher;
    }

    /**
     * Create a reminder repository.
     *
     * @param  \Cartalyst\Sentinel\Users\IlluminateUserRepository  $users
     * @return \Cartalyst\Sentinel\Reminders\IlluminateReminderRepository
     */
    protected function createReminders(IlluminateUserRepository $users)
    {
        $model = $this->config['reminders']['model'];

        $expires = $this->config['reminders']['expires'];

        return new IlluminateReminderRepository($users, $model, $expires);
    }
}
