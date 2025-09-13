<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_deliveries', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->decimal('weight');
            $table->decimal('length');
            $table->decimal('width');
            $table->decimal('height');
            $table->boolean('is_dangerous_product');
            $table->boolean('is_pre_order');
            $table->boolean('is_cost_by_seller');
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
        Schema::dropIfExists('product_deliveries');
    }
}
