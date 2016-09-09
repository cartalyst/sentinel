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

use ArrayIterator;
use Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository;
use Mockery as m;
use PHPUnit_Framework_TestCase;
use stdClass;

class IlluminatePersistenceRepositoryTest extends PHPUnit_Framework_TestCase
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

    public function testConstructorModel()
    {
        $persistence = new IlluminatePersistenceRepository($session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface'), 'PersistenceMock');

        $this->assertEquals('PersistenceMock', $persistence->getModel());
    }

    public function testCheckWithNoSessionOrCookie()
    {
        $persistence = new IlluminatePersistenceRepository($session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface'));
        $session->shouldReceive('get')->once();
        $cookie->shouldReceive('get')->once();
        $this->assertNull($persistence->check());
    }

    public function testCheckWithSession()
    {
        $persistence = new IlluminatePersistenceRepository($session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface'));
        $session->shouldReceive('get')->once()->andReturn('foo');
        $this->assertEquals('foo', $persistence->check());
    }

    public function testCheckWithCookie()
    {
        $persistence = new IlluminatePersistenceRepository($session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface'));
        $session->shouldReceive('get')->once();
        $cookie->shouldReceive('get')->once()->andReturn('bar');
        $this->assertEquals('bar', $persistence->check());
    }

    public function testFindByPersistenceCode()
    {
        $persistence = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface')]);

        $persistence->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Persistences\EloquentPersistence[newQuery]'));

        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

        $query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
        $query->shouldReceive('first')->once();

        $persistence->findByPersistenceCode('foobar');
    }

    public function testFindUserByPersistenceCode()
    {
        $persistence = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface')]);

        $persistenceRecord = new stdClass;
        $persistenceRecord->user = m::mock('UserMock');

        $persistence->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Persistences\EloquentPersistence[newQuery]'));

        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

        $query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn($persistenceRecord);

        $user = $persistence->findUserByPersistenceCode('foobar');

        $this->assertInstanceOf('UserMock', $user);
    }

    public function testFindUserByPersistenceCode1()
    {
        $persistence = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface')]);

        $persistenceRecord = new stdClass;
        $persistenceRecord->user = m::mock('UserMock');

        $persistence->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Persistences\EloquentPersistence[newQuery]'));

        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));

        $query->shouldReceive('where')->with('code', 'foobar')->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn([]);

        $user = $persistence->findUserByPersistenceCode('foobar');

        $this->assertFalse($user);
    }

    public function testPersist()
    {
        $persistence = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface')]);

        $persistence->shouldReceive('createModel')->once()->andReturn($model = m::mock('Illuminate\Database\Eloquent\Model'));

        $model->shouldReceive('setAttribute')->with('foo', '1')->once();
        $model->shouldReceive('setAttribute')->with('code', 'code')->once();
        $model->shouldReceive('save')->once();

        $persistable = m::mock('Cartalyst\Sentinel\Persistences\PersistableInterface');
        $persistable->shouldReceive('generatePersistenceCode')->once()->andReturn('code');
        $persistable->shouldReceive('getPersistableKey')->once()->andReturn('foo');
        $persistable->shouldReceive('getPersistableId')->once()->andReturn(1);
        $session->shouldReceive('put')->with('code')->once();

        $persistence->persist($persistable);
    }

    public function testPersistSingle()
    {
        $persistence = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface'), null, true]);

        $session->shouldReceive('get')->once();
        $cookie->shouldReceive('get')->once();

        $persistence->shouldReceive('createModel')->once()->andReturn($model = m::mock('Illuminate\Database\Eloquent\Model'));

        $model->shouldReceive('setAttribute')->with('foo', '1')->once();
        $model->shouldReceive('setAttribute')->with('code', 'code')->once();
        $model->shouldReceive('save')->once();

        $persistable = m::mock('Cartalyst\Sentinel\Persistences\PersistableInterface');
        $persistable->shouldReceive('getPersistableRelationship')->once()->andReturn('persistences');
        $persistable->shouldReceive('persistences')->once()->andReturn($builder = m::mock('Illuminate\Database\Eloquent\Builder'));
        $builder->shouldReceive('get')->once()->andReturn([]);
        $persistable->shouldReceive('generatePersistenceCode')->once()->andReturn('code');
        $persistable->shouldReceive('getPersistableKey')->once()->andReturn('foo');
        $persistable->shouldReceive('getPersistableId')->once()->andReturn(1);
        $session->shouldReceive('put')->with('code')->once();

        $persistence->persist($persistable);
    }

    public function testPersistAndRemember()
    {
        $persistence = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface')]);

        $persistence->shouldReceive('createModel')->once()->andReturn($model = m::mock('Illuminate\Database\Eloquent\Model'));

        $model->shouldReceive('setAttribute')->with('foo', '1')->once();
        $model->shouldReceive('setAttribute')->with('code', 'code')->once();
        $model->shouldReceive('save')->once();

        $persistable = m::mock('Cartalyst\Sentinel\Persistences\PersistableInterface');
        $persistable->shouldReceive('generatePersistenceCode')->once()->andReturn('code');
        $persistable->shouldReceive('getPersistableKey')->once()->andReturn('foo');
        $persistable->shouldReceive('getPersistableId')->once()->andReturn('1');

        $session->shouldReceive('put')->with('code')->once();
        $cookie->shouldReceive('put')->with('code')->once();

        $persistence->persistAndRemember($persistable);
    }

    public function testRemove()
    {
        $persistence = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface')]);
        $persistence->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Persistences\EloquentPersistence[newQuery]'));

        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('where')->once()->andReturn($model = m::mock('Illuminate\Database\Eloquent\Model'));
        $model->shouldReceive('delete')->once();
        $persistable = m::mock('Cartalyst\Sentinel\Persistences\PersistableInterface');
        $persistence->remove($persistable);
    }

    public function testFlush()
    {
        $persistence = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel]', [$session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface')]);
        $session->shouldReceive('get')->once();
        $cookie->shouldReceive('get')->once();
        $persistable = m::mock('Cartalyst\Sentinel\Persistences\PersistableInterface');
        $persistable->shouldReceive('persistences')->once()->andReturn($builder = m::mock('Illuminate\Database\Eloquent\Builder'));
        $builder->shouldReceive('get')->once()->andReturn([]);
        $persistable->shouldReceive('getPersistableRelationship')->once()->andReturn('persistences');
        $persistence->flush($persistable);
    }

    public function testFlushAndForget()
    {
        $persistence = m::mock('Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository[createModel,check]', [$session = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'), $cookie = m::mock('Cartalyst\Sentinel\Cookies\CookieInterface')]);
        $persistence->shouldReceive('check')->times(3)->andReturn('afoobar');

        $persistence->shouldReceive('createModel')->andReturn($model = m::mock('Cartalyst\Sentinel\Persistences\EloquentPersistence[newQuery]'));

        $model->shouldReceive('newQuery')->andReturn($query = m::mock('Illuminate\Database\Eloquent\Builder'));
        $query->shouldReceive('where')->with('code', 'afoobar')->andReturn($query);

        $session->shouldReceive('forget')->once();
        $cookie->shouldReceive('forget')->once();

        $persistable = m::mock('Cartalyst\Sentinel\Persistences\PersistableInterface');
        $persistable->shouldReceive('persistences')->once()->andReturn($query);
        $query->shouldReceive('get')->once()->andReturn($persistenceRecords = m::mock('Illuminate\Database\Eloquent\Collection'));
        $persistenceRecords->shouldReceive('getIterator')->once()->andReturn(new ArrayIterator([
            $record1 = m::mock('Illuminate\Database\Eloquent\Model'),
            $record2 = m::mock('Illuminate\Database\Eloquent\Model'),
        ]));

        $record1->shouldReceive('getAttribute')->once()->with('code')->andReturn('foobar');
        $record2->shouldReceive('getAttribute')->once()->with('code')->andReturn('foobar');

        $record1->shouldReceive('delete')->once();
        $record2->shouldReceive('delete')->once();

        $query->shouldReceive('delete')->once();
        $persistable->shouldReceive('getPersistableRelationship')->once()->andReturn('persistences');
        $persistence->flush($persistable);
    }
}
