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

return [

    /*
    |--------------------------------------------------------------------------
    | Session Key
    |--------------------------------------------------------------------------
    |
    | Please provide your session key for Sentinel.
    |
    */

    'session' => 'cartalyst_sentinel',

    /*
    |--------------------------------------------------------------------------
    | Cookie Key
    |--------------------------------------------------------------------------
    |
    | Please provide your cookie key for Sentinel.
    |
    */

    'cookie' => 'cartalyst_sentinel',

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    |
    | Please provide the user model used in Sentinel.
    |
    */

    'users' => [

        'model' => 'Cartalyst\Sentinel\Users\EloquentUser',

    ],

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    |
    | Please provide the role model used in Sentinel.
    |
    */

    'roles' => [

        'model' => 'Cartalyst\Sentinel\Roles\EloquentRole',

    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Here you may specify the permissions class. Sentinel ships with two
    | permission types.
    |
    | 'Cartalyst\Sentinel\Permissions\StandardPermissions'
    | 'Cartalyst\Sentinel\Permissions\StrictPermissions'
    |
    | "StandardPermissions" will assign a higher priority to the user
    | permissions over role permissions, once a user is allowed or denied
    | a specific permission, it will be used regardless of the
    | permissions set on the role.
    |
    | "StrictPermissions" will deny any permission as soon as it finds it
    | rejected on either the user or any of the assigned roles.
    |
    */

    'permissions' => [

        'class' => 'Cartalyst\Sentinel\Permissions\StandardPermissions',

    ],

    /*
    |--------------------------------------------------------------------------
    | Persistences
    |--------------------------------------------------------------------------
    |
    | Here you may specify the persistences model used and weather to use the
    | single persistence mode.
    |
    */

    'persistences' => [

        'model' => 'Cartalyst\Sentinel\Persistences\EloquentPersistence',

        'single' => false,

    ],

    /*
    |--------------------------------------------------------------------------
    | Checkpoints
    |--------------------------------------------------------------------------
    |
    | When logging in, checking for existing sessions and failed logins occur,
    | you may configure an indefinite number of "checkpoints". These are
    | classes which may respond to each event and handle accordingly.
    | We ship with two, a throttling checkpoint and an activation
    | checkpoint. Feel free to add, remove or re-order
    | these.
    |
    */

    'checkpoints' => [

        'throttle',
        'activation',

    ],

    /*
    |--------------------------------------------------------------------------
    | Activations
    |--------------------------------------------------------------------------
    |
    | Here you may specify the activations model used and the time (in seconds)
    | which activation codes expire. By default, activation codes expire after
    | three days. The lottery is used for garbage collection, expired
    | codes will be cleared automatically based on the provided odds.
    |
    */

    'activations' => [

        'model' => 'Cartalyst\Sentinel\Activations\EloquentActivation',

        'expires' => 259200,

        'lottery' => [2, 100],

    ],

    /*
    |--------------------------------------------------------------------------
    | Reminders
    |--------------------------------------------------------------------------
    |
    | Here you may specify the reminders model used and the time (in seconds)
    | which reminder codes expire. By default, reminder codes expire
    | after four hours. The lottery is used for garbage collection, expired
    | codes will be cleared automatically based on the provided odds.
    |
    */

    'reminders' => [

        'model' => 'Cartalyst\Sentinel\Reminders\EloquentReminder',

        'expires' => 14400,

        'lottery' => [2, 100],

    ],

    /*
    |--------------------------------------------------------------------------
    | Throttling
    |--------------------------------------------------------------------------
    |
    | Here, you may configure your site's throttling settings. There are three
    | types of throttling.
    |
    | The first type is "global". Global throttling will monitor the overall
    | failed login attempts across your site and can limit the effects of an
    | attempted DDoS attack.
    |
    | The second type is "ip". This allows you to throttle the failed login
    | attempts (across any account) of a given IP address.
    |
    | The third type is "user". This allows you to throttle the login attempts
    | on an individual user account.
    |
    | Each type of throttling has the same options. The first is the interval.
    | This is the time (in seconds) for which we check for failed logins. Any
    | logins outside this time are no longer assessed when throttling.
    |
    | The second option is thresholds. This may be approached one of two ways.
    | the first way, is by providing an key/value array. The key is the number
    | of failed login attempts, and the value is the delay, in seconds, before
    | the next attempt can occur.
    |
    | The second way is by providing an integer. If the number of failed login
    | attempts outweigh the thresholds integer, that throttle is locked until
    | there are no more failed login attempts within the specified interval.
    |
    | On this premise, we encourage you to use array thresholds for global
    | throttling (and perhaps IP throttling as well), so as to not lock your
    | whole site out for minutes on end because it's being DDoS'd. However,
    | for user throttling, locking a single account out because somebody is
    | attempting to breach it could be an appropriate response.
    |
    | You may use any type of throttling for any scenario, and the specific
    | configurations are designed to be customized as your site grows.
    |
    */

    'throttling' => [

        'model' => 'Cartalyst\Sentinel\Throttling\EloquentThrottle',

        'global' => [

            'interval' => 900,

            'thresholds' => [
                10 => 1,
                20 => 2,
                30 => 4,
                40 => 8,
                50 => 16,
                60 => 12
            ],

        ],

        'ip' => [

            'interval' => 900,

            'thresholds' => 5,

        ],

        'user' => [

            'interval' => 900,

            'thresholds' => 5,

        ],

    ],

];
