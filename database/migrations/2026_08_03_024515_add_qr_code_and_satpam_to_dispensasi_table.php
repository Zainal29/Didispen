<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            $table->string('qr_code')->nullable()->after('nomor_surat');
            $table->timestamp('waktu_keluar_aktual')->nullable()->after('jam_kembali');
            $table->timestamp('waktu_kembali_aktual')->nullable()->after('waktu_keluar_aktual');
            $table->foreignId('satpam_keluar_id')->nullable()->after('waktu_keluar_aktual')->constrained('users')->nullOnDelete();
            $table->foreignId('satpam_kembali_id')->nullable()->after('satpam_keluar_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            $table->dropForeign(['satpam_keluar_id', 'satpam_kembali_id']);
            $table->dropColumn(['qr_code', 'waktu_keluar_aktual', 'waktu_kembali_aktual', 'satpam_keluar_id', 'satpam_kembali_id']);
        });
    }
};