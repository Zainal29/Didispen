<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorialVideo extends Model
{
    protected $fillable = [
        'role',
        'title',
        'youtube_url',
        'video_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Helper untuk extract video ID dari URL YouTube
    public static function extractVideoId($url)
    {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $url, $matches);
        return $matches[1] ?? null;
    }
}
