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
            'print_start_time' => Setting::get('print_start_time', '06:00'),
            'print_end_time'   => Setting::get('print_end_time', '17:00'),
            'print_max_limit'  => Setting::get('print_max_limit', 3),
        ]);
    }

    /**
     * Simpan perubahan pengaturan.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'print_start_time' => ['required', 'date_format:H:i'],
            'print_end_time'   => ['required', 'date_format:H:i', 'after:print_start_time'],
            'print_max_limit'  => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        try {
            // Simpan ke database
            Setting::set('print_start_time', $data['print_start_time']);
            Setting::set('print_end_time', $data['print_end_time']);
            Setting::set('print_max_limit', $data['print_max_limit']);

            return redirect()->route('admin.settings.index')
                ->with('success', 'Pengaturan sistem berhasil diperbarui.');
                
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }
}