<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // Patient Registration
    public function patientRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:10|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|digits_between:10,12',
            'password' => 'required|min:6|regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.*\d).{6,}$/',
            'confirmPassword' => 'required|same:password',
        ], [
            'password.regex' => 'The password must contain at least one uppercase letter, one special character, and one digit.',
            'confirmPassword.same' => 'The confirm password must match the password.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $patient = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'patient',
        ]);

        return response()->json(['message' => 'Registration successful!'], 201);
    }

    // Patient Login
    public function patientLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6|regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.*\d).{6,}$/',
        ], [
            'password.regex' => 'The password must contain at least one uppercase letter, one special character, and one digit.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');
        $patient = User::where('email', $credentials['email'])->first();

        if (!$patient || !Hash::check($credentials['password'], $patient->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        if ($patient->role !== 'patient') {
            return response()->json(['error' => 'Unauthorized: Not a patient'], 403);
        }

        try {
            // Generate token using the 'api' guard
            $token = JWTAuth::guard('api')->fromUser($patient);
            return response()->json([
                'token' => $token,
                'user' => $patient
            ]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    // Doctor Registration
    public function doctorRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctorId' => 'required|unique:doctors,doctor_id|min:10',
            'name' => 'required|min:10|regex:/^[a-zA-Z\s.]+$/',
            'email' => 'required|email|unique:doctors,email',
            'phone' => 'required|digits_between:10,12',
            'password' => 'required|min:6|regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.*\d).{6,}$/',
            'confirmPassword' => 'required|same:password',
        ], [
            'password.regex' => 'The password must contain at least one uppercase letter, one special character, and one digit.',
            'confirmPassword.same' => 'The confirm password must match the password.',
        ]);

        if ($validator->fails()) {
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

        return response()->json(['message' => 'Doctor registration successful!'], 201);
    }

    // Doctor Login
    public function doctorLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctorId' => 'required|digits:14',
            'email' => 'required|email',
            'password' => 'required|min:6|regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.*\d).{6,}$/',
        ], [
            'password.regex' => 'The password must contain at least one uppercase letter, one special character, and one digit.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $credentials = $request->only('doctorId', 'email', 'password');
        $doctor = Doctor::where('doctor_id', $credentials['doctorId'])
                        ->where('email', $credentials['email'])
                        ->first();

        if (!$doctor || !Hash::check($credentials['password'], $doctor->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        try {
            $token = JWTAuth::guard('doctor')->fromUser($doctor);
            return response()->json([
                'token' => $token,
                'user' => $doctor
            ]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }
}