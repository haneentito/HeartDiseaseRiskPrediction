<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Schedule;
use App\Models\Notification;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function store(Request $request)
    {
        Log::info('Received appointment request: ' . json_encode($request->all()));

        // Validate request data with correct table references
        $request->validate([
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'patient_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:schedules,id',
            'day' => 'required|string|in:Sunday,Monday,Tuesday,Wednesday,Thursday',
            'time' => 'required|string|date_format:H:i',
        ]);

        $patient = Auth::user();
        Log::info('Authenticated user: ' . json_encode($patient));
        if (!$patient || $patient->id != $request->patient_id || $patient->role !== 'patient') {
            Log::warning('Unauthorized patient ID or role mismatch: Auth user ID: ' . ($patient ? $patient->id : 'null') . ', Request patient_id: ' . $request->patient_id . ', Role: ' . ($patient ? $patient->role : 'null'));
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Fetch doctor to ensure it exists
        $doctor = Doctor::find($request->doctor_id);
        Log::info('Doctor found: ' . json_encode($doctor));
        if (!$doctor) {
            Log::warning('Doctor not found: ' . $request->doctor_id);
            return response()->json(['error' => 'Invalid doctor'], 404);
        }

        // Find available schedule
        $schedule = Schedule::where('doctor_id', $request->doctor_id)
            ->where('id', $request->schedule_id)
            ->where('day', $request->day)
            ->where('time', $request->time)
            ->where('available', true)
            ->first();
        Log::info('Schedule query result: ' . json_encode($schedule));
        if (!$schedule) {
            Log::warning('Slot not available or schedule_id mismatch: ' . $request->doctor_id . ', ' . $request->schedule_id . ', ' . $request->day . ', ' . $request->time);
            return response()->json(['error' => 'Slot not available or invalid schedule'], 400);
        }

        // Create appointment
        $appointment = Appointment::create([
            'doctor_id' => $request->doctor_id,
            'patient_id' => $request->patient_id,
            'schedule_id' => $request->schedule_id,
            'day' => $request->day,
            'time' => $request->time,
            'status' => 'pending',
            'booked_at' => now(),
        ]);
        Log::info('Appointment created: ' . json_encode($appointment));

        // Update schedule availability
        $schedule->available = false;
        $schedule->save();
        Log::info('Schedule updated: ' . json_encode($schedule));

        // Create notification
        try {
            Notification::create([
                'doctor_id' => $request->doctor_id,
                'appointment_id' => $appointment->id,
                'message' => "New appointment request from {$patient->name}",
                'read' => false,
            ]);
            Log::info('Notification created for appointment: ' . $appointment->id);
        } catch (\Exception $e) {
            Log::error('Failed to create notification: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
        }

        Log::info('Appointment created successfully: ' . $appointment->id);
        return response()->json(['message' => 'Appointment request submitted', 'appointment' => $appointment], 201);
    }
}