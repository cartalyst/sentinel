<?php

namespace Cartalyst\Sentinel\Cookies;

class NullCookie implements CookieInterface
{
    /**
     * Put a value in the Sentinel cookie (to be stored until it's cleared).
     *
     * @param  mixed $value
     * @return void
     */
    public function put($value)
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
    public function forget()
    {
    }
}
