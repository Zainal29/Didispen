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
        Schema::create('pertukaran_jadwal_piket', function (Blueprint $table) {
            $table->id();

            $table->foreignId('jadwal_piket_id')
                ->constrained('jadwal_piket')
                ->restrictOnDelete();

            $table->foreignId('guru_asal_id')
                ->constrained('guru')
                ->restrictOnDelete();

            $table->foreignId('guru_pengganti_id')
                ->constrained('guru')
                ->restrictOnDelete();

            $table->date('tanggal');

            $table->text('alasan')->nullable();

            $table->enum('status', [
                'menunggu',
                'disetujui',
                'ditolak',
                'dibatalkan',
            ])->default('menunggu');

            $table->boolean('is_active')->default(false);

            $table->timestamp('diminta_at')->nullable();

            $table->timestamp('diproses_at')->nullable();

            $table->foreignId('diproses_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->index(['jadwal_piket_id', 'tanggal']);
            $table->index(['guru_asal_id', 'tanggal']);
            $table->index(['guru_pengganti_id', 'tanggal']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertukaran_jadwal_piket');
    }
};
