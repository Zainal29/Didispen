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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'external_id')) {
                $table->string('external_id')->nullable()->index()->after('id');
            }

            if (! Schema::hasColumn('users', 'sipintu_last_synced_at')) {
                $table->timestamp('sipintu_last_synced_at')->nullable()->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'external_id')) {
                $table->dropColumn('external_id');
            }

            if (Schema::hasColumn('users', 'sipintu_last_synced_at')) {
                $table->dropColumn('sipintu_last_synced_at');
            }
        });
    }
};
