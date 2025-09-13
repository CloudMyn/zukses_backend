<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_addresses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('seller_id');
            $table->string('name_receiver')->nullable();
            $table->string('number_receiver')->nullable();
            $table->integer('province_id');
            $table->integer('citie_id');
            $table->integer('subdistrict_id');
            $table->integer('postal_code_id');
            $table->string('full_address');
            $table->string('label')->nullable();
            $table->double('lat')->nullable();
            $table->double('long')->nullable();
            $table->boolean('is_primary')->default(0);
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
        Schema::dropIfExists('shop_addresses');
    }
}
