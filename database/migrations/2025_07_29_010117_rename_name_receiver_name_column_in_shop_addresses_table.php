<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameNameReceiverNameColumnInShopAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_addresses', function (Blueprint $table) {
            $table->renameColumn('name_receiver', 'name_shop');
            $table->renameColumn('number_receiver', 'number_shop');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_addresses', function (Blueprint $table) {
            $table->renameColumn('name_shop', 'name_receiver');
            $table->renameColumn('number_shop', 'number_receiver');
        });
    }
}
