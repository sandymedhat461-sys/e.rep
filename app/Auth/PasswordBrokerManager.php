<?php

namespace App\Auth;

use Illuminate\Auth\Passwords\PasswordBrokerManager as BasePasswordBrokerManager;

class PasswordBrokerManager extends BasePasswordBrokerManager
{
    protected function createTokenRepository(array $config)
    {
        if (isset($config['driver']) && $config['driver'] === 'cache') {
            return parent::createTokenRepository($config);
        }

        $key = $this->app['config']['app.key'];

        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return new UserTypeDatabaseTokenRepository(
            $this->app['db']->connection($config['connection'] ?? null),
            $this->app['hash'],
            $config['table'],
            $key,
            ($config['expire'] ?? 60) * 60,
            $config['throttle'] ?? 0,
            $config['user_type'] ?? null,
        );
    }
}
