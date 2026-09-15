<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            // Kolom untuk menyimpan batas waktu kembali (datetime)
            if (! Schema::hasColumn('dispensasi', 'batas_waktu_kembali')) {
                $table->timestamp('batas_waktu_kembali')->nullable()->after('jam_kembali');
            }

            // Kolom untuk menandai apakah siswa sudah diberi peringatan
            if (! Schema::hasColumn('dispensasi', 'is_warned')) {
                $table->boolean('is_warned')->default(false)->after('batas_waktu_kembali');
            }

            // Kolom untuk mencatat kapan peringatan dikirim
            if (! Schema::hasColumn('dispensasi', 'warned_at')) {
                $table->timestamp('warned_at')->nullable()->after('is_warned');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            $table->dropColumn(['batas_waktu_kembali', 'is_warned', 'warned_at']);
        });
    }
};
