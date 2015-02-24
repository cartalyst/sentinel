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

use Cartalyst\Sentinel\Activations\IlluminateActivationRepository;
use Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Cartalyst\Sentinel\Users\EloquentUser;
use Mockery as m;
use PHPUnit_Framework_TestCase;

class ActivationCheckpointTest extends PHPUnit_Framework_TestCase
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

    public function testActivated()
    {
        $checkpoint = new ActivationCheckpoint($users = m::mock('Cartalyst\Sentinel\Activations\IlluminateActivationRepository'));

        $users->shouldReceive('completed')->once()->andReturn(true);

        $checkpoint->login(m::mock('Cartalyst\Sentinel\Users\EloquentUser'));
    }

    /**
     * @expectedException \Cartalyst\Sentinel\Checkpoints\NotActivatedException
     */
    public function testNotActivated()
    {
        $checkpoint = new ActivationCheckpoint($users = m::mock('Cartalyst\Sentinel\Activations\IlluminateActivationRepository'));

        $users->shouldReceive('completed')->once();

        $checkpoint->check(m::mock('Cartalyst\Sentinel\Users\EloquentUser'));
    }

    public function testNotActivatedExceptionGetUser()
    {
        $checkpoint = new ActivationCheckpoint($users = m::mock('Cartalyst\Sentinel\Activations\IlluminateActivationRepository'));

        $users->shouldReceive('completed')->once();

        try {
            $checkpoint->check(m::mock('Cartalyst\Sentinel\Users\EloquentUser'));
        } catch (NotActivatedException $e) {
            $this->assertInstanceOf('Cartalyst\Sentinel\Users\EloquentUser', $e->getUser());
        }
    }
}
