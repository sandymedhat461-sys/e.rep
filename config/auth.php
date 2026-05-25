<?php

return [


    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],



    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'admin-api' => [
            'driver' => 'sanctum',
            'provider' => 'admins',
        ],

        'company-api' => [
            'driver' => 'sanctum',
            'provider' => 'companies',
        ],

        'doctor-api' => [
            'driver' => 'sanctum',
            'provider' => 'doctors',
        ],

        'rep-api' => [
            'driver' => 'sanctum',
            'provider' => 'reps',
        ],
    ],

 
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],

        'companies' => [
            'driver' => 'eloquent',
            'model' => App\Models\Company::class,
        ],

        'doctors' => [
            'driver' => 'eloquent',
            'model' => App\Models\Doctor::class,
        ],

        'reps' => [
            'driver' => 'eloquent',
            'model' => App\Models\MedicalRep::class,
        ],

      
    ],


    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],

        'admin' => [
            'provider' => 'admins',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
            'user_type' => 'admin',
        ],

        'company' => [
            'provider' => 'companies',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
            'user_type' => 'company',
        ],

        'doctor' => [
            'provider' => 'doctors',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
            'user_type' => 'doctor',
        ],

        'rep' => [
            'provider' => 'reps',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
            'user_type' => 'medical_rep',
        ],
    ],

    

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
