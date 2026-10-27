<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SatpamController extends Controller
{
    public function index()
    {
        // Mengambil semua user dengan role satpam
        $satpams = User::where('role', 'satpam')->latest()->get();
        return view('admin.satpam.index', compact('satpams'));
    }

    public function store(Request $request)
    {
        // Persyaratan dibuat mudah & fleksibel untuk petugas satpam
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:4'],
            'nis_nip' => ['nullable', 'string', 'max:50', 'unique:users,nis_nip'],
        ], [
            'name.required' => 'Nama lengkap satpam wajib diisi.',
            'email.required' => 'Email atau username login wajib diisi.',
            'email.unique' => 'Email / Username ini sudah digunakan oleh akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal :min karakter.',
            'nis_nip.unique' => 'ID / NIP Satpam sudah digunakan.',
        ]);

        User::create([
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'role' => 'satpam',
            'nis_nip' => $request->filled('nis_nip') ? trim($request->nis_nip) : 'SATPAM-' . rand(100, 999),
        ]);

        return redirect()->route('admin.satpam.index')->with('success', 'Akun Satpam berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $satpam = User::where('role', 'satpam')->findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'unique:users,email,' . $satpam->id],
            'nis_nip' => ['nullable', 'string', 'max:50', 'unique:users,nis_nip,' . $satpam->id],
            'password' => ['nullable', 'string', 'min:4'], // Password opsional saat diedit
        ], [
            'name.required' => 'Nama lengkap satpam wajib diisi.',
            'email.required' => 'Email atau username login wajib diisi.',
            'email.unique' => 'Email / Username ini sudah digunakan oleh akun lain.',
            'password.min' => 'Password baru minimal :min karakter.',
            'nis_nip.unique' => 'ID / NIP Satpam sudah digunakan.',
        ]);

        $data = [
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'nis_nip' => $request->filled('nis_nip') ? trim($request->nis_nip) : $satpam->nis_nip,
        ];

        // Jika password diisi, update passwordnya. Jika kosong, abaikan.
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $satpam->update($data);

        return redirect()->route('admin.satpam.index')->with('success', 'Data Satpam berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $satpam = User::where('role', 'satpam')->findOrFail($id);
        $satpam->delete();

        return redirect()->route('admin.satpam.index')->with('success', 'Akun Satpam berhasil dihapus!');
    }
}