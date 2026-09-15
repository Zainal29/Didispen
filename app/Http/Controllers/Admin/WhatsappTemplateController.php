<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WhatsappTemplateController extends Controller
{
    public function index()
    {
        $templates = WhatsappTemplate::orderBy('name')->get();
        return view('admin.whatsapp-templates.index', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'content' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        WhatsappTemplate::create($validated);

        return redirect()->route('admin.whatsapp-templates.index')
            ->with('success', 'Template WhatsApp berhasil ditambahkan!');
    }

    public function update(Request $request, WhatsappTemplate $whatsappTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'content' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $whatsappTemplate->update($validated);

        return redirect()->route('admin.whatsapp-templates.index')
            ->with('success', 'Template WhatsApp berhasil diperbarui!');
    }

    public function destroy(WhatsappTemplate $whatsappTemplate)
    {
        $whatsappTemplate->delete();
        return redirect()->route('admin.whatsapp-templates.index')
            ->with('success', 'Template berhasil dihapus!');
    }

    /**
     * AJAX Preview untuk melihat hasil render sebelum disimpan
     */
     public function preview(Request $request)
     {
         $request->validate(['content' => 'required|string']);

         $sampleData = [
             'nama_siswa' => 'MUHAMMAD ZAINAL ARIEF',
             'nomor_surat' => 'DISP-2026-0042',
             'catatan' => 'Alasan kurang jelas',
             'waktu_aktual' => now()->format('H:i'),
             'jam_kembali' => 'Jam Pelajaran ke-9',
             'durasi_terlambat' => '15 menit',
         ];

         // ✅ ESCAPE DULU SEBELUM REPLACE
         $content = e($request->content);
         foreach ($sampleData as $key => $value) {
             $content = str_replace('{' . $key . '}', e($value), $content);
         }

         return response()->json(['preview' => nl2br($content)]);
     }
}
