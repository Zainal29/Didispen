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
            if (!Schema::hasColumn('dispensasi', 'tanggal_selesai')) {
                $table->date('tanggal_selesai')->nullable()->after('tanggal_izin');
            }
            if (!Schema::hasColumn('dispensasi', 'durasi_hari')) {
                $table->unsignedSmallInteger('durasi_hari')->default(1)->after('tanggal_selesai');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('dispensasi', 'tanggal_selesai')) {
                $columns[] = 'tanggal_selesai';
            }
            if (Schema::hasColumn('dispensasi', 'durasi_hari')) {
                $columns[] = 'durasi_hari';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
