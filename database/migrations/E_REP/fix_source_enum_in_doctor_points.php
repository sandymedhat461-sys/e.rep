<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE doctor_points MODIFY COLUMN source ENUM('meeting', 'review', 'feedback', 'sample', 'manual', 'event') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE doctor_points MODIFY COLUMN source ENUM('meeting', 'review', 'feedback', 'sample', 'manual') NOT NULL");
    }
};
