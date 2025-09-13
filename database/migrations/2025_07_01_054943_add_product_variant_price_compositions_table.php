<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductVariantPriceCompositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_variant_price_compositions', function (Blueprint $table) {
            $table->id();
            $table->integer('product_variant_price_id');
            $table->unsignedBigInteger('product_variant_value_id');
            $table->timestamps();

            $table->foreign('product_variant_value_id', 'fk_price_value')
                ->references('id')
                ->on('product_variant_values')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_variant_price_compositions');
    }
}
