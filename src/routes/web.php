<?php

use Illuminate\Support\Facades\Route;
use Ultra\UltraConfigManager\Http\Controllers\UConfigController;

Route::resource('uconfig', UConfigController::class)->names('uconfig');
Route::get('/uconfig/{id}/audit', [UConfigController::class, 'audit'])->name('uconfig.audit');
