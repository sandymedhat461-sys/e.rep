<?php

namespace App\Support;

final class PersonalAccessTokenLabel
{
    public const ROLE_ADMIN = 'admin';

    public const ROLE_COMPANY = 'company';

    public const ROLE_DOCTOR = 'doctor';

    public const ROLE_MEDICAL_REP = 'medical rep';

    public static function make(string $displayName): string
    {
        $name = trim($displayName);

        if ($name === '') {
            $name = 'User';
        }

        return "{$name}";
    }
}
