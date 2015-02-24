<?php namespace Fuel\Core;

/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Sentinel
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2015, Cartalyst LLC
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
            return serialize('baz');
        }
    }

    public static function delete($key)
    {
        if ($key == 'foo') {
            $_SERVER['__cookie.delete'] = true;
        }
    }
}
