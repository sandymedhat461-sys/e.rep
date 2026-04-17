<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            if (!Schema::hasColumn('meetings', 'room_name')) {
                $table->string('room_name', 255)->nullable()->after('status');
            }
            if (!Schema::hasColumn('meetings', 'notes')) {
                $table->text('notes')->nullable()->after('meeting_link');
            }
        });

        Schema::table('doctor_points', function (Blueprint $table) {
            if (!Schema::hasColumn('doctor_points', 'description')) {
                $table->string('description', 500)->nullable()->after('value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doctor_points', function (Blueprint $table) {
            if (Schema::hasColumn('doctor_points', 'description')) {
                $table->dropColumn('description');
            }
        });

        Schema::table('meetings', function (Blueprint $table) {
            if (Schema::hasColumn('meetings', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('meetings', 'room_name')) {
                $table->dropColumn('room_name');
            }
        });
    }
};
