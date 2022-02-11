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

namespace Cartalyst\Sentinel\Tests\Activations;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Cartalyst\Sentinel\Activations\EloquentActivation;

class EloquentActivationTest extends TestCase
{
    /**
     * The Activation Eloquent instance.
     *
     * @var \Cartalyst\Sentinel\Activations\EloquentActivation
     */
    protected $activation;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->activation = new EloquentActivation();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->activation = null;

        m::close();
    }

    /** @test */
    public function it_can_get_the_completed_attribute_as_a_boolean()
    {
        $this->activation->completed = 1;

        $this->assertTrue($this->activation->completed);
    }

    /** @test */
    public function it_can_get_the_activation_code_using_the_getter()
    {
        $this->activation->code = 'foo';

        $this->assertSame('foo', $this->activation->getCode());
    }
}
