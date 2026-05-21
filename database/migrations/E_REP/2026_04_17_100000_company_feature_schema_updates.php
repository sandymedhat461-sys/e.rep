<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' && Schema::hasTable('companies')) {
            DB::statement("ALTER TABLE companies MODIFY COLUMN status ENUM('pending','active','approved','blocked') NOT NULL DEFAULT 'pending'");
            DB::table('companies')->where('status', 'active')->update(['status' => 'approved']);
        }

        Schema::table('drugs', function (Blueprint $table) {
            if (!Schema::hasColumn('drugs', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('drugs', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active');
            }
        });

        if (Schema::hasTable('drugs') && Schema::hasColumn('drugs', 'name')) {
            DB::table('drugs')->whereNull('name')->update(['name' => DB::raw('market_name')]);
        }

        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'max_capacity')) {
                $table->unsignedInteger('max_capacity')->nullable();
            }
            if (!Schema::hasColumn('events', 'status')) {
                $table->enum('status', ['upcoming', 'ongoing', 'completed', 'cancelled'])->default('upcoming');
            }
        });

        Schema::table('rewards', function (Blueprint $table) {
            if (!Schema::hasColumn('rewards', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('rewards', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('rewards', 'quantity_available')) {
                $table->unsignedInteger('quantity_available')->nullable();
            }
            if (!Schema::hasColumn('rewards', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active');
            }
        });

        if (Schema::hasTable('rewards') && Schema::hasColumn('rewards', 'name')) {
            DB::table('rewards')->whereNull('name')->update(['name' => DB::raw('title')]);
        }

        if ($driver === 'mysql' && Schema::hasTable('reward_redemptions')) {
            DB::statement("ALTER TABLE reward_redemptions MODIFY COLUMN status ENUM('pending','approved','rejected','delivered','fulfilled','cancelled') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('rep_targets', function (Blueprint $table) {
            if (!Schema::hasColumn('rep_targets', 'period_start')) {
                $table->dateTime('period_start')->nullable();
            }
            if (!Schema::hasColumn('rep_targets', 'period_end')) {
                $table->dateTime('period_end')->nullable();
            }
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('rep_targets', function (Blueprint $table) {
            if (Schema::hasColumn('rep_targets', 'period_end')) {
                $table->dropColumn('period_end');
            }
            if (Schema::hasColumn('rep_targets', 'period_start')) {
                $table->dropColumn('period_start');
            }
        });

        if ($driver === 'mysql' && Schema::hasTable('reward_redemptions')) {
            DB::statement("ALTER TABLE reward_redemptions MODIFY COLUMN status ENUM('pending','approved','rejected','delivered') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('rewards', function (Blueprint $table) {
            foreach (['status', 'quantity_available', 'description', 'name'] as $col) {
                if (Schema::hasColumn('rewards', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('events', 'max_capacity')) {
                $table->dropColumn('max_capacity');
            }
        });

        Schema::table('drugs', function (Blueprint $table) {
            if (Schema::hasColumn('drugs', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('drugs', 'name')) {
                $table->dropColumn('name');
            }
        });

        if ($driver === 'mysql' && Schema::hasTable('companies')) {
            DB::table('companies')->where('status', 'approved')->update(['status' => 'active']);
            DB::statement("ALTER TABLE companies MODIFY COLUMN status ENUM('pending','active','blocked') NOT NULL DEFAULT 'pending'");
        }
    }
};
