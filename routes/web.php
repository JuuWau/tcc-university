<?php

use App\Http\Controllers\SpecialtiesController;
use App\Http\Controllers\PeriodsController;
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

Route::prefix('specialties')->group(function () {
    Route::get('/', [SpecialtiesController::class, 'index'])->name('specialties.index');
    Route::get('/create', [SpecialtiesController::class, 'create'])->name('specialties.create');
    Route::post('/', [SpecialtiesController::class, 'store'])->name('specialties.store');
    Route::put('/{specialty}', [SpecialtiesController::class, 'update'])->name('specialties.update');
    Route::delete('/{specialty}', [SpecialtiesController::class, 'destroy'])->name('specialties.destroy');
});

Route::prefix('periods')->group(function () {
    Route::get('/', [PeriodsController::class, 'index'])->name('periods.index');
    Route::get('/create', [PeriodsController::class, 'create'])->name('periods.create');
    Route::post('/', [PeriodsController::class, 'store'])->name('periods.store');
    Route::put('/{period}', [PeriodsController::class, 'update'])->name('periods.update');
    Route::delete('/{period}', [PeriodsController::class, 'destroy'])->name('periods.destroy');
});

require __DIR__ . '/settings.php';
