<?php

use Illuminate\Support\Facades\Route;
use UltraProject\UConfig\Http\Controllers\UConfigController;

Route::group([
    'middleware' => ['web', 'auth', 'config_manager'],
    'prefix' => 'uconfig',
    'as' => 'uconfig.'
], function () {
    Route::resource('/', UConfigController::class);
}); 