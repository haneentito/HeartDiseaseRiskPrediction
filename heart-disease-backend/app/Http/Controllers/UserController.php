<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            Log::info('Starting validation for request: ' . json_encode($request->all()));

            $validated = $request->validate([
                'name' => 'required|min:10|regex:/^[a-zA-Z\s]+$/', 
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|digits_between:10,12',
                'password' => [
                    'required',
                    'min:6',
                    'regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*\d).{6,}$/'
                ],
            ], [
                'password.regex' => 'The password must include at least 1 uppercase letter, 1 special character, and 1 number.',
            ]);

            Log::info('Validation passed, creating user.');

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'patient',
            ]);

            Log::info('User created successfully: ' . $user->id);

            return response()->json(['message' => 'Registration successful!'], 201);
        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return response()->json(['error' => 'Registration failed: ' . $e->getMessage()], 422);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => [
                'required',
                'min:6',
                'regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*\d).{6,}$/'
            ],
        ], [
            'password.regex' => 'The password must include at least 1 uppercase letter, 1 special character, and 1 number.',
        ]);

        if ($validator->fails()) {
            Log::warning('Login validation failed for email: ' . $request->email . ' - Errors: ' . json_encode($validator->errors()));
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            Log::info('Starting login validation for request: ' . json_encode($request->all()));

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                Log::warning('Invalid credentials for email: ' . $request->email);
                return response()->json(['error' => 'Invalid email or password'], 401);
            }

            if ($user->role !== 'patient') {
                Log::warning('Unauthorized role for email: ' . $request->email . ', role: ' . $user->role);
                return response()->json(['error' => 'This login is for patients only'], 403);
            }

            try {
                // Set custom claims with a longer TTL (1 hour = 3600 seconds)
                $customClaims = ['exp' => now()->addSeconds(3600)->timestamp];
                $token = JWTAuth::claims($customClaims)->fromUser($user);
                Log::info('Token generated successfully for user: ' . $user->id . ' - Token: ' . substr($token, 0, 10) . '...');
                Log::info('User logged in successfully: ' . $user->id);

                return response()->json([
                    'message' => 'Login successful!',
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'role' => $user->role,
                    ],
                ], 200);
            } catch (JWTException $e) {
                Log::error('Token creation failed for email: ' . $request->email . ' - Error: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
                return response()->json(['error' => 'Could not create token'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Login failed for email: ' . $request->email . ' - Error: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            return response()->json(['error' => 'Login failed'], 500);
        }
    }

    public function profile(Request $request)
    {
        try {
            $user = auth('api')->user();
            Log::info('Profile accessed for user: ' . $user->id . ', Role: ' . $user->role);

            if (!($user instanceof \App\Models\User)) {
                throw new \Exception('Authenticated user is not a valid User model instance.');
            }

            return response()->json([
                'message' => 'Profile data retrieved successfully',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Profile access failed: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            return response()->json(['error' => 'Unauthorized or invalid token: ' . $e->getMessage()], 401);
        }
    }
}