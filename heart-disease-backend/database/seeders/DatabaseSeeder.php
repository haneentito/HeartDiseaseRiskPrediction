<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\HealthData;
use App\Models\Schedule;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\AiLog;
use App\Models\BlogPost;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create doctor for the doctor-home-profile page
        $doctor = Doctor::create([
            'doctor_id' => 'DOC001',
            'name' => 'Haneen Tito',
            'email' => 'haneen@gmail.com',
            'phone' => '111111',
            'password' => bcrypt('password123'),
            'role' => 'doctor',
            'rating' => 0,
            'views' => 0,
            'price' => '100',
            'languages' => 'English',
        ]);

        // Create schedules for the doctor
        Schedule::create(['doctor_id' => $doctor->doctor_id, 'day' => 'Friday', 'time' => '17:00']);
        Schedule::create(['doctor_id' => $doctor->doctor_id, 'day' => 'Tuesday', 'time' => '19:00']);
        Schedule::create(['doctor_id' => $doctor->doctor_id, 'day' => 'Wednesday', 'time' => '19:00']);
        Schedule::create(['doctor_id' => $doctor->doctor_id, 'day' => 'Thursday', 'time' => '19:00']);
        Schedule::create(['doctor_id' => $doctor->doctor_id, 'day' => 'Monday', 'time' => '18:00']);

        // Create additional doctors
        Doctor::factory(4)->create();

        // Create patients
        $patient = Patient::factory()->create([
            'name' => 'Ahmed Khaled',
            'email' => 'ahmed.khaled@example.com',
        ]);
        Patient::factory(9)->create();

        // Create health data for patients
        HealthData::factory()->create(['patient_id' => $patient->id]);
        HealthData::factory(9)->create();

        // Create appointments
        $schedule = Schedule::where('doctor_id', $doctor->doctor_id)->where('available', true)->first();
        if ($schedule) {
            Appointment::factory()->create([
                'doctor_id' => $doctor->doctor_id,
                'patient_id' => $patient->id,
                'day' => $schedule->day,
                'time' => $schedule->time,
            ]);
        }
        Appointment::factory(9)->create();

        // Create notifications
        Notification::factory()->create([
            'appointment_id' => 1,
            'message' => 'New appointment request from Ahmed Khaled',
        ]);
        Notification::factory(9)->create();

        // Create AI logs
        $healthData = HealthData::first();
        AiLog::factory()->create([
            'patient_id' => $patient->id,
            'health_data_id' => $healthData->id,
        ]);
        AiLog::factory(9)->create();

        // Create blog posts
        BlogPost::factory()->create([
            'author_id' => $doctor->doctor_id,
            'author_type' => 'doctor',
        ]);
        BlogPost::factory(9)->create();
    }
}