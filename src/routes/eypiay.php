<?php

use \JericIzon\Eypiay\Http\Controllers\EypiayBaseController;

$endpointPrefix = config('eypiay.endpoint-prefix');

Route::get($endpointPrefix. '/{tableName}', [EypiayBaseController::class, 'index']);
// Route::get($endpointPrefix. '/{tableName}/create', [EypiayBaseController::class, 'create']);

Route::post($endpointPrefix. '/{tableName}', [EypiayBaseController::class, 'store']);
Route::get($endpointPrefix. '/{tableName}/{id}', [EypiayBaseController::class, 'show']);
// Route::get($endpointPrefix. '/{tableName}/{id}/edit', [EypiayBaseController::class, 'edit']);

Route::match(['put', 'patch'], $endpointPrefix. '/{tableName}/{id}', [EypiayBaseController::class, 'update']);
Route::delete($endpointPrefix. '/{tableName}/{id}', [EypiayBaseController::class, 'destroy']);
