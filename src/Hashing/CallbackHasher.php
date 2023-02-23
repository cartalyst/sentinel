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

namespace Cartalyst\Sentinel\Hashing;

use Closure;

class CallbackHasher implements HasherInterface
{
    /**
     * The closure used for hashing a value.
     *
     * @var \Closure
     */
    protected $hash;

    /**
     * The closure used for checking a hashed value.
     *
     * @var \Closure
     */
    protected $check;

    /**
     * Constructor.
     *
     * @param \Closure $hash
     * @param \Closure $check
     *
     * @return void
     */
    public function __construct(Closure $hash, Closure $check)
    {
        $this->hash = $hash;

        $this->check = $check;
    }

    /**
     * {@inheritdoc}
     */
    public function hash(string $value): string
    {
        $callback = $this->hash;

        return $callback($value);
    }

    /**
     * {@inheritdoc}
     */
    public function check(string $value, string $hashedValue): bool
    {
        $callback = $this->check;

        return $callback($value, $hashedValue);
    }
}
