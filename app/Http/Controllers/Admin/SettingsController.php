<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            // Pengaturan Cetak (Existing)
            'print_start_time'    => Setting::get('print_start_time', '06:00'),
            'print_end_time'      => Setting::get('print_end_time', '17:00'),
            'student_print_limit' => Setting::get('student_print_limit', 3),
            'teacher_print_limit' => Setting::get('teacher_print_limit', 10),

            // ✅ Pengaturan Jam Dispensasi (Baru)
            'dispensasi_start_time'       => Setting::get('dispensasi_start_time', '07:00'),
            'dispensasi_end_time'         => Setting::get('dispensasi_end_time', '15:00'),
            'dispensasi_end_time_friday'  => Setting::get('dispensasi_end_time_friday', '14:00'),
            'dispensasi_days'             => explode(',', Setting::get('dispensasi_days', '1,2,3,4,5')),
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
        ]);

        try {
            // Simpan Pengaturan Cetak
            Setting::set('print_start_time', $data['print_start_time']);
            Setting::set('print_end_time', $data['print_end_time']);
            Setting::set('student_print_limit', $data['student_print_limit']);
            Setting::set('teacher_print_limit', $data['teacher_print_limit']);

            // ✅ Simpan Pengaturan Jam Dispensasi
            Setting::set('dispensasi_start_time', $data['dispensasi_start_time']);
            Setting::set('dispensasi_end_time', $data['dispensasi_end_time']);
            Setting::set('dispensasi_end_time_friday', $data['dispensasi_end_time_friday']);
            Setting::set('dispensasi_days', implode(',', $data['dispensasi_days']));

            return redirect()->route('admin.settings.index')
                ->with('success', 'Semua pengaturan sistem berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }
}
