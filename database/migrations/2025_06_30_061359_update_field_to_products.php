<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFieldToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Asumsi relasi ke tabel shop_profiles & product_categories sudah ada
            $table->foreignId('seller_id')->constrained('shop_profiles')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade');

            $table->string('name', 255);
            $table->text('description');
            $table->string('image')->nullable()->comment('URL gambar utama produk');
            $table->string('parent_sku')->nullable();

            // Untuk produk tanpa variasi
            $table->unsignedBigInteger('price')->nullable();
            $table->unsignedInteger('stock')->nullable();

            // Informasi Pengiriman
            $table->unsignedInteger('weight')->nullable()->comment('dalam gram');
            $table->unsignedInteger('package_length')->nullable()->comment('dalam cm');
            $table->unsignedInteger('package_width')->nullable()->comment('dalam cm');
            $table->unsignedInteger('package_height')->nullable()->comment('dalam cm');

            // Lainnya
            $table->string('condition')->default('Baru'); // Baru, Pernah Dipakai
            $table->boolean('is_hazardous')->default(false);
            $table->boolean('is_pre_order')->default(false);
            $table->boolean('shipping_insurance')->default(false);
            $table->timestamp('scheduled_date')->nullable();

            $table->enum('status', ['PUBLISHED', 'ARCHIVED', 'DRAFT'])->default('DRAFT');
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
        Schema::dropIfExists('products');
    }
}
