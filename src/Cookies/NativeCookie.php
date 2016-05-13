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

namespace Cartalyst\Sentinel\Cookies;

class NativeCookie implements CookieInterface
{
    /**
     * The cookie options.
     *
     * @var array
     */
    protected $options = [
        'name'      => 'cartalyst_sentinel',
        'domain'    => '',
        'path'      => '/',
        'secure'    => false,
        'http_only' => false,
    ];

    /**
     * Create a new cookie driver.
     *
     * @param  string|array  $options
     * @return void
     */
    public function __construct($options = [])
    {
        if (is_array($options)) {
            $this->options = array_merge($this->options, $options);
        } else {
            $this->options['name'] = $options;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function put($value)
    {
        $this->setCookie($value, $this->minutesToLifetime(2628000));
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        return $this->getCookie();
    }

    /**
     * {@inheritDoc}
     */
    public function forget()
    {
        $this->put(null, -2628000);
    }

    /**
     * Takes a minutes parameter (relative to now)
     * and converts it to a lifetime (unix timestamp).
     *
     * @param  int  $minutes
     * @return int
     */
    protected function minutesToLifetime($minutes)
    {
        return time() + ($minutes * 60);
    }

    /**
     * Returns a PHP cookie.
     *
     * @return mixed
     */
    protected function getCookie()
    {
        if (isset($_COOKIE[$this->options['name']])) {
            $value = $_COOKIE[$this->options['name']];

            if ($value) {
                return json_decode($value);
            }
        }
    }

    /**
     * Sets a PHP cookie.
     *
     * @param  mixed  $value
     * @param  int  $lifetime
     * @param  string  $path
     * @param  string  $domain
     * @param  bool  $secure
     * @param  bool  $httpOnly
     * @return void
     */
    protected function setCookie($value, $lifetime, $path = null, $domain = null, $secure = null, $httpOnly = null)
    {
        setcookie(
            $this->options['name'],
            json_encode($value),
            $lifetime,
            $path ?: $this->options['path'],
            $domain ?: $this->options['domain'],
            $secure ?: $this->options['secure'],
            $httpOnly ?: $this->options['http_only']
        );
    }
}
