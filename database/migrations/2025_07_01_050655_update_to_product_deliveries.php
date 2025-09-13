<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateToProductDeliveries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_deliveries', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->change();

            // Tambah foreign key (pastikan sebelumnya belum ada)
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Tambahkan default value jika belum ada
            $table->decimal('weight', 8, 2)->default(0)->change();
            $table->decimal('length', 8, 2)->default(0)->change();
            $table->decimal('width', 8, 2)->default(0)->change();
            $table->decimal('height', 8, 2)->default(0)->change();

            $table->boolean('is_dangerous_product')->default(false)->change();
            $table->boolean('is_pre_order')->default(false)->change();
            $table->boolean('is_cost_by_seller')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_deliveries', function (Blueprint $table) {
            //
        });
    }
}
