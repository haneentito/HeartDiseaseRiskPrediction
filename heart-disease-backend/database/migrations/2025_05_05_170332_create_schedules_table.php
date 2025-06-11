<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulesTable extends Migration
{
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('doctor_id'); 
            $table->string('day');
            $table->string('time');
            $table->boolean('available')->default(true);
            $table->timestamps();

            $table->foreign('doctor_id')->references('doctor_id')->on('doctors')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('schedules');
    }

}