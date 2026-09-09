@extends('siswa.layouts.app')

@section('title', 'Riwayat Pengajuan')
@section('page-title', 'Riwayat Pengajuan Dispensasi')

@section('content')
@include('components.alert')

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    {{-- Header + Filter --}}
    <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-gray-900"><i class="fas fa-clock-rotate-left mr-1.5 text-blue-600"></i>Riwayat Pengajuan</h3>
            <p class="text-[11px] text-gray-500 mt-0.5">Pantau seluruh pengajuan dispensasi Anda.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" class="flex-1 sm:flex-none">
                <select name="status" onchange="this.form.submit()"
                        class="w-full sm:w-auto h-10 px-3 rounded-xl border-2 border-gray-200 bg-white text-xs font-bold text-gray-600 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all">
                    <option value="">Semua Status</option>
                    <option value="menunggu"  {{ request('status') == 'menunggu'  ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak"   {{ request('status') == 'ditolak'   ? 'selected' : '' }}>Ditolak</option>
                    <option value="keluar"    {{ request('status') == 'keluar'    ? 'selected' : '' }}>Sedang Keluar</option>
                    <option value="selesai"   {{ request('status') == 'selesai'   ? 'selected' : '' }}>Selesai</option>
                </select>
            </form>
            <a href="{{ route('siswa.pengajuan.create') }}"
               class="inline-flex items-center px-3.5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 shadow-lg shadow-blue-500/30 flex-shrink-0">
                <i class="fas fa-plus mr-1.5"></i>Buat
            </a>
        </div>
    </div>

    {{-- ===== MOBILE: Kartu Riwayat ===== --}}
    <div class="md:hidden divide-y divide-gray-100">
        @forelse($pengajuan as $p)
            @php
                $badges = [
                    'menunggu'  => 'bg-amber-100 text-amber-700 border-amber-200',
                    'disetujui' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'ditolak'   => 'bg-red-100 text-red-700 border-red-200',
                    'keluar'    => 'bg-sky-100 text-sky-700 border-sky-200',
                    'selesai'   => 'bg-gray-100 text-gray-600 border-gray-200',
                ];
            @endphp
            <div class="p-4">
                <div class="flex justify-between items-start gap-2 mb-1.5">
                    <p class="font-mono font-bold text-gray-800 text-xs">{{ $p->nomor_surat }}</p>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border flex-shrink-0 {{ $badges[$p->status] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                        {{ ucfirst($p->status) }}
                    </span>
                </div>
                <p class="text-[11px] text-gray-500">{{ $p->created_at->format('d/m/Y') }} • <span class="capitalize">{{ str_replace('_', ' ', $p->kategori) }}</span></p>
                <p class="text-[11px] text-gray-500 mt-0.5 truncate"><i class="far fa-clock mr-1"></i>{{ $p->jam_keluar }} – {{ $p->jam_kembali }} • {{ $p->tujuan }}</p>

                <div class="flex items-center justify-between mt-3">
                    <a href="{{ route('siswa.pengajuan.show', $p) }}"
                       class="inline-flex items-center px-3.5 py-2 rounded-xl text-[11px] font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 shadow-md shadow-blue-500/20 active:scale-95 transition-transform">
                        <i class="fas fa-eye mr-1.5"></i>Detail
                    </a>

                    {{-- <i class="fas fa-check-circle"></i> TOMBOL FOTO BUKTI (MOBILE) --}}
                    @if($p->status === 'keluar')
                        @if($p->foto_bukti)
                            <button onclick="showPreview('{{ asset('storage/' . $p->foto_bukti) }}', '{{ $p->nomor_surat }}')" class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 active:bg-emerald-600 active:text-white transition-colors flex items-center justify-center" title="Lihat Foto Bukti">
                                <i class="fas fa-image"></i>
                            </button>
                        @else
                            <button onclick="openUploadModal({{ $p->id }})" class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 active:bg-blue-600 active:text-white transition-colors flex items-center justify-center" title="Upload Foto Bukti">
                                <i class="fas fa-camera"></i>
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="p-10 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-300 flex items-center justify-center text-2xl mb-3"><i class="fas fa-inbox"></i></div>
                <p class="text-gray-500 font-semibold text-sm">Belum ada pengajuan dispensasi</p>
            </div>
        @endforelse
    </div>

    {{-- ===== DESKTOP: Tabel ===== --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="p-4 text-left">No. Surat</th>
                    <th class="p-4 text-left">Tanggal</th>
                    <th class="p-4 text-left">Kategori</th>
                    <th class="p-4 text-left">Tujuan</th>
                    <th class="p-4 text-left">Waktu</th>
                    <th class="p-4 text-left">Status</th>
                    <th class="p-4 text-center">Foto Bukti</th> {{-- <i class="fas fa-check-circle"></i> DIGANTI DARI QR CODE --}}
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pengajuan as $p)
                    @php
                        $badges = [
                            'menunggu'  => 'bg-amber-100 text-amber-700 border-amber-200',
                            'disetujui' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'ditolak'   => 'bg-red-100 text-red-700 border-red-200',
                            'keluar'    => 'bg-sky-100 text-sky-700 border-sky-200',
                            'selesai'   => 'bg-gray-100 text-gray-600 border-gray-200',
                        ];
                    @endphp
                    <tr class="hover:bg-blue-50/40 transition-colors">
                        <td class="p-4 font-mono font-bold text-gray-800 text-xs">{{ $p->nomor_surat }}</td>
                        <td class="p-4 text-gray-500">{{ $p->created_at->format('d/m/Y') }}</td>
                        <td class="p-4 capitalize text-gray-600">{{ str_replace('_', ' ', $p->kategori) }}</td>
                        <td class="p-4 text-gray-600">{{ $p->tujuan }}</td>
                        <td class="p-4 text-gray-500 text-xs">{{ $p->jam_keluar }} – {{ $p->jam_kembali }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $badges[$p->status] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        {{-- <i class="fas fa-check-circle"></i> KOLOM DATA FOTO BUKTI --}}
                        <td class="p-4 text-center">
                            @if($p->status === 'keluar')
                                @if($p->foto_bukti)
                                    <div class="flex items-center justify-center gap-2">
                                        <img src="{{ asset('storage/' . $p->foto_bukti) }}"
                                             class="w-10 h-10 object-cover rounded-lg border border-emerald-300 cursor-pointer hover:scale-110 transition-transform"
                                             onclick="showPreview('{{ asset('storage/' . $p->foto_bukti) }}', '{{ $p->nomor_surat }}')">
                                        <button onclick="hapusFoto({{ $p->id }})" class="text-red-500 hover:text-red-700 p-1" title="Hapus Foto">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                @else
                                    <button onclick="openUploadModal({{ $p->id }})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors shadow-sm">
                                        <i class="fas fa-camera mr-1"></i> Upload
                                    </button>
                                @endif
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center"> {{-- <i class="fas fa-check-circle"></i> Colspan disesuaikan jadi 7 --}}
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-300 flex items-center justify-center text-2xl mb-3"><i class="fas fa-inbox"></i></div>
                            <p class="text-gray-500 font-semibold text-sm">Belum ada pengajuan dispensasi</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pengajuan->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">{{ $pengajuan->links() }}</div>
    @endif
</div>

{{-- ============ MODAL UPLOAD FOTO BUKTI ============ --}}
<div id="uploadModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl">
        <h3 class="text-lg font-bold mb-4 flex items-center"><i class="fas fa-camera text-emerald-600 mr-2"></i>Upload Foto Bukti</h3>
        <form id="uploadForm" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" id="dispensasiId">

            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center">
                <img id="previewImg" class="hidden max-h-48 mx-auto rounded-lg mb-3 shadow-sm">
                <p class="text-sm text-gray-600 mb-3">Foto akan dikompres otomatis agar cepat diupload</p>
                <label class="px-4 py-2 bg-blue-600 text-white rounded-lg cursor-pointer hover:bg-blue-700 transition-colors inline-flex items-center">
                    <i class="fas fa-image mr-2"></i> Pilih Foto / Kamera
                    <input type="file" id="fotoInput" name="foto_bukti" accept="image/*" capture="environment" class="hidden" required>
                </label>
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="closeUploadModal()" class="flex-1 py-2.5 bg-gray-200 rounded-xl font-bold hover:bg-gray-300 transition-colors">Batal</button>
                <button type="submit" id="btnSubmit" class="flex-1 py-2.5 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 transition-colors shadow-md shadow-emerald-500/20">Upload</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
{{-- <i class="fas fa-check-circle"></i> Library Auto-Compress Client-Side --}}
<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>

<script>
// 1. Modal Functions
function openUploadModal(id) {
    document.getElementById('dispensasiId').value = id;
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('uploadForm').reset();
    document.getElementById('previewImg').classList.add('hidden');
}

// 2. Auto-Compress Logic
document.getElementById('fotoInput').addEventListener('change', async function(e) {
    const file = e.target.files[0];
    if (!file) return;

    Swal.fire({ title: 'Mengompres foto...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const options = { maxSizeMB: 0.5, maxWidthOrHeight: 1024, useWebWorker: true };
        const compressedFile = await imageCompression(file, options);

        // <i class="fas fa-check-circle"></i> PERBAIKAN: Simpan compressedFile ke variabel global untuk form submit
        window.compressedFotoBukti = compressedFile;

        // Preview
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('previewImg').src = ev.target.result;
            document.getElementById('previewImg').classList.remove('hidden');
        };
        reader.readAsDataURL(compressedFile);
        Swal.close();
    } catch (error) {
        Swal.close();
        Swal.fire('Error', 'Gagal mengompres: ' + error.message, 'error');
    }
});

// 3. Handle Submit Upload
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('dispensasiId').value;
    const formData = new FormData(this);

    // <i class="fas fa-check-circle"></i> PERBAIKAN: Hapus file lama dan tambahkan compressed file
    formData.delete('foto_bukti');
    if (window.compressedFotoBukti) {
        formData.append('foto_bukti', window.compressedFotoBukti, window.compressedFotoBukti.name);
    }

    const btn = document.getElementById('btnSubmit');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Mengupload...';
    btn.disabled = true;

    try {
        const res = await fetch(`/siswa/pengajuan/${id}/upload-foto-bukti`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1500, showConfirmButton: false }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Terjadi kesalahan jaringan', 'error');
    } finally {
        btn.innerHTML = 'Upload';
        btn.disabled = false;
        window.compressedFotoBukti = null; // Reset
    }
});

// 4. Hapus Foto
async function hapusFoto(id) {
    if (await Swal.fire({ title: 'Hapus foto?', text: 'Foto bukti akan dihapus permanen.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Ya, Hapus' }).then(r => r.isConfirmed)) {
        try {
            const res = await fetch(`/siswa/pengajuan/${id}/hapus-foto-bukti`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', timer: 1500, showConfirmButton: false }).then(() => location.reload());
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Gagal menghapus foto', 'error');
        }
    }
}

// 5. Preview Foto Besar
function showPreview(url, nomorSurat) {
    Swal.fire({
        imageUrl: url,
        imageAlt: 'Foto Bukti ' + nomorSurat,
        showConfirmButton: false,
        background: '#fff',
        padding: '1rem'
    });
}
</script>
@endpush
@endsection
