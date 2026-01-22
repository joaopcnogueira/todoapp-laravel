<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard redirects to todos
    Route::get('/dashboard', function () {
        return redirect()->route('todos.index');
    })->name('dashboard');

    // Todo routes
    Route::prefix('tarefas')->name('todos.')->group(function () {
        Route::get('/', [TodoController::class, 'index'])->name('index');
        Route::get('/criar', [TodoController::class, 'create'])->name('create');
        Route::post('/', [TodoController::class, 'store'])->name('store');

        // Static routes must come before dynamic {todo} routes
        Route::get('/lixeira', [TodoController::class, 'trashed'])->name('trashed');
        Route::patch('/lixeira/{id}/restaurar', [TodoController::class, 'restore'])->name('restore');
        Route::delete('/lixeira/{id}', [TodoController::class, 'forceDelete'])->name('force-delete');

        // Dynamic routes with {todo} parameter
        Route::get('/{todo}', [TodoController::class, 'show'])->name('show');
        Route::get('/{todo}/editar', [TodoController::class, 'edit'])->name('edit');
        Route::put('/{todo}', [TodoController::class, 'update'])->name('update');
        Route::patch('/{todo}/alternar', [TodoController::class, 'toggle'])->name('toggle');
        Route::delete('/{todo}', [TodoController::class, 'destroy'])->name('destroy');

        // Time tracking routes
        Route::post('/{todo}/time/start', [TimeEntryController::class, 'start'])->name('time.start');
        Route::post('/{todo}/time/stop', [TimeEntryController::class, 'stop'])->name('time.stop');
        Route::delete('/{todo}/time/{timeEntry}', [TimeEntryController::class, 'destroy'])->name('time.destroy');
    });

    // Category routes
    Route::prefix('categorias')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
