<?php

return [
    'defaults' => [
        'guard' => 'users'
    ],

    'guards' => [
        'client' => [
            'driver' => 'jwt',
            'provider' => 'client'
        ],
        'users' => [
            'driver' => 'jwt',
            'provider' => 'users'
        ],
        'admins' => [
            'driver' => 'jwt',
            'provider' => 'admins'
        ]
    ],

    'providers' => [
        'client' => [
            'driver' => 'eloquent',
            'model' => \App\Client::class
        ],
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\Models\Users::class
        ],
        'admins' => [
            'driver' => 'eloquent',
            'model' => \App\Models\Admins::class
        ]
    ],

    'password' => []
];
