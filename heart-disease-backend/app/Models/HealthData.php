<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthData extends Model
{
    protected $table = 'health_data';
    protected $fillable = [
        'user_id', 'age', 'sex', 'chest_pain_type', 'resting_bp', 'cholestoral',
        'fasting_bs', 'resting_ecg', 'max_heart_rate', 'exercise_angina', 'oldpeak',
        'slope', 'num_vessels', 'thal', 'prediction'
    ];

    protected $casts = [
        'sex' => 'string',
        'chest_pain_type' => 'string',
        'fasting_bs' => 'string',
        'resting_ecg' => 'string',
        'exercise_angina' => 'string',
        'slope' => 'string',
        'num_vessels' => 'string',
        'thal' => 'string',
        'oldpeak' => 'float',
    ];
}