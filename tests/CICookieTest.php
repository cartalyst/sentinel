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

use Cartalyst\Sentinel\Cookies\CICookie;
use CI_Input;
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
            'value'  => serialize('bar'),
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
        $input->shouldReceive('cookie')->with('foo')->once()->andReturn(serialize('baz'));
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
