<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('drug_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('drug_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedTinyInteger('rating'); 
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('drug_id')->references('id')->on('drugs')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drug_reviews');
    }
};
