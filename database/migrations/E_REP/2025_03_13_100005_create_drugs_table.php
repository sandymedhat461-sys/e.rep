<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('market_name')->index();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('dosage', 50)->nullable();
            $table->text('side_effects')->nullable();
            $table->string('image', 255)->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
