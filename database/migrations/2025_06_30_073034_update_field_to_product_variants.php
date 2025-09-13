<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateFieldToProductVariants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::dropIfExists('product_variant_price_compositions');
        Schema::dropIfExists('product_variant_values');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_variations');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('price');
            $table->unsignedInteger('stock');
            $table->string('sku')->nullable();
            $table->string('image')->nullable()->comment('URL gambar spesifik varian');

            // Jika pengiriman per variasi aktif
            $table->unsignedInteger('weight')->nullable()->comment('dalam gram');
            $table->unsignedInteger('length')->nullable()->comment('dalam cm');
            $table->unsignedInteger('width')->nullable()->comment('dalam cm');
            $table->unsignedInteger('height')->nullable()->comment('dalam cm');

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
        Schema::table('product_variants', function (Blueprint $table) {
            //
        });
    }
}
