<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('seller_id');
            $table->integer('category_id');
            $table->string('name');
            $table->string('desc');
            $table->string('sku')->nullable();
            $table->decimal('price')->nullable();
            $table->integer('stock')->nullable();
            $table->integer('min_purchase');
            $table->integer('max_purchase');
            $table->boolean('is_used');
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
        Schema::dropIfExists('products');
    }
}
