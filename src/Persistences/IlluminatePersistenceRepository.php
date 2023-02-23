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
 * @version    7.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2023, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Sentinel\Persistences;

use Cartalyst\Sentinel\Users\UserInterface;
use Cartalyst\Support\Traits\RepositoryTrait;
use Cartalyst\Sentinel\Cookies\CookieInterface;
use Cartalyst\Sentinel\Sessions\SessionInterface;

class IlluminatePersistenceRepository implements PersistenceRepositoryInterface
{
    use RepositoryTrait;

    /**
     * Single session.
     *
     * @var bool
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
    protected $model = EloquentPersistence::class;

    /**
     * Create a new Sentinel persistence repository.
     *
     * @param \Cartalyst\Sentinel\Sessions\SessionInterface $session
     * @param \Cartalyst\Sentinel\Cookies\CookieInterface   $cookie
     * @param string                                        $model
     * @param bool                                          $single
     *
     * @return void
     */
    public function __construct(SessionInterface $session, CookieInterface $cookie, string $model = null, bool $single = false)
    {
        $this->model = $model;

        $this->session = $session;

        $this->cookie = $cookie;

        $this->single = $single;
    }

    /**
     * {@inheritdoc}
     */
    public function check(): ?string
    {
        if ($code = $this->session->get()) {
            return $code;
        }

        if ($code = $this->cookie->get()) {
            return $code;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findByPersistenceCode(string $code): ?PersistenceInterface
    {
        return $this->createModel()->newQuery()->where('code', $code)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByPersistenceCode(string $code): ?UserInterface
    {
        $persistence = $this->findByPersistenceCode($code);

        return $persistence ? $persistence->user : null;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(PersistableInterface $persistable, bool $remember = false): bool
    {
        if ($this->single) {
            $this->flush($persistable);
        }

        $code = $persistable->generatePersistenceCode();

        $this->session->put($code);

        if ($remember) {
            $this->cookie->put($code);
        }

        $persistence = $this->createModel();

        $persistence->{$persistable->getPersistableKey()} = $persistable->getPersistableId();

        $persistence->code = $code;

        return $persistence->save();
    }

    /**
     * {@inheritdoc}
     */
    public function persistAndRemember(PersistableInterface $persistable): bool
    {
        return $this->persist($persistable, true);
    }

    /**
     * {@inheritdoc}
     */
    public function forget(): ?bool
    {
        $code = $this->check();

        if ($code === null) {
            return null;
        }

        $this->session->forget();
        $this->cookie->forget();

        return $this->remove($code);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $code): ?bool
    {
        return $this->createModel()->newQuery()->where('code', $code)->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function flush(PersistableInterface $persistable, bool $forget = true): void
    {
        if ($forget) {
            $this->forget();
        }

        $relationship = $persistable->getPersistableRelationship();

        foreach ($persistable->{$relationship}()->get() as $persistence) {
            if ($persistence->code !== $this->check()) {
                $persistence->delete();
            }
        }
    }
}
