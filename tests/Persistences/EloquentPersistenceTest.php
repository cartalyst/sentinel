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
 * @version    2.0.17
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2017, Cartalyst LLC
 * @link       http://cartalyst.com
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
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_can_get_the_user_relationship()
    {
        $persistence = new EloquentPersistence();

        $this->addMockConnection($persistence);

        $this->assertInstanceOf(BelongsTo::class, $persistence->user());
    }

    /** @test */
    public function it_can_set_and_get_the_user_model_class_name()
    {
        $persistence = new EloquentPersistence();

        $this->assertSame(EloquentUser::class, $persistence->getUsersModel());

        $persistence->setUsersModel('FooClass');

        $this->assertSame('FooClass', $persistence->getUsersModel());
    }

    protected function addMockConnection($model)
    {
        $model->setConnectionResolver($resolver = m::mock(ConnectionResolverInterface::class));
        $resolver->shouldReceive('connection')->andReturn(m::mock(Connection::class)->makePartial());

        $model->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock(Grammar::class));
        $model->getConnection()->shouldReceive('getPostProcessor')->andReturn(m::mock(Processor::class));
    }
}
