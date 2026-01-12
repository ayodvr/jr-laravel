<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\SecretController;

Route::group(['prefix' => 'v1'], function () {
    Route::post('/secrets', [SecretController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/secrets/{id}', [SecretController::class, 'show']);
});
