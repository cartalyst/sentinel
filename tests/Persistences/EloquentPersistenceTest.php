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

namespace Cartalyst\Sentinel\Tests\Persistences;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Connection;
use Cartalyst\Sentinel\Users\EloquentUser;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cartalyst\Sentinel\Persistences\EloquentPersistence;

class EloquentPersistenceTest extends TestCase
{
    /**
     * The Persistence instance.
     *
     * @var \Cartalyst\Sentinel\Persistences\EloquentPersistence
     */
    protected $persistence;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->persistence = new EloquentPersistence();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->persistence = null;

        m::close();
    }

    /** @test */
    public function it_can_get_the_user_relationship()
    {
        $this->addMockConnection($this->persistence);

        $this->assertInstanceOf(BelongsTo::class, $this->persistence->user());
    }

    /** @test */
    public function it_can_set_and_get_the_user_model_class_name()
    {
        $this->assertSame(EloquentUser::class, $this->persistence->getUsersModel());

        $this->persistence->setUsersModel('FooClass');

        $this->assertSame('FooClass', $this->persistence->getUsersModel());
    }

    protected function addMockConnection($model)
    {
        $model->setConnectionResolver($resolver = m::mock(ConnectionResolverInterface::class));
        $resolver->shouldReceive('connection')->andReturn(m::mock(Connection::class)->makePartial());

        $model->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock(Grammar::class));
        $model->getConnection()->shouldReceive('getPostProcessor')->andReturn(m::mock(Processor::class));
    }
}
