<?php

namespace App\Http\Controllers;

use App\Models\Doctor;

class DoctorPublicController extends Controller
{
    public function show($id)
    {
        $doctor = Doctor::find($id);
        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }
        return response()->json($doctor);
    }

    public function index()
    {
        $doctors = Doctor::all();
        return response()->json($doctors);
    }
}