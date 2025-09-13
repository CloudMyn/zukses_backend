<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductVariantDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_variant_deliveries', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('product_variant_price_id');
            $table->float('weight')->default(0);
            $table->float('length')->default(0);
            $table->float('width')->default(0);
            $table->float('height')->default(0);

            $table->timestamps();

            $table->foreign('product_variant_price_id')
                ->references('id')->on('product_variant_prices')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_deliveries');
    }
}
