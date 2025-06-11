<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDoctorIdToNotificationsTable extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('doctor_id')->after('id');
            $table->foreign('doctor_id')->references('doctor_id')->on('doctors')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropColumn('doctor_id');
        });
    }
}