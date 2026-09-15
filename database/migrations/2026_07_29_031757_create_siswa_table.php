<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // ✅ Nullable (digabung dari make_siswa_class_and_major_nullable):
            // siswa hasil sync SiPintu belum tentu punya kelas/jurusan.
            $table->foreignId('jurusan_id')->nullable()->constrained('jurusan');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas');
            $table->string('nama_lengkap');
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_telepon')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
