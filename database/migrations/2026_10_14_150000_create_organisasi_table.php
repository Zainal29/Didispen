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
        if (!Schema::hasTable('organisasi')) {
            Schema::create('organisasi', function (Blueprint $table) {
                $table->id();
                $table->string('nama_organisasi', 100)->unique();
                $table->string('singkatan', 30)->nullable();
                $table->enum('kategori', ['osis', 'ekstrakurikuler', 'kejuruan', 'komunitas', 'lainnya'])->default('ekstrakurikuler');
                $table->foreignId('ketua_siswa_id')->nullable()->constrained('siswa')->nullOnDelete();
                $table->foreignId('pembina_guru_id')->nullable()->constrained('guru')->nullOnDelete();
                $table->text('deskripsi')->nullable();
                $table->boolean('status_aktif')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('dispensasi') && !Schema::hasColumn('dispensasi', 'organisasi_id')) {
            Schema::table('dispensasi', function (Blueprint $table) {
                $table->foreignId('organisasi_id')->nullable()->after('tipe_pengajuan')->constrained('organisasi')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('dispensasi') && Schema::hasColumn('dispensasi', 'organisasi_id')) {
            Schema::table('dispensasi', function (Blueprint $table) {
                $table->dropForeign(['organisasi_id']);
                $table->dropColumn('organisasi_id');
            });
        }

        Schema::dropIfExists('organisasi');
    }
};
