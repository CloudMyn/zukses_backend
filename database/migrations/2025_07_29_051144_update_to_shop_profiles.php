<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateToShopProfiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_profiles', function (Blueprint $table) {
            $table->string('full_name');
            $table->string('nik');
            $table->string('ktp_url');
            $table->string('selfie_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_profiles', function (Blueprint $table) {
            //
        });
    }
}
