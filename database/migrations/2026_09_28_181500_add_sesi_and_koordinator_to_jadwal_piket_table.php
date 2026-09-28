<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jadwal_piket', function (Blueprint $table) {
            if (! Schema::hasColumn('jadwal_piket', 'nama_sesi')) {
                $table->string('nama_sesi', 50)->nullable()->after('hari');
            }
            if (! Schema::hasColumn('jadwal_piket', 'koordinator_guru_id')) {
                $table->foreignId('koordinator_guru_id')->nullable()->after('jam_selesai')->constrained('guru')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_piket', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_piket', 'koordinator_guru_id')) {
                $table->dropForeign(['koordinator_guru_id']);
                $table->dropColumn('koordinator_guru_id');
            }
            if (Schema::hasColumn('jadwal_piket', 'nama_sesi')) {
                $table->dropColumn('nama_sesi');
            }
        });
    }
};
