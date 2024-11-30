<?php

use Illuminate\Support\Facades\Route;
use UltraProject\UConfig\Http\Controllers\UConfigController;

Route::group([
    'middleware' => ['web', 'auth', 'config_manager'],
], function () {
    Route::resource('config', UConfigController::class)->names('config.index');
    Route::get('config/{key}/delete', [UConfigController::class, 'destroy'])->name('config.delete');
    Route::get('config/{key}/edit', [UConfigController::class, 'edit'])->name('config.edit');
    Route::get('config/create', [UConfigController::class, 'create'])->name('config.create');
}); 