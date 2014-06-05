<?php namespace Cartalyst\Sentinel\Swipe;
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

use ApiBase;
use Cartalyst\Sentinel\Users\UserInterface;
use Closure;
use SwipeIdentityExpressApi;

class SentinelSwipe implements SwipeInterface {

	/**
	 * The shared API instance.
	 *
	 * @var \SwipeIdentityExpressApi
	 */
	protected $api;

	/**
	 * The email address used to authenticate with the API.
	 *
	 * @var string
	 */
	protected $email;

	/**
	 * The password used to authenticate with the API.
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * The key used to authenticate with the API.
	 *
	 * @var string
	 */
	protected $apiKey;

	/**
	 * The app code used to authenticate with the API.
	 *
	 * @var string
	 */
	protected $appCode;

	/**
	 * The IP address of the user authenticating.
	 *
	 * @var string
	 */
	protected $ipAddress;

	/**
	 * The Swipe API method, "swipe" or "sms".
	 *
	 * @var string
	 */
	protected $method = 'swipe';

	/**
	 * Flag for whether the object is in an SMS answering state.
	 *
	 * @var bool
	 */
	protected $answering = false;

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected $model = 'Cartalyst\Sentinel\Swipe\EloquentSwipe';

	/**
	 * Create a new Swipe Identity.
	 *
	 * @param  string  $email
	 * @param  string  $password
	 * @param  string  $apiKey
	 * @param  string  $appCode
	 * @param  string  $method
	 * @param  string  $model
	 */
	public function __construct($email, $password, $apiKey, $appCode, $ipAddress, $method = null,  $model = null)
	{
		$this->email = $email;
		$this->password = $password;
		$this->apiKey = $apiKey;
		$this->appCode = $appCode;
		$this->ipAddress = $ipAddress;

		if (isset($method))
		{
			$this->method = $method;
		}

		if (isset($model))
		{
			$this->model = $model;
		}
	}

	/**
	 * Destroy the object instance.
	 */
	public function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * {@inheritDoc}
	 */
	public function response(UserInterface $user)
	{
		$api = $this->getApi();

		$response = $api->doSecondFactor($user->getUserLogin(), $this->appCode, $this->ipAddress);
		$code = ApiBase::dispatchUser($response);

		return [$response, $code];
	}

	/**
	 * {@inheritDoc}
	 */
	public function saveNumber(UserInterface $user, $number)
	{
		$api = $this->getApi();

		$response = $api->setUserSmsNumber($user->getUserLogin(), $this->appCode, $number);

		return ($response->status == 1);
	}

	/**
	 * {@inheritDoc}
	 */
	public function checkAnswer(UserInterface $user, $answer, Closure $callback = null)
	{
		$this->answering = true;

		$api = $this->getApi();

		$response = $api->answerSMS($user->getUserLogin(), $this->appCode, $answer);

		if ($response->getReturnCode() == RC_SMS_ANSWER_REJECTED)
		{
			return false;
		}

		$response = isset($callback) ? $callback($user) : true;
		$this->answering = false;
		return $response;
	}

	/**
	 * Flag for whether the object is in an SMS answering state.
	 *
	 * @return bool
	 */
	public function isAnswering()
	{
		return $this->answering;
	}

	/**
	 * Lazily get an API instance associated with the object.
	 *
	 * @return \SwipeIdentityExpressApi
	 */
	public function getApi()
	{
		if ($this->api === null)
		{
			$this->api = $this->connect();
		}

		return $this->api;
	}

	/**
	 * Connect to the Swipe Identity API.
	 *
	 * @return \SwipeIdentityExpressApi
	 */
	protected function connect()
	{
		$api = $this->createApi();
		$api->startTransaction();
		$api->apiLogin($this->email, $this->password, $this->apiKey);

		return $api;
	}

	/**
	 * Create a new Swipe Identity API instance.
	 *
	 * @return \SwipeIdentityExpressApi
	 */
	protected function createApi()
	{
		return new SwipeIdentityExpressApi('https://api.swipeidentity.com/rs/expressapi/1.0/xml/');
	}

	/**
	 * Disconnects from the Swipe Identity API.
	 *
	 * @return void
	 */
	protected function disconnect()
	{
		if ($this->api !== null)
		{
			$this->api->endTransaction();
		}
	}

	/**
	 * Create a new instance of the model.
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function createModel()
	{
		$class = '\\'.ltrim($this->model, '\\');

		return new $class;
	}

	/**
	 * Runtime override of the model.
	 *
	 * @param  string  $model
	 * @return void
	 */
	public function setModel($model)
	{
		$this->model = $model;
	}

}
