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
 * @copyright  (c) 2011-2018, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Native\Facades;

use Odahcam\DP;

class Activation
{
    use DP\SingletonTrait;

    /**
     * Returns a new instance of the final object.
     */
    private static function newInstance()
    {
        return Sentinel::getActivationRepository();
    }
}
