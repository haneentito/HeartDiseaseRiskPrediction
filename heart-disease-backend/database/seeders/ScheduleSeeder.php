<?php
use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Doctor;

class ScheduleSeeder extends Seeder
{
    public function run()
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $times = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];
        $doctors = Doctor::all();

        foreach ($doctors as $doctor) {
            foreach ($days as $day) {
                foreach ($times as $time) {
                    Schedule::create([
                        'doctor_id' => $doctor->id,
                        'day' => $day,
                        'time' => $time,
                        'available' => rand(0, 1) === 1, // Randomly set availability
                    ]);
                }
            }
        }
    }
}