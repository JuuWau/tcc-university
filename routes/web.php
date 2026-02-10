<?php

use App\Http\Controllers\SpecialtiesController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\PeriodsController;
use App\Http\Controllers\UserInviteController;
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
})->middleware(['auth', 'verified'])->name('specialties');

Route::prefix('periods')->group(function () {
    Route::get('/', [PeriodsController::class, 'index'])->name('periods.index');
    Route::get('/create', [PeriodsController::class, 'create'])->name('periods.create');
    Route::post('/', [PeriodsController::class, 'store'])->name('periods.store');
    Route::put('/{period}', [PeriodsController::class, 'update'])->name('periods.update');
    Route::delete('/{period}', [PeriodsController::class, 'destroy'])->name('periods.destroy');
});

Route::prefix('students')->group(function () {
    Route::patch('/{student}/academic-data', [StudentsController::class, 'updateAcademicData'])->name('students.update.academic-data');
    Route::patch('/{student}', [StudentsController::class, 'update'])->name('students.update');
    Route::get('/', [StudentsController::class, 'index'])->name('students.index');
    Route::get('/table', [StudentsController::class, 'table'])->name('students.table');
    Route::get('/create', [StudentsController::class, 'create'])->name('students.create');
    Route::post('/', [StudentsController::class, 'store'])->name('students.store');
    Route::post('/resend-invite/{student}', [StudentsController::class, 'resendInvite'])->name('students.resend-invite');
    Route::get('/{student}', [StudentsController::class, 'show'])->name('students.show');
    Route::delete('/{student}', [StudentsController::class, 'destroy'])->name('students.destroy');
    Route::delete('/deactivate/{student}', [StudentsController::class, 'deactivate'])->name('students.deactivate');
    Route::delete('/activate/{student}', [StudentsController::class, 'activate'])->name('students.activate');
});

Route::prefix('invite')->group(function () {
    Route::get('/{token}', [UserInviteController::class, 'show'])->name('invite.show');
    Route::patch('/{token}', [UserInviteController::class, 'updateStudent'])->name('invite.updateStudent');
    Route::post('/{token}', [UserInviteController::class, 'store'])->name('invite.store');
});

require __DIR__ . '/settings.php';
