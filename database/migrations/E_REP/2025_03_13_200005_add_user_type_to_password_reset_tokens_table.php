<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * DB already updated via phpMyAdmin — migration for version control only.
     */
    public function up(): void
    {
        if (Schema::hasColumn('password_reset_tokens', 'user_type')) {
            return;
        }

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropPrimary(['email']);
            $table->enum('user_type', ['doctor', 'medical_rep', 'admin', 'company'])
                ->default('doctor')
                ->after('email');
            $table->primary(['email', 'user_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropPrimary(['email', 'user_type']);
            $table->dropColumn('user_type');
            $table->primary('email');
        });
    }
};
