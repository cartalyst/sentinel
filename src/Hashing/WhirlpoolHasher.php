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

class WhirlpoolHasher implements HasherInterface
{
    use Hasher;

    /**
     * {@inheritdoc}
     */
    public function hash(string $value): string
    {
        $salt = $this->createSalt();

        return $salt.hash('whirlpool', $salt.$value);
    }

    /**
     * {@inheritdoc}
     */
    public function check(string $value, string $hashedValue): bool
    {
        $salt = substr($hashedValue, 0, $this->saltLength);

        return $this->slowEquals($salt.hash('whirlpool', $salt.$value), $hashedValue);
    }
}
