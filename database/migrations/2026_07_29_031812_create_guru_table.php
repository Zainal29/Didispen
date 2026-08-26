<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nip')->unique();
            $table->string('nama_lengkap');
            $table->string('mata_pelajaran')->nullable();
            $table->string('no_telepon')->nullable();   // ✅ GABUNGAN: no. HP dari SIJUNA (field `hp`)
            $table->text('alamat')->nullable();         // ✅ GABUNGAN: alamat dari SIJUNA
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru');
    }
};
