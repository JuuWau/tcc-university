<?php

use App\Http\Controllers\PeriodsController;
use App\Http\Controllers\ProceduresController;
use App\Http\Controllers\SpecialtiesController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\UserInviteController;
use App\Http\Controllers\UsersController;
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
})->middleware(['auth', 'verified']);

Route::prefix('procedures')->group(function () {
    Route::get('/', [ProceduresController::class, 'index'])->name('procedures.index');
    Route::post('/', [ProceduresController::class, 'store'])->name('procedures.store');
    Route::put('/{procedure}', [ProceduresController::class, 'update'])->name('procedures.update');
    Route::delete('/{procedure}', [ProceduresController::class, 'destroy'])->name('procedures.destroy');
})->middleware(['auth', 'verified']);

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

Route::prefix('users')->group(function () {
    Route::get('/', [UsersController::class, 'index'])->name('users.index');
    Route::get('/table', [UsersController::class, 'table'])->name('users.table');
    Route::get('/{user}', [UsersController::class, 'show'])->name('users.show');
    Route::post('/', [UsersController::class, 'store'])->name('users.store');
    Route::patch('/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::patch('/{user}/role', [UsersController::class, 'updateRole'])->name('users.update.role');
    Route::post('/resend-invite/{user}', [UsersController::class, 'resendInvite'])->name('users.resend-invite');
    Route::delete('/deactivate/{user}', [UsersController::class, 'deactivate'])->name('users.deactivate');
    Route::delete('/activate/{user}', [UsersController::class, 'activate'])->name('users.activate');
    Route::delete('/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
})->middleware(['auth', 'verified']);

Route::prefix('invite')->group(function () {
    Route::get('/{token}', [UserInviteController::class, 'show'])->name('invite.show');
    Route::patch('/{token}', [UserInviteController::class, 'updateStudent'])->name('invite.updateStudent');
    Route::post('/{token}', [UserInviteController::class, 'store'])->name('invite.store');
});

require __DIR__ . '/settings.php';
