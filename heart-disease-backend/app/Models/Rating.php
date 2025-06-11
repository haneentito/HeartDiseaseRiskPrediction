<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = ['doctor_id', 'patient_id', 'rating'];

    // Define relationships
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id', 'id'); 
    }
}