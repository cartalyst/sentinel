<?php namespace Cartalyst\Sentinel\Persistence;
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

use Cartalyst\Sentinel\Cookies\CookieInterface;
use Cartalyst\Sentinel\Sessions\SessionInterface;

class SentinelPersistence implements PersistenceInterface {

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
	 * Create a new Sentinel persistence repository.
	 *
	 * @param  Cartalyst\Sentinel\Sessions\SessionInterface  $session
	 * @param  Cartalyst\Sentinel\Cookies\CookieInterface  $cookie
	 * @return void
	 */
	public function __construct(SessionInterface $session, CookieInterface $cookie)
	{
		if (isset($session))
		{
			$this->session = $session;
		}

		if (isset($cookie))
		{
			$this->cookie  = $cookie;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function check()
	{
		if ($code = $this->session->get())
		{
			return $code;
		}

		if ($code = $this->cookie->get())
		{
			return $code;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function add(PersistableInterface $persistable, $remember = false)
	{
		$code = $persistable->generatePersistenceCode();

		$this->session->put($code);

		if ($remember === true)
		{
			$this->cookie->put($code);
		}

		$persistable->addPersistenceCode($code);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addAndRemember(PersistableInterface $persistable)
	{
		$this->add($persistable, true);
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove(PersistableInterface $persistable)
	{
		$code = $this->check();

		if ($code === null)
		{
			return;
		}

		$this->session->forget();
		$this->cookie->forget();

		return $persistable->removePersistenceCode($code);
	}

	/**
	 * {@inheritDoc}
	 */
	public function flush(PersistableInterface $persistable)
	{
		$this->session->forget();
		$this->cookie->forget();

		foreach ($persistable->getPersistenceCodes() as $code)
		{
			$persistable->removePersistenceCode($code);
		}
	}

}
