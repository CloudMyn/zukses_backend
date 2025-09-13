<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterSubdistrictPolygonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_subdistrict_polygons', function (Blueprint $table) {
            $table->id();
            $table->integer('subdistrict_id')->index();
            $table->double('lat')->nullable();
            $table->double('long')->nullable();
            $table->longText('polygon');
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
        Schema::dropIfExists('master_subdistrict_polygons');
    }
}
