<?php namespace Fuel\Core;

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
 * @version    2.0.13
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2016, Cartalyst LLC
 * @link       http://cartalyst.com
 */

class Cookie
{
    public static function set($key, $value, $minutes)
    {
        $_SERVER['__cookie.set'] = [$key, $value, $minutes];
    }

    public static function get($key)
    {
        if ($key == 'foo') {
            return json_encode('baz');
        }
    }

    public static function delete($key)
    {
        if ($key == 'foo') {
            $_SERVER['__cookie.delete'] = true;
        }
    }
}
