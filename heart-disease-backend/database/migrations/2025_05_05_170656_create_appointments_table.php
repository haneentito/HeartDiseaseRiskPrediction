<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('schedule_id')->nullable(); // Add schedule_id
            $table->string('day');
            $table->string('time');
            $table->string('status')->default('pending'); // Add status column
            $table->timestamp('booked_at')->useCurrent();
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}