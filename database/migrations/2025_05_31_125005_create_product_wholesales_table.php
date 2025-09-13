<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductWholesalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_wholesales', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->integer('wholesale_min_quantity');
            $table->integer('wholesale_max_quantity');
            $table->decimal('wholesale_price');
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
        Schema::dropIfExists('product_wholesales');
    }
}
