<?php

use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/files', [FileUploadController::class, 'index'])->name('files.index');
