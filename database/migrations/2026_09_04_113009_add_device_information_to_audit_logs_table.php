<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('device_type', 50)
                ->nullable()
                ->after('ip_address');

            $table->string('os', 100)
                ->nullable()
                ->after('device_type');

            $table->string('browser', 100)
                ->nullable()
                ->after('os');

            $table->text('user_agent')
                ->nullable()
                ->after('browser');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn([
                'device_type',
                'os',
                'browser',
                'user_agent',
            ]);
        });
    }
};
