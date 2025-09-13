<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToRequermentProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requerment_products', function (Blueprint $table) {
            $table->integer('price')->nullable();
            $table->integer('delivery')->nullable();
            $table->integer('shipping')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requerment_products', function (Blueprint $table) {
            //
        });
    }
}
