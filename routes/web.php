<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LogController;
use Illuminate\Support\Facades\Route;

Route::get('/upload', [UploadController::class, 'index'])
    ->name('upload.form');

Route::post('/upload', [UploadController::class, 'store'])
    ->name('upload.store');

Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/imports/{upload}', [ImportController::class, 'show'])
    ->name('imports.show');

Route::get('/logs', [LogController::class, 'index'])
    ->name('logs.index');