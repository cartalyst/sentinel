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
 * @version    7.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2023, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Sentinel\Cookies;

class NullCookie implements CookieInterface
{
    /**
     * Put a value in the Sentinel cookie (to be stored until it's cleared).
     *
     * @param mixed $value
     *
     * @return void
     */
    public function put($value): void
    {
    }

    /**
     * Returns the Sentinel cookie value.
     *
     * @return mixed
     */
    public function get()
    {
        return null;
    }

    /**
     * Remove the Sentinel cookie.
     *
     * @return void
     */
    public function forget(): void
    {
    }
}
