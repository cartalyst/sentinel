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
 * @version    2.0.18
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2019, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Sessions;

use Fuel\Core\Session_Driver as Session;

class FuelPHPSession implements SessionInterface
{
    /**
     * The FuelPHP session driver.
     *
     * @var \Fuel\Core\Session_Driver
     */
    protected $store;

    /**
     * The session key.
     *
     * @var string
     */
    protected $key = 'cartalyst_sentinel';

    /**
     * Create a new FuelPHP Session driver.
     *
     * @param  \Fuel\Core\Session_Driver  $store
     * @param  string  $key
     * @return void
     */
    public function __construct(Session $store, $key = null)
    {
        $this->store = $store;

        if (isset($key)) {
            $this->key = $key;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function put($value)
    {
        $this->store->set($this->key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        return $this->store->get($this->key);
    }

    /**
     * {@inheritDoc}
     */
    public function forget()
    {
        $this->store->delete($this->key);
    }
}
