<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiLog extends Model
{
    protected $fillable = [
        'patient_id', 'health_data_id', 'prediction_score', 'input_data', 'model_version'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function healthData()
    {
        return $this->belongsTo(HealthData::class);
    }
}