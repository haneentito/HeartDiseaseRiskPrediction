<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToAppointmentsTable extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('time'); // Add status column
            $table->unsignedBigInteger('schedule_id')->nullable()->after('patient_id'); // Add schedule_id
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropColumn(['status', 'schedule_id']);
        });
    }
}