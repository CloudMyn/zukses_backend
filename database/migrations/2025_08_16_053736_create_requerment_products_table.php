<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequermentProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requerment_products', function (Blueprint $table) {
            $table->id();
            $table->integer('image_product');
            $table->integer('name_product');
            $table->integer('desc_product');
            $table->integer('variant_product')->nullable();
            $table->integer('address_pickup_product');
            $table->unsignedBigInteger('verificator');
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
        Schema::dropIfExists('requerment_products');
    }
}
