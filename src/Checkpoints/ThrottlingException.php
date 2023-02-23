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

namespace Cartalyst\Sentinel\Checkpoints;

use Carbon\Carbon;
use RuntimeException;

class ThrottlingException extends RuntimeException
{
    /**
     * The delay, in seconds.
     *
     * @var int
     */
    protected $delay = 0;

    /**
     * The throttling type which caused the exception.
     *
     * @var string
     */
    protected $type = '';

    /**
     * Returns the delay.
     *
     * @return int
     */
    public function getDelay(): int
    {
        return $this->delay;
    }

    /**
     * Sets the delay.
     *
     * @param int $delay
     *
     * @return $this
     */
    public function setDelay(int $delay): self
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns a Carbon object representing the time which the throttle is lifted.
     *
     * @return \Carbon\Carbon
     */
    public function getFree(): Carbon
    {
        return Carbon::now()->addSeconds($this->delay);
    }
}
