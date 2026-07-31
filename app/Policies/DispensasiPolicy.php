<?php
namespace App\Policies;

use App\Models\Dispensasi;
use App\Models\User;

class DispensasiPolicy
{
    public function view(User $user, Dispensasi $dispensasi): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isGuru()) {
            return $dispensasi->guruPiket?->guru?->user_id === $user->id;
        }
        if ($user->isSiswa()) {
            return $dispensasi->siswa?->user_id === $user->id;
        }
        return false;
    }
}
