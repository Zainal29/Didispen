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
        Schema::table('siswa', function (Blueprint $table) {
            if (! Schema::hasColumn('siswa', 'nis_nip')) {
                $table->string('nis_nip')->nullable()->unique()->after('user_id');
            }
            if (! Schema::hasColumn('siswa', 'status_aktif')) {
                $table->boolean('status_aktif')->default(true)->after('no_telepon');
            }
        });

        Schema::table('guru', function (Blueprint $table) {
            if (! Schema::hasColumn('guru', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable()->after('nama_lengkap');
            }
            if (! Schema::hasColumn('guru', 'status_aktif')) {
                $table->boolean('status_aktif')->default(true)->after('mata_pelajaran');
            }
            // Ubah tipe mata_pelajaran ke text agar bisa menampung multiple mapel
            $table->text('mata_pelajaran')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            if (Schema::hasColumn('siswa', 'nis_nip')) {
                $table->dropColumn('nis_nip');
            }
            if (Schema::hasColumn('siswa', 'status_aktif')) {
                $table->dropColumn('status_aktif');
            }
        });

        Schema::table('guru', function (Blueprint $table) {
            if (Schema::hasColumn('guru', 'tanggal_lahir')) {
                $table->dropColumn('tanggal_lahir');
            }
            if (Schema::hasColumn('guru', 'status_aktif')) {
                $table->dropColumn('status_aktif');
            }
        });
    }
};
