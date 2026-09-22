<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tutorial_videos', function (Blueprint $table) {
            $table->id();
            $table->string('role'); // siswa, guru, satpam, admin
            $table->string('title');
            $table->string('youtube_url');
            $table->string('video_id')->nullable(); // ID video YouTube
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutorial_videos');
    }
};
