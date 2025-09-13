<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateToRequermentProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requerment_products', function (Blueprint $table) {
            $table->integer('image_product')->nullable()->change();
            $table->integer('name_product')->nullable()->change();
            $table->integer('desc_product')->nullable()->change();
            $table->integer('address_pickup_product')->nullable()->change();
            $table->integer('verificator')->nullable()->change();
            $table->text('noted')->nullable();
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
