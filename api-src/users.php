<?php

return [
    'url' => 'users',
    'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
    'database' => [
        'table' => 'users',
        'hidden' => [
            'password'
        ],
        'fillable' => [
            'name',
            'email',
            'username',
            'password',
        ],
    ]
];
