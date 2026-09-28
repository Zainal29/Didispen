<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class StorePiketSwapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isGuru() && auth()->user()->guru !== null;
    }

    public function rules(): array
    {
        return [
            'jadwal_piket_id'   => ['required', 'integer', 'exists:jadwal_piket,id'],
            'guru_pengganti_id' => ['required', 'integer', 'exists:guru,id'],
            'tanggal'           => ['required', 'date'],
            'alasan'            => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'jadwal_piket_id.required'   => 'Jadwal piket wajib dipilih.',
            'jadwal_piket_id.exists'     => 'Jadwal piket tidak valid.',
            'guru_pengganti_id.required' => 'Guru pengganti wajib dipilih.',
            'guru_pengganti_id.exists'   => 'Guru pengganti tidak valid.',
            'tanggal.required'           => 'Tanggal tugas piket wajib diisi.',
            'tanggal.date'               => 'Format tanggal tidak valid.',
            'alasan.max'                 => 'Alasan maksimal 500 karakter.',
        ];
    }
}
