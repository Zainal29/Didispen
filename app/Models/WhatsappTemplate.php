<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WhatsappTemplate extends Model
{
    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'name',
        'slug',
        'content',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    /**
     * Render template dengan mengganti variabel {variabel} dengan data asli
     */
    public function render(array $data): string
    {
        $content = $this->content;

        foreach ($data as $key => $value) {
            $content = str_replace('{' . $key . '}', $value ?? '-', $content);
        }

        return $content;
    }

    /**
     * Ambil variabel yang tersedia di template ini (untuk info di UI)
     */
    public function getAvailableVariablesAttribute(): array
    {
        preg_match_all('/\{(\w+)\}/', $this->content, $matches);
        return array_unique($matches[1] ?? []);
    }
}
