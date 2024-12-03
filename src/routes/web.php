<?php

use Illuminate\Support\Facades\Route;
use UltraProject\UConfig\Http\Controllers\UConfigController;

Route::resource('uconfig', UConfigController::class)->names('uconfig');
Route::get('/uconfig/{id}/audit', [UConfigController::class, 'audit'])->name('uconfig.audit');
