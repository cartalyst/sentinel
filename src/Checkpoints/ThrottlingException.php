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

namespace Cartalyst\Sentinel\Checkpoints;

use Carbon\Carbon;
use RuntimeException;

class ThrottlingException extends RuntimeException
{
    /**
     * The delay, in seconds.
     *
     * @var string
     */
    protected $delay;

    /**
     * The throttling type which caused the exception.
     *
     * @var string
     */
    protected $type;

    /**
     * Returns the delay.
     *
     * @return int
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * Sets the delay.
     *
     * @param  int  $delay
     * @return void
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;
    }

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param  string  $type
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Returns a Carbon object representing the time which the throttle is lifted.
     *
     * @return \Carbon\Carbon
     */
    public function getFree()
    {
        return Carbon::now()->addSeconds($this->delay);
    }
}
