<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('employees')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('employees.index');
    Route::get('/table', [UserController::class, 'table'])->name('employees.table');
    Route::get('/create', [UserController::class, 'create'])->name('employees.create');
    Route::post('/', [UserController::class, 'store'])->name('employees.store');
    Route::get('/{id}/edit', [UserController::class, 'edit'])->name('employees.edit');
    Route::put('/{id}', [UserController::class, 'update'])->name('employees.update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('employees.destroy');
});

require __DIR__ . '/settings.php';
