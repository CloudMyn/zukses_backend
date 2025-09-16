<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToOrderItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {

            $table->bigInteger('original_price')->default(0);
            $table->bigInteger('insurance')->default(0);
            $table->bigInteger('service_fee')->default(0);
            $table->bigInteger('payment_fee')->default(0);
            $table->bigInteger('discount')->default(0); // diskon dari penjual
            $table->bigInteger('subsidy')->default(0); // subsidi dari platform
            $table->bigInteger('voucher')->default(0); // potongan dari voucher
            $table->string('shipping')->default(''); // ekspedisi pengiriman
            $table->bigInteger('price_shipping')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            //
        });
    }
}
