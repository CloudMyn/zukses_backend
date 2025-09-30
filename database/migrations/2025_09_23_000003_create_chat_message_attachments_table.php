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
        Schema::create('chat_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->string('type', 32); // image|video|audio|file|sticker
            $table->string('url', 2048);
            $table->string('filename', 512)->nullable();
            $table->string('content_type', 128)->nullable();
            $table->integer('size_bytes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->foreign('message_id')->references('id')->on('chat_messages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_message_attachments');
    }
};