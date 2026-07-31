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
            $table->date('tanggal');
            $table->enum('shift', ['pagi', 'siang']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_piket');
    }
};
