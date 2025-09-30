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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['private', 'group', 'order_support', 'product_support', 'system']);
            $table->string('title', 512)->nullable();
            $table->unsignedBigInteger('owner_user_id')->nullable();
            $table->unsignedBigInteger('owner_shop_profile_id')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('last_message_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->boolean('is_open')->default(true);
            $table->timestamps();
            
            $table->foreign('owner_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('owner_shop_profile_id')->references('id')->on('shop_profiles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};