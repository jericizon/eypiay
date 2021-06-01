<?php

return [
    'url' => 'users',
    'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
    'request' => [
        'validations' => [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required|confirmed'
        ],
        'casts' => [
            'password' => 'hash',
        ]
    ],
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
