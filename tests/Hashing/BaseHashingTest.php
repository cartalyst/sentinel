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
 * @version    6.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2022, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Sentinel\Tests\Hashing;

use RuntimeException;
use PHPUnit\Framework\TestCase;

abstract class BaseHashingTest extends TestCase
{
    /**
     * The Hasher instance.
     *
     * @var \Cartalyst\Sentinel\Hashing\HasherInterface
     */
    protected $hasher;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        if (! $this->hasher) {
            throw new RuntimeException();
        }
    }

    /** @test */
    public function a_password_can_be_hashed()
    {
        $hashedValue = $this->hasher->hash('password');

        $this->assertTrue($hashedValue !== 'password');
        $this->assertTrue($this->hasher->check('password', $hashedValue));
        $this->assertFalse($this->hasher->check('fail', $hashedValue));
    }

    /** @test */
    public function a_password_that_is_not_very_long_in_length_can_be_hashed()
    {
        $hashedValue = $this->hasher->hash('foo');

        $this->assertTrue($hashedValue !== 'foo');
        $this->assertTrue($this->hasher->check('foo', $hashedValue));
        $this->assertFalse($this->hasher->check('fail', $hashedValue));
    }

    /** @test */
    public function a_password_with_utf8_characters_can_be_hashed()
    {
        $hashedValue = $this->hasher->hash('fÄÓñ');

        $this->assertTrue($hashedValue !== 'fÄÓñ');
        $this->assertTrue($this->hasher->check('fÄÓñ', $hashedValue));
    }

    /** @test */
    public function a_password_with_various_symbols_can_be_hashed()
    {
        $hashedValue = $this->hasher->hash('!"#$%^&*()-_,./:;<=>?@[]{}`~|');

        $this->assertTrue($hashedValue !== '!"#$%^&*()-_,./:;<=>?@[]{}`~|');
        $this->assertTrue($this->hasher->check('!"#$%^&*()-_,./:;<=>?@[]{}`~|', $hashedValue));
    }
}
