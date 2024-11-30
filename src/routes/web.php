<?php

use Illuminate\Support\Facades\Route;
use UltraProject\UConfig\Http\Controllers\UConfigController;

Route::group([
    'middleware' => ['web', 'auth', 'uconfig.check_role'],
], function () {
    Route::resource('uconfig', UConfigController::class)->names('config.index');
    Route::get('uconfig/{key}/delete', [UConfigController::class, 'destroy'])->name('config.delete');
    Route::get('uconfig/{key}/edit', [UConfigController::class, 'edit'])->name('config.edit');
    Route::get('uconfig/create', [UConfigController::class, 'create'])->name('config.create');
}); 