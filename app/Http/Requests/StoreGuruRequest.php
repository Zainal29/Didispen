<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuruRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // ✅ REVISI SIPINTU: Tidak ada rule password.
        // Password dikelola SiPintu (sudah di-hash saat sinkronisasi).
        $userId = $this->route('guru')?->user_id;
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($userId)],
            'nip' => ['required', 'string', Rule::unique('guru')->ignore($this->route('guru')?->id)],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'mata_pelajaran' => ['nullable', 'string', 'max:255'],
        ];
    }
}
