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

namespace Cartalyst\Sentinel\Cookies;

use Fuel\Core\Cookie;

class FuelPHPCookie implements CookieInterface
{
    /**
     * The cookie key.
     *
     * @var string
     */
    protected $key = 'cartalyst_sentinel';

    /**
     * Create a new FuelPHP cookie driver.
     *
     * @param  string  $key
     * @return void
     */
    public function __construct($key = null)
    {
        if (isset($key)) {
            $this->key = $key;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function put($value)
    {
        Cookie::set($this->key, json_encode($value), 2628000);
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        $value = Cookie::get($this->key);

        if ($value) {
            return json_decode($value);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function forget()
    {
        Cookie::delete($this->key);
    }
}
