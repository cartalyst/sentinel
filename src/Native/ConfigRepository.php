<?php namespace Cartalyst\Sentinel\Native;
/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the license.txt file.
 *
 * @package    Sentinel
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use ArrayAccess;

class ConfigRepository implements ArrayAccess {

	protected $file;

	protected $config = [];

	public function __construct($file = null)
	{
		$this->file = $file ?: __DIR__.'/../config/config.php';

		$this->load();
	}

	protected function load()
	{
		$this->config = require $this->file;
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetExists($key)
	{
		return isset($this->config[$key]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetGet($key)
	{
		return $this->config[$key];
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetSet($key, $value)
	{
		$this->config[$key] = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetUnset($key)
	{
		unset($this->config[$key]);
	}

}
