<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TandaTanganController extends Controller
{
    public function __construct(private AuditLogService $auditLog) {}

    public function index()
    {
        $guru = auth()->user()->guru;
        return view('guru.tanda-tangan.index', compact('guru'));
    }

    public function store(Request $request)
    {
        // Validasi inline (menggantikan UploadSignatureRequest)
        $request->validate([
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $guru = auth()->user()->guru;
        $oldData = $guru->only(['digital_signature']);

        // Hapus tanda tangan lama jika ada
        if ($guru->digital_signature) {
            Storage::disk('public')->delete($guru->digital_signature);
        }

        // Simpan file baru ke folder storage/app/public/signatures
        $file = $request->file('signature');
        $path = $file->store('signatures', 'public');

        // Update database
        $guru->update([
            'digital_signature' => $path,
        ]);

        // Catat ke audit log
        $this->auditLog->log(
            auth()->id(), 
            'upload_signature', 
            'guru', 
            $guru->id, 
            $oldData, 
            ['digital_signature' => $path]
        );

        return redirect()->route('guru.tanda-tangan.index')
            ->with('success', 'Tanda tangan digital berhasil diupload.');
    }

    public function destroy()
    {
        $guru = auth()->user()->guru;

        if ($guru->digital_signature) {
            // Hapus file dari storage
            Storage::disk('public')->delete($guru->digital_signature);
            
            $oldData = $guru->only(['digital_signature']);
            
            // Update database jadi null
            $guru->update([
                'digital_signature' => null,
            ]);

            // Catat ke audit log
            $this->auditLog->log(
                auth()->id(), 
                'delete_signature', 
                'guru', 
                $guru->id, 
                $oldData, 
                null
            );
        }

        return redirect()->route('guru.tanda-tangan.index')
            ->with('success', 'Tanda tangan digital berhasil dihapus.');
    }
}