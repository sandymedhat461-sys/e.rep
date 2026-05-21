<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('active_ingredients', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by_company_id')->nullable()->after('side_effect');
            $table->foreign('created_by_company_id')
                ->references('id')
                ->on('companies')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('active_ingredients', function (Blueprint $table) {
            $table->dropForeign(['created_by_company_id']);
            $table->dropColumn('created_by_company_id');
        });
    }
};
