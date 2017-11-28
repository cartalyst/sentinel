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

namespace Cartalyst\Sentinel\Hashing;

class BcryptHasher implements HasherInterface
{
    use Hasher;

    /**
     * The hash strength.
     *
     * @var int
     */
    public $strength = 8;

    /**
     * {@inheritDoc}
     */
    public function hash($value)
    {
        $salt = $this->createSalt();

        // Format the strength
        $strength = str_pad($this->strength, 2, '0', STR_PAD_LEFT);

        // Create prefix - "$2y$"" fixes blowfish weakness
        $prefix = PHP_VERSION_ID < 50307 ? '$2a$' : '$2y$';

        return crypt($value, $prefix.$strength.'$'.$salt.'$');
    }

    /**
     * {@inheritDoc}
     */
    public function check($value, $hashedValue)
    {
        return $this->slowEquals(crypt($value, $hashedValue), $hashedValue);
    }
}
