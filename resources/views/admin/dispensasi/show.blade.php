@extends('admin.layouts.app')

@section('title', 'Detail Pengajuan')
@section('page-title', 'Detail Pengajuan Dispensasi')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
            <div class="min-w-0">
                <h3 class="text-xl sm:text-2xl font-bold text-gray-800 break-all">{{ $dispensasi->nomor_surat }}</h3>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Diajukan: {{ $dispensasi->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i:s') }} WIB</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @if($dispensasi->isOrganisasi())
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200 flex items-center gap-1.5">
                        <i class="fas fa-users"></i> {{ $dispensasi->nama_organisasi ?? 'Organisasi' }}
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                        Rombongan ({{ $dispensasi->anggotaSiswa->count() + 1 }} Siswa)
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200 flex items-center gap-1.5">
                        <i class="fas fa-user"></i> Dispensasi Individu
                    </span>
                @endif
                @if($dispensasi->trashed())
                    <span class="px-3 py-1 rounded-full text-xs sm:text-sm font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        <i class="fas fa-archive mr-1"></i> Diarsipkan
                    </span>
                @endif
                <span class="px-3 py-1 rounded-full text-xs sm:text-sm font-bold flex-shrink-0 {{ $statusColors[$dispensasi->status] ?? 'bg-gray-100' }}">
                    {{ ucfirst($dispensasi->status) }}
                </span>
            </div>
        </div>

        @if($dispensasi->trashed())
            <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-center gap-3">
                <i class="fas fa-info-circle text-amber-600 text-xl flex-shrink-0"></i>
                <div class="text-xs sm:text-sm text-amber-900">
                    <p class="font-bold">Dispensasi ini berada di arsip (Soft Deleted)</p>
                    <p class="text-amber-700 mt-0.5">Dihapus dari daftar aktif pada: {{ $dispensasi->deleted_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB. Anda dapat memulihkannya kapan saja.</p>
                </div>
            </div>
        @endif

        @if($dispensasi->status === 'menunggu')
            <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-300 flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-base animate-pulse"></i>
                </div>
                <div class="text-xs sm:text-sm text-amber-900 flex-1">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <p class="font-bold">Pengajuan Menunggu Persetujuan Guru Piket</p>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-200 text-amber-900 border border-amber-300">
                            <i class="fas fa-hourglass-half text-[10px]"></i> Kadaluarsa: {{ $dispensasi->getBatasWaktuExpiredText() }}
                        </span>
                    </div>
                    <p class="text-amber-800 mt-1">Pengajuan ini belum disetujui dan akan otomatis berubah menjadi <strong>Kadaluarsa</strong> jika tidak disetujui hingga {{ $dispensasi->sampai_pulang ? 'jam KBM sekolah hari ini berakhir' : 'batas waktu izin' }} (pukul {{ $dispensasi->getBatasWaktuExpiredText() }}).</p>
                </div>
            </div>
        @elseif($dispensasi->status === 'kadaluarsa')
            <div class="mb-6 p-4 rounded-xl bg-orange-50 border border-orange-200 flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-700 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-calendar-xmark text-lg"></i>
                </div>
                <div class="text-xs sm:text-sm text-orange-900 flex-1">
                    <p class="font-bold">Dispensasi Kadaluarsa</p>
                    <p class="text-orange-800 mt-0.5">{{ $dispensasi->catatan_admin ?? 'Permohonan dispensasi ini telah kadaluarsa karena melewati batas waktu operasional KBM.' }}</p>
                </div>
            </div>
        @endif

        <!--<div class="p-5 sm:p-6 space-y-5">-->

        {{-- Foto Verifikasi (Jika Ada) --}}
        @if($dispensasi->foto_verifikasi)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start gap-4">
                <div class="w-20 h-20 rounded-lg overflow-hidden border border-blue-200 flex-shrink-0 bg-white cursor-pointer hover:shadow-lg transition-shadow" onclick="openPhotoModal('{{ Storage::url($dispensasi->foto_verifikasi) }}', 'Foto Verifikasi - {{ $dispensasi->siswa?->nama_lengkap ?? '-' }}')">
                    <img src="{{ Storage::url($dispensasi->foto_verifikasi) }}" alt="Foto {{ $dispensasi->siswa?->nama_lengkap ?? '-' }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
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
                <div class="w-20 h-20 rounded-lg overflow-hidden border border-emerald-200 flex-shrink-0 bg-white cursor-pointer hover:shadow-lg transition-shadow" onclick="openPhotoModal('{{ Storage::url($dispensasi->foto_bukti) }}', 'Foto Bukti Kedatangan - {{ $dispensasi->siswa?->nama_lengkap ?? '-' }}')">
                    <img src="{{ Storage::url($dispensasi->foto_bukti) }}" alt="Foto Bukti {{ $dispensasi->siswa?->nama_lengkap ?? '-' }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
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
        <div id="photoModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6 bg-slate-950/85 backdrop-blur-md" onclick="closePhotoModal(event)">
            <div class="relative flex max-h-[92dvh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl border border-slate-700/30" onclick="event.stopPropagation()">
                {{-- Header Modal --}}
                <div class="w-full bg-white px-4 py-3 sm:px-5 sm:py-3.5 flex items-center justify-between border-b border-gray-200 shrink-0">
                    <h3 id="photoModalTitle" class="min-w-0 text-sm font-bold text-gray-900 flex items-center gap-2 truncate pr-2">
                        <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-image text-sm"></i>
                        </span>
                        <span class="truncate">Preview Foto</span>
                    </h3>
                    <button onclick="closePhotoModal()" class="w-9 h-9 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 active:bg-gray-200 flex items-center justify-center transition-colors shrink-0" aria-label="Tutup preview foto">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                {{-- Body Modal --}}
                <div class="min-h-0 flex-1 w-full bg-slate-950 p-2 sm:p-4 flex items-center justify-center overflow-auto max-h-[calc(92dvh-115px)]">
                    <img id="photoModalImage" src="" alt="Preview" class="max-w-full max-h-[70dvh] w-auto h-auto object-contain rounded-xl shadow-lg">
                </div>

                {{-- Footer Modal --}}
                <div class="px-4 py-2.5 sm:px-5 bg-gray-50 border-t border-gray-200 flex items-center justify-between shrink-0">
                    <span class="text-[11px] text-gray-500 font-medium truncate pr-2 flex items-center gap-1">
                        <i class="fas fa-info-circle text-xs text-gray-400"></i> Ketuk luar untuk menutup
                    </span>
                    <button type="button"
                            onclick="closePhotoModal()"
                            class="px-4 py-2 min-h-[36px] rounded-xl bg-white border border-gray-300 hover:bg-gray-100 active:bg-gray-200 text-gray-700 text-xs font-bold transition-colors shadow-2xs">
                        Tutup
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
                        <p class="font-semibold">{{ $dispensasi->siswa?->nama_lengkap ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">NIS:</span>
                        <p class="font-mono">{{ $dispensasi->siswa?->user?->nis_nip ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Kelas:</span>
                        <p class="font-semibold">{{ $dispensasi->siswa?->kelas?->nama_kelas ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Jurusan:</span>
                        <p>{{ $dispensasi->siswa?->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
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
                    @if($dispensasi->dibuat_manual_oleh_guru)
                    <div class="bg-violet-50 p-3 rounded border-l-4 border-violet-400">
                        <span class="text-violet-800 font-semibold text-xs uppercase"><i class="fas fa-user-tie mr-1"></i>Dibuat Manual oleh Guru Piket</span>
                        <p class="text-violet-900 mt-1">Pengajuan ini dibuat langsung oleh {{ $dispensasi->guru?->nama_lengkap ?? 'Guru Piket' }} untuk siswa.</p>
                    </div>
                    @endif
                    <div>
                        <span class="text-gray-500">Alasan Siswa:</span>
                        <p class="font-medium mt-1 text-gray-800">{{ $dispensasi->alasan }}</p>
                    </div>
                    @if($dispensasi->catatan_admin && !$dispensasi->dibuat_manual_oleh_guru)
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

        <div class="border-t border-gray-100 pt-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <a href="{{ route('admin.semua.pengajuan') }}" class="min-h-[44px] inline-flex items-center justify-center px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
            </a>

            <div class="flex items-center gap-2 flex-wrap">
                @if($dispensasi->trashed())
                    <button type="button"
                            onclick="confirmRestore({{ $dispensasi->id }}, '{{ $dispensasi->nomor_surat }}')"
                            class="min-h-[44px] px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition-colors flex items-center justify-center gap-2 shadow-xs">
                        <i class="fas fa-trash-restore"></i>
                        <span>Pulihkan Dispensasi</span>
                    </button>
                    <button type="button"
                            onclick="confirmForceDelete({{ $dispensasi->id }}, '{{ $dispensasi->nomor_surat }}', '{{ addslashes($dispensasi->siswa?->nama_lengkap ?? '-') }}')"
                            class="min-h-[44px] px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition-colors flex items-center justify-center gap-2 shadow-xs">
                        <i class="fas fa-times-circle"></i>
                        <span>Hapus Permanen</span>
                    </button>
                @else
                    <button type="button"
                            onclick="confirmSoftDelete({{ $dispensasi->id }}, '{{ $dispensasi->nomor_surat }}', '{{ addslashes($dispensasi->siswa?->nama_lengkap ?? '-') }}')"
                            class="min-h-[44px] px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-semibold transition-colors flex items-center justify-center gap-2 shadow-xs">
                        <i class="fas fa-archive"></i>
                        <span>Pindahkan ke Arsip</span>
                    </button>
                @endif
            </div>
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
    const titleSpan = titleEl.querySelector('span:last-child') || titleEl.querySelector('span');
    if (titleSpan) titleSpan.textContent = title;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closePhotoModal(event) {
    if (event && event.target !== event.currentTarget && !event.target.closest('button')) return;

    const modal = document.getElementById('photoModal');
    document.getElementById('photoModalImage').removeAttribute('src');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePhotoModal();
    }
});

function confirmSoftDelete(id, nomorSurat, namaSiswa) {
    Swal.fire({
        title: 'Arsipkan Dispensasi?',
        html: `
            <div class="text-left">
                <p class="mb-2">Data dispensasi ini akan dipindahkan ke <strong>Arsip</strong> (Soft Delete).</p>
                <div class="bg-gray-50 p-3 rounded-lg text-sm border border-gray-200">
                    <p><strong>No. Surat:</strong> ${nomorSurat}</p>
                    <p><strong>Siswa:</strong> ${namaSiswa}</p>
                </div>
                <p class="text-amber-700 text-xs mt-3">
                    <i class="fas fa-info-circle mr-1"></i> Data tetap tersimpan aman dan dapat dipulihkan kapan saja.
                </p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-archive mr-1"></i> Ya, Arsipkan',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            submitFormAction(`/admin/semua-pengajuan/${id}`, 'DELETE', 'Mengarsipkan...');
        }
    });
}

function confirmRestore(id, nomorSurat) {
    Swal.fire({
        title: 'Pulihkan Dispensasi?',
        text: `Pulihkan dispensasi #${nomorSurat} kembali ke daftar aktif?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash-restore mr-1"></i> Ya, Pulihkan',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            submitFormAction(`/admin/semua-pengajuan/${id}/restore`, 'POST', 'Memulihkan...');
        }
    });
}

function confirmForceDelete(id, nomorSurat, namaSiswa) {
    Swal.fire({
        title: 'Hapus Permanen?',
        html: `
            <div class="text-left">
                <p class="mb-2">Hapus data ini secara <strong>permanen</strong>?</p>
                <p class="text-red-600 text-xs mt-2 font-semibold">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    File foto bukti dan QR code akan terhapus permanen dari server. Tindakan ini tidak dapat dibatalkan!
                </p>
            </div>
        `,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash mr-1"></i> Hapus Permanen',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            submitFormAction(`/admin/semua-pengajuan/${id}/force-delete`, 'DELETE', 'Menghapus permanen...');
        }
    });
}

function submitFormAction(url, method, loadingText) {
    Swal.fire({
        title: loadingText || 'Memproses...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfInput);

    if (method !== 'POST') {
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = method;
        form.appendChild(methodInput);
    }

    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
