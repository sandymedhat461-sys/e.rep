<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->unsignedBigInteger('sharer_id');
            $table->string('sharer_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_shares');
    }
};
