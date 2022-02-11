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

namespace Cartalyst\Sentinel\Checkpoints;

use Cartalyst\Sentinel\Users\UserInterface;
use Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface;

class ThrottleCheckpoint implements CheckpointInterface
{
    /**
     * The Throttle repository instance.
     *
     * @var \Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface
     */
    protected $throttle;

    /**
     * The cached IP address, used for checkpoints checks.
     *
     * @var string
     */
    protected $ipAddress;

    /**
     * Constructor.
     *
     * @param \Cartalyst\Sentinel\Throttling\ThrottleRepositoryInterface $throttle
     * @param string                                                     $ipAddress
     *
     * @return void
     */
    public function __construct(ThrottleRepositoryInterface $throttle, $ipAddress = null)
    {
        $this->throttle = $throttle;

        if (isset($ipAddress)) {
            $this->ipAddress = $ipAddress;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function login(UserInterface $user): bool
    {
        return $this->checkThrottling('login', $user);
    }

    /**
     * {@inheritdoc}
     */
    public function check(UserInterface $user): bool
    {
        return $this->checkThrottling('check', $user);
    }

    /**
     * {@inheritdoc}
     */
    public function fail(UserInterface $user = null): bool
    {
        // We'll check throttling firstly from any previous attempts. This
        // will throw the required exceptions if the user has already
        // tried to login too many times.
        $this->checkThrottling('login', $user);

        // Now we've checked previous attempts, we'll log this latest attempt.
        // It'll be picked up the next time if the user tries again.
        $this->throttle->log($this->ipAddress, $user);

        return true;
    }

    /**
     * Checks the throttling status of the given user.
     *
     * @param string                                       $action
     * @param \Cartalyst\Sentinel\Users\UserInterface|null $user
     *
     * @return bool
     */
    protected function checkThrottling(string $action, UserInterface $user = null): bool
    {
        // If we are just checking an existing logged in person, the global delay
        // shouldn't stop them being logged in at all. Only their IP address and
        // user a
        if ($action === 'login') {
            $globalDelay = $this->throttle->globalDelay();

            if ($globalDelay > 0) {
                $this->throwException("Too many unsuccessful attempts have been made globally, logins are locked for another [{$globalDelay}] second(s).", 'global', $globalDelay);
            }
        }

        // Suspicious activity from a single IP address will not only lock
        // logins but also any logged in users from that IP address. This
        // should deter a single hacker who may have guessed a password
        // within the configured throttling limit.
        if (isset($this->ipAddress)) {
            $ipDelay = $this->throttle->ipDelay($this->ipAddress);

            if ($ipDelay > 0) {
                $this->throwException("Suspicious activity has occured on your IP address and you have been denied access for another [{$ipDelay}] second(s).", 'ip', $ipDelay);
            }
        }

        // We will only suspend people logging into a user account. This will
        // leave the logged in user unaffected. Picture a famous person who's
        // account is being locked as they're logged in, purely because
        // others are trying to hack it.
        if ($action === 'login' && isset($user)) {
            $userDelay = $this->throttle->userDelay($user);

            if ($userDelay > 0) {
                $this->throwException("Too many unsuccessful login attempts have been made against your account. Please try again after another [{$userDelay}] second(s).", 'user', $userDelay);
            }
        }

        return true;
    }

    /**
     * Throws a throttling exception.
     *
     * @param string $message
     * @param string $type
     * @param int    $delay
     *
     * @throws \Cartalyst\Sentinel\Checkpoints\ThrottlingException
     *
     * @return void
     */
    protected function throwException(string $message, string $type, int $delay): void
    {
        $exception = new ThrottlingException($message);

        $exception->setDelay($delay);

        $exception->setType($type);

        throw $exception;
    }
}
