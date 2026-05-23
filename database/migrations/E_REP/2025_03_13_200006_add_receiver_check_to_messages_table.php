<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * DB already updated via phpMyAdmin — migration for version control only.
     */
    public function up(): void
    {
        // Messages table now uses polymorphic receiver_id/receiver_type — check constraint not needed.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Messages table now uses polymorphic receiver_id/receiver_type — check constraint not needed.
    }
};
