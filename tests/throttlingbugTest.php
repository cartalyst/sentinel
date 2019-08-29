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
 * @version    3.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2019, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Capsule\Manager as Capsule;
use Cartalyst\Sentinel\Native\SentinelBootstrapper;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;

use Cartalyst\Sentinel\Native\Facades\Sentinel as Sentinel;

class throttlingbugTest extends TestCase
{
    static $db;
    static $migrator;
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        self::$db = $db = new Capsule;
        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:'
        ]);
        $db->setAsGlobal();
        $db->bootEloquent();

        $container = new Container;
        $container->instance('db', $db->getDatabaseManager());

        Facade::setFacadeApplication($container);

        self::$migrator = new Migrator(
            $repository = new DatabaseMigrationRepository($db->getDatabaseManager(), 'migrations'),
            $db->getDatabaseManager(),
            new Filesystem
        );

        if (! $repository->repositoryExists()) {
            $repository->createRepository();
        }

        self::$migrator->run([__DIR__ . '/../src/migrations']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        self::$migrator->reset([__DIR__ . '/../src/migrations']);
        self::$migrator->run([__DIR__ . '/../src/migrations']);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @test
     */
    public function can_create_database()
    {
        $this->assertTrue(self::$db->schema()->hasTable('users'));
    }

    /**
     * @test
     */
    public function an_exception_will_be_thrown_after_attempting_to_login_too_many_times_using_sentinel_facade()
    {
        //$this->printTables('throttle');
        $this->expectException(ThrottlingException::class);

        $credentials = [
            'email' => 'throttlingfoo@bar.com',
            'password' => 'password'
        ];
        $badCredentials = [
            'email' => 'throttlingfoo@bar.com',
            'password' => 'wrongpassword'
        ];
        $user = Sentinel::registerAndActivate($credentials);
        for ($i=0; $i<=5; $i++) {
            Sentinel::authenticate($badCredentials);
        }
        //$this->printTables('throttle');
    }

    /**
     * @test
     */
    public function an_exception_will_be_thrown_after_attempting_to_login_too_many_times_using_sentinelbootstrapper()
    {
        $this->expectException(ThrottlingException::class);
        $this->expectExceptionMessage('Too many unsuccessful login attempts have been made against your account.');

        $sentinel = $this->createSentinel();
        $credentials = [
            'email' => 'throttlingfoo@bar.com',
            'password' => 'password'
        ];
        $badCredentials = [
            'email' => 'throttlingfoo@bar.com',
            'password' => 'wrongpassword'
        ];
        $user = $sentinel->registerAndActivate($credentials);
        for ($i=0; $i<=5; $i++) {
            $sentinel = $this->createSentinel();
            $sentinel->authenticate($badCredentials);
        }
    }

    protected function createSentinel()
    {
        $bootstrap = new SentinelBootStrapper();
        return $bootstrap->createSentinel();
    }

    protected function printTables(?string $table = null)
    {
        if ($table) {
            print_r(self::$db->table($table)->get());
            return;
        }
        $tables = self::$db::select('SELECT name FROM sqlite_master WHERE type =\'table\' AND name NOT LIKE \'sqlite_%\';');

        foreach ($tables as $table) {
            print_r($table->name);
            print_r(self::$db->table($table->name)->get());
        }
    }
}
