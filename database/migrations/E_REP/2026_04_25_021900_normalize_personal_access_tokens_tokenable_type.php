<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('personal_access_tokens')) {
            return;
        }

        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\\Models\\Admin')
            ->update(['tokenable_type' => 'admin']);

        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\\Models\\Company')
            ->update(['tokenable_type' => 'company']);

        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\\Models\\Doctor')
            ->update(['tokenable_type' => 'doctor']);

        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\\Models\\MedicalRep')
            ->update(['tokenable_type' => 'medical_rep']);

        // Back-compat: normalize legacy "rep" to "medical_rep" for consistency.
        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'rep')
            ->update(['tokenable_type' => 'medical_rep']);
    }

    public function down(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('personal_access_tokens')) {
            return;
        }

        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'admin')
            ->update(['tokenable_type' => 'App\\Models\\Admin']);

        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'company')
            ->update(['tokenable_type' => 'App\\Models\\Company']);

        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'doctor')
            ->update(['tokenable_type' => 'App\\Models\\Doctor']);

        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'medical_rep')
            ->update(['tokenable_type' => 'App\\Models\\MedicalRep']);
    }
};

