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

namespace Cartalyst\Sentinel\Persistences;

interface PersistableInterface
{
    /**
     * Returns the persistable key value.
     *
     * @return string
     */
    public function getPersistableId(): string;

    /**
     * Returns the persistable key name.
     *
     * @return string
     */
    public function getPersistableKey(): string;

    public function setPersistableKey(string $key);

    /**
     * Returns the persistable relationship name.
     *
     * @return string
     */
    public function getPersistableRelationship(): string;

    public function setPersistableRelationship(string $persistableRelationship);

    /**
     * Generates a random persist code.
     *
     * @return string
     */
    public function generatePersistenceCode(): string;
}
