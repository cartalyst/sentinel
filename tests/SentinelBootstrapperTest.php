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
 * @version    2.0.13
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2016, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\tests;

use Cartalyst\Sentinel\Native\SentinelBootstrapper;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class SentinelBootstrapperTest extends PHPUnit_Framework_TestCase
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

    public function testInstantiate()
    {
        $bootstrapper = new SentinelBootstrapper();

        $sentinel = $bootstrapper->createSentinel();

        $this->assertInstanceOf('Cartalyst\Sentinel\Sentinel', $sentinel);
    }
}
