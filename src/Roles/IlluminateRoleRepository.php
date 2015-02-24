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

namespace Cartalyst\Sentinel\Roles;

use Cartalyst\Support\Traits\RepositoryTrait;

class IlluminateRoleRepository implements RoleRepositoryInterface
{

    use RepositoryTrait;

    /**
     * The Eloquent role model name.
     *
     * @var string
     */
    protected $model = 'Cartalyst\Sentinel\Roles\EloquentRole';

    /**
     * Create a new Illuminate role repository.
     *
     * @param  string  $model
     * @return void
     */
    public function __construct($model = null)
    {
        if (isset($model)) {
            $this->model = $model;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findById($id)
    {
        return $this
            ->createModel()
            ->newQuery()
            ->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findBySlug($slug)
    {
        return $this
            ->createModel()
            ->newQuery()
            ->where('slug', $slug)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function findByName($name)
    {
        return $this
            ->createModel()
            ->newQuery()
            ->where('name', $name)
            ->first();
    }
}
