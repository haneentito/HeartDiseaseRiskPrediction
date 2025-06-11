<?php

namespace App\Http\Controllers;

use App\Models\AiLog;
use App\Models\HealthData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:patient');
    }

    public function index()
    {
        $patient = Auth::user();
        $aiLogs = $patient->aiLogs()->with('healthData')->get();
        return response()->json($aiLogs);
    }

    public function predict(Request $request)
    {
        $request->validate([
            'health_data_id' => 'required|exists:health_data,id',
        ]);

        $patient = Auth::user();
        $healthData = HealthData::where('id', $request->health_data_id)
            ->where('patient_id', $patient->id)
            ->first();

        if (!$healthData) {
            return response()->json(['error' => 'Health data not found'], 404);
        }

        $predictionScore = $this->mockPredict($healthData);

        $aiLog = AiLog::create([
            'patient_id' => $patient->id,
            'health_data_id' => $healthData->id,
            'prediction_score' => $predictionScore,
            'input_data' => json_encode($healthData->toArray()),
            'model_version' => '1.0.0',
        ]);

        $healthData->prediction_score = $predictionScore;
        $healthData->save();

        return response()->json(['message' => 'Prediction completed', 'data' => $aiLog], 201);
    }

    private function mockPredict($healthData)
    {
        $score = 0;
        if ($healthData->age > 50) $score += 20;
        if ($healthData->cholesterol > 200) $score += 20;
        if ($healthData->blood_pressure_systolic > 140) $score += 15;
        if ($healthData->smoking) $score += 15;
        if ($healthData->diabetes) $score += 15;
        if ($healthData->family_history) $score += 10;
        return min($score, 100);
    }
}