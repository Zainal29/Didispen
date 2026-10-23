<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            if (! Schema::hasColumn('dispensasi', 'warned_by')) {
                $table->string('warned_by', 30)->nullable()->default('bot')->after('warned_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            if (Schema::hasColumn('dispensasi', 'warned_by')) {
                $table->dropColumn('warned_by');
            }
        });
    }
};
