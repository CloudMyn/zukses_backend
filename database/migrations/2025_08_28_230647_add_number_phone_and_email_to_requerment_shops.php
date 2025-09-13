<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNumberPhoneAndEmailToRequermentShops extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requerment_shops', function (Blueprint $table) {
            $table->integer('number_phone')->nullable();
            $table->integer('email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requerment_shops', function (Blueprint $table) {
            //
        });
    }
}
