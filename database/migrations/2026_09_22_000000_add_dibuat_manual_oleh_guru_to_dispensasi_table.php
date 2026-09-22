<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            $table->boolean('dibuat_manual_oleh_guru')->default(false)->after('catatan_admin');
        });

        // Pertahankan penanda pada data lama yang sebelumnya memakai catatan ini.
        DB::table('dispensasi')
            ->where('catatan_admin', 'like', 'Dibuat manual oleh Guru Piket:%')
            ->update(['dibuat_manual_oleh_guru' => true]);
    }

    public function down(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            $table->dropColumn('dibuat_manual_oleh_guru');
        });
    }
};
