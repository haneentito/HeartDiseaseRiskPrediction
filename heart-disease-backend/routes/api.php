<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PredictController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;
use Tymon\JWTAuth\Facades\JWTAuth;

// Public routes (no authentication required)
Route::middleware('api')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/doctor/register', [DoctorController::class, 'register']);
    Route::post('/doctor/login', [DoctorController::class, 'login']);
    Route::get('/doctors', [DoctorController::class, 'index']);
    Route::get('/doctors/{id}', [DoctorController::class, 'show']);
    Route::get('/doctors/{id}/schedule', [DoctorController::class, 'publicSchedule']);
    Route::post('/predict', [PredictController::class, 'predict']);
    Route::post('/appointments', [DoctorController::class, 'createPublicAppointment']);

    // Password reset routes for doctors
    Route::post('/doctor/password/reset-request', [ResetPasswordController::class, 'sendDoctorResetLinkEmail'])->name('doctor.password.email');
    Route::post('/doctor/password/reset', [ResetPasswordController::class, 'resetDoctor'])->name('doctor.password.update');

    // Password reset routes for patients
    Route::post('/password/reset-request', [ResetPasswordController::class, 'sendPatientResetLinkEmail'])->name('patient.password.email');
    Route::post('/password/reset', [ResetPasswordController::class, 'resetPatient'])->name('patient.password.update');

    // Contact route (remove the /api prefix here)
    Route::post('/contact', [ContactController::class, 'store']);
});

// Patient authenticated routes
Route::middleware('auth:api')->group(function () {
    Route::post('/doctors/rate', [DoctorController::class, 'rate']);
    Route::get('/patient/profile', [PatientController::class, 'profile']);
    Route::get('/patient/health-data', [PatientController::class, 'healthData']);
    Route::post('/patient/health-data', [PatientController::class, 'updateHealthData']);
    Route::get('/patient/appointments', [PatientController::class, 'appointments']);
    Route::post('/patient/storeAppointment', [AppointmentController::class, 'store']);
    Route::post('/doctors/{id}/appointments', [DoctorController::class, 'storeAppointment']);
    Route::get('/profile', [UserController::class, 'profile']);
});

// Doctor authenticated routes
Route::middleware('auth:doctor')->group(function () {
    Route::get('/doctor/notifications', [DoctorController::class, 'notifications']);
    Route::get('/doctor/appointments', [DoctorController::class, 'appointments']);
    Route::post('/doctor/appointments/{id}/accept', [DoctorController::class, 'acceptAppointment']);
    Route::post('/doctor/appointments/{id}/decline', [DoctorController::class, 'declineAppointment']);
    Route::get('/doctor/profile', [DoctorController::class, 'profile']);
    Route::get('/doctor/schedule', [DoctorController::class, 'schedule']);
    Route::post('/doctor/schedule', [DoctorController::class, 'createSchedule']);
    Route::delete('/doctor/schedule/{id}', [DoctorController::class, 'deleteSchedule']);
    Route::put('/doctor/schedule/{id}', [DoctorController::class, 'updateSchedule']);
});