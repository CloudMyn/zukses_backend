<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateToRequermentShops extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requerment_shops', function (Blueprint $table) {
            $table->integer('logo')->nullable()->change();
            $table->integer('name_shop')->nullable()->change();
            $table->integer('desc_shop')->nullable()->change();
            $table->integer('type_shop')->nullable()->change();
            $table->integer('full_name')->nullable()->change();
            $table->integer('nik')->nullable()->change();
            $table->integer('ktp')->nullable()->change();
            $table->integer('selfie')->nullable()->change();
            $table->json('address_pickup')->nullable()->change();
            $table->json('rekening')->nullable()->change();
            $table->integer('verificator')->nullable()->change();
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
