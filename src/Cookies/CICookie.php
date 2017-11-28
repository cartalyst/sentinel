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

use CI_Input as Input;

class CICookie implements CookieInterface
{
    /**
     * The CodeIgniter input object.
     *
     * @var \CI_Input
     */
    protected $input;

    /**
     * The cookie options.
     *
     * @var array
     */
    protected $options = [
        'name'   => 'cartalyst_sentinel',
        'domain' => '',
        'path'   => '/',
        'prefix' => '',
        'secure' => false,
    ];

    /**
     * Create a new CodeIgniter cookie driver.
     *
     * @param  \CI_Input  $input
     * @param  string|array  $options
     * @return void
     */
    public function __construct(Input $input, $options = [])
    {
        $this->input = $input;

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
        $options = array_merge($this->options, [
            'value'  => json_encode($value),
            'expire' => 2628000,
        ]);

        $this->input->set_cookie($options);
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        $value = $this->input->cookie($this->options['name']);

        if ($value) {
            return json_decode($value);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function forget()
    {
        $this->input->set_cookie([
            'name'   => $this->options['name'],
            'value'  => '',
            'expiry' => '',
        ]);
    }
}
