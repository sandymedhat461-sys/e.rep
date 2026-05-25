<?php

namespace App\Auth;

use Illuminate\Auth\Passwords\PasswordBrokerManager as BasePasswordBrokerManager;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class PasswordBrokerManager extends BasePasswordBrokerManager
{
    protected function createTokenRepository(array $config)
    {
        if (isset($config['driver']) && $config['driver'] === 'cache') {
            return parent::createTokenRepository($config);
        }

        $key = (string) $this->app->make('config')->get('app.key');

        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return new UserTypeDatabaseTokenRepository(
            $this->app->make('db')->connection($config['connection'] ?? null),
            $this->app->make(HasherContract::class),
            (string) $config['table'],
            $key,
            ($config['expire'] ?? 60) * 60,
            $config['throttle'] ?? 0,
            isset($config['user_type']) ? (string) $config['user_type'] : null,
        );
    }
}
