@extends('admin.layouts.app')

@section('title', 'Detail Pengajuan')
@section('page-title', 'Detail Pengajuan Dispensasi')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800">{{ $dispensasi->nomor_surat }}</h3>
                <p class="text-sm text-gray-500">Diajukan: {{ $dispensasi->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i:s') }} WIB</p>
            </div>
            @php
                $statusColors = [
                    'menunggu' => 'bg-yellow-100 text-yellow    -800',
                    'disetujui' => 'bg-green-100 text-green-800',
                    'ditolak' => 'bg-red-100 text-red-800',
                    'keluar' => 'bg-blue-100 text-blue-800',
                    'selesai' => 'bg-gray-100 text-gray-800',
                ];
            @endphp
            <span class="px-3 py-1 rounded-full text-sm font-bold {{ $statusColors[$dispensasi->status] ?? 'bg-gray-100' }}">
                {{ ucfirst($dispensasi->status) }}
            </span>
        </div>

        <!--<div class="p-5 sm:p-6 space-y-5">-->

        {{-- Foto Verifikasi (Jika Ada) --}}
        @if($dispensasi->foto_verifikasi)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start gap-4">
                <div class="w-20 h-20 rounded-lg overflow-hidden border border-blue-200 flex-shrink-0 bg-white cursor-pointer hover:shadow-lg transition-shadow" onclick="openPhotoModal('{{ Storage::url($dispensasi->foto_verifikasi) }}', 'Foto Verifikasi - {{ $dispensasi->siswa->nama_lengkap }}')">
                    <img src="{{ Storage::url($dispensasi->foto_verifikasi) }}" alt="Foto {{ $dispensasi->siswa->nama_lengkap }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-blue-900 mb-1 flex items-center">
                        <i class="fas fa-camera mr-1.5"></i> Foto Verifikasi Siswa
                        <span class="ml-2 text-[10px] font-normal text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">
                            <i class="fas fa-expand mr-1"></i>Klik untuk memperbesar
                        </span>
                    </h4>
                    <p class="text-xs text-blue-700 mb-2">
                        Gunakan foto ini untuk memverifikasi identitas siswa secara visual sebelum melakukan scan QR Code.
                    </p>
                    <p class="text-[10px] text-blue-600">
                        <i class="fas fa-info-circle mr-1"></i> Foto akan dihapus otomatis setelah siswa kembali.
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- Foto Bukti Siswa --}}
        @if($dispensasi->foto_bukti)
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
            <div class="flex items-start gap-4">
                <div class="w-20 h-20 rounded-lg overflow-hidden border border-emerald-200 flex-shrink-0 bg-white cursor-pointer hover:shadow-lg transition-shadow" onclick="openPhotoModal('{{ Storage::url($dispensasi->foto_bukti) }}', 'Foto Bukti Kedatangan - {{ $dispensasi->siswa->nama_lengkap }}')">
                    <img src="{{ Storage::url($dispensasi->foto_bukti) }}" alt="Foto Bukti {{ $dispensasi->siswa->nama_lengkap }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-emerald-900 mb-1 flex items-center">
                        <i class="fas fa-image mr-1.5"></i> Foto Bukti Kedatangan
                        <span class="ml-2 text-[10px] font-normal text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">
                            <i class="fas fa-expand mr-1"></i>Klik untuk memperbesar
                        </span>
                    </h4>
                    <p class="text-xs text-emerald-700 mb-2">
                        Foto ini diupload oleh siswa sebagai bukti kedatangan di lokasi tujuan.
                    </p>
                    @if($dispensasi->foto_bukti_uploaded_at)
                    <p class="text-[10px] text-emerald-600">
                        <i class="far fa-clock mr-1"></i> Diupload: {{ $dispensasi->foto_bukti_uploaded_at->isoFormat('D MMMM Y, HH:mm') }} WIB
                    </p>
                    @endif
                </div>
            </div>
        </div>
        @endif
        {{-- MODAL PREVIEW FOTO --}}
        <div id="photoModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" onclick="closePhotoModal(event)">
            <div class="relative max-w-4xl w-full max-h-[90vh] flex flex-col items-center" onclick="event.stopPropagation()">
                {{-- Header Modal --}}
                <div class="w-full bg-white rounded-t-xl px-4 py-3 flex items-center justify-between border-b border-gray-200">
                    <h3 id="photoModalTitle" class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-image text-blue-600"></i>
                        <span>Preview Foto</span>
                    </h3>
                    <button onclick="closePhotoModal()" class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Body Modal --}}
                <div class="w-full bg-white rounded-b-xl p-4 flex items-center justify-center overflow-auto">
                    <img id="photoModalImage" src="" alt="Preview" class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-lg">
                </div>

                {{-- Footer Actions --}}
                <div class="absolute top-1/2 -translate-y-1/2 left-4">
                    <button onclick="closePhotoModal()" class="w-10 h-10 rounded-full bg-white/90 hover:bg-white text-gray-700 shadow-lg flex items-center justify-center transition-all hover:scale-110">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm mb-6">
            {{-- Data Siswa --}}
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Data Siswa</h4>
                <div class="space-y-2">
                    <div>
                        <span class="text-gray-500">Nama:</span>
                        <p class="font-semibold">{{ $dispensasi->siswa->nama_lengkap }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">NIS:</span>
                        <p class="font-mono">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Kelas:</span>
                        <p class="font-semibold">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Jurusan:</span>
                        <p>{{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Detail Pengajuan --}}
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Detail Pengajuan</h4>
                <div class="space-y-2">
                    <div>
                        <span class="text-gray-500">Kategori:</span>
                        <p class="font-semibold capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Tujuan:</span>
                        <p class="font-semibold">{{ $dispensasi->tujuan }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Lokasi:</span>
                        <p class="font-semibold">{{ $dispensasi->lokasi ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Jam Keluar:</span>
                        {{-- jam_keluar disimpan sebagai string jam (bukan timestamp), jadi tetap echo langsung --}}
                        <p class="font-semibold text-indigo-700">
                            {{ $dispensasi->jam_keluar }}
                            <span class="text-xs text-gray-500 block mt-1 font-normal">
                                <i class="far fa-clock mr-1"></i>
                                {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <span class="text-gray-500">Jam Kembali:</span>
                        <p class="font-semibold text-indigo-700">
                            {{ $dispensasi->jam_kembali }}
                            <span class="text-xs text-gray-500 block mt-1 font-normal">
                                <i class="far fa-clock mr-1"></i>
                                {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Info Grid --}}
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <p class="text-gray-500 text-[10px] font-semibold uppercase mb-0.5">Kategori</p>
                    <p class="font-semibold text-gray-900 capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <p class="text-gray-500 text-[10px] font-semibold uppercase mb-0.5">Lokasi</p>
                    <p class="font-semibold text-gray-900 truncate">{{ $dispensasi->lokasi ?? '-' }}</p>
                </div>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                <p class="text-gray-500 text-[10px] font-semibold uppercase tracking-wider mb-1">Tujuan</p>
                <p class="text-sm text-gray-800 font-medium">{{ $dispensasi->tujuan }}</p>
            </div>



            {{-- Alasan & Catatan --}}
            <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg border border-gray-100">
                <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Alasan & Catatan</h4>
                <div class="space-y-3">
                    <div>
                        <span class="text-gray-500">Alasan Siswa:</span>
                        <p class="font-medium mt-1 text-gray-800">{{ $dispensasi->alasan }}</p>
                    </div>
                    @if($dispensasi->catatan_admin)
                    <div class="bg-yellow-50 p-3 rounded border-l-4 border-yellow-400">
                        <span class="text-gray-700 font-semibold text-xs uppercase">Catatan Admin / Guru:</span>
                        <p class="text-gray-800 mt-1">{{ $dispensasi->catatan_admin }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Data Guru Piket --}}
            <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg border border-gray-100">
                <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Guru Piket Penanggung Jawab</h4>
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xl">
                        {{ substr($dispensasi->guru?->nama_lengkap ?? 'G', 0, 1) }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $dispensasi->guru?->nama_lengkap ?? 'Menunggu persetujuan' }}</p>
                        <p class="text-sm text-gray-500">
                            Tanggal Piket: {{ $dispensasi->created_at ? $dispensasi->created_at->format('d M Y') : '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- TIMELINE STATUS (DIPERBAIKI: Menambahkan status "Menunggu Satpam") --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center border-b border-gray-100 pb-3">
                <i class="fas fa-history text-gray-400 mr-2"></i>Alur & Riwayat Status
            </h3>
            <div class="space-y-4">
                {{-- 1. Diajukan --}}
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 flex-shrink-0"></div>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-gray-900">Pengajuan Dibuat</p>
                        <p class="text-[10px] text-gray-500">{{ $dispensasi->created_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                    </div>
                </div>

                {{-- 2. Disetujui / Ditolak --}}
                @if($dispensasi->status === 'ditolak')
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-red-500 mt-1.5 flex-shrink-0"></div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-red-600">Ditolak oleh Guru Piket</p>
                            <p class="text-[10px] text-gray-500">{{ $dispensasi->updated_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                        </div>
                    </div>
                @elseif($dispensasi->guru)
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 mt-1.5 flex-shrink-0"></div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-gray-900">Disetujui oleh Guru Piket</p>
                            <p class="text-[10px] text-gray-500">{{ $dispensasi->updated_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                        </div>
                    </div>
                @endif

                {{-- 3. Menunggu Scan Keluar --}}
                @if($dispensasi->status === 'disetujui')
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-amber-500 mt-1.5 flex-shrink-0 animate-pulse"></div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-amber-600">Menunggu Konfirmasi Satpam (Keluar)</p>
                            <p class="text-[10px] text-gray-500">Silakan tunjukkan QR Code kepada petugas satpam di pos.</p>
                        </div>
                    </div>
                @endif

                {{-- 4. Konfirmasi Keluar --}}
                @if($dispensasi->waktu_keluar_aktual)
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-sky-500 mt-1.5 flex-shrink-0"></div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-gray-900">Dikonfirmasi Keluar oleh Satpam</p>
                            <p class="text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                        </div>
                    </div>
                @endif

                {{-- 5. Menunggu Scan Kembali --}}
                @if($dispensasi->status === 'keluar')
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-amber-500 mt-1.5 flex-shrink-0 animate-pulse"></div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-amber-600">Menunggu Konfirmasi Satpam (Kembali)</p>
                            <p class="text-[10px] text-gray-500">Tunjukkan QR Code yang sama saat kembali ke sekolah.</p>
                        </div>
                    </div>
                @endif

                {{-- 6. Konfirmasi Kembali --}}
                @if($dispensasi->waktu_kembali_aktual)
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 mt-1.5 flex-shrink-0"></div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-gray-900">Dikonfirmasi Kembali oleh Satpam</p>
                            <p class="text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                            @if($dispensasi->is_warned)
                                <p class="text-[10px] text-red-500 font-semibold mt-0.5"><i class="fas fa-exclamation-circle mr-1"></i>Tercatat Terlambat</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="border-t pt-4 flex space-x-3">
            <a href="{{ route('admin.semua.pengajuan') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
            </a>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openPhotoModal(imageUrl, title) {
    const modal = document.getElementById('photoModal');
    const image = document.getElementById('photoModalImage');
    const titleEl = document.getElementById('photoModalTitle');

    image.src = imageUrl;
    titleEl.innerHTML = `<i class="fas fa-image text-blue-600"></i><span>${title}</span>`;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent background scroll
}

function closePhotoModal(event) {
    if (event && event.target !== event.currentTarget && !event.target.closest('button')) return;

    const modal = document.getElementById('photoModal');
    modal.classList.add('hidden');
    document.body.style.overflow = ''; // Restore scroll
}

// Close modal dengan tombol ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePhotoModal();
    }
});
</script>
@endpush
