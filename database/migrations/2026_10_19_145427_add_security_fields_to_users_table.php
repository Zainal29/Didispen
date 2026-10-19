<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Mencatat waktu kapan password pertama kali/terakhir diubah
        // Jika null = berarti masih pakai password default!
        $table->timestamp('password_changed_at')->nullable()->after('password');
        
        // Nomor WhatsApp aktif untuk verifikasi dan notifikasi
        if (!Schema::hasColumn('users', 'no_wa')) {
            $table->string('no_wa', 20)->nullable()->after('email');
        }
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['password_changed_at', 'no_wa']);
    });
}

};
