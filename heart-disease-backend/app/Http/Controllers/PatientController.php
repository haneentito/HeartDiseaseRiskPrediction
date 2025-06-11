<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\HealthData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function profile()
    {
        return response()->json(Auth::user());
    }

    public function healthData()
    {
        $patient = Auth::user();
        $healthData = $patient->healthData()->latest()->get();
        return response()->json($healthData);
    }

    public function updateHealthData(Request $request)
    {
        $request->validate([
            'age' => 'nullable|integer|min:1',
            'gender' => 'nullable|in:male,female',
            'cholesterol' => 'nullable|integer|min:0',
            'blood_pressure_systolic' => 'nullable|integer|min:0',
            'blood_pressure_diastolic' => 'nullable|integer|min:0',
            'smoking' => 'nullable|boolean',
            'diabetes' => 'nullable|boolean',
            'bmi' => 'nullable|numeric|min:0',
            'heart_rate' => 'nullable|integer|min:0',
            'family_history' => 'nullable|boolean',
        ]);

        $patient = Auth::user();
        $healthData = $patient->healthData()->create($request->only([
            'age', 'gender', 'cholesterol', 'blood_pressure_systolic',
            'blood_pressure_diastolic', 'smoking', 'diabetes', 'bmi',
            'heart_rate', 'family_history'
        ]));

        return response()->json(['message' => 'Health data updated', 'data' => $healthData], 201);
    }

    public function appointments()
    {
        $patient = Auth::user();
        if (!$patient || $patient->role !== 'patient') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $appointments = Appointment::where('patient_id', $patient->id)
            ->select('id', 'doctor_id', 'day', 'time', 'status', 'booked_at')
            ->get();
        return response()->json($appointments);
    }

    public function storeAppointment(Request $request)
    {
        try {
            Log::info('Store appointment request received: ' . json_encode($request->all()));

            $patient = Auth::user();
            if (!$patient || $patient->role !== 'patient') {
                Log::warning('Unauthorized attempt to store appointment by user: ' . ($patient ? $patient->id : 'unknown'));
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Validate and cast IDs to integers
            $validated = $request->validate([
                'doctor_id' => 'required|string|exists:doctors,id',
                'patient_id' => 'required|string|exists:users,id',
                'schedule_id' => 'required|integer|exists:schedules,id',
                'day' => 'required|string|in:Sunday,Monday,Tuesday,Wednesday,Thursday',
                'time' => 'required|string|date_format:H:i', // Updated to date_format
            ]);

            $doctorId = (int)$validated['doctor_id'];
            $patientId = (int)$validated['patient_id'];
            $scheduleId = (int)$validated['schedule_id'];

            // Ensure patient_id matches the authenticated user
            if ($patientId !== $patient->id) {
                Log::warning('Patient ID mismatch: Request ' . $patientId . ', Authenticated ' . $patient->id);
                return response()->json(['error' => 'Patient ID does not match authenticated user'], 403);
            }

            // Check if the doctor exists
            $doctor = Doctor::find($doctorId);
            if (!$doctor) {
                Log::warning('Invalid doctor ID: ' . $doctorId);
                return response()->json(['error' => 'Invalid doctor'], 404);
            }

            // Create the appointment
            $appointment = Appointment::create([
                'doctor_id' => $doctorId,
                'patient_id' => $patientId,
                'schedule_id' => $scheduleId,
                'day' => $validated['day'],
                'time' => $validated['time'],
                'status' => 'pending',
                'booked_at' => now(),
            ]);

            Log::info('Appointment created successfully: ' . $appointment->id);
            return response()->json([
                'message' => 'Appointment created successfully',
                'appointment' => $appointment
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to store appointment: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            return response()->json(['error' => 'Failed to create appointment: ' . $e->getMessage()], 500);
        }
    }
}