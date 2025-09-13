<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDropFieldToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('parent_sku');
            $table->dropColumn('weight');
            $table->dropColumn('package_length');
            $table->dropColumn('package_width');
            $table->dropColumn('package_height');
            $table->dropColumn('condition');
            $table->dropColumn('is_hazardous');
            $table->dropColumn('is_pre_order');
            $table->dropColumn('shipping_insurance');
            $table->dropColumn('scheduled_date');
            $table->dropColumn('status');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
}
