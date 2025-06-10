<?php

namespace App\Http\Controllers;

use App\Models\HealthData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PredictController extends Controller
{
    public function predict(Request $request)
    {
        Log::info('Incoming request data:', $request->all());

        $validatedData = $request->validate([
            'age' => 'required|in:0,1',
            'gender' => 'required|in:0,1',
            'chest_pain_type' => 'required|in:0,1,2,3',
            'fasting_bs' => 'required|in:0,1',
            'rest_ecg' => 'required|in:0,1,2',
            'exercise_angina' => 'required|in:0,1',
            'ex_slope' => 'required|in:0,1,2',
            'num_major_vessels' => 'required|in:0,1,2,3,4',
            'thal' => 'required|in:0,1,2',
            'cholesterol' => 'required|in:0,1,2',
            'heart_rate' => 'required|in:0,1',
            'model' => 'required|in:log,mlp,svc,nb',
        ]);

        Log::info('Validated data:', $validatedData);

        // Align with columns.pkl order: ['age', 'chest_pain_type', 'cholesterol', 'ex_slope', 'exercise_angina', 'fasting_bs', 'gender', 'heart_rate', 'num_major_vessels', 'rest_ecg']
        $mlData = [
            'age' => (int) $validatedData['age'],
            'chest_pain_type' => (int) $validatedData['chest_pain_type'],
            'cholesterol' => (int) $validatedData['cholesterol'],
            'ex_slope' => (int) $validatedData['ex_slope'],
            'exercise_angina' => (int) $validatedData['exercise_angina'],
            'fasting_bs' => (int) $validatedData['fasting_bs'],
            'gender' => (int) $validatedData['gender'],
            'heart_rate' => (int) $validatedData['heart_rate'],
            'num_major_vessels' => (int) $validatedData['num_major_vessels'],
            'rest_ecg' => (int) $validatedData['rest_ecg'],
            'model' => $validatedData['model'], // Additional parameter for Flask API
        ];

        Log::info('Data sent to Flask API:', $mlData);

        try {
            $response = Http::timeout(10)->post('http://127.0.0.1:5000/predict', $mlData);
            Log::info('Flask API response:', $response->json());

            if ($response->failed()) {
                Log::error('Flask API request failed:', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['error' => 'Failed to connect to ML model'], 500);
            }

            $result = $response->json();
            if (isset($result['error'])) {
                Log::error('Flask API error:', ['error' => $result['error']]);
                return response()->json(['error' => $result['error']], 400);
            }

            $prediction = $result['prediction'];
            $predictionLabel = $prediction === 1 ? 'Positive for Heart Disease' : 'Negative for Heart Disease';
        } catch (\Exception $e) {
            Log::error('Error communicating with ML model:', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Error communicating with ML model: ' . $e->getMessage()], 500);
        }

        $dataToSave = [
            'user_id' => auth()->id() ?? null,
            'age' => $request->input('age'),
            'sex' => (string) $validatedData['gender'],
            'chest_pain_type' => (string) $validatedData['chest_pain_type'],
            'resting_bp' => $request->input('restingBP', 0),
            'cholestoral' => $request->input('cholestoral', 0),
            'fasting_bs' => (string) $validatedData['fasting_bs'],
            'resting_ecg' => (string) $validatedData['rest_ecg'],
            'max_heart_rate' => $request->input('maxHeartRate', 0),
            'exercise_angina' => (string) $validatedData['exercise_angina'],
            'oldpeak' => $request->input('oldpeak', 0),
            'slope' => (string) $validatedData['ex_slope'],
            'num_vessels' => (string) $validatedData['num_major_vessels'],
            'thal' => (string) $validatedData['thal'],
            'prediction' => $predictionLabel,
        ];

     $record = HealthData::create($dataToSave);
     Log::info('Database record created:', $record->toArray());

$responseData = ['prediction' => $prediction];
Log::info('Returning response:', $responseData);
return response()->json($responseData, 200);
}
}