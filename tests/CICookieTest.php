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

use Cartalyst\Sentinel\Cookies\CICookie;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class CICookieTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup resources and dependencies.
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        require_once __DIR__.'/stubs/ci/CI_Input.php';
    }

    /**
     * Close mockery.
     *
     * @return void
     */
    public function tearDown()
    {
        m::close();
    }

    public function testPut()
    {
        $cookie = new CICookie($input = m::mock('CI_Input'), 'foo');

        $input->shouldReceive('set_cookie')->with([
            'name'   => 'foo',
            'value'  => json_encode('bar'),
            'expire' => 2628000,
            'domain' => '',
            'path'   => '/',
            'prefix' => '',
            'secure' => false,
        ]);

        $cookie->put('bar');
    }

    public function testGet()
    {
        $cookie = new CICookie($input = m::mock('CI_Input'), 'foo');
        $input->shouldReceive('cookie')->with('foo')->once()->andReturn(json_encode('baz'));
        $this->assertEquals('baz', $cookie->get());
    }

    public function testForget()
    {
        $cookie = new CICookie($input = m::mock('CI_Input'), 'foo');
        $input->shouldReceive('set_cookie')->with([
            'name'   => 'foo',
            'value'  => '',
            'expiry' => '',
        ])->once();
        $cookie->forget();
    }
}
