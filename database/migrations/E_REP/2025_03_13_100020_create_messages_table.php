<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('sender_type');
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->string('receiver_type');
            $table->text('body');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->index(['sender_type', 'sender_id']);
            $table->index(['receiver_type', 'receiver_id']);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
