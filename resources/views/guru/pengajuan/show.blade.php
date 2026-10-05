@extends('guru.layouts.app')

@section('title', 'Detail Dispensasi')
@section('page-title', 'Detail Pengajuan')

@section('content')
@include('guru.partials.bluetooth-printer')

@php
    $statusColors = [
        'menunggu'  => 'bg-amber-100 text-amber-700 border border-amber-200',
        'disetujui' => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
        'keluar'    => 'bg-sky-100 text-sky-700 border border-sky-200',
        'ditolak'   => 'bg-red-100 text-red-700 border border-red-200',
        'selesai'   => 'bg-gray-100 text-gray-700 border border-gray-200',
    ];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- ============ KOLOM KIRI: Detail Dispensasi ============ --}}
    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="bg-blue-600 p-4 sm:p-5 text-white">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div class="min-w-0">
                    <p class="text-blue-100 text-[10px] font-semibold uppercase tracking-wider">Surat Dispensasi</p>
                    <h2 class="text-lg sm:text-xl font-bold font-mono tracking-tight break-all mt-0.5">{{ $dispensasi->nomor_surat }}</h2>
                    <p class="text-blue-100 text-xs mt-1.5 flex items-center flex-wrap">
                        <i class="far fa-calendar-plus mr-1"></i>Diajukan: {{ $dispensasi->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                    </p>
                </div>
                @php
                    $badge = $dispensasi->status_badge;
                @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold flex-shrink-0 border {{ $badge['class'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                    {{ $badge['text'] }}
                </span>
            </div>
        </div>

        <div class="p-5 sm:p-6 space-y-5">

        @php
            $hasFotoVerif = !empty($dispensasi->foto_verifikasi) && \Illuminate\Support\Facades\Storage::disk('public')->exists($dispensasi->foto_verifikasi);
            $hasFotoBukti = !empty($dispensasi->foto_bukti) && \Illuminate\Support\Facades\Storage::disk('public')->exists($dispensasi->foto_bukti);
        @endphp

        {{-- Foto Verifikasi --}}
        @if($hasFotoVerif)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex flex-col sm:flex-row items-start gap-4">
                <div class="w-20 h-20 rounded-lg overflow-hidden border border-blue-200 flex-shrink-0 bg-white cursor-pointer hover:shadow-lg transition-shadow" onclick="openPhotoModal('{{ Storage::url($dispensasi->foto_verifikasi) }}', 'Foto Verifikasi - {{ $dispensasi->siswa->nama_lengkap }}')">
                    <img src="{{ Storage::url($dispensasi->foto_verifikasi) }}" alt="Foto {{ $dispensasi->siswa->nama_lengkap }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-bold text-blue-900 mb-1 flex flex-wrap items-center gap-2">
                        <i class="fas fa-camera mr-1.5"></i>
                        <span class="break-words">Foto Verifikasi Siswa</span>
                        <span class="text-[10px] font-normal text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full whitespace-nowrap">
                            <i class="fas fa-expand mr-1"></i>Klik untuk memperbesar
                        </span>
                    </h4>
                    <p class="text-xs text-blue-700 mb-2 break-words">
                        Gunakan foto ini untuk memverifikasi identitas siswa secara visual sebelum melakukan scan QR Code.
                    </p>
                    <p class="text-[10px] text-blue-600 break-words">
                        <i class="fas fa-info-circle mr-1"></i> Foto akan dihapus otomatis setelah siswa kembali.
                    </p>
                </div>
            </div>
        </div>
        @elseif($dispensasi->status === 'selesai')
        <div class="bg-gray-50 border border-dashed border-gray-300 rounded-lg p-3 text-center">
            <p class="text-xs text-gray-500 font-medium"><i class="fas fa-camera text-gray-400 mr-1.5"></i>Foto verifikasi sudah dihapus karena dispensasi telah diselesaikan.</p>
        </div>
        @endif

        {{-- Foto Bukti Siswa --}}
        @if($hasFotoBukti)
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
            <div class="flex flex-col sm:flex-row items-start gap-4">
                <div class="w-20 h-20 rounded-lg overflow-hidden border border-emerald-200 flex-shrink-0 bg-white cursor-pointer hover:shadow-lg transition-shadow" onclick="openPhotoModal('{{ Storage::url($dispensasi->foto_bukti) }}', 'Foto Bukti Kedatangan - {{ $dispensasi->siswa->nama_lengkap }}')">
                    <img src="{{ Storage::url($dispensasi->foto_bukti) }}" alt="Foto Bukti {{ $dispensasi->siswa->nama_lengkap }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-bold text-emerald-900 mb-1 flex flex-wrap items-center gap-2">
                        <i class="fas fa-image mr-1.5"></i>
                        <span class="break-words">Foto Bukti Kedatangan</span>
                        <span class="text-[10px] font-normal text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full whitespace-nowrap">
                            <i class="fas fa-expand mr-1"></i>Klik untuk memperbesar
                        </span>
                    </h4>
                    <p class="text-xs text-emerald-700 mb-2 break-words">
                        Foto ini diupload oleh siswa sebagai bukti kedatangan di lokasi tujuan.
                    </p>
                    @if($dispensasi->foto_bukti_uploaded_at)
                    <p class="text-[10px] text-emerald-600 break-words">
                        <i class="far fa-clock mr-1"></i> Diupload: {{ $dispensasi->foto_bukti_uploaded_at->isoFormat('D MMMM Y, HH:mm') }} WIB
                    </p>
                    @endif
                </div>
            </div>
        </div>
        @elseif($dispensasi->status === 'selesai')
        <div class="bg-gray-50 border border-dashed border-gray-300 rounded-lg p-3 text-center">
            <p class="text-xs text-gray-500 font-medium"><i class="fas fa-image text-gray-400 mr-1.5"></i>Foto bukti sudah dihapus karena dispensasi telah diselesaikan.</p>
        </div>
        @endif

        {{-- MODAL PREVIEW FOTO --}}
        <div id="photoModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-3 sm:p-6 bg-black/80 backdrop-blur-sm" onclick="closePhotoModal(event)">
            <div class="relative flex h-[calc(100dvh-1.5rem)] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl sm:h-[calc(100dvh-3rem)]" onclick="event.stopPropagation()">
                {{-- Header Modal --}}
                <div class="w-full bg-white rounded-t-xl px-4 py-3 flex items-center justify-between border-b border-gray-200">
                    <h3 id="photoModalTitle" class="min-w-0 text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-image text-blue-600"></i>
                        <span class="truncate">Preview Foto</span>
                    </h3>
                    <button onclick="closePhotoModal()" class="ml-3 w-11 h-11 flex-shrink-0 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition-colors" aria-label="Tutup preview foto">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                {{-- Body Modal --}}
                <div class="min-h-0 flex-1 w-full bg-gray-950 p-3 sm:p-5 flex items-center justify-center overflow-hidden">
                    <img id="photoModalImage" src="" alt="Preview" class="max-w-full max-h-full w-auto h-auto object-contain rounded-lg shadow-lg">
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

            <div>
                <p class="text-gray-500 text-[10px] font-semibold uppercase tracking-wider mb-1">Alasan</p>
                <p class="text-sm text-gray-800 font-medium leading-relaxed">{{ $dispensasi->alasan }}</p>
            </div>

            <div>
                <p class="text-gray-500 text-[10px] font-semibold uppercase tracking-wider mb-1">Tujuan</p>
                <p class="text-sm text-gray-800 font-medium">{{ $dispensasi->tujuan }}</p>
            </div>

         

            {{-- Jam Keluar & Jam Kembali --}}
            {{-- Jam Keluar & Jam Kembali --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-blue-600 mb-1">Jam Keluar</p>
                    @php
                        $waktuKeluar = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar, $dispensasi->created_at?->dayOfWeek);
                    @endphp
                    <p class="text-sm font-bold text-gray-900">
                        {{ $waktuKeluar !== '-' ? $waktuKeluar . ' WIB' : $dispensasi->jam_keluar }}
                    </p>
                    @if(str_contains((string)$dispensasi->jam_keluar, 'Jam Pelajaran'))
                        <p class="text-xs text-blue-600 mt-1 font-medium">
                            <i class="far fa-clock mr-1"></i>{{ $dispensasi->jam_keluar }}
                        </p>
                    @endif
                </div>
                <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-blue-600 mb-1">Jam Kembali</p>
                    @php
                        $waktuKembali = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali, $dispensasi->created_at?->dayOfWeek);
                    @endphp
                    <p class="text-sm font-bold text-gray-900">
                        {{ $waktuKembali !== '-' ? $waktuKembali . ' WIB' : $dispensasi->jam_kembali }}
                    </p>
                    @if(str_contains((string)$dispensasi->jam_kembali, 'Jam Pelajaran'))
                        <p class="text-xs text-blue-600 mt-1 font-medium">
                            <i class="far fa-clock mr-1"></i>{{ $dispensasi->jam_kembali }}
                        </p>
                    @endif
                </div>
            </div>


            @if($dispensasi->dibuat_manual_oleh_guru)
                <div class="bg-violet-50 border border-violet-200 rounded-lg p-3.5">
                    <span class="text-violet-700 text-[10px] font-semibold uppercase tracking-wider block mb-1"><i class="fas fa-user-tie mr-1"></i>Dibuat Manual oleh Guru Piket</span>
                    <p class="text-violet-900 text-sm font-medium">Pengajuan ini dibuat langsung oleh {{ $dispensasi->guru?->nama_lengkap ?? 'Guru Piket' }} untuk siswa.</p>
                </div>
            @endif

            @if($dispensasi->catatan_admin)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3.5">
                    <span class="text-amber-800 text-[10px] font-bold uppercase tracking-wider block mb-1 flex items-center gap-1.5">
                        <i class="fas fa-comment-dots text-amber-600"></i> Catatan Guru Piket / Keterangan Sistem
                    </span>
                    <p class="text-amber-950 text-sm font-medium leading-relaxed">{{ $dispensasi->catatan_admin }}</p>
                </div>
            @endif

         

            {{-- PERINGATAN TERLAMBAT --}}
            @php
                $isTerlambat = $dispensasi->status === 'keluar' &&
                               $dispensasi->batas_waktu_kembali &&
                               now()->greaterThan($dispensasi->batas_waktu_kembali);

                if ($isTerlambat) {
                    $terlambatMenit = \App\Helpers\DispensasiTimeHelper::hitungMenitTerlambat($dispensasi->batas_waktu_kembali);
                    $terlambatJam = floor($terlambatMenit / 60);
                    $terlambatSisaMenit = $terlambatMenit % 60;
                }
            @endphp

            @if($isTerlambat)
            <div class="bg-red-50 border border-red-200 rounded-lg p-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-lg bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-red-900 font-bold text-sm mb-1 flex items-center gap-2">
                            SISWA TERLAMBAT KEMBALI
                        </h3>
                        <p class="text-red-700 text-sm mb-3">
                            Siswa telah melewati batas waktu kembali selama
                            <span class="font-semibold text-red-900 bg-red-100 px-2 py-0.5 rounded">
                                @if($terlambatJam > 0) {{ $terlambatJam }} jam {{ $terlambatSisaMenit }} menit @else {{ $terlambatMenit }} menit @endif
                            </span>
                        </p>

                        <div class="bg-white rounded-md p-3 mb-3 border border-red-100">
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div>
                                    <p class="text-red-500 text-[10px] font-semibold uppercase mb-0.5">Batas Kembali</p>
                                    <p class="font-bold text-red-900">
                                        <i class="far fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($dispensasi->batas_waktu_kembali)->format('H:i') }} WIB
                                    </p>
                                </div>
                                <div>
                                    <p class="text-red-500 text-[10px] font-semibold uppercase mb-0.5">Status Saat Ini</p>
                                    <p class="font-bold text-red-900">
                                        <i class="fas fa-walking mr-1"></i> Keluar (Belum Kembali)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('guru.scan') }}"
                               class="inline-flex items-center justify-center px-4 py-2.5 min-h-[44px] bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors text-sm">
                                <i class="fas fa-qrcode mr-1.5"></i> Scan QR Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @else
                @if($dispensasi->status === 'keluar' && $dispensasi->batas_waktu_kembali)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-amber-900 font-semibold text-sm">Siswa Sedang Keluar</p>
                            <p class="text-amber-700 text-xs">
                                Batas kembali: <strong>{{ \Carbon\Carbon::parse($dispensasi->batas_waktu_kembali)->format('H:i') }} WIB</strong>
                            </p>
                        </div>
                    </div>
                </div>
                @elseif($dispensasi->isNotReturned())
                <div class="bg-red-50 border border-red-200 rounded-lg p-5">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user-xmark text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-red-900">Siswa Tidak Kembali ke Sekolah</h4>
                            <p class="text-xs text-red-700 mt-1 leading-relaxed">
                                Siswa keluar pukul {{ $dispensasi->waktu_keluar_aktual?->format('H:i') }} WIB, namun tidak pernah melakukan scan kembali di pos gerbang hingga KBM berakhir. Dispensasi ditutup otomatis oleh sistem.
                            </p>
                        </div>
                    </div>
                </div>
                @elseif($dispensasi->isReturnedLate())
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-5">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock-rotate-left text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-amber-900">Kembali Terlambat ({{ $dispensasi->getLateDurationText() }})</h4>
                            <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                                Siswa kembali pada pukul {{ $dispensasi->waktu_kembali_aktual?->format('H:i') }} WIB (Batas waktu: {{ $dispensasi->batas_waktu_kembali?->format('H:i') }} WIB).
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            @endif

            {{-- Status Sudah Dihubungi --}}
            @if($dispensasi->is_warned)
            <div class="p-4 bg-purple-50 border border-purple-200 rounded-lg flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="text-purple-800 font-bold text-sm mb-1">Sudah Dihubungi</p>
                    <p class="text-purple-600 text-xs font-medium">
                        <i class="far fa-clock mr-1"></i>
                        Dihubungi pada: {{ $dispensasi->warned_at ? \Carbon\Carbon::parse($dispensasi->warned_at)->isoFormat('D MMMM Y, HH:mm') . ' WIB' : '-' }}
                    </p>
                    @if($dispensasi->siswa?->no_telepon)
                    <p class="text-purple-500 text-[10px] mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        No. Telepon: <span class="font-mono font-semibold">{{ $dispensasi->siswa->no_telepon }}</span>
                    </p>
                    @endif
                </div>
            </div>
            @endif

           {{-- Action Buttons --}}
<div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-3">

    @if($dispensasi->status === 'menunggu')
        <form method="POST" action="{{ route('guru.pengajuan.approve', $dispensasi) }}" class="flex-1">
            @csrf
            <button type="submit"
                    data-confirm="Setujui dispensasi {{ $dispensasi->siswa->nama_lengkap }} dan generate QR Code?"
                    class="w-full inline-flex justify-center items-center px-4 py-3 min-h-[44px] rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                <i class="fas fa-check mr-2"></i>Setujui & Generate QR
            </button>
        </form>

        <button onclick="rejectDispensasi()"
                class="flex-1 inline-flex justify-center items-center px-4 py-3 min-h-[44px] rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors">
            <i class="fas fa-times mr-2"></i>Tolak
        </button>
    @elseif($dispensasi->status === 'disetujui' && empty($dispensasi->waktu_keluar_aktual))
        <div class="flex-1 flex flex-col sm:flex-row gap-2">
            <div class="flex-1 px-4 py-3 min-h-[44px] rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-600 flex-shrink-0"></i>
                <span>Disetujui. Menunggu scan keluar di Satpam.</span>
            </div>
            <button onclick="rejectDispensasi('Batalkan Dispensasi', 'Alasan pembatalan (Siswa tidak jadi keluar / membatalkan izin):')"
                    class="inline-flex justify-center items-center px-4 py-3 min-h-[44px] rounded-lg text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors whitespace-nowrap">
                <i class="fas fa-ban mr-1.5"></i>Batalkan Dispensasi
            </button>
        </div>
    @else
        <div class="flex-1 px-4 py-3 min-h-[44px] rounded-lg bg-gray-50 border border-gray-200 text-xs text-gray-600 flex items-center">
            <i class="fas fa-info-circle mr-2 text-blue-500 text-sm flex-shrink-0"></i>
            <span>
                Status: <strong class="capitalize">{{ $dispensasi->status }}</strong>.
                <span class="text-gray-500 block sm:inline mt-1 sm:mt-0">
                    (Konfirmasi keluar/kembali dilakukan oleh Satpam via Scan QR)
                </span>
            </span>
        </div>
    @endif

    {{-- Navigasi --}}
    <div class="flex flex-col sm:flex-row gap-2 sm:w-auto">
        <a href="{{ route('guru.pengajuan.create') }}"
           class="inline-flex justify-center items-center px-4 py-3 min-h-[44px] rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Buat Dispensasi Baru
        </a>

        <a href="{{ route('guru.pengajuan.index') }}"
           class="inline-flex justify-center items-center px-4 py-3 min-h-[44px] rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">
            <i class="fas fa-list mr-2"></i>
            Daftar Pengajuan
        </a>
    </div>


            </div>
        </div>
    </div>

    {{-- ============ KOLOM KANAN: Info Siswa & Guru ============ --}}
    <div class="space-y-4">

        {{-- Data Siswa --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h4 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5 mb-3 flex items-center">
                <i class="fas fa-user-graduate text-blue-600 mr-2"></i>Data Siswa
            </h4>
            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-gray-500 font-medium block mb-0.5">Nama Lengkap</span>
                    <p class="font-semibold text-gray-900 text-sm">{{ $dispensasi->siswa->nama_lengkap }}</p>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block mb-0.5">NIS</span>
                    <p class="font-mono font-semibold text-gray-900">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block mb-0.5">Kelas</span>
                    <p class="font-semibold text-gray-900">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block mb-0.5">Jurusan</span>
                    <p class="text-gray-700 font-medium">{{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block mb-0.5">No. Telepon</span>
                    <p class="text-gray-700 font-medium">{{ $dispensasi->siswa->no_telepon ?? '-' }}</p>
                </div>
            </div>
        </div>
        {{-- Timeline Status --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center uppercase tracking-wider">
                <i class="fas fa-history text-purple-600 mr-2"></i>Riwayat
            </h3>
            <div class="relative border-l-2 border-gray-200 ml-2.5 space-y-6 pb-2">
                <div class="relative pl-6">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-blue-500 border-2 border-white shadow-sm"></div>
                    <p class="text-xs font-bold text-gray-800">Pengajuan Dibuat</p>
                    <p class="text-[10px] text-gray-500 mt-0.5">{{ $dispensasi->created_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                </div>
                @if($dispensasi->status === 'ditolak')
                <div class="relative pl-6">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-red-500 border-2 border-white shadow-sm"></div>
                    <p class="text-xs font-bold text-gray-800">Ditolak Guru Piket</p>
                    @if($dispensasi->guru)
                        <p class="text-[11px] text-gray-600 mt-0.5">Oleh: {{ $dispensasi->guru->nama_lengkap }}</p>
                    @endif
                    @if($dispensasi->rejected_at)
                        <p class="text-[10px] text-gray-500 mt-0.5">{{ $dispensasi->rejected_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                    @endif
                    @if($dispensasi->catatan_admin)
                        <p class="text-xs text-red-600 mt-0.5">Alasan: {{ $dispensasi->catatan_admin }}</p>
                    @endif
                </div>
                @elseif(in_array($dispensasi->status, ['disetujui', 'keluar', 'selesai']))
                <div class="relative pl-6">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow-sm"></div>
                    <p class="text-xs font-bold text-gray-800">
                        {{ $dispensasi->dibuat_manual_oleh_guru ? 'Dibuat Manual & Disetujui' : 'Disetujui Guru Piket' }}
                    </p>
                    @if($dispensasi->guru)
                        <p class="text-[11px] text-gray-600 mt-0.5">Oleh: {{ $dispensasi->guru->nama_lengkap }}</p>
                    @endif
                    @if($dispensasi->approved_at)
                        <p class="text-[10px] text-gray-500 mt-0.5">{{ $dispensasi->approved_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                    @endif
                </div>
                @endif
                @if($dispensasi->waktu_keluar_aktual)
                <div class="relative pl-6">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-sky-500 border-2 border-white shadow-sm"></div>
                    <p class="text-xs font-bold text-gray-800">Konfirmasi Keluar (Scan)</p>
                    <p class="text-[10px] text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                </div>
                @endif
                @if($dispensasi->waktu_kembali_aktual)
                <div class="relative pl-6">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow-sm"></div>
                    <p class="text-xs font-bold text-gray-800">Konfirmasi Kembali (Selesai)</p>
                    <p class="text-[10px] text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                </div>
                @endif
                @if($dispensasi->is_warned)
                <div class="relative pl-6">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-purple-500 border-2 border-white shadow-sm"></div>
                    <p class="text-xs font-bold text-purple-800">Sudah Dihubungi</p>
                    <p class="text-[10px] text-gray-500 mt-0.5">
                        {{ $dispensasi->warned_at ? \Carbon\Carbon::parse($dispensasi->warned_at)->isoFormat('D MMM Y, HH:mm') . ' WIB' : '-' }}
                    </p>
                </div>
                @endif
            </div>
        </div>

        {{-- Guru Piket --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h4 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5 mb-3 flex items-center">
                <i class="fas fa-user-tie text-blue-600 mr-2"></i>Guru Piket Penanggung Jawab
            </h4>
            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-gray-500 font-medium block mb-0.5">Nama Guru</span>
                    <p class="font-semibold text-gray-900 text-sm">{{ $dispensasi->guru?->nama_lengkap ?? 'Menunggu persetujuan' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block mb-0.5">Tanggal Piket</span>
                    <p class="text-gray-700 font-medium">{{ $dispensasi->created_at ? $dispensasi->created_at->format('d M Y') : '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Mencegah teks panjang keluar dari kolom */
    .break-words {
        word-break: break-word;
        overflow-wrap: break-word;
    }
</style>
@endpush

@push('scripts')
<script>

function openPhotoModal(imageUrl, title) {
    const modal = document.getElementById('photoModal');
    const image = document.getElementById('photoModalImage');
    const titleEl = document.getElementById('photoModalTitle');

    image.src = imageUrl;
    titleEl.querySelector('span').textContent = title;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent background scroll
}

function closePhotoModal(event) {
    if (event && event.target !== event.currentTarget && !event.target.closest('button')) return;

    const modal = document.getElementById('photoModal');
    document.getElementById('photoModalImage').removeAttribute('src');
    modal.classList.add('hidden');
    document.body.style.overflow = ''; // Restore scroll
}

// Close modal dengan tombol ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePhotoModal();
    }
});

function rejectDispensasi(title = 'Tolak Dispensasi', text = 'Masukkan alasan penolakan untuk {{ $dispensasi->siswa->nama_lengkap }}:') {
    Swal.fire({
        title: title,
        text: text,
        input: 'textarea',
        inputPlaceholder: 'Tuliskan alasan yang jelas...',
        inputAttributes: { rows: 4 },
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value || value.trim() === '') return 'Alasan wajib diisi!';
        }
    }).then(result => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('guru.pengajuan.reject', $dispensasi) }}';
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            const reason = document.createElement('input');
            reason.type = 'hidden';
            reason.name = 'catatan_admin';
            reason.value = result.value;
            form.append(csrf, reason);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Konfirmasi approve dengan SweetAlert
document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const form = this.closest('form');
        Swal.fire({
            title: 'Konfirmasi Persetujuan',
            text: this.dataset.confirm,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(result => { if (result.isConfirmed) form.submit(); });
    });
});

// Fungsi deteksi perangkat untuk tombol cetak otomatis
function handleCetakOtomatis(event) {
    event.preventDefault();
    window.open("{{ route('guru.cetak-struk', $dispensasi) }}", "_blank");
}

// Ubah teks tombol secara otomatis saat halaman dimuat
document.addEventListener("DOMContentLoaded", function() {
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const textCetak = document.getElementById('textCetak');
    const iconCetak = document.getElementById('iconCetak');

    if (isMobile) {
        if(textCetak) textCetak.textContent = "Buka Struk PNG (HP)";
        if(iconCetak) iconCetak.className = "fas fa-image mr-1.5";
    } else {
        if(textCetak) textCetak.textContent = "Cetak Struk Thermal (PC)";
        if(iconCetak) iconCetak.className = "fas fa-print mr-1.5";
    }
});
</script>
@endpush
@endsection
