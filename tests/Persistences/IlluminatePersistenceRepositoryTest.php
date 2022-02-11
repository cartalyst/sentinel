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
use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Cartalyst\Sentinel\Users\UserInterface;
use Illuminate\Database\Eloquent\Collection;
use Cartalyst\Sentinel\Cookies\CookieInterface;
use Cartalyst\Sentinel\Sessions\SessionInterface;
use Cartalyst\Sentinel\Persistences\PersistableInterface;
use Cartalyst\Sentinel\Persistences\PersistenceInterface;
use Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository;

class IlluminatePersistenceRepositoryTest extends TestCase
{
    protected $session;

    protected $cookie;

    protected function setUp(): void
    {
        $this->session = m::mock(SessionInterface::class);

        $this->cookie = m::mock(CookieInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $persistence = new IlluminatePersistenceRepository($this->session, $this->cookie, 'PersistenceMock');

        $this->assertSame('PersistenceMock', $persistence->getModel());
    }

    /** @test */
    public function it_can_check_without_session_or_cookie()
    {
        $persistence = new IlluminatePersistenceRepository($this->session, $this->cookie);

        $this->session->shouldReceive('get')->once();
        $this->cookie->shouldReceive('get')->once();

        $this->assertNull($persistence->check());
    }

    /** @test */
    public function it_can_check_with_session()
    {
        $persistence = new IlluminatePersistenceRepository($this->session, $this->cookie);

        $this->session->shouldReceive('get')->once()->andReturn('foo');

        $this->assertSame('foo', $persistence->check());
    }

    /** @test */
    public function it_can_check_with_cookie()
    {
        $persistence = new IlluminatePersistenceRepository($this->session, $this->cookie);

        $this->session->shouldReceive('get')->once();
        $this->cookie->shouldReceive('get')->once()->andReturn('bar');

        $this->assertSame('bar', $persistence->check());
    }

    /** @test */
    public function it_can_find_by_a_persistence_code()
    {
        $persistence = m::mock(PersistenceInterface::class);

        $query = m::mock(Builder::class);
        $query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn($persistence);

        $model = m::mock('Cartalyst\Sentinel\Persistences\EloquentPersistence');
        $model->shouldReceive('newQuery')->andReturn($query);

        $persistenceRepository = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$this->session, $this->cookie]);
        $persistenceRepository->shouldReceive('createModel')->andReturn($model);

        $this->assertInstanceOf(PersistenceInterface::class, $persistenceRepository->findByPersistenceCode('foobar'));
    }

    /** @test */
    public function it_can_find_a_user_from_a_persistence_code()
    {
        $user = m::mock(UserInterface::class);

        $persistence       = m::mock(PersistenceInterface::class);
        $persistence->user = $user;

        $query = m::mock(Builder::class);
        $query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn($persistence);

        $model = m::mock('Cartalyst\Sentinel\Persistences\EloquentPersistence');
        $model->shouldReceive('newQuery')->andReturn($query);

        $persistenceRepository = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$this->session, $this->cookie]);
        $persistenceRepository->shouldReceive('createModel')->andReturn($model);

        $user = $persistenceRepository->findUserByPersistenceCode('foobar');

        $this->assertInstanceOf(UserInterface::class, $user);
    }

    /** @test */
    public function it_will_not_find_a_user_from_an_invalid_persistence_code()
    {
        $query = m::mock(Builder::class);
        $query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn(null);

        $model = m::mock('Cartalyst\Sentinel\Persistences\EloquentPersistence');
        $model->shouldReceive('newQuery')->andReturn($query);

        $persistenceRepository = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$this->session, $this->cookie]);
        $persistenceRepository->shouldReceive('createModel')->andReturn($model);

        $user = $persistenceRepository->findUserByPersistenceCode('foobar');

        $this->assertNull($user);
    }

    /** @test */
    public function it_can_persist_a_persistence()
    {
        $this->session->shouldReceive('get')->once();
        $this->session->shouldReceive('put')->with('code')->once();

        $this->cookie->shouldReceive('get')->once();

        $builder = m::mock(Builder::class);
        $builder->shouldReceive('get')->once()->andReturn([]);

        $model = m::mock(Model::class);
        $model->shouldReceive('setAttribute')->with('foo', '1')->once();
        $model->shouldReceive('setAttribute')->with('code', 'code')->once();
        $model->shouldReceive('save')->once()->andReturn(true);

        $persistable = m::mock(PersistableInterface::class);
        $persistable->shouldReceive('getPersistableRelationship')->once()->andReturn('persistences');
        $persistable->shouldReceive('persistences')->once()->andReturn($builder);

        $persistable->shouldReceive('generatePersistenceCode')->once()->andReturn('code');
        $persistable->shouldReceive('getPersistableKey')->once()->andReturn('foo');
        $persistable->shouldReceive('getPersistableId')->once()->andReturn(1);

        $persistenceRepository = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$this->session, $this->cookie, null, true]);
        $persistenceRepository->shouldReceive('createModel')->once()->andReturn($model);

        $this->assertTrue($persistenceRepository->persist($persistable));
    }

    /** @test */
    public function it_can_persist_and_remember_a_persistence()
    {
        $this->session->shouldReceive('put')->with('code')->once();
        $this->cookie->shouldReceive('put')->with('code')->once();

        $model = m::mock(Model::class);
        $model->shouldReceive('setAttribute')->with('foo', '1')->once();
        $model->shouldReceive('setAttribute')->with('code', 'code')->once();
        $model->shouldReceive('save')->once()->andReturn(true);

        $persistable = m::mock(PersistableInterface::class);
        $persistable->shouldReceive('generatePersistenceCode')->once()->andReturn('code');
        $persistable->shouldReceive('getPersistableKey')->once()->andReturn('foo');
        $persistable->shouldReceive('getPersistableId')->once()->andReturn('1');

        $persistence = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$this->session, $this->cookie]);
        $persistence->shouldReceive('createModel')->once()->andReturn($model);

        $this->assertTrue($persistence->persistAndRemember($persistable));
    }

    /** @test */
    public function it_can_remove_a_persistence()
    {
        $persistence = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$this->session, $this->cookie]);
        $persistence->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Persistences\EloquentPersistence'));

        $model->shouldReceive('newQuery')->andReturn($query = m::mock(Builder::class));
        $query->shouldReceive('where')->once()->andReturn($model = m::mock(Model::class));
        $model->shouldReceive('delete')->once()->andReturn(true);

        $persistable = m::mock(PersistableInterface::class);

        $this->assertTrue($persistence->remove($persistable));
    }

    /** @test */
    public function it_can_flush()
    {
        $this->session->shouldReceive('get')->once();
        $this->cookie->shouldReceive('get')->once();

        $persistence = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$this->session, $this->cookie]);

        $builder = m::mock(Builder::class);
        $builder->shouldReceive('get')->once()->andReturn([]);

        $persistable = m::mock(PersistableInterface::class);
        $persistable->shouldReceive('persistences')->once()->andReturn($builder);
        $persistable->shouldReceive('getPersistableRelationship')->once()->andReturn('persistences');

        $this->assertNull($persistence->flush($persistable));
    }

    /** @test */
    public function it_can_flush_and_forget()
    {
        $this->session->shouldReceive('forget')->once();
        $this->cookie->shouldReceive('forget')->once();

        $record1 = m::mock(Model::class);
        $record1->shouldReceive('getAttribute')->once()->with('code')->andReturn('foobar');
        $record1->shouldReceive('delete')->once();

        $record2 = m::mock(Model::class);
        $record2->shouldReceive('getAttribute')->once()->with('code')->andReturn('foobar');
        $record2->shouldReceive('delete')->once();

        $persistenceRecords = m::mock(Collection::class);
        $persistenceRecords->shouldReceive('getIterator')->once()->andReturn(new ArrayIterator([$record1, $record2]));

        $model = m::mock('Cartalyst\Sentinel\Persistences\EloquentPersistence');
        $model->shouldReceive('newQuery')->andReturn($query = m::mock(Builder::class));

        $query->shouldReceive('where')->with('code', 'afoobar')->andReturn($query);
        $query->shouldReceive('get')->once()->andReturn($persistenceRecords);
        $query->shouldReceive('delete')->once();

        $persistable = m::mock(PersistableInterface::class);
        $persistable->shouldReceive('persistences')->once()->andReturn($query);
        $persistable->shouldReceive('getPersistableRelationship')->once()->andReturn('persistences');

        $persistence = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$this->session, $this->cookie]);
        $this->session->shouldReceive('get')->times(3)->andReturn('afoobar');
        $persistence->shouldReceive('createModel')->andReturn($model);

        $this->assertNull($persistence->flush($persistable));
    }
}
