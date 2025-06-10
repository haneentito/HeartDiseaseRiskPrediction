<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    public function getDoctorSchedule($doctorId)
    {
        $doctor = Doctor::find($doctorId);
        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }

        $schedule = $doctor->schedules()->get();
        return response()->json($schedule);
    }
}
