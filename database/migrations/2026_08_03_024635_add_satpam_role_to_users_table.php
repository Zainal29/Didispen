<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // ✅ TAMBAHKAN BARIS INI UNTUK MEMPERBAIKI ERROR

return new class extends Migration
{
    public function up(): void
    {
        // Tambah role 'satpam' ke enum yang sudah ada
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','guru','siswa','satpam') NOT NULL DEFAULT 'siswa'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','guru','siswa') NOT NULL DEFAULT 'siswa'");
    }
};