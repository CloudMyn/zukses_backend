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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender_user_id')->nullable();
            $table->unsignedBigInteger('sender_shop_profile_id')->nullable();
            $table->text('content')->nullable();
            $table->enum('content_type', ['text', 'system', 'template', 'product_card', 'order_card'])->default('text');
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('parent_message_id')->nullable();
            $table->unsignedBigInteger('reply_to_message_id')->nullable();
            $table->timestamps();
            $table->timestamp('edited_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->foreign('sender_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('sender_shop_profile_id')->references('id')->on('shop_profiles')->onDelete('set null');
            $table->foreign('parent_message_id')->references('id')->on('chat_messages')->onDelete('set null');
            $table->foreign('reply_to_message_id')->references('id')->on('chat_messages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};