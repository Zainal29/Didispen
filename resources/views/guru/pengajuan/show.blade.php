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

<div class="space-y-5">

    {{-- ============ HEADER CARD (PERSIS SEPERTI SISWA) ============ --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Nomor Surat</p>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-base sm:text-2xl font-bold text-slate-900 font-mono tracking-tight break-all">{{ $dispensasi->nomor_surat }}</h1>
                    @if($dispensasi->isRombongan())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            <i class="ti ti-users text-xs mr-1"></i> Dispensasi Rombongan ({{ $dispensasi->anggotaSiswa->count() + 1 }} Siswa)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                            <i class="ti ti-user text-[11px]"></i> Dispensasi Individu
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-1.5">
                    <i class="ti ti-calendar text-slate-400"></i>
                    <span>Diajukan: {{ $dispensasi->created_at->isoFormat('dddd, D MMMM Y, HH:mm') }} WIB</span>
                </p>
            </div>
            @php
                $badge = $dispensasi->status_badge;
            @endphp
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border {{ $badge['class'] }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                {{ $badge['text'] }}
            </span>
        </div>
    </div>

    {{-- ============ 2 KOLOM KONTEN ============ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ============ KOLOM KIRI: Detail Dispensasi ============ --}}
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col p-5 sm:p-6 space-y-5">

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
                        <i class="ti ti-camera mr-1.5"></i>
                        <span class="break-words">Foto Verifikasi Siswa</span>
                        <span class="text-[10px] font-normal text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full whitespace-nowrap">
                            <i class="ti ti-arrows-maximize mr-1"></i>Klik untuk memperbesar
                        </span>
                    </h4>
                    <p class="text-xs text-blue-700 mb-2 break-words">
                        Gunakan foto ini untuk memverifikasi identitas siswa secara visual sebelum melakukan scan QR Code.
                    </p>
                    <p class="text-[10px] text-blue-600 break-words">
                        <i class="ti ti-info-circle mr-1"></i> Foto akan dihapus otomatis setelah siswa kembali.
                    </p>
                </div>
            </div>
        </div>
        @elseif($dispensasi->status === 'selesai')
        <div class="bg-gray-50 border border-dashed border-gray-300 rounded-lg p-3 text-center">
            <p class="text-xs text-gray-500 font-medium"><i class="ti ti-camera text-gray-400 mr-1.5"></i>Foto verifikasi sudah dihapus karena dispensasi telah diselesaikan.</p>
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
                        <i class="ti ti-photo mr-1.5"></i>
                        <span class="break-words">Foto Bukti Kedatangan</span>
                        <span class="text-[10px] font-normal text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full whitespace-nowrap">
                            <i class="ti ti-arrows-maximize mr-1"></i>Klik untuk memperbesar
                        </span>
                    </h4>
                    <p class="text-xs text-emerald-700 mb-2 break-words">
                        Foto ini diupload oleh siswa sebagai bukti kedatangan di lokasi tujuan.
                    </p>
                    @if($dispensasi->foto_bukti_uploaded_at)
                    <p class="text-[10px] text-emerald-600 break-words">
                        <i class="ti ti-clock mr-1"></i> Diupload: {{ $dispensasi->foto_bukti_uploaded_at->isoFormat('D MMMM Y, HH:mm') }} WIB
                    </p>
                    @endif
                </div>
            </div>
        </div>
        @elseif($dispensasi->status === 'selesai')
        <div class="bg-gray-50 border border-dashed border-gray-300 rounded-lg p-3 text-center">
            <p class="text-xs text-gray-500 font-medium"><i class="ti ti-photo text-gray-400 mr-1.5"></i>Foto bukti sudah dihapus karena dispensasi telah diselesaikan.</p>
        </div>
        @endif

        {{-- MODAL PREVIEW FOTO --}}
        <div id="photoModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6 bg-slate-950/85 backdrop-blur-md" onclick="closePhotoModal(event)">
            <div class="relative flex max-h-[92dvh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl border border-slate-700/30" onclick="event.stopPropagation()">
                {{-- Header Modal --}}
                <div class="w-full bg-white px-4 py-3 sm:px-5 sm:py-3.5 flex items-center justify-between border-b border-gray-200 shrink-0">
                    <h3 id="photoModalTitle" class="min-w-0 text-sm font-bold text-gray-900 flex items-center gap-2 truncate pr-2">
                        <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="ti ti-photo text-base"></i>
                        </span>
                        <span class="truncate">Preview Foto</span>
                    </h3>
                    <button onclick="closePhotoModal()" class="w-9 h-9 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 active:bg-gray-200 flex items-center justify-center transition-colors shrink-0" aria-label="Tutup preview foto">
                        <i class="ti ti-x text-lg"></i>
                    </button>
                </div>

                {{-- Body Modal --}}
                <div class="min-h-0 flex-1 w-full bg-slate-950 p-2 sm:p-4 flex items-center justify-center overflow-auto max-h-[calc(92dvh-115px)]">
                    <img id="photoModalImage" src="" alt="Preview" class="max-w-full max-h-[70dvh] w-auto h-auto object-contain rounded-xl shadow-lg">
                </div>

                {{-- Footer Modal --}}
                <div class="px-4 py-2.5 sm:px-5 bg-gray-50 border-t border-gray-200 flex items-center justify-between shrink-0">
                    <span class="text-[11px] text-gray-500 font-medium truncate pr-2 flex items-center gap-1">
                        <i class="ti ti-info-circle text-xs text-gray-400"></i> Ketuk luar untuk menutup
                    </span>
                    <button type="button"
                            onclick="closePhotoModal()"
                            class="px-4 py-2 min-h-[36px] rounded-xl bg-white border border-gray-300 hover:bg-gray-100 active:bg-gray-200 text-gray-700 text-xs font-bold transition-colors shadow-2xs">
                        Tutup
                    </button>
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
                    <p class="text-gray-500 text-[10px] font-semibold uppercase mb-0.5">Tanggal Pelaksanaan</p>
                    <p class="font-semibold text-gray-900">
                        {{ \Carbon\Carbon::parse($dispensasi->tanggal_izin ?? $dispensasi->created_at)->translatedFormat('d M Y') }}
                        @if($dispensasi->tanggal_izin && $dispensasi->tanggal_izin->isTomorrow())
                            <span class="ml-1 text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded font-bold">Besok</span>
                        @endif
                    </p>
                </div>
            </div>

            @if($dispensasi->isRombongan())
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-xs">
                    <div class="flex items-start justify-between gap-2.5 mb-3">
                        <div class="flex items-start gap-2.5 sm:gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg shadow-sm shrink-0 mt-0.5">
                                <i class="ti ti-users"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-blue-600 block leading-tight">Dispensasi Rombongan</span>
                                <h4 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight leading-snug mt-0.5 break-words">
                                    {{ $dispensasi->siswa->nama_lengkap }} & Rombongan
                                </h4>
                            </div>
                        </div>
                        <span class="text-xs bg-blue-50 text-blue-700 font-bold px-2.5 py-1 rounded-full border border-blue-200 shrink-0">
                            Total: {{ $dispensasi->anggotaSiswa->count() + 1 }} Siswa
                        </span>
                    </div>

                    <div class="space-y-2">
                        <p class="text-[11px] font-bold uppercase text-slate-800 tracking-wider">Daftar Anggota Rombongan:</p>
                        <div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-100 overflow-hidden shadow-xs">
                            <div class="p-2.5 sm:px-3.5 text-xs hover:bg-slate-50 transition-colors">
                                <div class="flex items-start sm:items-center justify-between gap-2.5 sm:gap-3">
                                    <div class="flex items-start sm:items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[11px] flex-shrink-0 mt-0.5 sm:mt-0">
                                            1
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <span class="font-bold text-slate-900 break-words leading-snug block">{{ $dispensasi->siswa->nama_lengkap }}</span>
                                            <p class="text-[10px] text-slate-500 font-mono mt-0.5">NIS: {{ $dispensasi->siswa->user->nis_nip ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="hidden sm:flex items-center gap-2 flex-shrink-0">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] bg-amber-50 text-amber-700 font-bold border border-amber-200">
                                            <i class="ti ti-crown text-[10px] text-amber-600"></i> Ketua
                                        </span>
                                    </div>
                                </div>
                                <div class="sm:hidden flex items-center gap-1.5 mt-2 pl-[34px]">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] bg-amber-50 text-amber-700 font-bold border border-amber-200">
                                        <i class="ti ti-crown text-[10px] text-amber-600"></i> Ketua
                                    </span>
                                </div>
                            </div>
                            @foreach($dispensasi->anggotaSiswa as $idx => $ang)
                            <div class="p-2.5 sm:px-3.5 text-xs hover:bg-slate-50 transition-colors">
                                <div class="flex items-start sm:items-center justify-between gap-2.5 sm:gap-3">
                                    <div class="flex items-start sm:items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                        <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-[11px] flex-shrink-0 mt-0.5 sm:mt-0">
                                            {{ $idx + 2 }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <span class="font-medium text-slate-800 break-words leading-snug block">{{ $ang->nama_lengkap }}</span>
                                            <p class="text-[10px] text-slate-500 font-mono mt-0.5">NIS: {{ $ang->user->nis_nip ?? $ang->nis_nip ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="hidden sm:flex items-center gap-2 flex-shrink-0">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $ang->kelas?->nama_kelas ?? '-' }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 font-semibold border border-slate-200">
                                            Anggota
                                        </span>
                                    </div>
                                </div>
                                <div class="sm:hidden flex items-center gap-1.5 mt-2 pl-[34px]">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $ang->kelas?->nama_kelas ?? '-' }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 font-semibold border border-slate-200">
                                        Anggota
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div>
                <p class="text-gray-500 text-[10px] font-semibold uppercase tracking-wider mb-1">Alasan</p>
                <p class="text-sm text-gray-800 font-medium leading-relaxed">{{ $dispensasi->alasan }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <p class="text-gray-500 text-[10px] font-semibold uppercase tracking-wider mb-1">Tujuan</p>
                    <p class="text-sm text-gray-800 font-medium">{{ $dispensasi->tujuan }}</p>
                </div>
                @if($dispensasi->lokasi)
                <div>
                    <p class="text-gray-500 text-[10px] font-semibold uppercase tracking-wider mb-1">Lokasi</p>
                    <p class="text-sm text-gray-800 font-medium">{{ $dispensasi->lokasi }}</p>
                </div>
                @endif
            </div>

            {{-- Jam Keluar & Jam Kembali --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-blue-600 mb-1">Jam Keluar</p>
                    @php
                        $waktuKeluar = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar, $dispensasi->tanggal_izin?->dayOfWeek ?? $dispensasi->created_at?->dayOfWeek);
                    @endphp
                    <p class="text-sm font-bold text-gray-900">
                        {{ $waktuKeluar !== '-' ? $waktuKeluar . ' WIB' : $dispensasi->jam_keluar }}
                    </p>
                    @if(str_contains((string)$dispensasi->jam_keluar, 'Jam Pelajaran'))
                        <p class="text-xs text-blue-600 mt-1 font-medium">
                            <i class="ti ti-clock mr-1"></i>{{ $dispensasi->jam_keluar }}
                        </p>
                    @endif
                </div>
                <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-blue-600 mb-1">Jam Kembali</p>
                    @if($dispensasi->sampai_pulang)
                        <p class="text-sm font-bold text-purple-900">
                            Sampai Pulang Sekolah
                        </p>
                        <p class="text-xs text-purple-600 mt-1 font-medium">
                            <i class="ti ti-circle-check mr-1"></i>Izin sampai selesai KBM (tidak perlu scan kembali)
                        </p>
                    @else
                        @php
                            $waktuKembali = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali, $dispensasi->tanggal_izin?->dayOfWeek ?? $dispensasi->created_at?->dayOfWeek);
                        @endphp
                        <p class="text-sm font-bold text-gray-900">
                            {{ $waktuKembali !== '-' ? $waktuKembali . ' WIB' : $dispensasi->jam_kembali }}
                        </p>
                        @if(str_contains((string)$dispensasi->jam_kembali, 'Jam Pelajaran'))
                            <p class="text-xs text-blue-600 mt-1 font-medium">
                                <i class="ti ti-clock mr-1"></i>{{ $dispensasi->jam_kembali }}
                            </p>
                        @endif
                    @endif
                </div>
            </div>


            @if($dispensasi->dibuat_manual_oleh_guru)
                <div class="bg-violet-50 border border-violet-200 rounded-lg p-3.5">
                    <span class="text-violet-700 text-[10px] font-semibold uppercase tracking-wider block mb-1"><i class="ti ti-user mr-1"></i>Dibuat Manual oleh Guru Piket</span>
                    <p class="text-violet-900 text-sm font-medium">Pengajuan ini dibuat langsung oleh {{ $dispensasi->guru?->nama_lengkap ?? 'Guru Piket' }} untuk siswa.</p>
                </div>
            @endif

            @if($dispensasi->catatan_admin)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3.5">
                    <span class="text-amber-800 text-[10px] font-bold uppercase tracking-wider block mb-1 flex items-center gap-1.5">
                        <i class="ti ti-message-dots text-amber-600"></i> Catatan Guru Piket / Keterangan Sistem
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
                        <i class="ti ti-alert-triangle text-2xl"></i>
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
                                        <i class="ti ti-clock mr-1"></i> {{ \Carbon\Carbon::parse($dispensasi->batas_waktu_kembali)->format('H:i') }} WIB
                                    </p>
                                </div>
                                <div>
                                    <p class="text-red-500 text-[10px] font-semibold uppercase mb-0.5">Status Saat Ini</p>
                                    <p class="font-bold text-red-900">
                                        <i class="ti ti-walk mr-1"></i> Keluar (Belum Kembali)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('guru.scan') }}"
                               class="inline-flex items-center justify-center px-4 py-2.5 min-h-[44px] bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors text-sm">
                                <i class="ti ti-qrcode mr-1.5"></i> Scan QR Kembali
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
                            <i class="ti ti-clock text-lg"></i>
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
                            <i class="ti ti-user-x text-xl"></i>
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
                            <i class="ti ti-history text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-amber-900">Kembali Terlambat ({{ $dispensasi->getLateDurationText() }})</h4>
                            <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                                Siswa kembali pada pukul {{ $dispensasi->waktu_kembali_aktual?->format('H:i') }} WIB (Batas waktu: {{ $dispensasi->batas_waktu_kembali?->format('H:i') }} WIB).
                            </p>
                        </div>
                    </div>
                </div>
                @elseif($dispensasi->isReturnedEarly())
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-5">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                            <i class="ti ti-clock-check text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-emerald-900">Kembali Lebih Awal</h4>
                            <p class="text-xs text-emerald-800 mt-1 leading-relaxed">
                                Siswa telah kembali ke sekolah pada pukul {{ $dispensasi->waktu_kembali_aktual?->format('H:i') }} WIB (lebih awal dari batas waktu {{ $dispensasi->batas_waktu_kembali?->format('H:i') }} WIB).
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            @endif

            {{-- Status Sudah Dihubungi --}}
            @if($dispensasi->is_warned)
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <i class="ti ti-brand-whatsapp text-2xl"></i>
                </div>
                <div class="flex-1">
                    <p class="text-emerald-900 font-bold text-sm mb-1">Sudah Dihubungi</p>
                    <p class="text-emerald-700 text-xs font-medium">
                        <i class="ti ti-clock mr-1"></i>
                        Dihubungi pada: {{ $dispensasi->warned_at ? \Carbon\Carbon::parse($dispensasi->warned_at)->isoFormat('D MMMM Y, HH:mm') . ' WIB' : '-' }}
                    </p>
                    @if($dispensasi->siswa?->no_telepon)
                    <p class="text-emerald-800 text-[11px] mt-1 flex items-center gap-1">
                        <i class="ti ti-brand-whatsapp text-emerald-600"></i>
                        No. WhatsApp: <span class="font-mono font-bold">{{ $dispensasi->siswa->no_telepon }}</span>
                    </p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Peringatan Kadaluarsa Menunggu untuk Guru Piket --}}
            @if($dispensasi->status === 'menunggu')
            <div class="p-4 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-300 rounded-xl flex items-start gap-3.5 shadow-xs mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 border border-amber-200 flex items-center justify-center flex-shrink-0">
                    <i class="ti ti-alert-triangle text-lg animate-pulse"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <h4 class="text-sm font-bold text-amber-900">Perhatian: Pengajuan Ini Akan Segera Kadaluarsa</h4>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-200 text-amber-900 border border-amber-300 self-start sm:self-auto">
                            <i class="ti ti-hourglass-low text-xs"></i> Batas: {{ $dispensasi->getBatasWaktuExpiredText() }}
                        </span>
                    </div>
                    <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                        Permohonan ini masih <strong>menunggu keputusan Guru Piket</strong> dan akan <strong>otomatis kadaluarsa</strong> jika batas waktu izin (pukul {{ $dispensasi->getBatasWaktuExpiredText() }}) terlewati. Segera selesaikan dengan menekan tombol <strong>Setujui & Generate QR</strong> atau <strong>Tolak</strong> di bawah ini.
                    </p>
                </div>
            </div>
            @elseif($dispensasi->status === 'kadaluarsa')
            <div class="p-4 bg-orange-50 border border-orange-200 rounded-xl flex items-start gap-3.5 shadow-xs mb-3">
                <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center flex-shrink-0">
                    <i class="ti ti-calendar-off text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-bold text-orange-900">Dispensasi Kadaluarsa</h4>
                    <p class="text-xs text-orange-800 mt-1 leading-relaxed">
                        {{ $dispensasi->catatan_admin ?? 'Permohonan dispensasi ini telah kadaluarsa karena melewati batas waktu operasional izin.' }}
                    </p>
                </div>
            </div>
            @endif

           {{-- Action Buttons --}}
<div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-3">

    @if($dispensasi->status === 'menunggu')
        @if($isSedangBertugasAktif ?? false)
            <form method="POST" action="{{ route('guru.pengajuan.approve', $dispensasi) }}" class="flex-1">
                @csrf
                <button type="submit"
                        data-confirm="Setujui dispensasi {{ $dispensasi->siswa->nama_lengkap }} dan generate QR Code?"
                        class="w-full inline-flex justify-center items-center px-4 py-3 min-h-[44px] rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                    <i class="ti ti-check mr-2"></i>Setujui & Generate QR
                </button>
            </form>

            <button onclick="rejectDispensasi()"
                    class="flex-1 inline-flex justify-center items-center px-4 py-3 min-h-[44px] rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors">
                <i class="ti ti-x mr-2"></i>Tolak
            </button>
        @else
            <div class="flex-1 px-4 py-3 min-h-[44px] rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-center gap-2">
                <i class="ti ti-info-circle text-amber-600 text-base flex-shrink-0"></i>
                <span><strong>Mode Pemantau:</strong> Anda tidak sedang bertugas piket pada sesi aktif saat ini, sehingga tidak dapat menyetujui atau menolak pengajuan ini.</span>
            </div>
        @endif
    @elseif($dispensasi->status === 'disetujui' && empty($dispensasi->waktu_keluar_aktual))
        <div class="flex-1 flex flex-col sm:flex-row gap-2">
            <div class="flex-1 px-4 py-3 min-h-[44px] rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center gap-2">
                <i class="ti ti-circle-check text-emerald-600 flex-shrink-0"></i>
                <span>Disetujui. Menunggu scan keluar di Satpam.</span>
            </div>
            @if($isSedangBertugasAktif ?? false)
                <button onclick="rejectDispensasi('Batalkan Dispensasi', 'Alasan pembatalan (Siswa tidak jadi keluar / membatalkan izin):')"
                        class="inline-flex justify-center items-center px-4 py-3 min-h-[44px] rounded-lg text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors whitespace-nowrap">
                    <i class="ti ti-ban mr-1.5"></i>Batalkan Dispensasi
                </button>
            @endif
        </div>
    @elseif($dispensasi->status === 'kadaluarsa')
        <div class="flex-1 px-4 py-3 min-h-[44px] rounded-lg bg-orange-50 border border-orange-200 text-xs text-orange-900 flex items-center gap-2">
            <i class="ti ti-calendar-off text-orange-600 text-base flex-shrink-0"></i>
            <span>
                Status: <strong>Kadaluarsa</strong>. Siswa tidak dapat scan di pos satpam karena batas waktu izin telah terlewati.
            </span>
        </div>
    @else
        <div class="flex-1 px-4 py-3 min-h-[44px] rounded-lg bg-gray-50 border border-gray-200 text-xs text-gray-600 flex items-center">
            <i class="ti ti-info-circle mr-2 text-blue-500 text-base flex-shrink-0"></i>
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
        @if($isSedangBertugasAktif ?? false)
            <a href="{{ route('guru.pengajuan.create') }}"
               class="inline-flex justify-center items-center px-4 py-3 min-h-[44px] rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                <i class="ti ti-plus mr-2"></i>
                Buat Dispensasi Baru
            </a>
        @endif

        <a href="{{ route('guru.pengajuan.index') }}"
           class="inline-flex justify-center items-center px-4 py-3 min-h-[44px] rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">
            <i class="ti ti-list mr-2"></i>
            Daftar Pengajuan
        </a>
    </div>


            </div>
        </div>

        {{-- ============ KOLOM KANAN: Info Siswa & Guru ============ --}}
        <div class="space-y-4">

            {{-- Data Siswa --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="ti ti-user text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 leading-tight">Data Pemohon</h4>
                        <p class="text-[11px] text-slate-500">Identitas pemohon dispensasi</p>
                    </div>
                </div>
                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block mb-0.5">Nama Lengkap</span>
                        <p class="font-bold text-slate-900 text-sm leading-snug">{{ $dispensasi->siswa->nama_lengkap }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                        <div>
                            <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block mb-0.5">
                                <i class="ti ti-id text-slate-400 mr-1"></i>NIS
                            </span>
                            <p class="font-mono font-bold text-slate-800 text-xs">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block mb-0.5">
                                <i class="ti ti-school text-slate-400 mr-1"></i>Kelas
                            </span>
                            <p class="font-bold text-slate-800 text-xs">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block mb-0.5">
                            <i class="ti ti-book text-slate-400 mr-1"></i>Jurusan
                        </span>
                        <p class="text-slate-700 font-medium text-xs">{{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block mb-1">
                            <i class="ti ti-brand-whatsapp text-emerald-600 mr-1"></i>No. Telepon / WhatsApp
                        </span>
                        @if(!empty($dispensasi->siswa->no_telepon))
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $dispensasi->siswa->no_telepon);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                } elseif (str_starts_with($cleanPhone, '8')) {
                                    $cleanPhone = '62' . $cleanPhone;
                                }
                            @endphp
                            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 hover:text-emerald-700 font-mono transition-colors">
                                <i class="ti ti-brand-whatsapp text-base text-emerald-600"></i>
                                <span>{{ $dispensasi->siswa->no_telepon }}</span>
                                <i class="ti ti-external-link text-[11px] text-emerald-500"></i>
                            </a>
                        @else
                            <p class="text-slate-400 text-xs italic">Belum diatur</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Timeline Status --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="ti ti-history text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 leading-tight">Riwayat</h3>
                        <p class="text-[11px] text-slate-500">Jejak aktivitas pengajuan</p>
                    </div>
                </div>
                <div class="relative border-l-2 border-slate-200 ml-3.5 space-y-6 pb-2">
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-blue-500 border-2 border-white shadow-sm"></div>
                        <p class="text-xs font-bold text-slate-800">Pengajuan Dibuat</p>
                        <p class="text-[10px] text-slate-500 mt-0.5">{{ $dispensasi->created_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                    </div>
                    @if($dispensasi->status === 'ditolak')
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-rose-500 border-2 border-white shadow-sm"></div>
                        <p class="text-xs font-bold text-slate-800">Ditolak Guru Piket</p>
                        @if($dispensasi->guru)
                            <p class="text-[11px] text-slate-600 mt-0.5">Oleh: {{ $dispensasi->guru->nama_lengkap }}</p>
                        @endif
                        @if($dispensasi->rejected_at)
                            <p class="text-[10px] text-slate-500 mt-0.5">{{ $dispensasi->rejected_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                        @endif
                        @if($dispensasi->catatan_admin)
                            <p class="text-xs text-rose-600 mt-0.5">Alasan: {{ $dispensasi->catatan_admin }}</p>
                        @endif
                    </div>
                    @elseif(in_array($dispensasi->status, ['disetujui', 'keluar', 'selesai']))
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow-sm"></div>
                        <p class="text-xs font-bold text-slate-800">
                            {{ $dispensasi->dibuat_manual_oleh_guru ? 'Dibuat Manual & Disetujui' : 'Disetujui Guru Piket' }}
                        </p>
                        @if($dispensasi->guru)
                            <p class="text-[11px] text-slate-600 mt-0.5">Oleh: {{ $dispensasi->guru->nama_lengkap }}</p>
                        @endif
                        @if($dispensasi->approved_at)
                            <p class="text-[10px] text-slate-500 mt-0.5">{{ $dispensasi->approved_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                        @endif
                    </div>
                    @endif
                    @if($dispensasi->waktu_keluar_aktual)
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-blue-500 border-2 border-white shadow-sm"></div>
                        <p class="text-xs font-bold text-slate-800">Konfirmasi Keluar (Scan)</p>
                        <p class="text-[10px] text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                    </div>
                    @endif
                    @if($dispensasi->waktu_kembali_aktual)
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow-sm"></div>
                        <p class="text-xs font-bold text-slate-800">Konfirmasi Kembali (Selesai)</p>
                        <p class="text-[10px] text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                    </div>
                    @endif
                    @if($dispensasi->is_warned)
                    <div class="relative pl-6">
                        <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow-sm"></div>
                        <p class="text-xs font-bold text-emerald-800">Sudah Dihubungi</p>
                        <p class="text-[10px] text-slate-500 mt-0.5">
                            {{ $dispensasi->warned_at ? \Carbon\Carbon::parse($dispensasi->warned_at)->isoFormat('D MMM Y, HH:mm') . ' WIB' : '-' }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Guru Piket --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="ti ti-user-shield text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 leading-tight">Guru Piket Penanggung Jawab</h4>
                        <p class="text-[11px] text-slate-500">Petugas yang menangani</p>
                    </div>
                </div>
                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block mb-0.5">Nama Guru</span>
                        <p class="font-bold text-slate-900 text-sm">{{ $dispensasi->guru?->nama_lengkap ?? 'Menunggu persetujuan' }}</p>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block mb-0.5">Tanggal Piket</span>
                        <p class="text-slate-700 font-medium text-xs">{{ $dispensasi->created_at ? $dispensasi->created_at->format('d M Y') : '-' }}</p>
                    </div>
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
    const titleSpan = titleEl.querySelector('span:last-child') || titleEl.querySelector('span');
    if (titleSpan) titleSpan.textContent = title;
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
        if(iconCetak) iconCetak.className = "ti ti-photo mr-1.5";
    } else {
        if(textCetak) textCetak.textContent = "Cetak Struk Thermal (PC)";
        if(iconCetak) iconCetak.className = "ti ti-printer mr-1.5";
    }
});
</script>
@endpush
@endsection
