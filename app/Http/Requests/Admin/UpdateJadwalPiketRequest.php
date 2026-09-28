<?php

namespace App\Http\Requests\Admin;

use App\Models\Guru;
use App\Models\JadwalPiket;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJadwalPiketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'hari' => ['required', 'integer', 'between:1,7'],
            'nama_sesi' => ['nullable', 'string', 'max:50'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'koordinator_guru_id' => ['nullable', 'exists:guru,id'],
            'tanggal_mulai_berlaku' => ['required', 'date'],
            'tanggal_selesai_berlaku' => ['nullable', 'date', 'after_or_equal:tanggal_mulai_berlaku'],
            'guru' => ['required', 'array', 'min:1'],
            'guru.*' => ['required', 'exists:guru,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'hari.required' => 'Hari piket wajib dipilih.',
            'hari.between' => 'Pilihan hari tidak valid (1-7).',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai harus HH:MM (contoh: 07:00).',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.date_format' => 'Format jam selesai harus HH:MM (contoh: 09:30).',
            'jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.',
            'tanggal_mulai_berlaku.required' => 'Tanggal mulai berlaku wajib diisi.',
            'tanggal_mulai_berlaku.date' => 'Format tanggal mulai berlaku tidak valid.',
            'tanggal_selesai_berlaku.date' => 'Format tanggal selesai berlaku tidak valid.',
            'tanggal_selesai_berlaku.after_or_equal' => 'Tanggal selesai berlaku tidak boleh sebelum tanggal mulai berlaku.',
            'guru.required' => 'Pilih minimal satu Guru Piket.',
            'guru.array' => 'Data guru piket harus berupa array.',
            'guru.min' => 'Pilih minimal satu Guru Piket.',
            'guru.*.exists' => 'Guru yang dipilih tidak terdaftar di sistem.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 1. Validasi guru yang dipilih harus aktif
            if ($this->has('guru') && is_array($this->guru)) {
                $hasInactiveGuru = Guru::whereIn('id', $this->guru)
                    ->where('status_aktif', false)
                    ->exists();

                if ($hasInactiveGuru) {
                    $validator->errors()->add('guru', 'Guru yang dipilih harus berstatus aktif.');
                }
            }

            // 2. Validasi jadwal overlap jika validasi field dasar lolos
            if (! $validator->errors()->has('hari') &&
                ! $validator->errors()->has('jam_mulai') &&
                ! $validator->errors()->has('jam_selesai') &&
                ! $validator->errors()->has('tanggal_mulai_berlaku')) {

                $currentJadwal = $this->route('jadwal');
                $currentId = $currentJadwal instanceof JadwalPiket ? $currentJadwal->id : (int) $currentJadwal;

                $hari = (int) $this->hari;
                $jamMulai = strlen($this->jam_mulai) === 5 ? $this->jam_mulai . ':00' : $this->jam_mulai;
                $jamSelesai = strlen($this->jam_selesai) === 5 ? $this->jam_selesai . ':00' : $this->jam_selesai;
                $tglMulai = $this->tanggal_mulai_berlaku;
                $tglSelesai = $this->tanggal_selesai_berlaku;

                $query = JadwalPiket::where('is_active', true)
                    ->where('id', '!=', $currentId)
                    ->where('hari', $hari)
                    ->where('jam_mulai', '<', $jamSelesai)
                    ->where('jam_selesai', '>', $jamMulai);

                $query->where(function ($q) use ($tglMulai, $tglSelesai) {
                    $q->where(function ($sub) use ($tglSelesai) {
                        if ($tglSelesai) {
                            $sub->whereDate('tanggal_mulai_berlaku', '<=', $tglSelesai);
                        }
                    })->where(function ($sub) use ($tglMulai) {
                        $sub->whereNull('tanggal_selesai_berlaku')
                            ->orWhereDate('tanggal_selesai_berlaku', '>=', $tglMulai);
                    });
                });

                if ($query->exists()) {
                    $validator->errors()->add('jam_mulai', 'Jadwal bertabrakan dengan jadwal lain pada hari dan periode yang sama.');
                }
            }
        });
    }
}
