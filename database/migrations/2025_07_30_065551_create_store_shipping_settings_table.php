<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreShippingSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_shipping_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->unique()->constrained('shop_profiles')->onDelete('cascade');

            // Pengaturan untuk Kurir Toko (Custom Courier)
            $table->boolean('is_store_courier_active')->default(false);
            $table->json('distance_tiers')->nullable()->comment('Pengaturan ongkir berdasarkan jarak');
            $table->json('weight_tiers')->nullable()->comment('Pengaturan ongkir berdasarkan berat');

            // Pengaturan untuk Kurir Pihak Ketiga
            // Menyimpan ID dari tabel 'courier_services' yang diaktifkan oleh penjual
            $table->json('enabled_service_ids')->nullable();
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
        Schema::dropIfExists('store_shipping_settings');
    }
}
