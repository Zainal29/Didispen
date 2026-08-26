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
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols()],
            'nis_nip' => ['nullable', 'string', 'max:50'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'satpam',
            'nis_nip' => $request->nis_nip ?? 'SATPAM-' . rand(100, 999),
        ]);

        return redirect()->route('admin.satpam.index')->with('success', 'Akun Satpam berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $satpam = User::where('role', 'satpam')->findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $satpam->id],
            'nis_nip' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', Password::min(12)->mixedCase()->numbers()->symbols()], // Password opsional saat diedit
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'nis_nip' => $request->nis_nip,
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