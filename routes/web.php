<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FolderController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/berkas', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/berkas/upload', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/berkas', [DocumentController::class, 'store'])->name('documents.store');
    Route::post('/berkas/bulk', [DocumentController::class, 'bulk'])->name('documents.bulk');

    Route::get('/berkas/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::put('/berkas/{document}', [DocumentController::class, 'update'])->name('documents.update');
    Route::delete('/berkas/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/berkas/{document}/unduh', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/berkas/{document}/pratinjau', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::post('/berkas/{document}/bintang', [DocumentController::class, 'toggleStar'])->name('documents.star');
    Route::post('/berkas/{document}/analisis', [DocumentController::class, 'reanalyze'])->name('documents.reanalyze');

    Route::post('/berkas/{document}/pulihkan', [DocumentController::class, 'restore'])->withTrashed()->name('documents.restore');
    Route::delete('/berkas/{document}/permanen', [DocumentController::class, 'forceDestroy'])->withTrashed()->name('documents.force');

    Route::post('/folder', [FolderController::class, 'store'])->name('folders.store');
    Route::put('/folder/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('/folder/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

    Route::get('/kategori', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/kategori', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/kategori/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/kategori/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});
