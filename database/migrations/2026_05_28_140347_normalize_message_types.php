<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalize sender_type
        DB::table('messages')
            ->whereIn('sender_type', ['Doctor', 'App\\Models\\Doctor'])
            ->update(['sender_type' => 'doctor']);

        DB::table('messages')
            ->whereIn('sender_type', ['MedicalRep', 'rep', 'App\\Models\\MedicalRep'])
            ->update(['sender_type' => 'medical_rep']);

        // Normalize receiver_type
        DB::table('messages')
            ->whereIn('receiver_type', ['Doctor', 'App\\Models\\Doctor'])
            ->update(['receiver_type' => 'doctor']);

        DB::table('messages')
            ->whereIn('receiver_type', ['MedicalRep', 'rep', 'App\\Models\\MedicalRep'])
            ->update(['receiver_type' => 'medical_rep']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible data normalization.
    }
};
