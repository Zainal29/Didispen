<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Pastikan kolom guru_id ada di dispensasi
        if (! Schema::hasColumn('dispensasi', 'guru_id')) {
            Schema::table('dispensasi', function (Blueprint $table) {
                $table->foreignId('guru_id')->nullable()->after('siswa_id')
                    ->constrained('guru')->nullOnDelete();
            });
        }

        // 2) Backfill data lama (jika tabel perantara masih ada)
        if (Schema::hasTable('guru_piket') && Schema::hasColumn('dispensasi', 'guru_piket_id')) {
            DB::statement('UPDATE dispensasi d
                           INNER JOIN guru_piket gp ON gp.id = d.guru_piket_id
                           SET d.guru_id = gp.guru_id
                           WHERE d.guru_id IS NULL');
        }

        // 3) Buang kolom perantara
        if (Schema::hasColumn('dispensasi', 'guru_piket_id')) {
            Schema::table('dispensasi', function (Blueprint $table) {
                $table->dropConstrainedForeignId('guru_piket_id');
            });
        }

        // 4) Hapus tabel jadwal
        if (Schema::hasTable('guru_piket')) {
            Schema::dropIfExists('guru_piket');
        }
    }

    public function down(): void {}
};
