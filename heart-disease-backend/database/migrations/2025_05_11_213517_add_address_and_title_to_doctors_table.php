<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
       Schema::table('doctors', function (Blueprint $table) {
       $table->string('address')->nullable();
       $table->string('title')->nullable();
});
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            //
        });
    }
};
