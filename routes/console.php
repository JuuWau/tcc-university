<?php

use App\Actions\Appointments\MarkPastScheduledAppointmentsAsNoShowAction;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    app(MarkPastScheduledAppointmentsAsNoShowAction::class)->execute();
})->dailyAt('00:00');