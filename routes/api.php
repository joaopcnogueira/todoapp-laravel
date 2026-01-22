<?php

use App\Http\Controllers\Api\CategoryController;
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
});
