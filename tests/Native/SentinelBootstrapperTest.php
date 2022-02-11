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

namespace Cartalyst\Sentinel\Tests\Native;

use PHPUnit\Framework\TestCase;
use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Native\SentinelBootstrapper;

class SentinelBootstrapperTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $bootstrapper = new SentinelBootstrapper();

        $sentinel = $bootstrapper->createSentinel();

        $this->assertInstanceOf(Sentinel::class, $sentinel);
    }
}
