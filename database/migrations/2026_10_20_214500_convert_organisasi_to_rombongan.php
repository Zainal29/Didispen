<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop foreign key dispensasi -> organisasi jika ada
        if (Schema::hasTable('dispensasi')) {
            Schema::table('dispensasi', function (Blueprint $table) {
                if (Schema::hasColumn('dispensasi', 'organisasi_id')) {
                    try {
                        $table->dropForeign(['organisasi_id']);
                    } catch (\Throwable $e) {}
                }
            });
        }

        // 2. Drop table organisasi & dispensasi_guru jika ada
        Schema::dropIfExists('dispensasi_guru');
        Schema::dropIfExists('organisasi');

        // 3. Update kolom tipe_pengajuan di tabel dispensasi
        if (Schema::hasTable('dispensasi') && Schema::hasColumn('dispensasi', 'tipe_pengajuan')) {
            try {
                // Perluas enum agar mendukung 'rombongan'
                DB::statement("ALTER TABLE dispensasi MODIFY COLUMN tipe_pengajuan ENUM('individu', 'rombongan', 'organisasi') DEFAULT 'individu'");
                // Migrasikan data lama bertipe 'organisasi' menjadi 'rombongan'
                DB::table('dispensasi')->where('tipe_pengajuan', 'organisasi')->update(['tipe_pengajuan' => 'rombongan']);
                // Kunci enum hanya ke 'individu' dan 'rombongan'
                DB::statement("ALTER TABLE dispensasi MODIFY COLUMN tipe_pengajuan ENUM('individu', 'rombongan') DEFAULT 'individu'");
            } catch (\Throwable $e) {
                // Fallback untuk driver selain MySQL (misal SQLite di test)
            }
        }

        // 4. Hapus kolom organisasi_id, nama_organisasi, tanggal_selesai, durasi_hari jika ada
        if (Schema::hasTable('dispensasi')) {
            Schema::table('dispensasi', function (Blueprint $table) {
                $columnsToDrop = [];
                if (Schema::hasColumn('dispensasi', 'organisasi_id')) {
                    $columnsToDrop[] = 'organisasi_id';
                }
                if (Schema::hasColumn('dispensasi', 'nama_organisasi')) {
                    $columnsToDrop[] = 'nama_organisasi';
                }
                if (Schema::hasColumn('dispensasi', 'tanggal_selesai')) {
                    $columnsToDrop[] = 'tanggal_selesai';
                }
                if (Schema::hasColumn('dispensasi', 'durasi_hari')) {
                    $columnsToDrop[] = 'durasi_hari';
                }

                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert jika diperlukan
    }
};
