<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'path' => env('EYPIAY_PATH', 'app/Eypiay'),
    'min_query' => env('EYPIAY_MIN_QUERY', 10),
    'max_query' => env('EYPIAY_MAX_QUERY', 100),
    'debug' => env('EYPIAY_DEBUG', env('APP_DEBUG', false)),
    'param_splitter' => env('EYPIAY_PARAM_SPLITTER', '|'),
];
