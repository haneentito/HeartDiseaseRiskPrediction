<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHealthDataTable extends Migration
{
    public function up()
    {
        Schema::create('health_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->integer('age');
            $table->enum('sex', ['0', '1']); // 0: Female, 1: Male
            $table->enum('chest_pain_type', ['0', '1', '2', '3']); // 0: Typical, 1: Atypical, 2: Non-Anginal, 3: Asymptomatic
            $table->integer('resting_bp'); // Resting Blood Pressure (mmHg)
            $table->integer('cholestoral'); // Serum Cholestoral (mg/dl)
            $table->enum('fasting_bs', ['0', '1']); // 0: No, 1: Yes (>120 mg/dl)
            $table->enum('resting_ecg', ['0', '1', '2']); // 0: Normal, 1: ST-T Wave, 2: Left Ventricular
            $table->integer('max_heart_rate'); // Maximum Heart Rate Achieved
            $table->enum('exercise_angina', ['0', '1']); // 0: No, 1: Yes
            $table->float('oldpeak'); // Oldpeak (ST Depression)
            $table->enum('slope', ['0', '1', '2']); // 0: Upsloping, 1: Flat, 2: Downsloping
            $table->enum('num_vessels', ['0', '1', '2', '3']); // Number of Major Vessels (0-3)
            $table->enum('thal', ['0', '1', '2']); // 0: Normal, 1: Fixed Defect, 2: Reversable Defect
            $table->string('prediction')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('health_data');
    }
}