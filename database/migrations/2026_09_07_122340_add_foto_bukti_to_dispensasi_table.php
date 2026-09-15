<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            // Foto verifikasi: untuk identifikasi wajah (sementara)
            // Sudah ada: $table->string('foto_verifikasi')->nullable();

            // ✅ BARU: Foto bukti saat sampai di tujuan (permanen untuk arsip)
            $table->string('foto_bukti')->nullable()->after('foto_verifikasi');
            $table->timestamp('foto_bukti_uploaded_at')->nullable()->after('foto_bukti');
        });
    }

    public function down(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            $table->dropColumn(['foto_bukti', 'foto_bukti_uploaded_at']);
        });
    }
};
