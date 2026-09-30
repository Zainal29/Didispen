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
            'name' => 'required|string|max:100|unique:whatsapp_templates,name',
            'content' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active') ? true : false;

        WhatsappTemplate::create($validated);
        \App\Services\WhatsappMessageService::clearCache();

        return redirect()->route('admin.whatsapp-templates.index')
            ->with('success', 'Template WhatsApp berhasil ditambahkan!');
    }

    public function update(Request $request, WhatsappTemplate $whatsappTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:whatsapp_templates,name,' . $whatsappTemplate->id,
            'content' => 'required|string',
            'is_active' => 'nullable',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $whatsappTemplate->update($validated);
        \App\Services\WhatsappMessageService::clearCache();

        return redirect()->route('admin.whatsapp-templates.index')
            ->with('success', 'Template WhatsApp berhasil diperbarui!');
    }

    public function destroy(WhatsappTemplate $whatsappTemplate)
    {
        $whatsappTemplate->delete();
        \App\Services\WhatsappMessageService::clearCache();

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
            // Variabel Siswa / Dispensasi
            'nama_siswa'      => 'MUHAMMAD ZAINAL ARIEF',
            'nis'             => '4717',
            'kelas'           => 'XII PPLG 2',
            'jurusan'         => 'PPLG',
            'nomor_surat'     => 'DISP-2026-0042',
            'kategori'        => 'Keperluan Sekolah',
            'catatan'         => 'Alasan tugas OSIS di luar sekolah',
            'waktu_aktual'    => now()->format('H:i'),
            'jam_keluar'      => 'Jam Pelajaran ke-3',
            'jam_kembali'     => 'Jam Pelajaran ke-7',
            'durasi_terlambat' => '15 menit',
            'tujuan'          => 'Dinas Pendidikan Kabupaten Jepara',
            'lokasi'          => 'Jepara',
            'alasan'          => 'Rapat koordinasi pengurus OSIS',
            'link_dispensasi' => rtrim(config('app.url', 'https://didispen.smkn1bangsri.sch.id'), '/') . '/siswa/pengajuan/123',
            'link_web'        => rtrim(config('app.url', 'https://didispen.smkn1bangsri.sch.id'), '/'),

            // Variabel Guru / Piket
            'nama_guru'   => 'Budi Santoso, S.Pd.',
            'hari'        => 'Senin',
            'nama_sesi'   => 'Sesi 1',
            'jam_mulai'   => '07:00',
            'jam_selesai' => '09:30',
            'koordinator' => "Almu'idul 'Afwan, S.Pd.",
            'tanggal'     => now()->translatedFormat('d F Y'),
        ];

        // Escape dulu sebelum replace agar aman
        $content = e($request->content);
        foreach ($sampleData as $key => $value) {
            $content = str_replace('{' . $key . '}', e($value), $content);
        }

        // Deteksi placeholder yang tidak dikenal (masih ada {xxx} setelah replace)
        preg_match_all('/\{(\w+)\}/', $request->content, $matches);
        $allPlaceholders   = array_unique($matches[1] ?? []);
        $knownPlaceholders = array_keys($sampleData);
        $unknownPlaceholders = array_diff($allPlaceholders, $knownPlaceholders);

        return response()->json([
            'preview'             => nl2br($content),
            'unknown_placeholders' => array_values($unknownPlaceholders),
        ]);
    }
}
