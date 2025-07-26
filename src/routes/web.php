<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StorageController;

Route::get('/', [StorageController::class, 'index'])->name('storage.index');
Route::post('/create', [StorageController::class, 'create'])->name('storage.create');
Route::post('/delete', [StorageController::class, 'delete'])->name('storage.delete');