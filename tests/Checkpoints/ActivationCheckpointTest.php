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

namespace Cartalyst\Sentinel\Tests\Checkpoints;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Cartalyst\Sentinel\Users\EloquentUser;
use Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Cartalyst\Sentinel\Activations\IlluminateActivationRepository;

class ActivationCheckpointTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function an_activated_user_can_login()
    {
        $users = m::mock(IlluminateActivationRepository::class);
        $users->shouldReceive('completed')->once()->andReturn(true);

        $user = m::mock(EloquentUser::class);

        $checkpoint = new ActivationCheckpoint($users);

        $status = $checkpoint->login($user);

        $this->assertTrue($status);
    }

    /** @test */
    public function an_exception_will_be_thrown_when_authenticating_a_non_activated_user()
    {
        $users = m::mock(IlluminateActivationRepository::class);
        $users->shouldReceive('completed')->once()->andReturn(false);

        $user = m::mock(EloquentUser::class);

        try {
            $checkpoint = new ActivationCheckpoint($users);

            $checkpoint->check($user);
        } catch (NotActivatedException $e) {
            $this->assertInstanceOf(EloquentUser::class, $e->getUser());
        }
    }

    /** @test */
    public function an_exception_will_be_thrown_when_the_user_is_not_activated_and_determining_if_the_user_is_logged_in()
    {
        $this->expectException(NotActivatedException::class);

        $users = m::mock(IlluminateActivationRepository::class);
        $users->shouldReceive('completed')->once();

        $user = m::mock(EloquentUser::class);

        $checkpoint = new ActivationCheckpoint($users);
        $checkpoint->check($user);
    }
}
