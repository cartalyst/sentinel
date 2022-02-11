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

namespace Cartalyst\Sentinel\Throttling;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Cartalyst\Sentinel\Users\UserInterface;
use Cartalyst\Support\Traits\RepositoryTrait;

class IlluminateThrottleRepository implements ThrottleRepositoryInterface
{
    use RepositoryTrait;

    /**
     * The interval which failed logins are checked, to prevent brute force.
     *
     * @var int
     */
    protected $globalInterval = 900;

    /**
     * The global thresholds configuration array.
     *
     * If an array is set, the key is the number of failed login attempts
     * and the value is the delay in seconds before another login can
     * occur.
     *
     * If an integer is set, it represents the number of attempts
     * before throttling locks out in the current interval.
     *
     * @var array|int
     */
    protected $globalThresholds = [
        10 => 1,
        20 => 2,
        30 => 4,
        40 => 8,
        50 => 16,
        60 => 32,
    ];

    /**
     * Cached global throttles collection within the interval.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $globalThrottles;

    /**
     * The interval at which point one IP address' failed logins are checked.
     *
     * @var int
     */
    protected $ipInterval = 900;

    /**
     * Works identical to global thresholds, except specific to an IP address.
     *
     * @var array|int
     */
    protected $ipThresholds = 5;

    /**
     * The cached IP address throttle collections within the interval.
     *
     * @var array
     */
    protected $ipThrottles = [];

    /**
     * The interval at which point failed logins for one user are checked.
     *
     * @var int
     */
    protected $userInterval = 900;

    /**
     * Works identical to global and IP address thresholds, regarding a user.
     *
     * @var array|int
     */
    protected $userThresholds = 5;

    /**
     * The cached user throttle collections within the interval.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $userThrottles = [];

    /**
     * Create a new Illuminate throttle repository.
     *
     * @param string    $model
     * @param int       $globalInterval
     * @param array|int $globalThresholds
     * @param int       $ipInterval
     * @param array|int $ipThresholds
     * @param int       $userInterval
     * @param array|int $userThresholds
     *
     * @return void
     */
    public function __construct(
        $model = 'Cartalyst\Sentinel\Throttling\EloquentThrottle',
        $globalInterval = null,
        $globalThresholds = null,
        $ipInterval = null,
        $ipThresholds = null,
        $userInterval = null,
        $userThresholds = null
    ) {
        $this->model = $model;

        if (isset($globalInterval)) {
            $this->setGlobalInterval($globalInterval);
        }

        if (isset($globalThresholds)) {
            $this->setGlobalThresholds($globalThresholds);
        }

        if (isset($ipInterval)) {
            $this->setIpInterval($ipInterval);
        }

        if (isset($ipThresholds)) {
            $this->setIpThresholds($ipThresholds);
        }

        if (isset($userInterval)) {
            $this->setUserInterval($userInterval);
        }

        if (isset($userThresholds)) {
            $this->setUserThresholds($userThresholds);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function globalDelay()
    {
        return $this->delay('global');
    }

    /**
     * {@inheritdoc}
     */
    public function ipDelay($ipAddress)
    {
        return $this->delay('ip', $ipAddress);
    }

    /**
     * {@inheritdoc}
     */
    public function userDelay(UserInterface $user)
    {
        return $this->delay('user', $user);
    }

    /**
     * {@inheritdoc}
     */
    public function log($ipAddress = null, UserInterface $user = null)
    {
        $global = $this->createModel();
        $global->fill([
            'type' => 'global',
        ]);
        $global->save();

        // Reset global throttles cache
        $this->globalThrottles = null;

        if ($ipAddress !== null) {
            $ipAddressThrottle = $this->createModel();
            $ipAddressThrottle->fill([
                'type' => 'ip',
                'ip'   => $ipAddress,
            ]);
            $ipAddressThrottle->save();
        }

        if ($user !== null) {
            $userThrottle = $this->createModel();
            $userThrottle->fill([
                'type' => 'user',
            ]);
            $userThrottle->user_id = $user->getUserId();
            $userThrottle->save();
        }
    }

    /**
     * Returns the global interval.
     *
     * @return int
     */
    public function getGlobalInterval()
    {
        return $this->globalInterval;
    }

    /**
     * Sets the global interval.
     *
     * @param int $globalInterval
     *
     * @return void
     */
    public function setGlobalInterval($globalInterval)
    {
        $this->globalInterval = (int) $globalInterval;
    }

    /**
     * Returns the global thresholds.
     *
     * @return array|int
     */
    public function getGlobalThresholds()
    {
        return $this->globalThresholds;
    }

    /**
     * Sets the global thresholds.
     *
     * @param array|int $globalThresholds
     *
     * @return void
     */
    public function setGlobalThresholds($globalThresholds)
    {
        $this->globalThresholds = is_array($globalThresholds) ? $globalThresholds : (int) $globalThresholds;
    }

    /**
     * Returns the IP address interval.
     *
     * @return int
     */
    public function getIpInterval()
    {
        return $this->ipInterval;
    }

    /**
     * Sets the IP address interval.
     *
     * @param int $ipInterval
     *
     * @return void
     */
    public function setIpInterval($ipInterval)
    {
        $this->ipInterval = (int) $ipInterval;
    }

    /**
     * Returns the IP address thresholds.
     *
     * @return array|int
     */
    public function getIpThresholds()
    {
        return $this->ipThresholds;
    }

    /**
     * Sets the IP address thresholds.
     *
     * @param array|int $ipThresholds
     *
     * @return void
     */
    public function setIpThresholds($ipThresholds)
    {
        $this->ipThresholds = is_array($ipThresholds) ? $ipThresholds : (int) $ipThresholds;
    }

    /**
     * Returns the user interval.
     *
     * @return int
     */
    public function getUserInterval()
    {
        return $this->userInterval;
    }

    /**
     * Sets the user interval.
     *
     * @param int $userInterval
     *
     * @return void
     */
    public function setUserInterval($userInterval)
    {
        $this->userInterval = (int) $userInterval;
    }

    /**
     * Returns the user thresholds.
     *
     * @return array|int
     */
    public function getUserThresholds()
    {
        return $this->userThresholds;
    }

    /**
     * Sets the user thresholds.
     *
     * @param array|int $userThresholds
     *
     * @return void
     */
    public function setUserThresholds($userThresholds)
    {
        $this->userThresholds = is_array($userThresholds) ? $userThresholds : (int) $userThresholds;
    }

    /**
     * Returns a delay for the given type.
     *
     * @param string $type
     * @param mixed  $argument
     *
     * @return int
     */
    protected function delay($type, $argument = null)
    {
        // Based on the given type, we will generate method and property names
        $typeStudly = Str::studly($type);

        $method = 'get'.$typeStudly.'Throttles';

        $thresholds = $type.'Thresholds';

        $throttles = $this->{$method}($argument);

        if (! $throttles->count()) {
            return 0;
        }

        if (is_array($this->{$thresholds})) {
            // Great, now we compare our delay against the most recent attempt
            $last = $throttles->last();

            foreach (array_reverse($this->{$thresholds}, true) as $attempts => $delay) {
                if ($throttles->count() <= $attempts) {
                    continue;
                }

                if ($last->created_at->diffInSeconds() < $delay) {
                    return $this->secondsToFree($last, $delay);
                }
            }
        } elseif ($throttles->count() >= $this->{$thresholds}) {
            $interval = $type.'Interval';

            $first = $throttles->first();

            return $this->secondsToFree($first, $this->{$interval});
        }

        return 0;
    }

    /**
     * Returns the global throttles collection.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getGlobalThrottles()
    {
        if ($this->globalThrottles === null) {
            $this->globalThrottles = $this->loadGlobalThrottles();
        }

        return $this->globalThrottles;
    }

    /**
     * Loads and returns the global throttles collection.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function loadGlobalThrottles()
    {
        $interval = Carbon::now()
            ->subSeconds($this->globalInterval)
        ;

        return $this->createModel()
            ->newQuery()
            ->where('type', 'global')
            ->where('created_at', '>', $interval)
            ->get()
        ;
    }

    /**
     * Returns the IP address throttles collection.
     *
     * @param string $ipAddress
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getIpThrottles($ipAddress)
    {
        if (! array_key_exists($ipAddress, $this->ipThrottles)) {
            $this->ipThrottles[$ipAddress] = $this->loadIpThrottles($ipAddress);
        }

        return $this->ipThrottles[$ipAddress];
    }

    /**
     * Loads and returns the IP address throttles collection.
     *
     * @param string $ipAddress
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function loadIpThrottles($ipAddress)
    {
        $interval = Carbon::now()
            ->subSeconds($this->ipInterval)
        ;

        return $this
            ->createModel()
            ->newQuery()
            ->where('type', 'ip')
            ->where('ip', $ipAddress)
            ->where('created_at', '>', $interval)
            ->get()
        ;
    }

    /**
     * Returns the user throttles collection.
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface $user
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getUserThrottles(UserInterface $user)
    {
        $key = $user->getUserId();

        if (! array_key_exists($key, $this->userThrottles)) {
            $this->userThrottles[$key] = $this->loadUserThrottles($user);
        }

        return $this->userThrottles[$key];
    }

    /**
     * Loads and returns the user throttles collection.
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface $user
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function loadUserThrottles(UserInterface $user)
    {
        $interval = Carbon::now()
            ->subSeconds($this->userInterval)
        ;

        return $this
            ->createModel()
            ->newQuery()
            ->where('type', 'user')
            ->where('user_id', $user->getUserId())
            ->where('created_at', '>', $interval)
            ->get()
        ;
    }

    /**
     * Returns the seconds to free based on the given throttle and
     * the presented delay in seconds, by comparing it to now.
     *
     * @param \Cartalyst\Sentinel\Throttling\EloquentThrottle $throttle
     * @param int                                             $interval
     *
     * @return int
     */
    protected function secondsToFree(EloquentThrottle $throttle, $interval)
    {
        return $throttle->created_at->addSeconds($interval)->diffInSeconds();
    }
}
