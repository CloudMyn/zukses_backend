<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequermentShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requerment_shops', function (Blueprint $table) {
            $table->id();
            $table->integer('logo');
            $table->integer('name_shop');
            $table->integer('desc_shop');
            $table->integer('type_shop');
            $table->integer('full_name');
            $table->integer('nik');
            $table->integer('ktp');
            $table->integer('selfie');
            $table->integer('address_pickup');
            $table->integer('rekening');
            $table->integer('verificator');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('requerment_shops');
    }
}
