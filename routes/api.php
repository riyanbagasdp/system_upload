<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AttachmentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Semua route di sini otomatis punya prefix "/api"
| Jadi "/auth/login" = "/api/auth/login"
|--------------------------------------------------------------------------
*/

// 🔹 Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('auth.logout');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('auth.me');
});

// 🔹 Protected routes (butuh token JWT)
Route::middleware('auth:api')->group(function () {

    // ✅ Task CRUD
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // ✅ Attachments
    Route::post('/tasks/{id}/attachments', [AttachmentController::class, 'upload'])->name('attachments.upload');
    Route::get('/attachments/{id}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{id}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // ✅ Export
    Route::post('/tasks/export', [TaskController::class, 'export'])->name('tasks.export');
    Route::get('/tasks/export/{file}', [TaskController::class, 'downloadExport'])->name('tasks.export.download');
});
