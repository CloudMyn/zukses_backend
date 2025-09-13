<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            // Menambahkan kolom baru sebagai nullable
            $table->string('user_profile_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variant_price_id')->nullable();
            $table->text('text');
            $table->timestamps(); // Ini akan membuat created_at dan updated_at

            // Opsional: Jika Anda ingin menambahkan foreign key constraints
            // Pastikan tabel referensi (misal: user_profiles, products, variant_prices) sudah ada
            // $table->foreign('user_profile_id')->references('id')->on('user_profiles')->onDelete('set null');
            // $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            // $table->foreign('variant_price_id')->references('id')->on('variant_prices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
