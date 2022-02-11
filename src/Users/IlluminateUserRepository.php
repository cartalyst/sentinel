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

namespace Cartalyst\Sentinel\Users;

use Closure;
use Carbon\Carbon;
use InvalidArgumentException;
use Cartalyst\Support\Traits\EventTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Cartalyst\Support\Traits\RepositoryTrait;
use Cartalyst\Sentinel\Hashing\HasherInterface;

class IlluminateUserRepository implements UserRepositoryInterface
{
    use EventTrait, RepositoryTrait;

    /**
     * The Hasher instance.
     *
     * @var \Cartalyst\Sentinel\Hashing\HasherInterface
     */
    protected $hasher;

    /**
     * The User model FQCN.
     *
     * @var string
     */
    protected $model = EloquentUser::class;

    /**
     * Constructor.
     *
     * @param \Cartalyst\Sentinel\Hashing\HasherInterface $hasher
     * @param \Illuminate\Contracts\Events\Dispatcher     $dispatcher
     * @param string|null                                 $model
     *
     * @return void
     */
    public function __construct(HasherInterface $hasher, Dispatcher $dispatcher = null, string $model = null)
    {
        $this->hasher = $hasher;

        $this->dispatcher = $dispatcher;

        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function findById(int $id): ?UserInterface
    {
        return $this->createModel()->newQuery()->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByCredentials(array $credentials): ?UserInterface
    {
        if (empty($credentials)) {
            return null;
        }

        $instance = $this->createModel();

        $loginNames = $instance->getLoginNames();

        [$logins] = $this->parseCredentials($credentials, $loginNames);

        if (empty($logins)) {
            return null;
        }

        $query = $instance->newQuery();

        if (is_array($logins)) {
            foreach ($logins as $key => $value) {
                $query->where($key, $value);
            }
        } else {
            $query->whereNested(function ($query) use ($loginNames, $logins) {
                foreach ($loginNames as $index => $name) {
                    $method = $index === 0 ? 'where' : 'orWhere';

                    $query->{$method}($name, $logins);
                }
            });
        }

        return $query->first();
    }

    /**
     * {@inheritdoc}
     */
    public function findByPersistenceCode(string $code): ?UserInterface
    {
        return $this->createModel()
            ->newQuery()
            ->whereHas('persistences', function ($q) use ($code) {
                $q->where('code', $code);
            })
            ->first()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function recordLogin(UserInterface $user): bool
    {
        $user->last_login = Carbon::now();

        return (bool) $user->save();
    }

    /**
     * {@inheritdoc}
     */
    public function recordLogout(UserInterface $user): bool
    {
        return (bool) $user->save();
    }

    /**
     * {@inheritdoc}
     */
    public function validateCredentials(UserInterface $user, array $credentials): bool
    {
        return $this->hasher->check($credentials['password'], $user->password);
    }

    /**
     * {@inheritdoc}
     */
    public function validForCreation(array $credentials): bool
    {
        return $this->validateUser($credentials);
    }

    /**
     * {@inheritdoc}
     */
    public function validForUpdate($user, array $credentials): bool
    {
        if ($user instanceof UserInterface) {
            $user = $user->getUserId();
        }

        return $this->validateUser($credentials, $user);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $credentials, Closure $callback = null): ?UserInterface
    {
        $user = $this->createModel();

        $this->fireEvent('sentinel.user.creating', compact('user', 'credentials'));

        $this->fill($user, $credentials);

        if ($callback) {
            $result = $callback($user);

            if ($result === false) {
                return null;
            }
        }

        $user->save();

        $this->fireEvent('sentinel.user.created', compact('user', 'credentials'));

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function update($user, array $credentials): UserInterface
    {
        if (! $user instanceof UserInterface) {
            $user = $this->findById($user);
        }

        $this->fireEvent('sentinel.user.updating', compact('user', 'credentials'));

        $this->fill($user, $credentials);

        $user->save();

        $this->fireEvent('sentinel.user.updated', compact('user', 'credentials'));

        return $user;
    }

    /**
     * Fills a user with the given credentials, intelligently.
     *
     * @param \Cartalyst\Sentinel\Users\UserInterface $user
     * @param array                                   $credentials
     *
     * @return void
     */
    public function fill(UserInterface $user, array $credentials): void
    {
        $this->fireEvent('sentinel.user.filling', compact('user', 'credentials'));

        $loginNames = $user->getLoginNames();

        [$logins, $password, $attributes] = $this->parseCredentials($credentials, $loginNames);

        if (is_array($logins)) {
            $user->fill($logins);
        } else {
            $loginName = reset($loginNames);

            $user->fill([
                $loginName => $logins,
            ]);
        }

        $user->fill($attributes);

        if (isset($password)) {
            $password = $this->hasher->hash($password);

            $user->fill([
                'password' => $password,
            ]);
        }

        $this->fireEvent('sentinel.user.filled', compact('user', 'credentials'));
    }

    /**
     * Returns the hasher instance.
     *
     * @return \Cartalyst\Sentinel\Hashing\HasherInterface
     */
    public function getHasher(): HasherInterface
    {
        return $this->hasher;
    }

    /**
     * Sets the hasher instance.
     *
     * @param \Cartalyst\Sentinel\Hashing\HasherInterface $hasher
     *
     * @return void
     */
    public function setHasher(HasherInterface $hasher): void
    {
        $this->hasher = $hasher;
    }

    /**
     * Parses the given credentials to return logins, password and others.
     *
     * @param array $credentials
     * @param array $loginNames
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function parseCredentials(array $credentials, array $loginNames): array
    {
        if (isset($credentials['password'])) {
            $password = $credentials['password'];

            unset($credentials['password']);
        } else {
            $password = null;
        }

        $passedNames = array_intersect_key($credentials, array_flip($loginNames));

        if (count($passedNames) > 0) {
            $logins = [];

            foreach ($passedNames as $name => $value) {
                $logins[$name] = $credentials[$name];
                unset($credentials[$name]);
            }
        } elseif (isset($credentials['login'])) {
            $logins = $credentials['login'];
            unset($credentials['login']);
        } else {
            $logins = [];
        }

        return [$logins, $password, $credentials];
    }

    /**
     * Validates the user.
     *
     * @param array $credentials
     * @param int   $id
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    protected function validateUser(array $credentials, int $id = null): bool
    {
        $instance = $this->createModel();

        $loginNames = $instance->getLoginNames();

        // We will simply parse credentials which checks logins and passwords
        [$logins, $password] = $this->parseCredentials($credentials, $loginNames);

        if ($id === null) {
            if (empty($logins)) {
                throw new InvalidArgumentException('No [login] credential was passed.');
            }

            if (empty($password)) {
                throw new InvalidArgumentException('You have not passed a [password].');
            }
        }

        return true;
    }
}
