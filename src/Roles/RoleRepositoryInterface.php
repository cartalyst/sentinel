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
 * @version    2.0.17
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2017, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Roles;

interface RoleRepositoryInterface
{
    /**
     * Finds a role by the given primary key.
     *
     * @param  int  $id
     * @return \Cartalyst\Sentinel\Roles\RoleInterface
     */
    public function findById($id);

    /**
     * Finds a role by the given slug.
     *
     * @param  string  $slug
     * @return \Cartalyst\Sentinel\Roles\RoleInterface
     */
    public function findBySlug($slug);

    /**
     * Finds a role by the given name.
     *
     * @param  string  $name
     * @return \Cartalyst\Sentinel\Roles\RoleInterface
     */
    public function findByName($name);
}
