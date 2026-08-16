<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guru_piket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            
            // ✅ 7 HARI: Senin sampai Minggu (otomatis, tidak perlu input manual)
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'])
                  ->unique();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_piket');
    }
};