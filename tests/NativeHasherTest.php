<?php

/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Sentinel
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\tests;

use Cartalyst\Sentinel\Hashing\NativeHasher;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class NativeHasherTest extends PHPUnit_Framework_TestCase
{
    /**
     * Close mockery.
     *
     * @return void
     */
    public function tearDown()
    {
        m::close();
    }

    public function testHashing()
    {
        $hasher = new NativeHasher;
        $hashedValue = $hasher->hash('password');
        $this->assertTrue($hashedValue !== 'password');
        $this->assertTrue($hasher->check('password', $hashedValue));
        $this->assertFalse($hasher->check('fail', $hashedValue));
    }

    public function testShortValue()
    {
        $hasher = new NativeHasher;
        $hashedValue = $hasher->hash('foo');
        $this->assertTrue($hashedValue !== 'foo');
        $this->assertTrue($hasher->check('foo', $hashedValue));
    }

    public function testUtf8Value()
    {
        $hasher = new NativeHasher;
        $hashedValue = $hasher->hash('fÄÓñ');
        $this->assertTrue($hashedValue !== 'fÄÓñ');
        $this->assertTrue($hasher->check('fÄÓñ', $hashedValue));
    }

    public function testSymbolsValue()
    {
        $hasher = new NativeHasher;
        $hashedValue = $hasher->hash('!"#$%^&*()-_,./:;<=>?@[]{}`~|');
        $this->assertTrue($hashedValue !== '!"#$%^&*()-_,./:;<=>?@[]{}`~|');
        $this->assertTrue($hasher->check('!"#$%^&*()-_,./:;<=>?@[]{}`~|', $hashedValue));
    }
}
