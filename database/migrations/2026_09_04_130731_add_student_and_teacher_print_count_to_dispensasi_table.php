<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            if (! Schema::hasColumn('dispensasi', 'student_print_count')) {
                $table->unsignedInteger('student_print_count')
                    ->default(0)
                    ->after('printed_at');
            }

            if (! Schema::hasColumn('dispensasi', 'teacher_print_count')) {
                $table->unsignedInteger('teacher_print_count')
                    ->default(0)
                    ->after('student_print_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dispensasi', function (Blueprint $table) {
            if (Schema::hasColumn('dispensasi', 'teacher_print_count')) {
                $table->dropColumn('teacher_print_count');
            }

            if (Schema::hasColumn('dispensasi', 'student_print_count')) {
                $table->dropColumn('student_print_count');
            }
        });
    }
};
