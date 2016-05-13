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
 * @version    2.0.12
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Persistences;

interface PersistableInterface
{
    /**
     * Returns the persistable key name.
     *
     * @return string
     */
    public function getPersistableKey();

    /**
     * Returns the persistable key value.
     *
     * @return string
     */
    public function getPersistableId();

    /**
     * Returns the persistable relationship name.
     *
     * @return string
     */
    public function getPersistableRelationship();

    /**
     * Generates a random persist code.
     *
     * @return string
     */
    public function generatePersistenceCode();
}
