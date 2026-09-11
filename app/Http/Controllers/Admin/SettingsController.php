<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        // Default jadwal jika belum pernah disimpan
        $defaultJadwal = json_encode([
            'regular' => [
                1 => ['start' => '07:00', 'end' => '07:45'], 2 => ['start' => '07:45', 'end' => '08:30'],
                3 => ['start' => '08:30', 'end' => '09:15'], 4 => ['start' => '09:30', 'end' => '10:15'],
                5 => ['start' => '10:15', 'end' => '11:00'], 6 => ['start' => '11:00', 'end' => '11:45'],
                7 => ['start' => '12:15', 'end' => '13:00'], 8 => ['start' => '13:00', 'end' => '13:45'],
                9 => ['start' => '13:45', 'end' => '14:30'], 10 => ['start' => '14:30', 'end' => '15:15'],
            ],
            'friday' => [
                1 => ['start' => '07:00', 'end' => '07:45'], 2 => ['start' => '07:45', 'end' => '08:30'],
                3 => ['start' => '08:30', 'end' => '09:15'], 4 => ['start' => '09:30', 'end' => '10:15'],
                5 => ['start' => '10:15', 'end' => '11:00'], 6 => ['start' => '11:00', 'end' => '11:45'],
                7 => ['start' => '12:15', 'end' => '13:00'], 8 => ['start' => '13:00', 'end' => '13:50'],
            ]
        ]);

        return view('admin.settings.index', [
            // Pengaturan Cetak (Existing)
            'print_start_time'    => Setting::get('print_start_time', '06:00'),
            'print_end_time'      => Setting::get('print_end_time', '17:00'),
            'student_print_limit' => Setting::get('student_print_limit', 3),
            'teacher_print_limit' => Setting::get('teacher_print_limit', 10),

            // ✅ Pengaturan Jam Dispensasi
            'dispensasi_start_time'       => Setting::get('dispensasi_start_time', '07:00'),
            'dispensasi_end_time'         => Setting::get('dispensasi_end_time', '15:00'),
            'dispensasi_end_time_friday'  => Setting::get('dispensasi_end_time_friday', '14:00'),
            'dispensasi_days'             => explode(',', Setting::get('dispensasi_days', '1,2,3,4,5')),

            // ✅ PERBAIKAN: Kirim variabel jam_pelajaran ke view agar tidak undefined
            'jam_pelajaran' => Setting::get('jam_pelajaran', $defaultJadwal),
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

            // ✅ Validasi Jam Dispensasi
            'dispensasi_start_time'      => ['required', 'date_format:H:i'],
            'dispensasi_end_time'        => ['required', 'date_format:H:i', 'after:dispensasi_start_time'],
            'dispensasi_end_time_friday' => ['required', 'date_format:H:i', 'after:dispensasi_start_time'],
            'dispensasi_days'            => ['required', 'array', 'min:1'],

            // ✅ TAMBAHKAN INI: Validasi input Jam Pelajaran dari form
            'jam_pelajaran_regular' => ['required', 'array'],
            'jam_pelajaran_regular.*.start' => ['required', 'date_format:H:i'],
            'jam_pelajaran_regular.*.end'   => ['required', 'date_format:H:i'],

            'jam_pelajaran_friday'  => ['required', 'array'],
            'jam_pelajaran_friday.*.start'  => ['required', 'date_format:H:i'],
            'jam_pelajaran_friday.*.end'    => ['required', 'date_format:H:i'],
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

            // 3. ✅ TAMBAHKAN INI: Simpan Jadwal Jam Pelajaran sebagai JSON
            $jadwalPelajaran = [
                'regular' => $data['jam_pelajaran_regular'],
                'friday'  => $data['jam_pelajaran_friday'],
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
