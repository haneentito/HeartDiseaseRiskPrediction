<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\User;
use App\Models\Notification;
use App\Models\Appointment;
use App\Models\Schedule;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

/**
 * Class DoctorController
 * Handles doctor-related operations such as registration, login, profile management,
 * schedule management, appointments, and notifications.
 */
class DoctorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:doctor')->except(['register', 'login', 'index', 'show', 'publicSchedule', 'createPublicAppointment', 'rate']);
    }

    // === Authentication and Registration Section ===

    /**
     * Registers a new doctor by validating input data 
     * and creating a new doctor record in the database.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
    'doctorId' => 'required|unique:doctors,doctor_id|digits:14', // Updated to digits:14
    'name' => 'required|min:5|regex:/^[a-zA-Z\s]+$/',
    'email' => 'required|email|unique:doctors,email',
    'phone' => 'required|digits_between:10,12',
    'password' => [
        'required',
        'min:6',
        'regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*\d).{6,}$/'
    ],
    'confirmPassword' => 'required|same:password', // Added
], [
    'password.regex' => 'The password must include at least one uppercase letter, one special character, and one number.',
    'confirmPassword.same' => 'The confirm password must match the password.', // Added
]);

        if ($validator->fails()) {
            Log::warning('Doctor registration validation failed: ' . json_encode($validator->errors()));
            return response()->json(['error' => $validator->errors()], 422);
        }

        $doctor = Doctor::create([
            'doctor_id' => $request->doctorId,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'doctor',
            'rating' => 0,
            'views' => 0,
        ]);
        try {
    $token = JWTAuth::fromUser($doctor);
    Log::info('Doctor registered and logged in successfully: ' . $doctor->doctor_id);
    return response()->json([
        'message' => 'Doctor registration successful!',
        'token' => $token,
        'user' => [
            'id' => $doctor->id,
            'doctor_id' => $doctor->doctor_id,
            'name' => $doctor->name,
            'email' => $doctor->email,
            'phone' => $doctor->phone,
            'role' => $doctor->role,
            'rating' => (int) $doctor->rating,
            'views' => (int) $doctor->views,
        ],
    ], 201);
} catch (JWTException $e) {
    Log::error('JWT token creation failed: ' . $e->getMessage());
    return response()->json(['message' => 'Doctor registration successful, but auto-login failed.'], 201);
}

    }

    /**
     * Logs in a doctor by validating credentials 
     * and generating a JWT token upon successful authentication.
     */
    public function login(Request $request)
    {
        try {
            Log::info('Starting doctor login for request: ' . json_encode($request->all()));

            $validator = Validator::make($request->all(), [
                'doctorId' => 'required|digits:14',
                'email' => 'required|email',
                'password' => [
                    'required',
                    'min:6',
                    'regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*\d).{6,}$/'
                ],
            ], [
                'password.regex' => 'The password must include at least one uppercase letter, one special character, and one digit.',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed: ' . json_encode($validator->errors()));
                return response()->json(['error' => $validator->errors()], 422);
            }

            $credentials = $request->only('doctorId', 'email', 'password');
            $doctor = Doctor::where('doctor_id', $credentials['doctorId'])
                            ->where('email', $credentials['email'])
                            ->first();

            if (!$doctor || !Hash::check($credentials['password'], $doctor->password)) {
                Log::warning('Invalid credentials for doctorId: ' . $credentials['doctorId'] . ', email: ' . $credentials['email']);
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            try {
                $token = auth('doctor')->login($doctor);
                Log::info('Doctor logged in successfully: ' . $doctor->doctor_id);
                return response()->json([
                    'token' => $token,
                    'user' => [
                        'id' => $doctor->id,
                        'doctor_id' => $doctor->doctor_id,
                        'name' => $doctor->name,
                        'email' => $doctor->email,
                        'phone' => $doctor->phone,
                        'role' => $doctor->role,
                        'rating' => $doctor->rating,
                        'views' => $doctor->views,
                    ],
                ], 200);
            } catch (JWTException $e) {
                Log::error('JWT token creation failed: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
                return response()->json(['error' => 'Could not create token'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Doctor login failed: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            return response()->json(['error' => 'Login failed'], 500);
        }
    }

    /**
     * Requests a password reset link by validating the email 
     * and sending a reset link to the doctor's email.
     */

    public function requestPasswordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:doctors,email',
        ], [
            'email.required' => 'The email field is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.exists' => 'This email is not registered as a doctor.'
        ]);

        if ($validator->fails()) {
            Log::warning('Password reset request validation failed for email: ' . $request->email . ', Errors: ' . json_encode($validator->errors()));
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $status = Password::broker('doctors')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            Log::info('Password reset link sent to email: ' . $request->email);
            return response()->json(['message' => 'Password reset link sent to your email.'], 200);
        }

        Log::error('Failed to send password reset link for email: ' . $request->email . ', Status: ' . $status);
        return response()->json(['error' => 'Unable to send reset link. Please try again later.'], 500);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:doctors,email',
            'token' => 'required|string',
            'password' => 'required|min:6|confirmed',
        ], [
            'email.required' => 'The email field is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.exists' => 'This email is not registered as a doctor.',
            'token.required' => 'The reset token is required.',
            'password.required' => 'The new password is required.',
            'password.min' => 'The password must be at least 6 characters.',
            'password.confirmed' => 'The password confirmation does not match.'
        ]);

        if ($validator->fails()) {
            Log::warning('Password reset validation failed for email: ' . $request->email . ', Errors: ' . json_encode($validator->errors()));
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $status = Password::broker('doctors')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
                Log::info('Password updated for doctor with email: ' . $user->email);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            Log::info('Password reset successfully for email: ' . $request->email);
            return response()->json(['message' => 'Password reset successfully.'], 200);
        }

        Log::error('Failed to reset password for email: ' . $request->email . ', Status: ' . $status);
        return response()->json(['error' => 'Unable to reset password. The token may be invalid or expired.'], 400);
    }

    // === Profile and Schedule Management Section ===

    /**
     * Fetches the authenticated doctor's profile data.
     */
    public function profile(Request $request)
    {
        $doctor = auth('doctor')->user();
        if (!$doctor) {
            Log::warning('Unauthorized access attempt to doctor profile');
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        Log::info('Doctor profile fetched: ' . $doctor->doctor_id);
        return response()->json([
            'id' => $doctor->id,
            'doctor_id' => $doctor->doctor_id,
            'name' => $doctor->name,
            'email' => $doctor->email,
            'phone' => $doctor->phone,
            'role' => $doctor->role,
            'rating' => (int) $doctor->rating,
            'views' => (int) $doctor->views,
        ], 200);
    }

    /**
     * Rate a doctor by a patient.
     */
   public function rate(Request $request)
    {
        try {
            Log::info('Starting rate method', ['request' => $request->all(), 'token' => $request->header('Authorization')]);
            $patient = Auth::guard('api')->user();
            Log::info('Authenticated patient', ['patient_id' => $patient ? $patient->id : 'null']);
            if (!$patient) {
                Log::error('No authenticated patient found');
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $validator = Validator::make($request->all(), [
                'doctor_id' => 'required|string|exists:doctors,doctor_id',
                'rating' => 'required|integer|min:1|max:5',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed', ['errors' => $validator->errors()]);
                return response()->json(['error' => $validator->errors()], 422);
            }

            if ($patient->id != $request->patient_id) {
                Log::error('Patient ID mismatch', ['patient_id' => $patient->id, 'request_patient_id' => $request->patient_id]);
                return response()->json(['error' => 'Unauthorized: Patient ID mismatch'], 403);
            }

            $doctor = Doctor::where('doctor_id', $request->doctor_id)->first();
            if (!$doctor) {
                Log::error('Doctor not found', ['doctor_id' => $request->doctor_id]);
                return response()->json(['error' => 'Doctor not found'], 404);
            }

            // Check for existing rating by this patient for this doctor
            $existingRating = Rating::where('doctor_id', $request->doctor_id)
                                  ->where('patient_id', $patient->id)
                                  ->first();

            if ($existingRating) {
                // Update existing rating
                $existingRating->rating = $request->rating;
                $existingRating->save();
                Log::info('Updated existing rating for doctor', ['doctor_id' => $request->doctor_id, 'patient_id' => $patient->id, 'rating' => $request->rating]);
            } else {
                // Create new rating
                Rating::create([
                    'doctor_id' => $request->doctor_id,
                    'patient_id' => $patient->id,
                    'rating' => $request->rating,
                ]);
                Log::info('Created new rating for doctor', ['doctor_id' => $request->doctor_id, 'patient_id' => $patient->id, 'rating' => $request->rating]);
            }

            // Recalculate average rating
            $averageRating = Rating::where('doctor_id', $request->doctor_id)->avg('rating');
            $doctor->rating = round($averageRating, 1);
            $doctor->save();
            Log::info('Updated doctor average rating', ['doctor_id' => $request->doctor_id, 'average_rating' => $doctor->rating]);

            return response()->json(['message' => 'Rating submitted successfully']);
        } catch (\Exception $e) {
            Log::error('Error in rate method: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
    /**
     * Fetches the authenticated doctor's schedule.
     */
    public function schedule()
    {
        $doctor = auth('doctor')->user();
        if (!$doctor) {
            Log::warning('Unauthorized access attempt to doctor schedule');
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        $schedules = Schedule::where('doctor_id', $doctor->doctor_id)
            ->select('id', 'doctor_id', 'day', 'time')
            ->get();

        Log::info('Doctor schedule fetched: ' . $doctor->doctor_id);
        return response()->json($schedules, 200);
    }

    /**
     * Creates a new schedule slot for the authenticated doctor.
     */
    public function createSchedule(Request $request)
    {
        // Authenticate the doctor
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor) {
            Log::warning('Unauthorized attempt to create schedule slot');
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'time' => 'required|date_format:H:i', // Ensures "HH:MM" format (e.g., "07:08")
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for creating schedule slot: ' . json_encode($validator->errors()));
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Normalize the time format explicitly (redundant due to validation, but ensures clarity)
        $time = date('H:i', strtotime($request->time));

        // Check if the slot already exists for the doctor
        $existingSlot = Schedule::where('doctor_id', $doctor->doctor_id)
            ->where('day', $request->day)
            ->where('time', $time)
            ->first();

        if ($existingSlot) {
            Log::warning('Duplicate schedule slot attempt: ' . $doctor->doctor_id . ', ' . $request->day . ', ' . $time);
            return response()->json(['error' => 'This time slot already exists for the selected day'], 422);
        }

        // Create the schedule slot
        $schedule = Schedule::create([
            'doctor_id' => $doctor->doctor_id,
            'day' => $request->day,
            'time' => $time,
            'available' => true,
        ]);

        Log::info('Schedule slot created for doctor: ' . $doctor->doctor_id);
        return response()->json([
            'id' => $schedule->id,
            'doctor_id' => $schedule->doctor_id,
            'day' => $schedule->day,
            'time' => $schedule->time,
        ], 201);
    }

    /**
     * Updates an existing schedule slot for the authenticated doctor.
     */
    public function updateSchedule(Request $request, $id)
    {
        $doctor = auth('doctor')->user();
        if (!$doctor) {
            Log::warning('Unauthorized attempt to update schedule slot');
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for updating schedule slot: ' . json_encode($validator->errors()));
            return response()->json(['error' => $validator->errors()], 422);
        }

        $slot = Schedule::where('id', $id)->where('doctor_id', $doctor->doctor_id)->first();
        if (!$slot) {
            Log::warning('Schedule slot not found or unauthorized: ' . $id . ', Doctor: ' . $doctor->doctor_id);
            return response()->json(['error' => 'Slot not found or not authorized'], 404);
        }

        // Check for duplicate slots (excluding the current slot being updated)
        $existingSlot = Schedule::where('doctor_id', $doctor->doctor_id)
            ->where('day', $request->day)
            ->where('time', $request->time)
            ->where('id', '!=', $id)
            ->first();

        if ($existingSlot) {
            Log::warning('Duplicate schedule slot attempt during update: ' . $doctor->doctor_id . ', ' . $request->day . ', ' . $request->time);
            return response()->json(['error' => 'This time slot already exists for the selected day'], 422);
        }

        $slot->update([
            'day' => $request->day,
            'time' => $request->time,
        ]);

        Log::info('Schedule slot updated for doctor: ' . $doctor->doctor_id . ', Slot ID: ' . $id);
        return response()->json([
            'id' => $slot->id,
            'doctor_id' => $slot->doctor_id,
            'day' => $slot->day,
            'time' => $slot->time,
        ], 200);
    }

    /**
     * Deletes a schedule slot for the authenticated doctor.
     */
    public function deleteSchedule($id)
    {
        $doctor = auth('doctor')->user();
        if (!$doctor) {
            Log::warning('Unauthorized attempt to delete schedule slot');
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        $slot = Schedule::where('id', $id)->where('doctor_id', $doctor->doctor_id)->first();
        if (!$slot) {
            Log::warning('Schedule slot not found or unauthorized for deletion: ' . $id . ', Doctor: ' . $doctor->doctor_id);
            return response()->json(['error' => 'Slot not found or not authorized'], 404);
        }

        $slot->delete();
        Log::info('Schedule slot deleted for doctor: ' . $doctor->doctor_id . ', Slot ID: ' . $id);
        return response()->json(null, 204);
    }

    // === Notification and Appointment Management Section ===

    /**
 * Fetches notifications for the authenticated doctor.
 */
public function notifications()
    {
        $token = request()->header('Authorization');
        Log::info('Received token for notifications: ' . $token);
        $doctor = auth('doctor')->user();
        Log::info('Authenticated doctor: ' . ($doctor ? $doctor->doctor_id : 'null'));
        if (!$doctor) {
            Log::warning('Unauthorized access attempt to notifications');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            Log::info('Fetching notifications for doctor: ' . $doctor->doctor_id);
            $notifications = Notification::where('doctor_id', $doctor->doctor_id)
                ->where('read', false) // Only fetch unread notifications
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'message' => $notification->message, // Use message field
                        'appointment_id' => $notification->appointment_id, // Include appointment_id
                    ];
                })->all();

            Log::info('Notifications fetched: ' . json_encode($notifications));
            return response()->json($notifications, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching notifications for doctor ' . $doctor->doctor_id . ': ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Fetches appointments for the authenticated doctor.
     */
    public function appointments()
    {
        $doctor = auth('doctor')->user();
        if (!$doctor) {
            Log::warning('Unauthorized access attempt to appointments');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $appointments = Appointment::where('appointments.doctor_id', $doctor->doctor_id)
            ->join('users', 'appointments.patient_id', '=', 'users.id')
            ->select('appointments.*', 'users.name as patient_name')
            ->get();

        Log::info('Appointments fetched for doctor: ' . $doctor->doctor_id);
        return response()->json($appointments, 200);
    }

    /**
     * Accepts an appointment by updating its status.
     */
    public function acceptAppointment(Request $request, $id)
    {
        $doctor = auth('doctor')->user();
        if (!$doctor) {
            Log::warning('Unauthorized attempt to accept appointment');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $appointment = Appointment::where('doctor_id', $doctor->doctor_id)->find($id);
        if (!$appointment) {
            Log::warning('Appointment not found for acceptance: ' . $id . ', Doctor: ' . $doctor->doctor_id);
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        $appointment->update(['status' => 'accepted']);
        Notification::where('appointment_id', $id)->delete();

        Log::info('Appointment accepted: ' . $id . ', Doctor: ' . $doctor->doctor_id);
        return response()->json(['message' => 'Appointment accepted'], 200);
    }

    /**
     * Declines an appointment by updating its status.
     */
    public function declineAppointment(Request $request, $id)
    {
        $doctor = auth('doctor')->user();
        if (!$doctor) {
            Log::warning('Unauthorized attempt to decline appointment');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $appointment = Appointment::where('doctor_id', $doctor->doctor_id)->find($id);
        if (!$appointment) {
            Log::warning('Appointment not found for declination: ' . $id . ', Doctor: ' . $doctor->doctor_id);
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        $appointment->update(['status' => 'declined']);
        Notification::where('appointment_id', $id)->delete();

        Log::info('Appointment declined: ' . $id . ', Doctor: ' . $doctor->doctor_id);
        return response()->json(['message' => 'Appointment declined'], 200);
    }

    // === Public Data Section ===

    /**
     * Fetches a list of all doctors for public access.
     */
    public function index()
{
    try {
        $doctors = Doctor::select('doctor_id', 'name', 'role', 'phone', 'email', 'rating', 'views', 'price', 'languages')
            ->get()
            ->map(function ($doctor) {
                // Calculate average rating from ratings table
                $averageRating = Rating::where('doctor_id', $doctor->doctor_id)->avg('rating') ?? 0;
                return [
                    'id' => $doctor->doctor_id,
                    'name' => $doctor->name,
                    'role' => $doctor->role,
                    'price' => $doctor->price ?? 100,
                    'languages' => $doctor->languages ?? 'English',
                    'rating' => (int) round($averageRating), // Use averaged rating instead of raw rating
                    'views' => (int) $doctor->views,
                ];
            });

        Log::info('Doctors list fetched successfully');
        return response()->json($doctors, 200);
    } catch (\Exception $e) {
        Log::error('Error fetching doctors: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
        return response()->json(['error' => 'Internal Server Error'], 500);
    }
}

    /**
     * Shows details of a specific doctor by ID for public access.
     */
    public function show($id)
{
    try {
        $doctor = Doctor::where('doctor_id', $id)->first();
        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }

        // Increment views
        $doctor->increment('views');

        return response()->json([
            'id' => $doctor->doctor_id,
            'name' => $doctor->name,
            'role' => $doctor->role,
            'price' => $doctor->price ?? 100,
            'languages' => $doctor->languages ?? 'English',
            'rating' => $doctor->rating,
            'views' => $doctor->views,
            'email' => $doctor->email,
            'phone' => $doctor->phone,
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error fetching doctor: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
        return response()->json(['error' => 'Internal Server Error'], 500);
    }
}

    /**
     * Fetches a public schedule for a specific doctor by ID.
     */
    public function publicSchedule($id)
{
    try {
        Log::info('Attempting to fetch doctor with ID: ' . $id);
        $doctor = Doctor::find($id);
        if (!$doctor) {
            Log::warning('Doctor not found for public schedule: ' . $id);
            return response()->json(['error' => 'Doctor not found'], 404);
        }

        Log::info('Doctor found: ' . $doctor->id);
        Log::info('Fetching schedules for doctor: ' . $id);
        $schedules = Schedule::where('doctor_id', $id)
            ->select('id', 'doctor_id', 'day', 'time')
            ->get();

        Log::info('Schedules fetched: ' . $schedules->count() . ' entries');
        $schedulesWithAvailability = $schedules->map(function ($schedule) {
            Log::info('Checking availability for schedule: ' . $schedule->id . ', day: ' . $schedule->day . ', time: ' . $schedule->time);
            $isBooked = Appointment::where('doctor_id', $schedule->doctor_id)
                ->where('day', $schedule->day)
                ->where('time', $schedule->time)
                ->where('status', 'accepted')
                ->exists();
            $schedule->available = !$isBooked;
            return $schedule;
        });

        Log::info('Public schedule fetched for doctor: ' . $id);
        return response()->json($schedulesWithAvailability, 200);
    } catch (\Exception $e) {
        Log::error('Error fetching public schedule for doctor ' . $id . ': ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
        return response()->json(['error' => 'Internal Server Error', 'message' => $e->getMessage()], 500);
    }
}

    /**
     * Creates a new public appointment by a patient.
     */
    public function createPublicAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'patient_id' => 'required|exists:users,id',
            'day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for public appointment: ' . json_encode($validator->errors()));
            return response()->json(['error' => $validator->errors()], 422);
        }

        $scheduleExists = Schedule::where('doctor_id', $request->doctor_id)
            ->where('day', $request->day)
            ->where('time', $request->time)
            ->where('available', true)
            ->exists();

        if (!$scheduleExists) {
            Log::warning('Selected time slot not available: ' . $request->doctor_id . ', ' . $request->day . ', ' . $request->time);
            return response()->json(['error' => 'Selected time slot is not available for this doctor'], 422);
        }

        $appointmentExists = Appointment::where('doctor_id', $request->doctor_id)
            ->where('day', $request->day)
            ->where('time', $request->time)
            ->where('status', 'accepted')
            ->exists();

        if ($appointmentExists) {
            Log::warning('Time slot already booked: ' . $request->doctor_id . ', ' . $request->day . ', ' . $request->time);
            return response()->json(['error' => 'Selected time slot is already booked'], 422);
        }

        $appointment = Appointment::create([
            'doctor_id' => $request->doctor_id,
            'patient_id' => $request->patient_id,
            'day' => $request->day,
            'time' => $request->time,
            'status' => 'pending',
        ]);

        // Fetch the patient's name dynamically
        $patient = User::find($request->patient_id);
        $notificationMessage = "New appointment request from " . ($patient ? $patient->name : "Unknown Patient (ID: {$request->patient_id})");

        Notification::create([
            'doctor_id' => $request->doctor_id,
            'appointment_id' => $appointment->id,
            'message' => $notificationMessage,
            'read' => false,
        ]);

        Log::info('Public appointment created: ' . $appointment->id);
        return response()->json(['message' => 'Appointment created successfully'], 201);
    }

    /**
     * Creates a new appointment by the authenticated doctor.
     */
    public function storeAppointment(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($user->role !== 'patient') {
            return response()->json(['error' => 'Please log in as a patient to book an appointment'], 403);
        }

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:users,id',
            'day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        if ($user->id != $request->patient_id) {
            return response()->json(['error' => 'Unauthorized: Patient ID mismatch'], 403);
        }

        $doctor = Doctor::where('doctor_id', $id)->first();
        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }

        $scheduleExists = Schedule::where('doctor_id', $doctor->id)
            ->where('day', $request->day)
            ->where('time', $request->time)
            ->where('available', true)
            ->exists();

        if (!$scheduleExists) {
            return response()->json(['error' => 'Selected time slot is not available'], 422);
        }

        $appointmentExists = Appointment::where('doctor_id', $doctor->id)
            ->where('day', $request->day)
            ->where('time', $request->time)
            ->where('status', 'accepted')
            ->exists();

        if ($appointmentExists) {
            return response()->json(['error' => 'Selected time slot is already booked'], 422);
        }

        $schedule = Schedule::where('doctor_id', $doctor->id)
            ->where('day', $request->day)
            ->where('time', $request->time)
            ->first();
        $schedule->available = false;
        $schedule->save();

        $appointment = Appointment::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $user->id,
            'day' => $request->day,
            'time' => $request->time,
            'status' => 'pending',
        ]);

        // Fetch the patient's name dynamically
        $patient = User::find($user->id);
        $notificationMessage = "New appointment request from " . ($patient ? $patient->name : "Unknown Patient (ID: {$user->id})");

        Notification::create([
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'message' => $notificationMessage,
            'read' => false,
        ]);

        return response()->json(['message' => 'Appointment created successfully'], 201);
    }
}
    