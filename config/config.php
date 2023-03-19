<?php

return [
    'endpoint-prefix' => 'eypiay',

    'tables' => [
        'users' => [
            'model' => App\Models\User::class,
            'casts' => [
                'password' => 'hash',
            ],
        ]
    ]
];
