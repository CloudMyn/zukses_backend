<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('name_receiver');
            $table->string('number_receiver');
            $table->integer('province_id');
            $table->integer('citie_id');
            $table->integer('subdistrict_id');
            $table->integer('postal_code_id');
            $table->string('full_address');
            $table->string('label');
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
        Schema::dropIfExists('user_addresses');
    }
}
