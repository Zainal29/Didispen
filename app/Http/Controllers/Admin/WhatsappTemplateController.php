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
        // $validated = $request->validate([
        //     'name' => 'required|string|max:100',
        //     'content' => 'required|string',
        //     'is_active' => 'boolean',
        // ]);
        $validated = $request->validate([
                   // ✅ Tambahkan validasi unique agar tidak bisa membuat nama yang sama
                   'name' => 'required|string|max:100|unique:whatsapp_templates,name',
                   'content' => 'required|string',
                   'is_active' => 'nullable|boolean', // nullable agar tidak error saat checkbox tidak dicentang
               ]);

                   $validated['slug'] = Str::slug($validated['name']);
                   // ✅ Konversi checkbox ke boolean yang benar
                   $validated['is_active'] = $request->has('is_active') ? true : false;

                   WhatsappTemplate::create($validated);

                   return redirect()->route('admin.whatsapp-templates.index')
                       ->with('success', 'Template WhatsApp berhasil ditambahkan!');
               }
    //     $validated['slug'] = Str::slug($validated['name']);
    //     WhatsappTemplate::create($validated);

    //     return redirect()->route('admin.whatsapp-templates.index')
    //         ->with('success', 'Template WhatsApp berhasil ditambahkan!');
    // }

    public function update(Request $request, WhatsappTemplate $whatsappTemplate)
    {
        // $validated = $request->validate([
        //     'name' => 'required|string|max:100',
        //     'content' => 'required|string',
        //     'is_active' => 'boolean',
        // ]);

        $validated = $request->validate([
                'name' => 'required|string|max:100|unique:whatsapp_templates,name,' . $whatsappTemplate->id,
                'content' => 'required|string',
                // ✅ HAPUS validasi boolean
                'is_active' => 'nullable',
            ]);

            $validated['slug'] = Str::slug($validated['name']);
            // ✅ Konversi checkbox ke boolean yang benar
            $validated['is_active'] = $request->has('is_active') ? true : false;

            $whatsappTemplate->update($validated);

            return redirect()->route('admin.whatsapp-templates.index')
                ->with('success', 'Template WhatsApp berhasil diperbarui!');
        }

    //     $validated['slug'] = Str::slug($validated['name']);
    //     $whatsappTemplate->update($validated);

    //     return redirect()->route('admin.whatsapp-templates.index')
    //         ->with('success', 'Template WhatsApp berhasil diperbarui!');
    // }

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
