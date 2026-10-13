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
            if (!Schema::hasColumn('dispensasi', 'tipe_pengajuan')) {
                $table->enum('tipe_pengajuan', ['individu', 'organisasi'])->default('individu')->after('nomor_surat');
            }
            if (!Schema::hasColumn('dispensasi', 'nama_organisasi')) {
                $table->string('nama_organisasi', 100)->nullable()->after('tipe_pengajuan');
            }
            if (!Schema::hasColumn('dispensasi', 'tanggal_izin')) {
                $table->date('tanggal_izin')->nullable()->after('lokasi');
            }
            if (!Schema::hasColumn('dispensasi', 'sampai_pulang')) {
                $table->boolean('sampai_pulang')->default(false)->after('jam_kembali');
            }
        });

        // Update status enum agar mendukung 'kadaluarsa'
        try {
            DB::statement("ALTER TABLE dispensasi MODIFY COLUMN status ENUM('menunggu', 'disetujui', 'ditolak', 'keluar', 'selesai', 'kadaluarsa') DEFAULT 'menunggu'");
        } catch (\Throwable $e) {
            // Abaikan jika tidak didukung driver non-MySQL
        }

        // Backfill tanggal_izin untuk record yang sudah ada
        DB::table('dispensasi')->whereNull('tanggal_izin')->update([
            'tanggal_izin' => DB::raw('DATE(created_at)')
        ]);

        if (!Schema::hasTable('dispensasi_anggota')) {
            Schema::create('dispensasi_anggota', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dispensasi_id')->constrained('dispensasi')->cascadeOnDelete();
                $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['dispensasi_id', 'siswa_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispensasi_anggota');

        Schema::table('dispensasi', function (Blueprint $table) {
            $columns = ['tipe_pengajuan', 'nama_organisasi', 'tanggal_izin', 'sampai_pulang'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('dispensasi', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        try {
            DB::statement("ALTER TABLE dispensasi MODIFY COLUMN status ENUM('menunggu', 'disetujui', 'ditolak', 'keluar', 'selesai') DEFAULT 'menunggu'");
        } catch (\Throwable $e) {
        }
    }
};
