<?php

namespace App\Auth;

use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Database\ConnectionInterface;

class UserTypeDatabaseTokenRepository extends DatabaseTokenRepository
{
    public function __construct(
        ConnectionInterface $connection,
        HasherContract $hasher,
        string $table,
        string $hashKey,
        int $expires = 3600,
        int $throttle = 60,
        protected ?string $userType = null,
    ) {
        parent::__construct($connection, $hasher, $table, $hashKey, $expires, $throttle);
    }

    protected function deleteExisting(CanResetPasswordContract $user)
    {
        return $this->queryForUser($user)->delete();
    }

    protected function getPayload($email, #[\SensitiveParameter] $token)
    {
        return [
            'email' => $email,
            'user_type' => $this->userType,
            'token' => $this->hasher->make($token),
            'created_at' => now(),
        ];
    }

    public function exists(CanResetPasswordContract $user, #[\SensitiveParameter] $token)
    {
        $record = (array) $this->queryForUser($user)->first();

        return $record &&
               ! $this->tokenExpired($record['created_at']) &&
                 $this->hasher->check($token, $record['token']);
    }

    public function recentlyCreatedToken(CanResetPasswordContract $user)
    {
        $record = (array) $this->queryForUser($user)->first();

        return $record && $this->tokenRecentlyCreated($record['created_at']);
    }

    protected function queryForUser(CanResetPasswordContract $user)
    {
        $query = $this->getTable()->where('email', $user->getEmailForPasswordReset());

        if ($this->userType !== null) {
            $query->where('user_type', $this->userType);
        }

        return $query;
    }
}
