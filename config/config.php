<?php

return [

    'debug' => env('EYPIAY_DEBUG', false),

    'endpoint-prefix' => env('EYPIAY_ENDPOINT_PREFIX', 'eypiay'),

    // global resource
    'resource' => '\JericIzon\Eypiay\Http\Resources\EypiayBaseResource',

    // 'tables' => [
    //     'users' => [
    //         'model' => App\Models\User::class,
    //         'validations' => [
    //             'name' => 'required',
    //             'email' => 'required|email'
    //         ],
    //         'casts' => [
    //             'password' => 'hash',
    //         ],
    //     ]
    // ]
];
