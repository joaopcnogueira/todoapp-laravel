<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TimeEntryController;
use App\Http\Controllers\Api\TodoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Category API routes
    Route::apiResource('categories', CategoryController::class)->names([
        'index' => 'api.categories.index',
        'store' => 'api.categories.store',
        'show' => 'api.categories.show',
        'update' => 'api.categories.update',
        'destroy' => 'api.categories.destroy',
    ]);

    // Todo API routes
    Route::get('todos/trashed', [TodoController::class, 'trashed'])->name('api.todos.trashed');
    Route::patch('todos/{id}/restore', [TodoController::class, 'restore'])->name('api.todos.restore');
    Route::delete('todos/{id}/force', [TodoController::class, 'forceDelete'])->name('api.todos.force-delete');
    Route::patch('todos/{todo}/toggle', [TodoController::class, 'toggle'])->name('api.todos.toggle');
    Route::apiResource('todos', TodoController::class)->names([
        'index' => 'api.todos.index',
        'store' => 'api.todos.store',
        'show' => 'api.todos.show',
        'update' => 'api.todos.update',
        'destroy' => 'api.todos.destroy',
    ]);

    // Time tracking routes
    Route::post('todos/{todo}/time/start', [TimeEntryController::class, 'start'])->name('api.todos.time.start');
    Route::post('todos/{todo}/time/stop', [TimeEntryController::class, 'stop'])->name('api.todos.time.stop');
    Route::get('todos/{todo}/time/status', [TimeEntryController::class, 'status'])->name('api.todos.time.status');
    Route::delete('todos/{todo}/time/{timeEntry}', [TimeEntryController::class, 'destroy'])->name('api.todos.time.destroy');
});
