<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            $table->string('qr_token', 64)->nullable()->unique()->after('qr_code');
        });

        DB::table('dispensasi')
            ->whereNull('qr_token')
            ->orderBy('id')
            ->eachById(function (object $dispensasi): void {
                DB::table('dispensasi')
                    ->where('id', $dispensasi->id)
                    ->update(['qr_token' => Str::random(64)]);
            });
    }

    public function down(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            $table->dropUnique(['qr_token']);
            $table->dropColumn('qr_token');
        });
    }
};
