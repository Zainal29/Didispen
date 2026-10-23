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
        // 1. Tabel Laporan Satpam (Temuan & Kejadian Gerbang)
        if (!Schema::hasTable('laporan_satpam')) {
            Schema::create('laporan_satpam', function (Blueprint $table) {
                $table->id();
                $table->foreignId('satpam_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
                $table->foreignId('dispensasi_id')->nullable()->constrained('dispensasi')->nullOnDelete();
                $table->string('kategori')->default('rombongan_tidak_kembali'); // rombongan_tidak_kembali, terobos_gerbang, terlambat_kembali, pelanggaran_lain
                $table->text('keterangan');
                $table->string('status')->default('menunggu'); // menunggu, ditindaklanjuti, selesai
                $table->text('catatan_tindak_lanjut')->nullable();
                $table->foreignId('tindak_lanjut_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('tindak_lanjut_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'created_at']);
                $table->index('kategori');
            });
        }

        // 2. Tambah kolom pelacakan kembali pada dispensasi_anggota
        if (Schema::hasTable('dispensasi_anggota')) {
            Schema::table('dispensasi_anggota', function (Blueprint $table) {
                if (!Schema::hasColumn('dispensasi_anggota', 'status_kembali')) {
                    $table->string('status_kembali')->default('belum_kembali')->after('siswa_id'); // belum_kembali, kembali, tidak_kembali
                }
                if (!Schema::hasColumn('dispensasi_anggota', 'catatan_kembali')) {
                    $table->string('catatan_kembali')->nullable()->after('status_kembali');
                }
                if (!Schema::hasColumn('dispensasi_anggota', 'waktu_kembali')) {
                    $table->timestamp('waktu_kembali')->nullable()->after('catatan_kembali');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_satpam');

        if (Schema::hasTable('dispensasi_anggota')) {
            Schema::table('dispensasi_anggota', function (Blueprint $table) {
                $table->dropColumn(['status_kembali', 'catatan_kembali', 'waktu_kembali']);
            });
        }
    }
};
