<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\TimeHelper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function index()
    {
        $jadwalPelajaran = TimeHelper::getAllJadwal();

       return view('admin.settings.index', [
    // Pengaturan Cetak (Existing)
    'print_start_time'    => Setting::get('print_start_time', '06:00'),
    'print_end_time'      => Setting::get('print_end_time', '17:00'),
    'student_print_limit' => Setting::get('student_print_limit', 3),
    'teacher_print_limit' => Setting::get('teacher_print_limit', 10),

    // Pengaturan Jam Operasional Dispensasi
    'dispensasi_start_time'      => Setting::get('dispensasi_start_time', '07:00'),
    'dispensasi_end_time'        => Setting::get('dispensasi_end_time', '15:00'),
    'dispensasi_end_time_friday' => Setting::get('dispensasi_end_time_friday', '14:00'),
    'dispensasi_days'            => explode(',', Setting::get('dispensasi_days', '1,2,3,4,5')),

    // Guru fallback WhatsApp
    'fallback_guru_piket_id' => Setting::get('fallback_guru_piket_id'),

    // Daftar Guru aktif untuk dropdown fallback
    'guruFallback' => Guru::query()
        ->where('status_aktif', true)
        ->orderBy('nama_lengkap')
        ->get(),

    // Jadwal Jam Pelajaran
    'jam_pelajaran' => $jadwalPelajaran,
]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
           

            // Validasi Cetak
            'print_start_time'    => ['required', 'date_format:H:i'],
            'print_end_time'      => ['required', 'date_format:H:i', 'after:print_start_time'],
            'student_print_limit' => ['required', 'integer', 'min:1', 'max:20'],
            'teacher_print_limit' => ['required', 'integer', 'min:1', 'max:50'],

            // Validasi Jam Operasional Dispensasi
            'dispensasi_start_time'      => ['required', 'date_format:H:i'],
            'dispensasi_end_time'        => ['required', 'date_format:H:i', 'after:dispensasi_start_time'],
            'dispensasi_end_time_friday' => ['required', 'date_format:H:i', 'after:dispensasi_start_time'],
            'dispensasi_days'            => ['required', 'array', 'min:1'],

            // Validasi Jadwal Pelajaran (Senin-Selasa: 10 jam)
            'jam_pelajaran_senin_selasa'         => ['required', 'array'],
            'jam_pelajaran_senin_selasa.*.start' => ['required', 'date_format:H:i'],
            'jam_pelajaran_senin_selasa.*.end'   => ['required', 'date_format:H:i'],

            // Validasi Jadwal Pelajaran (Rabu-Kamis: 11 jam)
            'jam_pelajaran_rabu_kamis'           => ['required', 'array'],
            'jam_pelajaran_rabu_kamis.*.start'   => ['required', 'date_format:H:i'],
            'jam_pelajaran_rabu_kamis.*.end'     => ['required', 'date_format:H:i'],

            // Validasi Jadwal Pelajaran (Jumat: 8 jam)
            'jam_pelajaran_jumat'                => ['required', 'array'],
            'jam_pelajaran_jumat.*.start'        => ['required', 'date_format:H:i'],
            'jam_pelajaran_jumat.*.end'          => ['required', 'date_format:H:i'],

            // Validasi Jadwal Pelajaran (Sabtu Testing: 10 jam)
            'jam_pelajaran_sabtu'                => ['required', 'array'],
            'jam_pelajaran_sabtu.*.start'        => ['required', 'date_format:H:i'],
            'jam_pelajaran_sabtu.*.end'          => ['required', 'date_format:H:i'],

            // Validasi Jadwal Pelajaran (Minggu Testing: 10 jam)
            'jam_pelajaran_minggu'               => ['required', 'array'],
            'jam_pelajaran_minggu.*.start'       => ['required', 'date_format:H:i'],
            'jam_pelajaran_minggu.*.end'         => ['required', 'date_format:H:i'],

            'fallback_guru_piket_id' => [
                'nullable',
                'integer',
                Rule::exists('gurus', 'id')->where(
                    fn ($query) => $query->where('status_aktif', true)
                ),
            ],
        ]);

        try {
            // 1. Simpan Pengaturan Cetak
            Setting::set('print_start_time', $data['print_start_time']);
            Setting::set('print_end_time', $data['print_end_time']);
            Setting::set('student_print_limit', $data['student_print_limit']);
            Setting::set('teacher_print_limit', $data['teacher_print_limit']);

            // 2. Simpan Pengaturan Jam Dispensasi
            Setting::set('dispensasi_start_time', $data['dispensasi_start_time']);
            Setting::set('dispensasi_end_time', $data['dispensasi_end_time']);
            Setting::set('dispensasi_end_time_friday', $data['dispensasi_end_time_friday']);
            Setting::set('dispensasi_days', implode(',', $data['dispensasi_days']));

            Setting::set(
                'fallback_guru_piket_id',
                $data['fallback_guru_piket_id'] ?? null
            );
            
            // 3. Simpan Jadwal Jam Pelajaran 5 Pola
            $jadwalPelajaran = [
                'senin_selasa' => $data['jam_pelajaran_senin_selasa'],
                'rabu_kamis'   => $data['jam_pelajaran_rabu_kamis'],
                'jumat'        => $data['jam_pelajaran_jumat'],
                'sabtu'        => $data['jam_pelajaran_sabtu'],
                'minggu'       => $data['jam_pelajaran_minggu'],
            ];
            Setting::set('jam_pelajaran', json_encode($jadwalPelajaran));

            return redirect()->route('admin.settings.index')
                ->with('success', 'Semua pengaturan sistem berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }
}
