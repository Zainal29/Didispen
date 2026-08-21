<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->foreignId('jurusan_id')->nullable()->change();
            $table->foreignId('kelas_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->foreignId('jurusan_id')->nullable(false)->change();
            $table->foreignId('kelas_id')->nullable(false)->change();
        });
    }
};
