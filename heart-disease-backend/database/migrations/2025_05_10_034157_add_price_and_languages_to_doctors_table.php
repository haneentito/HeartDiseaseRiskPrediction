<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceAndLanguagesToDoctorsTable extends Migration
{
    public function up()
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->nullable()->after('views');
            $table->string('languages')->nullable()->after('price');
        });
    }

    public function down()
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['price', 'languages']);
        });
    }
}