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
 * @version    2.0.18
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2019, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Persistences;

use Cartalyst\Sentinel\Cookies\CookieInterface;
use Cartalyst\Sentinel\Persistences\PersistableInterface;
use Cartalyst\Sentinel\Sessions\SessionInterface;
use Cartalyst\Support\Traits\RepositoryTrait;

class IlluminatePersistenceRepository implements PersistenceRepositoryInterface
{
    use RepositoryTrait;

    /**
     * Single session.
     *
     * @var boolean
     */
    protected $single = false;

    /**
     * Session storage driver.
     *
     * @var \Cartalyst\Sentinel\Sessions\SessionInterface
     */
    protected $session;

    /**
     * Cookie storage driver.
     *
     * @var \Cartalyst\Sentinel\Cookies\CookieInterface
     */
    protected $cookie;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = 'Cartalyst\Sentinel\Persistences\EloquentPersistence';

    /**
     * Create a new Sentinel persistence repository.
     *
     * @param  \Cartalyst\Sentinel\Sessions\SessionInterface  $session
     * @param  \Cartalyst\Sentinel\Cookies\CookieInterface  $cookie
     * @param  string  $model
     * @param  bool  $single
     * @return void
     */
    public function __construct(
        SessionInterface $session,
        CookieInterface $cookie,
        $model = null,
        $single = false
    ) {
        if (isset($model)) {
            $this->model = $model;
        }

        if (isset($session)) {
            $this->session = $session;
        }

        if (isset($cookie)) {
            $this->cookie  = $cookie;
        }

        $this->single = $single;
    }

    /**
     * {@inheritDoc}
     */
    public function check()
    {
        if ($code = $this->session->get()) {
            return $code;
        }

        if ($code = $this->cookie->get()) {
            return $code;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findByPersistenceCode($code)
    {
        $persistence = $this->createModel()
            ->newQuery()
            ->where('code', $code)
            ->first();

        return $persistence ? $persistence : false;
    }

    /**
     * {@inheritDoc}
     */
    public function findUserByPersistenceCode($code)
    {
        $persistence = $this->findByPersistenceCode($code);

        return $persistence ? $persistence->user : false;
    }

    /**
     * {@inheritDoc}
     */
    public function persist(PersistableInterface $persistable, $remember = false)
    {
        if ($this->single) {
            $this->flush($persistable);
        }

        $code = $persistable->generatePersistenceCode();

        $this->session->put($code);

        if ($remember === true) {
            $this->cookie->put($code);
        }

        $persistence = $this->createModel();

        $persistence->{$persistable->getPersistableKey()} = $persistable->getPersistableId();
        $persistence->code = $code;

        return $persistence->save();
    }

    /**
     * {@inheritDoc}
     */
    public function persistAndRemember(PersistableInterface $persistable)
    {
        return $this->persist($persistable, true);
    }

    /**
     * {@inheritDoc}
     */
    public function forget()
    {
        $code = $this->check();

        if ($code === null) {
            return;
        }

        $this->session->forget();
        $this->cookie->forget();

        return $this->remove($code);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($code)
    {
        return $this->createModel()
            ->newQuery()
            ->where('code', $code)
            ->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function flush(PersistableInterface $persistable, $forget = true)
    {
        if ($forget) {
            $this->forget();
        }

        foreach ($persistable->{$persistable->getPersistableRelationship()}()->get() as $persistence) {
            if ($persistence->code !== $this->check()) {
                $persistence->delete();
            }
        }
    }
}
