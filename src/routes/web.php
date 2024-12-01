<?php

use Illuminate\Support\Facades\Route;
use UltraProject\UConfig\Http\Controllers\UConfigController;

Route::group([
    'middleware' => ['web', 'auth', 'uconfig.check_role'],
], function () {
    Route::resource('uconfig', UConfigController::class)->names('config.index');

}); 