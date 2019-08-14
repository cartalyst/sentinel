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

namespace Cartalyst\Sentinel\Hashing;

class Sha256Hasher implements HasherInterface
{
    use Hasher;

    /**
     * {@inheritDoc}
     */
    public function hash($value)
    {
        $salt = $this->createSalt();

        return $salt.hash('sha256', $salt.$value);
    }

    /**
     * {@inheritDoc}
     */
    public function check($value, $hashedValue)
    {
        $salt = substr($hashedValue, 0, $this->saltLength);

        return $this->slowEquals($salt.hash('sha256', $salt.$value), $hashedValue);
    }
}
