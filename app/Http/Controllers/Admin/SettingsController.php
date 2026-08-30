<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Tampilkan halaman pengaturan.
     */
    public function index()
    {
        return view('admin.settings.index', [
            'print_start_time'    => Setting::get('print_start_time', '06:00'),
            'print_end_time'      => Setting::get('print_end_time', '17:00'),
            'student_print_limit' => Setting::get('student_print_limit', 3),
            'teacher_print_limit' => Setting::get('teacher_print_limit', 10),
        ]);
    }

    /**
     * Simpan perubahan pengaturan.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'print_start_time'    => ['required', 'date_format:H:i'],
            'print_end_time'      => ['required', 'date_format:H:i', 'after:print_start_time'],
            'student_print_limit' => ['required', 'integer', 'min:1', 'max:20'],
            'teacher_print_limit' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        try {
            Setting::set('print_start_time', $data['print_start_time']);
            Setting::set('print_end_time', $data['print_end_time']);
            Setting::set('student_print_limit', $data['student_print_limit']);
            Setting::set('teacher_print_limit', $data['teacher_print_limit']);

            return redirect()->route('admin.settings.index')
                ->with('success', 'Pengaturan sistem berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }
}
