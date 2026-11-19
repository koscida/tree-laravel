<?php

use App\Http\Controllers\FileMappingController;
use App\Http\Controllers\FileRowController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/files', [FileUploadController::class, 'index'])->name('files.index');
Route::post('/files', [FileUploadController::class, 'store'])->name('files.store');
Route::get('/files/{fileUploadId}', [FileUploadController::class, 'show'])->name('files.show');
Route::get('/files/{fileUploadId}/file_rows', [FileRowController::class, 'index'])->name('file_rows.index');
Route::post('/files/{fileUploadId}/file_rows', [FileRowController::class, 'store'])->name('file_rows.store');
Route::get('/files/{fileUploadId}/file_mappings', [FileMappingController::class, 'index'])->name('file_mappings.index');
Route::post('/files/{fileUploadId}/file_mappings', [FileMappingController::class, 'store'])->name('file_mappings.store');

Route::get('/file_settings', [SettingsController::class, 'index'])->name('file_settings.index');
