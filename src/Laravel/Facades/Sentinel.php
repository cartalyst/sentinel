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

namespace Cartalyst\Sentinel\Laravel\Facades;

use Cartalyst\Sentinel\Users\UserInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static UserInterface|bool register(array $credentials, \Closure|bool $callback = null)
 * @method static UserInterface|bool registerAndActivate(array $credentials)
 * @method static bool activate(mixed $user)
 * @method static UserInterface|bool check()
 * @method static UserInterface|bool forceCheck()
 * @method static bool guest()
 * @method static UserInterface|bool authenticate(UserInterface|array $credentials, $remember = false, $login = true)
 * @method static UserInterface|bool authenticateAndRemember(UserInterface|array $credentials)
 * @method static UserInterface|bool forceAuthenticate(UserInterface|array $credentials, $remember = false)
 * @method static UserInterface|bool forceAuthenticateAndRemember(UserInterface|array $credentials)
 * @method static UserInterface|bool stateless(UserInterface|array $credentials)
 * @method static mixed basic()
 * @method static UserInterface|bool login(UserInterface $user, $remember = false)
 * @method static UserInterface|bool loginAndRemember(UserInterface $user)
 * @method static bool logout(UserInterface $user = null, $everywhere = false)
 */
class Sentinel extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'sentinel';
    }
}
