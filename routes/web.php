<?php

use App\Http\Controllers\ActionLogsController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PeriodsController;
use App\Http\Controllers\ProceduresController;
use App\Http\Controllers\ClinicController;
// use App\Http\Controllers\ConfirmAppointmentController;
// use App\Http\Controllers\ScheduleAttendanceController;
use App\Http\Controllers\ScheduleEnrollmentController;
use App\Http\Controllers\AppointmentConfirmationController;
use App\Http\Controllers\AppointmentReportsController;
use App\Http\Controllers\ClinicManagementController;
use App\Http\Controllers\PatientsReportController;
use App\Http\Controllers\ScheduleSlotController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\SpecialtiesController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\StudentsReportController;
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

Route::get('initialPage', function () {
    return Inertia::render('InitialPage');
})->middleware(['auth', 'verified'])->name('initialPage');

Route::get('reports/students', function () {
    return Inertia::render('reports/StudentsReportMock');
})->middleware(['auth', 'verified'])->name('reports.students');

Route::get('/my-profile', [ProfileController::class, 'redirect'])
    ->middleware(['auth'])
    ->name('profile.redirect');

Route::prefix('schedules')->group(function () {
    Route::post('open', [ScheduleSlotController::class, 'storeOpenSchedule'])->name('schedules.open.store');
    Route::get('open-schedule', [ScheduleSlotController::class, 'openSchedule'])->name('schedules.openSchedule');
    Route::get('open-clinics', [ScheduleSlotController::class, 'openClinicsManagement'])->name('schedules.openClinics');
    Route::get('/open-clinics/table',[ScheduleSlotController::class, 'openClinicsTable'])->name('schedules.openClinics.table');
    Route::get('open-clinics/{clinic}', [ScheduleSlotController::class, 'clinicOpenSchedules'])->name('schedules.openClinics.show');
    Route::post('open-clinics/{clinic}', [ScheduleSlotController::class, 'storeOpenClinicDay'])->name('schedules.openClinics.storeDay');
    Route::patch('slots/{slot}', [ScheduleSlotController::class, 'updateSlot'])->name('schedules.slots.update');
    Route::put('multiple-slots', [ScheduleSlotController::class, 'updateMultipleSlots'])->name('schedules.slots.multiple.update');
    Route::delete('slots/{slot}', [ScheduleSlotController::class, 'destroySlot'])->name('schedules.slots.destroy');
    Route::delete('multiple-slots', [ScheduleSlotController::class, 'destroyMultipleSlots'])->name('schedules.slots.multiple.destroy');
})->middleware(['auth', 'verified'])->name('schedules');

// Route::prefix('confirm-appointment')->group(function () {
//     Route::get('confirm-appointment', [ConfirmAppointmentController::class, 'index'])->name('confirmAppointment.index');
// })->middleware(['auth', 'verified'])->name('confirm-appointment');

Route::prefix('schedule-enrollment')->group(function () {
    Route::post('open', [ScheduleEnrollmentController::class, 'storeOpenSchedule'])->name('schedules.enrollment.open.store');
    Route::get('open-clinics', [ScheduleEnrollmentController::class, 'openClinicsSchedullesEnrollmentManagement'])->name('schedules.enrollment.openClinics')->middleware('permission:open-schedule-management-student.view');
    Route::get('open-clinics/table', [ScheduleEnrollmentController::class, 'openClinicsSchedullesEnrollmentTable'] )->name('schedules.enrollment.openClinics.table');
    Route::get('open-clinic/{clinic}', [ScheduleEnrollmentController::class, 'clinicOpenSchedulesEnrollment'])->name('schedules.enrollment.openClinic.show')->middleware('permission:open-schedule-management-student.view');
    Route::post('open-clinics/{clinic}', [ScheduleEnrollmentController::class, 'storeOpenClinicDay'])->name('schedules.enrollment.openClinics.storeDay');
    Route::patch('slots/{slot}', [ScheduleEnrollmentController::class, 'updateSlot'])->name('schedules.enrollment.slots.update')->middleware('permission:open-schedule-management.updateSlot');
    Route::post('multiple-slots', [ScheduleEnrollmentController::class, 'enrollMultipleSlots'])->name('schedules.enrollment.slots.enrollment.multiple')->middleware('permission:open-schedule-management-student.enroll');
    Route::delete('slots/{slot}', [ScheduleEnrollmentController::class, 'destroySlot'])->name('schedules.enrollment.slots.destroy')->middleware('permission:open-schedule-management.deleteSlot');
    Route::delete('multiple-slots', [ScheduleEnrollmentController::class, 'destroyMultipleSlots'])->name('schedules.enrollment.slots.multiple.destroy')->middleware('permission:open-schedule-management.deleteSlot');
    Route::post('enroll', [ScheduleEnrollmentController::class, 'store'])->name('schedules.enrollment.store')->middleware('permission:open-schedule-management.enrollStudent');
    Route::post('student-enroll', [ScheduleEnrollmentController::class, 'enrollSlot'])->name('schedules.enrollment.enrollSlot')->middleware('permission:open-schedule-management-student.enroll');
    Route::get('slots/{slot}/students', [ScheduleEnrollmentController::class, 'slotStudents'])->name('schedules.enrollment.slots.students');
    Route::delete('slots/{slot}/students/{student}', [ScheduleEnrollmentController::class, 'removeStudentFromSlot'])->name('schedules.enrollment.slots.students.destroy');
});

// Route::prefix('schedule-attendance')->group(function () {
//     Route::get('schedule-attendance', [ScheduleAttendanceController::class, 'index'])->name('schedule.attendance.index');
// })->middleware(['auth', 'verified'])->name('schedule-attendance');

Route::prefix('reports/appointments')->name('reports.appointments.')->group(function () {
    Route::get('/', [AppointmentReportsController::class, 'index'])->name('index');
    Route::get('/data', [AppointmentReportsController::class, 'data'])->name('reports.appointments.data');
    Route::get('/export-excel', [AppointmentReportsController::class, 'exportExcel'])->name('reports.appointments.exportExcel');
})->middleware('permission:appointments-reports.view');

Route::prefix('reports/students')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [StudentsReportController::class, 'index'])->name('reports.students.index');
    Route::get('/data', [StudentsReportController::class, 'data'])->name('reports.students.data');
    Route::get('/export', [StudentsReportController::class, 'exportExcel'])->name('reports.students.export');
})->middleware('permission:students-reports.view');

Route::prefix('reports/patients')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [PatientsReportController::class, 'index'])->name('reports.patients.index');
    Route::get('/data', [PatientsReportController::class, 'data'])->name('reports.patients.data');
    Route::get('/export', [PatientsReportController::class, 'exportExcel'])->name('reports.patients.export');
})->middleware('permission:patients-reports.view');

Route::get('/reports/clinics-by-student', function () {
    return Inertia::render('reports/ClinicsByStudentReportMock');
});

Route::prefix('specialties')->group(function () {
    Route::get('/', [SpecialtiesController::class, 'index'])->name('specialties.index')->middleware('permission:specialties.view');
    Route::get('/options', [SpecialtiesController::class, 'options'])->name('specialties.options');
    Route::post('/', [SpecialtiesController::class, 'store'])->name('specialties.store')->middleware('permission:specialties.create');
    Route::put('/{specialty}', [SpecialtiesController::class, 'update'])->name('specialties.update')->middleware('permission:specialties.update');
    Route::delete('/{specialty}', [SpecialtiesController::class, 'destroy'])->name('specialties.destroy')->middleware('permission:specialties.delete');
})->middleware(['auth', 'verified', 'permission:registrations.view'])->name('specialties');

Route::prefix('periods')->group(function () {
    Route::get('/', [PeriodsController::class, 'index'])->name('periods.index')->middleware('permission:periods.view');
    Route::post('/', [PeriodsController::class, 'store'])->name('periods.store')->middleware('permission:periods.create');
    Route::put('/{period}', [PeriodsController::class, 'update'])->name('periods.update')->middleware('permission:periods.update');
    Route::delete('/{period}', [PeriodsController::class, 'destroy'])->name('periods.destroy')->middleware('permission:periods.delete');
})->middleware(['auth', 'verified', 'permission:registrations.view']);

Route::prefix('clinics')->group(function () {
    Route::get('/', [ClinicController::class, 'index'])->name('clinics.index')->middleware('permission:clinics.view');
    Route::get('/table', [ClinicController::class, 'table'])->name('clinics.table');
    Route::post('/', [ClinicController::class, 'store'])->name('clinics.store')->middleware('permission:clinics.create');
    Route::put('/{clinic}', [ClinicController::class, 'update'])->name('clinics.update')->middleware('permission:clinics.update');
    Route::patch('/{clinic}/deactivate', [ClinicController::class, 'deactivate'])->name('clinics.deactivate')->middleware('permission:clinics.deactivate');
    Route::patch('/{clinic}/activate', [ClinicController::class, 'activate'])->name('clinics.activate')->middleware('permission:clinics.activate');
    Route::delete('/{clinic}', [ClinicController::class, 'destroy'])->name('clinics.destroy')->middleware('permission:clinics.delete');
})->middleware(['auth', 'verified', 'permission:registrations.view']);

Route::prefix('procedures')->group(function () {
    Route::get('/', [ProceduresController::class, 'index'])->name('procedures.index')->middleware('permission:procedures.view');
    Route::post('/', [ProceduresController::class, 'store'])->name('procedures.store')->middleware('permission:procedures.create');
    Route::put('/{procedure}', [ProceduresController::class, 'update'])->name('procedures.update')->middleware('permission:procedures.update');
    Route::delete('/{procedure}', [ProceduresController::class, 'destroy'])->name('procedures.destroy')->middleware('permission:procedures.delete');
    Route::get('/list', [ProceduresController::class, 'list'])->name('procedures.list');
})->middleware(['auth', 'verified', 'permission:registrations.view']);

Route::prefix('students')->group(function () {
    Route::patch('/{student}/academic-data', [StudentsController::class, 'updateAcademicData'])->name('students.update.academic-data')->middleware('permission:students.personal-page.updateHeaderData');
    Route::get('/{student}/clinics', [StudentsController::class, 'availableClinics'])->name('students.availableClinics');
    Route::get('/{student}/schedule', [StudentsController::class, 'schedule'])->name('students.schedule');
    Route::patch('/{student}', [StudentsController::class, 'update'])->name('students.update')->middleware('permission:students.personal-page.updatePersonalData');
    Route::get('/', [StudentsController::class, 'index'])->name('students.index')->middleware('permission:students.view');
    Route::get('/table', [StudentsController::class, 'table'])->name('students.table');
    Route::post('/', [StudentsController::class, 'store'])->name('students.store')->middleware('permission:students.create');
    Route::post('/resend-invite/{student}', [StudentsController::class, 'resendInvite'])->name('students.invite');
    Route::get('/options', [StudentsController::class, 'options'])->name('students.options');
    Route::get('/{student}', [StudentsController::class, 'show'])->name('students.show')->middleware('permission:students.personal-page.view');
    Route::delete('/{student}', [StudentsController::class, 'destroy'])->name('students.destroy')->middleware('permission:students.delete');
    Route::delete('/deactivate/{student}', [StudentsController::class, 'deactivate'])->name('students.deactivate')->middleware('permission:students.deactivate');
    Route::delete('/activate/{student}', [StudentsController::class, 'activate'])->name('students.activate')->middleware('permission:students.activate');
    Route::get('/{student}/patients', [StudentsController::class, 'patients'])->name('students.patients');
})->middleware(['auth', 'verified']);

Route::prefix('student-calendar')->group(function () {
    Route::get('/{student}/appointments', [AppointmentController::class, 'listByStudent'])->name('student-calendar.listByStudent');
    Route::patch('/{appointment}/time', [AppointmentController::class, 'updateTime'])->name('student-calendar.updateTime');
    Route::put('/{appointment}', [AppointmentController::class, 'updateAppointment'])->name('student-calendar.updateAppointment');
    Route::post('/{student}', [AppointmentController::class, 'store'])->name('student-calendar.store');
});

Route::prefix('patient-calendar')->group(function () {
    Route::get('/{patient}/available-days', [AppointmentController::class, 'availableDays'])->name('patient-calendar.availableDays');
    Route::get('/{patient}/available-times', [AppointmentController::class, 'availableTimes'])->name('student-calendar.availableTimes');
    Route::post('/{patient}', [AppointmentController::class, 'storePatientCalendar'])->name('patient-calendar.store');
    Route::put('/{patient}/{appointment}', [AppointmentController::class, 'updatePatientCalendar'])->name('patient-calendar.update');
    Route::patch('/{patient}/{appointment}/time', [AppointmentController::class, 'updatePatientCalendarTime'])->name('patient-calendar.updateTime');
});

Route::prefix('patients')->group(function () {
    Route::get('/', [PatientController::class, 'index'])->name('patients.index')->middleware('permission:patients.view');
    Route::get('/table', [PatientController::class, 'table'])->name('patients.table');
    Route::post('/import', [PatientController::class, 'import'])->name('patients.import')->middleware('permission:patients.import');;
    Route::get('/imports/{import}', [PatientController::class, 'importStatus'])->name('patients.import.status');
    Route::get('/{patient}', [PatientController::class, 'show'])->name('patients.show')->whereNumber('patient');
    Route::post('/', [PatientController::class, 'store'])->name('patients.store')->middleware('permission:patients.create');;
    Route::patch('/{patient}', [PatientController::class, 'update'])->name('patients.update')->middleware('permission:patients.update');;
    Route::patch('/{patient}/student', [PatientController::class, 'updateStudent'])->name('patients.update.student');
    Route::patch('/{patient}/student-data', [PatientController::class, 'updateStudentData'])->name('patients.update.student-data')->middleware('permission:patients.updateHeaderData');
    Route::delete('/deactivate/{patient}', [PatientController::class, 'deactivate'])->name('patients.deactivate')->middleware('permission:patients.deactivate');
    Route::delete('/activate/{patient}', [PatientController::class, 'activate'])->name('patients.activate')->middleware('permission:patients.activate');
    Route::delete('/{patient}', [PatientController::class, 'destroy'])->name('patients.destroy')->middleware('permission:patients.delete');
    Route::get('/options/{clinic}', [PatientController::class, 'availablePatients'])->name('patients.availablePatients');
    Route::get('/{patient}/clinics/table', [PatientController::class, 'clinicsTable'])->name('patients.clinics.clinicsTable');
    Route::delete('/{patient}/clinics/{clinic}/remove-enrollment', [PatientController::class, 'removeEnrollment'])->name('patients.clinics.remove-enrollment')->middleware('patients.personal-page.removeEnrollmentClinic');
    Route::post('/clinics/{clinic}/enroll', [PatientController::class, 'enrollClinic'])->name('patients.clinics.enrollClinic')->middleware('patients.personal-page.enrollClinic');
    Route::post('/clinics/{clinic}/waiting-list', [PatientController::class, 'addToWaitingList'])->name('patients.clinics.addToWaitingList')->middleware('patients.personal-page.addToWaitingList');
    Route::get('/{patient}/available-clinics', [PatientController::class, 'availableClinics'])->name('patients.clinics.availableClinics');
    Route::get('/{patient}/schedules', [PatientController::class, 'schedules'])->name('patients.schedules');
    Route::get('/schedule/{patient}/clinics', [PatientController::class, 'getEnrolledClinics'])->name('patients.getEnrolledClinics');
    Route::get('/schedule/{clinic}/periods', [PatientController::class, 'getClinicPeriods'])->name('patients.getClinicPeriods');
    Route::get('/schedule/{patient}/students', [PatientController::class, 'getClinicStudents'])->name('patients.getClinicStudents');
})->middleware(['auth', 'verified']);

Route::prefix('users')->group(function () {
    Route::get('/', [UsersController::class, 'index'])->name('users.index')->middleware('permission:users.view');
    Route::get('/table', [UsersController::class, 'table'])->name('users.table');
    Route::get('/{user}', [UsersController::class, 'show'])->name('users.show');
    Route::post('/', [UsersController::class, 'store'])->name('users.store')->middleware('permission:users.create');
    Route::patch('/{user}', [UsersController::class, 'update'])->name('users.update')->middleware('permission:users.personal-page.updatePersonalData');
    Route::patch('/{user}/role', [UsersController::class, 'updateRole'])->name('users.update.role')->middleware('permission:users.personal-page.updateRole');
    Route::post('/resend-invite/{user}', [UsersController::class, 'resendInvite'])->name('users.resend-invite')->middleware('permission:users.invite');;
    Route::delete('/deactivate/{user}', [UsersController::class, 'deactivate'])->name('users.deactivate')->middleware('permission:users.deactivate');;
    Route::delete('/activate/{user}', [UsersController::class, 'activate'])->name('users.activate')->middleware('permission:users.personal-page.activate');
    Route::delete('/{user}', [UsersController::class, 'destroy'])->name('users.destroy')->middleware('permission:users.delete');
    Route::get('/logs', [ActionLogsController::class, 'index'])->name('action-logs.index')->middleware('permission:action-logs.view');
})->middleware(['auth', 'verified']);

Route::prefix('attendance')->name('attendance')->group(function () {
    Route::get('/clinics', [AttendanceController::class, 'clinics'])->name('attendance.index')->middleware('permission:attendance.view');
    Route::get('/clinics/table', [AttendanceController::class, 'clinicsTable'])->name('attendance.clinics.table');
    Route::get('/clinics/{clinic}', [AttendanceController::class, 'showClinic'])->name('attendance.showClinic');
    Route::get('/clinics/{clinic}/dates', [AttendanceController::class, 'getDates'])->name('attendance.dates');
    Route::get('/schedule-slots/{slot}/students', [AttendanceController::class, 'getStudents'])->name('attendance.students');
    Route::put('/schedule-slots/{slot}', [AttendanceController::class, 'updateAttendance'])->name('attendance.updateAttendance')->middleware('permission:attendance.update');
});

Route::prefix('appointments-confirmation')->name('appointments-confirmation.')->group(function () {
    Route::get('/', [AppointmentConfirmationController::class, 'index'])->name('appointments-confirmation.index')->middleware('permission:appointments-confirmation.view');
    Route::get('/list', [AppointmentConfirmationController::class, 'list'])->name('appointments-confirmation.list');
    Route::patch('/{appointment}/status', [AppointmentConfirmationController::class, 'updateStatus'])->name('appointments-confirmation.updateStatus')->middleware('permission:appointments-confirmation.update');
});

Route::prefix('clinics-management')->name('clinics-management.')->group(function () {
    Route::get('/', [ClinicManagementController::class, 'index'])->name('index')->middleware('permission:clinics-management.view');
    Route::get('/clinicsTable', [ClinicManagementController::class, 'clinicsTable'])->name('clinics-management.table');
    Route::get('/{clinic}', [ClinicManagementController::class, 'show'])->name('show');
    Route::get('/{clinic}/table', [ClinicManagementController::class, 'table'])->name('table');
    Route::post('/{clinic}/enroll', [ClinicManagementController::class, 'enroll'])->name('enroll')->middleware('permission:clinics-management.enrollClinic');
    Route::delete('/{clinic}/remove-enrollment/{patient}', [ClinicManagementController::class, 'removeEnrollment'])->name('remove-enrollment')->middleware('permission:clinics-management.removeEnrollmentClinic');
    Route::post('/{clinic}/waiting-list', [ClinicManagementController::class, 'storeWaitingList'])->name('storeWaitingList')->middleware('permission:clinics-management.addPatientToWaitingList');
});

Route::prefix('action-logs')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/users/{user}/table', [ActionLogsController::class, 'userTable'])->name('action-logs.users.table');
    Route::get('/students/{student}/table', [ActionLogsController::class, 'studentTable'])->name('action-logs.students.table');
    Route::get('/patients/{patient}/table', [ActionLogsController::class, 'patientTable'])->name('action-logs.patients.table');
    Route::get('/filters', [ActionLogsController::class, 'filters'])->name('action-logs.filters');
});

Route::prefix('invite')->group(function () {
    Route::get('/{token}', [UserInviteController::class, 'show'])->name('invite.show');
    Route::patch('/{token}', [UserInviteController::class, 'updateStudent'])->name('invite.updateStudent');
    Route::post('/{token}', [UserInviteController::class, 'store'])->name('invite.store');
});

require __DIR__ . '/settings.php';
