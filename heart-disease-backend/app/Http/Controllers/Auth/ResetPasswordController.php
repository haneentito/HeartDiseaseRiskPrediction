<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Notifications\ResetPasswordNotification;

class ResetPasswordController extends Controller
{
    public function sendDoctorResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $doctor = Doctor::where('email', $request->email)->first();
        if (!$doctor) {
            return response()->json(['error' => 'Email not found'], 404);
        }

        $token = Str::random(64);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        $doctor->notify(new ResetPasswordNotification($token, 'doctor'));

        return response()->json(['message' => 'Password reset link sent to your email']);
    }

    public function sendPatientResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'Email not found'], 404);
        }

        $token = Str::random(64);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        $user->notify(new ResetPasswordNotification($token, 'patient'));

        return response()->json(['message' => 'Password reset link sent to your email']);
    }

    public function resetDoctor(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $reset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return response()->json(['error' => 'Invalid token or email'], 400);
        }

        $doctor = Doctor::where('email', $request->email)->first();
        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }

        $doctor->password = Hash::make($request->password);
        $doctor->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successfully']);
    }

    public function resetPatient(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $reset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return response()->json(['error' => 'Invalid token or email'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successfully']);
    }
}