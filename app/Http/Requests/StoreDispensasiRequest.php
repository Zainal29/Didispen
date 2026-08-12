<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDispensasiRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'kategori' => ['required', 'in:pribadi,kesehatan,acara_sekolah,keluarga,lainnya'],
            'alasan' => ['required', 'string', 'min:10', 'max:500'],
            'tujuan' => ['required', 'string', 'min:5', 'max:255'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'jam_keluar' => ['required', 'date', 'after:now'],
            'jam_kembali' => ['required', 'date', 'after:jam_keluar'],
        ];
    }
}
