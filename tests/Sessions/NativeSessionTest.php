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

use stdClass;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Cartalyst\Sentinel\Sessions\NativeSession;

class NativeSessionTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_can_start_the_session()
    {
        $session = new NativeSession('__sentinel');

        $this->assertInstanceOf(NativeSession::class, $session);
    }

    /** @test */
    public function it_can_put_a_value_on_session()
    {
        $session = new NativeSession('__sentinel');

        $class      = new stdClass();
        $class->foo = 'bar';

        $session->put($class);

        $this->assertSame(serialize($class), $_SESSION['__sentinel']);

        unset($_SESSION['__sentinel']);
    }

    /** @test */
    public function it_can_get_a_value_from_session()
    {
        $session = new NativeSession('__sentinel');

        $this->assertNull($session->get());

        $class      = new stdClass();
        $class->foo = 'bar';

        $_SESSION['__sentinel'] = serialize($class);

        $this->assertNotNull($session->get());

        unset($_SESSION['__sentinel']);
    }

    /** @test */
    public function it_can_forget_a_value_from_the_session()
    {
        $session = new NativeSession('__sentinel');

        $_SESSION['__sentinel'] = 'bar';

        $this->assertSame('bar', $_SESSION['__sentinel']);

        $session->forget();

        $this->assertFalse(isset($_SESSION['__sentinel']));
    }
}
