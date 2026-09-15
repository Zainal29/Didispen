<?php
namespace App\Services;

use App\Models\Notifikasi;

class NotifikasiService
{
    public function send(int $userId, string $message, ?string $link = null): Notifikasi
    {
        return Notifikasi::create([
            'user_id' => $userId,
            'message' => $message,
            'link' => $link,
        ]);
    }

    public function markAsRead(int $id): void
    {
        Notifikasi::where('id', $id)->update(['is_read' => true]);
    }

    public function markAllAsRead(int $userId): void
    {
        Notifikasi::where('user_id', $userId)->update(['is_read' => true]);
    }
}
