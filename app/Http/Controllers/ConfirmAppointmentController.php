<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ConfirmAppointmentController extends Controller
{
    public function index()
    {
        return Inertia::render('confirm-appointment/ConfirmAppointment');
    }
}
