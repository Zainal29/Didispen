<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:superadmin {identifier : NIS / NIP atau Email User} {--remove : Cabut status Super Admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjadikan akun (Siswa / Guru / Admin) sebagai Super Admin atau mencabut haknya.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $identifier = trim((string) $this->argument('identifier'));
        $isRemove   = $this->option('remove');

        $user = User::where('nis_nip', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (! $user) {
            $this->error("❌ Pengguna dengan NIS/Email '{$identifier}' tidak ditemukan.");
            return Command::FAILURE;
        }

        $newStatus = ! $isRemove;
        $user->is_superadmin = $newStatus;
        $user->save();

        if ($newStatus) {
            $this->info("✅ Berhasil! Pengguna '{$user->name}' ({$user->email} - NIS: {$user->nis_nip}) sekarang berstatus SUPER ADMIN.");
        } else {
            $this->warn("⚠️ Status Super Admin untuk pengguna '{$user->name}' ({$user->email}) telah dicabut.");
        }

        return Command::SUCCESS;
    }
}
