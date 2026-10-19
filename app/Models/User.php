<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Guru;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;


    // protected $fillable = ['name', 'email', 'password', 'role', 'nis_nip'];
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_superadmin',
        'nis_nip',
        'external_id',
        'sipintu_last_synced_at',
        'password_changed_at', 
        'no_wa',               
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password_changed_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'is_superadmin' => 'boolean',
            'sipintu_last_synced_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function siswa(): HasOne
    {
        return $this->hasOne(Siswa::class);
    }

    public function guru(): HasOne
    {
        return $this->hasOne(Guru::class);
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isSuperAdmin(): bool
    {
        return (bool) ($this->is_superadmin ?? false);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->isSuperAdmin();
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    /**
     * Cek apakah user masih menggunakan password bawaan 'password'
     */
    public function isDefaultPassword(): bool
    {
        if ($this->password_changed_at !== null) {
            return false;
        }

        return \Illuminate\Support\Facades\Hash::check('password', $this->password);
    }

    /**
     * Ambil nomor WhatsApp / telepon yang tersedia (dari users.no_wa atau relasi siswa/guru)
     */
    public function getPhoneNumber(): ?string
    {
        return $this->no_wa ?: ($this->siswa?->no_telepon ?: $this->guru?->no_telepon);
    }

    /**
     * Cek apakah user perlu melengkapi keamanan (ganti password default & isi No WA)
     */
    public function needsSecurityUpdate(): bool
    {
        // Hanya berlaku untuk siswa dan guru (admin biasanya mandiri)
        if (! in_array($this->role, ['siswa', 'guru'], true)) {
            return false;
        }

        return $this->isDefaultPassword() || empty($this->getPhoneNumber());
    }

    public function unreadNotifikasi(): HasMany
    {
        return $this->notifikasi()->where('is_read', false);
    }
}
