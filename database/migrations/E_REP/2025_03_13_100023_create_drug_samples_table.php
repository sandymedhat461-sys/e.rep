<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('drug_samples', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('drug_id');
            $table->unsignedBigInteger('rep_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('status', 50)->default('pending');
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('drug_id')->references('id')->on('drugs')->onDelete('cascade');
            $table->foreign('rep_id')->references('id')->on('medical_reps')->onDelete('cascade');
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('drug_samples');
    }
};
