<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $siswa = $this->route('siswa');
        $userId = $siswa ? $siswa->user_id : null;

        return [
            'nama_lengkap' => 'required|string|max:255',
            'nis_nip'      => [
                'required', 'string', 'max:50', 
                Rule::unique('users', 'nis_nip')->ignore($userId)
            ],
            'email'        => [
                'required', 'string', 'email', 'max:255', 
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password'     => $this->isMethod('post') ? 'required|string|min:6' : 'nullable|string|min:6',
            'jurusan_id'   => 'required|exists:jurusan,id',
            'kelas_id'     => 'required|exists:kelas,id',
            'tanggal_lahir'=> 'nullable|date',
            'alamat'       => 'nullable|string|max:500',
            'no_telepon'   => 'nullable|string|max:15',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nis_nip.required'      => 'NIS wajib diisi.',
            'nis_nip.unique'        => 'NIS ini sudah terdaftar.',
            'email.required'        => 'Email wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.unique'          => 'Email sudah terdaftar.',
            'password.required'     => 'Password wajib diisi.',
            'password.min'          => 'Password minimal 6 karakter.',
            'jurusan_id.required'   => 'Jurusan wajib dipilih.',
            'kelas_id.required'     => 'Kelas wajib dipilih.',
        ];
    }
}