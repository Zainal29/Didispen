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
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE dispensasi MODIFY COLUMN status ENUM('menunggu', 'disetujui', 'ditolak', 'keluar', 'selesai', 'dibatalkan', 'kadaluarsa') NOT NULL DEFAULT 'menunggu'");
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE dispensasi MODIFY COLUMN status ENUM('menunggu', 'disetujui', 'ditolak', 'keluar', 'selesai') NOT NULL DEFAULT 'menunggu'");
    }
};
