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

namespace Cartalyst\Sentinel\Tests\Sessions;

use Mockery as m;
use Illuminate\Session\Store;
use PHPUnit\Framework\TestCase;
use Cartalyst\Sentinel\Sessions\IlluminateSession;

class IlluminateSessionTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_can_put_a_value_on_session()
    {
        $store = m::mock(Store::class);
        $store->shouldReceive('put')->with('foo', 'bar')->once();
        $store->shouldReceive('get')->once()->andReturn('bar');

        $session = new IlluminateSession($store, 'foo');

        $session->put('bar');

        $this->assertSame('bar', $session->get());
    }

    /** @test */
    public function it_can_get_a_value_from_session()
    {
        $store = m::mock(Store::class);
        $store->shouldReceive('get')->with('foo')->once()->andReturn('bar');

        $session = new IlluminateSession($store, 'foo');

        $this->assertSame('bar', $session->get());
    }

    /** @test */
    public function it_can_forget_a_value_from_the_session()
    {
        $store = m::mock(Store::class);
        $store->shouldReceive('forget')->with('foo')->once();
        $store->shouldReceive('get')->once()->andReturn(null);

        $session = new IlluminateSession($store, 'foo');

        $session->forget();

        $this->assertNull($session->get());
    }
}
