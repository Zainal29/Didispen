@extends('siswa.layouts.app')
@section('title', 'Detail Pengajuan Dispensasi')
@section('page-title', 'Detail Pengajuan Dispensasi')
@section('content')

<div class="max-w-5xl mx-auto space-y-4 pb-36 sm:pb-16">

    {{-- Header Card --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Nomor Surat</p>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-base sm:text-xl font-bold text-slate-900 font-mono tracking-tight break-all">{{ $dispensasi->nomor_surat }}</h1>
                    @if($dispensasi->isRombongan())
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            <i class="ti ti-users text-xs"></i> Dispensasi Rombongan
                        </span>
                        @if(auth()->user()->siswa && auth()->user()->siswa->id !== $dispensasi->siswa_id)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                Anggota Rombongan
                            </span>
                        @endif
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            <i class="ti ti-user text-xs"></i> Dispensasi Individu
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
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

    {{-- ============================================================ --}}
    {{-- HUBUNGI GURU PIKET BANNER --}}
    {{-- ============================================================ --}}
    @if($popupEligible)
    <div id="piketBanner" class="bg-white border border-amber-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5">
            <div class="flex items-start gap-3 sm:gap-4">
                {{-- Icon --}}
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center flex-shrink-0 text-amber-600">
                    <i class="ti ti-user-shield text-xl"></i>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">
                                Guru Piket Belum Merespons
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Anda dapat menghubungi Guru Piket yang sedang bertugas melalui WhatsApp.
                            </p>
                        </div>

                        {{-- Countdown --}}
                        <div class="inline-flex items-center self-start sm:self-auto gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200">
                            <i class="ti ti-clock text-amber-600 text-xs"></i>
                            <span id="countdownDisplay" class="text-xs font-bold font-mono text-amber-800">
                                @php
                                    $mins = intdiv($popupSecondsLeft, 60);
                                    $secs = $popupSecondsLeft % 60;
                                @endphp
                                {{ sprintf('%02d:%02d', $mins, $secs) }}
                            </span>
                        </div>
                    </div>

                    {{-- Action --}}
                    <div class="mt-3">
                        <button type="button"
                                onclick="openPiketPopup()"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 min-h-[44px] px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold shadow-sm transition-colors">
                            <i class="ti ti-brand-whatsapp text-base"></i>
                            <span>Hubungi Guru Piket</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- STATUS NOTIFICATION BANNERS --}}
    @php
        $isTerlambatDetail = $dispensasi->status === 'keluar' && $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);
        $terlambatText = $dispensasi->getLateDurationText();
    @endphp

    @if($isTerlambatDetail)
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 sm:p-5">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="w-10 h-10 rounded-xl bg-rose-100 border border-rose-200 flex items-center justify-center flex-shrink-0 text-rose-600">
                    <i class="ti ti-alert-triangle text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-rose-900 font-bold text-sm mb-1">PERINGATAN: ANDA TERLAMBAT!</h3>
                    <p class="text-rose-700 text-xs sm:text-sm mb-3">
                        Anda telah melewati batas waktu kembali selama
                        <span class="bg-rose-100 px-2 py-0.5 rounded-md font-bold text-rose-900 border border-rose-200">{{ $terlambatText }}</span>
                    </p>
                    <div class="bg-white/80 rounded-xl p-3 mb-4 border border-rose-100">
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <p class="text-rose-600 text-[10px] font-bold uppercase mb-0.5">Batas Kembali</p>
                                <p class="font-bold text-rose-900 flex items-center gap-1">
                                    <i class="ti ti-clock"></i>{{ \Carbon\Carbon::parse($dispensasi->batas_waktu_kembali)->format('H:i') }} WIB
                                </p>
                            </div>
                            <div>
                                <p class="text-rose-600 text-[10px] font-bold uppercase mb-0.5">Status</p>
                                <p class="font-bold text-rose-900 flex items-center gap-1">
                                    <i class="ti ti-walk"></i>Belum Kembali
                                </p>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('siswa.dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center min-h-[44px] px-4 py-2 bg-white border border-rose-200 text-rose-700 hover:bg-rose-50 active:bg-rose-100 font-bold rounded-xl transition-colors text-xs">
                        <i class="ti ti-smart-home mr-1.5 text-base"></i>Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    @elseif($dispensasi->status === 'keluar' && $dispensasi->batas_waktu_kembali)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 border border-amber-200 flex items-center justify-center flex-shrink-0 text-amber-700">
                <i class="ti ti-clock text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-amber-900 font-bold text-xs sm:text-sm">Sedang Keluar - Harap Kembali Tepat Waktu</p>
                <p class="text-amber-700 text-xs mt-0.5">
                    Batas kembali: <strong>{{ \Carbon\Carbon::parse($dispensasi->batas_waktu_kembali)->format('H:i') }} WIB</strong>
                </p>
            </div>
        </div>
    @elseif($dispensasi->isNotReturned())
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 sm:p-5">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 border border-rose-200">
                    <i class="ti ti-user-x text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-rose-900">Dispensasi Ditutup: Tidak Kembali ke Sekolah</h3>
                    <p class="text-xs text-rose-700 mt-1 leading-relaxed">
                        Anda tercatat keluar dari sekolah namun <strong>tidak melakukan scan kembali di pos gerbang Satpam</strong> hingga kegiatan belajar mengajar berakhir. Status ditutup otomatis dengan catatan peringatan.
                    </p>
                </div>
            </div>
        </div>
    @elseif($dispensasi->isReturnedLate())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 sm:p-5">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 border border-amber-200">
                    <i class="ti ti-history text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-amber-900">Dispensasi Selesai (Kembali Terlambat)</h3>
                    <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                        Dispensasi telah selesai, namun Anda kembali melewati batas waktu yang ditentukan selama <strong>{{ $dispensasi->getLateDurationText() }}</strong> (Kembali pukul {{ $dispensasi->waktu_kembali_aktual?->format('H:i') }} WIB, Batas waktu: {{ $dispensasi->batas_waktu_kembali?->format('H:i') }} WIB).
                    </p>
                </div>
            </div>
        </div>
    @elseif($dispensasi->status === 'dibatalkan')
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="w-10 h-10 rounded-xl bg-slate-200 text-slate-600 flex items-center justify-center shrink-0 border border-slate-300">
                    <i class="ti ti-ban text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-slate-900">Dispensasi Dibatalkan</h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Permohonan dispensasi ini telah dibatalkan (siswa tidak jadi keluar sekolah) dan QR Code dinonaktifkan.
                    </p>
                </div>
            </div>
        </div>
    @elseif($dispensasi->status === 'menunggu')
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-amber-100 text-amber-700 border border-amber-200 flex items-center justify-center shrink-0">
                    <i class="ti ti-alert-triangle text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                        <h3 class="text-sm font-bold text-amber-900">
                            Pengajuan Ini Akan Segera Kadaluarsa
                        </h3>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300 self-start sm:self-auto">
                            <i class="ti ti-hourglass-empty text-xs"></i> Kadaluarsa: {{ $dispensasi->getBatasWaktuExpiredText() }}
                        </span>
                    </div>
                    <p class="text-xs text-amber-800 mt-1.5 leading-relaxed">
                        Permohonan dispensasi Anda masih <strong>menunggu persetujuan</strong> dari Guru Piket. Pengajuan ini <strong>akan kadaluarsa</strong> jika tidak disetujui hingga batas izin berakhir (pukul <strong>{{ $dispensasi->getBatasWaktuExpiredText() }}</strong>). Segera temui Guru Piket di ruang piket untuk menyelesaikan proses permohonan.
                    </p>
                    @if(!empty($popupEligible))
                    <div class="mt-3">
                        <button type="button" onclick="openPiketPopup()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 min-h-[44px] px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold shadow-xs transition-colors">
                            <i class="ti ti-brand-whatsapp text-sm"></i> Hubungi Guru Piket via WhatsApp
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    @elseif($dispensasi->status === 'kadaluarsa')
        <div class="bg-orange-50 border border-orange-200 rounded-2xl p-4 sm:p-5">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0 border border-orange-200">
                    <i class="ti ti-calendar-off text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-orange-900">Dispensasi Kadaluarsa</h3>
                    <p class="text-xs text-orange-800 mt-1 leading-relaxed">
                        {{ $dispensasi->catatan_admin ?? 'Permohonan ini kadaluarsa secara otomatis karena melewati batas waktu jam pelajaran sekolah.' }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Kolom Kiri: Informasi Dispensasi --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Card Informasi Dispensasi --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
                <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5 border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="ti ti-file-text text-slate-400 text-base"></i>Informasi Dispensasi
                    </h3>
                    <span class="text-xs font-bold text-slate-500 font-mono">
                        {{ \Carbon\Carbon::parse($dispensasi->tanggal_izin ?? $dispensasi->created_at)->translatedFormat('d M Y') }}
                    </span>
                </div>

                @if($dispensasi->isRombongan())
                <div class="mb-5 bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-xs">
                    {{-- Header Rombongan --}}
                    <div class="flex items-start justify-between gap-2.5 mb-3">
                        <div class="flex items-start gap-2.5 sm:gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg shadow-sm shrink-0 mt-0.5">
                                <i class="ti ti-users"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-blue-600 block leading-tight">Dispensasi Rombongan</span>
                                <h4 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight capitalize leading-snug mt-0.5 break-words">
                                    {{ $dispensasi->siswa->nama_lengkap }} & Rombongan
                                </h4>
                            </div>
                        </div>
                        <div class="shrink-0">
                            @if($dispensasi->siswa_id === auth()->user()->siswa?->id)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-600 text-white shadow-xs whitespace-nowrap">
                                    <i class="ti ti-crown text-xs"></i> <span class="hidden sm:inline">Anda: </span>Pemohon
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-200 text-slate-700 whitespace-nowrap">
                                    <i class="ti ti-user-check text-xs"></i> <span class="hidden sm:inline">Anda: </span>Anggota
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Info Pemohon --}}
                    <div class="py-2.5 px-3 bg-white rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-2 text-xs text-slate-600 mb-4 shadow-xs">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <i class="ti ti-user-edit text-blue-600 flex-shrink-0 text-sm"></i>
                            <span>Diajukan oleh:</span>
                            <strong class="text-slate-900">{{ $dispensasi->siswa->nama_lengkap }}</strong>
                            <span class="text-slate-500 font-medium">({{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }})</span>
                        </div>
                        <span class="font-mono text-[11px] text-slate-500">NIS: {{ $dispensasi->siswa->user->nis_nip ?? $dispensasi->siswa->nis_nip ?? '-' }}</span>
                    </div>

                    {{-- Daftar Anggota Rombongan --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                <i class="ti ti-id text-blue-600 text-sm"></i>Daftar Anggota Rombongan
                            </span>
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $dispensasi->anggotaSiswa->count() + 1 }} Siswa
                            </span>
                        </div>

                        <div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-100 overflow-hidden shadow-xs">
                            {{-- Ketua Rombongan --}}
                            @php
                                $isKetuaViewing = auth()->user()->siswa?->id === $dispensasi->siswa->id;
                            @endphp
                            <div class="p-3 sm:px-4 text-xs {{ $isKetuaViewing ? 'bg-blue-50/50 font-semibold' : 'hover:bg-slate-50' }} transition-colors">
                                <div class="flex items-start sm:items-center justify-between gap-2 sm:gap-3">
                                    <div class="flex items-start sm:items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[11px] flex-shrink-0 mt-0.5 sm:mt-0">
                                            1
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-bold text-slate-900 leading-snug break-words">{{ $dispensasi->siswa->nama_lengkap }}</span>
                                                @if($isKetuaViewing)
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-600 text-white flex-shrink-0">Anda</span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-slate-500 font-mono mt-0.5">NIS: {{ $dispensasi->siswa->user->nis_nip ?? $dispensasi->siswa->nis_nip ?? '-' }}</p>
                                        </div>
                                    </div>

                                    {{-- Desktop Badges --}}
                                    <div class="hidden sm:flex items-center gap-2 flex-shrink-0">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="ti ti-crown text-xs text-amber-600"></i> Ketua
                                        </span>
                                    </div>
                                </div>

                                {{-- Mobile Badges --}}
                                <div class="sm:hidden flex items-center gap-1.5 mt-2 pl-[34px]">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="ti ti-crown text-[10px] text-amber-600"></i> Ketua
                                    </span>
                                </div>
                            </div>

                            {{-- Anggota Lainnya --}}
                            @foreach($dispensasi->anggotaSiswa as $idx => $ang)
                                @php
                                    $isCurrentAnggota = auth()->user()->siswa?->id === $ang->id;
                                @endphp
                                <div class="p-3 sm:px-4 text-xs {{ $isCurrentAnggota ? 'bg-blue-50/50' : 'hover:bg-slate-50' }} transition-colors">
                                    <div class="flex items-start sm:items-center justify-between gap-2 sm:gap-3">
                                        <div class="flex items-start sm:items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                            <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-[11px] flex-shrink-0 mt-0.5 sm:mt-0">
                                                {{ $idx + 2 }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="font-medium text-slate-900 leading-snug break-words">{{ $ang->nama_lengkap }}</span>
                                                    @if($isCurrentAnggota)
                                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-600 text-white flex-shrink-0">Anda</span>
                                                    @endif
                                                </div>
                                                <p class="text-[11px] text-slate-500 font-mono mt-0.5">NIS: {{ $ang->user->nis_nip ?? $ang->nis_nip ?? '-' }}</p>
                                            </div>
                                        </div>

                                        {{-- Desktop Badges --}}
                                        <div class="hidden sm:flex items-center gap-2 flex-shrink-0">
                                            <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                {{ $ang->kelas?->nama_kelas ?? '-' }}
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                Anggota
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Mobile Badges --}}
                                    <div class="sm:hidden flex items-center gap-1.5 mt-2 pl-[34px]">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $ang->kelas?->nama_kelas ?? '-' }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            Anggota
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <div class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200">
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-1 flex items-center gap-1">
                                <i class="ti ti-tag text-slate-400"></i> Kategori
                            </p>
                            <p class="font-semibold text-slate-900 capitalize text-sm">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200">
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-1 flex items-center gap-1">
                                <i class="ti ti-target text-slate-400"></i> Tujuan
                            </p>
                            <p class="font-semibold text-slate-900 text-sm">{{ $dispensasi->tujuan }}</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200">
                        <p class="text-[10px] font-bold text-slate-500 uppercase mb-1 flex items-center gap-1">
                            <i class="ti ti-align-left text-slate-400"></i> Alasan
                        </p>
                        <p class="font-medium text-slate-800 text-sm leading-relaxed">{{ $dispensasi->alasan }}</p>
                    </div>
                    @if($dispensasi->lokasi)
                    <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200">
                        <p class="text-[10px] font-bold text-slate-500 uppercase mb-1 flex items-center gap-1">
                            <i class="ti ti-map-pin text-slate-400"></i> Lokasi
                        </p>
                        <p class="font-semibold text-slate-900 text-sm">{{ $dispensasi->lokasi }}</p>
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 {{ $dispensasi->waktu_kembali_aktual ? 'sm:grid-cols-3' : '' }} gap-3 sm:gap-4 mt-4 pt-4 border-t border-slate-100">
                    <div class="bg-blue-50/60 rounded-xl p-3 sm:p-3.5 border border-blue-100">
                        <p class="text-[10px] font-bold text-blue-700 uppercase mb-1 flex items-center gap-1">
                            <i class="ti ti-clock"></i> Jam Keluar
                        </p>
                        <p class="font-bold text-blue-900 text-sm">{{ $dispensasi->jam_keluar }}</p>
                        @php $waktuKeluar = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar, $dispensasi->tanggal_izin?->dayOfWeek ?? $dispensasi->created_at?->dayOfWeek); @endphp
                        @if($waktuKeluar !== '-' && $waktuKeluar !== $dispensasi->jam_keluar)
                            <p class="text-blue-700 text-xs mt-1 flex items-center gap-1"><i class="ti ti-clock text-[11px]"></i>{{ $waktuKeluar }}</p>
                        @endif
                    </div>
                    <div class="bg-amber-50/60 rounded-xl p-3 sm:p-3.5 border border-amber-100">
                        <p class="text-[10px] font-bold text-amber-700 uppercase mb-1 flex items-center gap-1">
                            <i class="ti ti-clock"></i> {{ $dispensasi->sampai_pulang ? 'Jam Kembali' : 'Batas Kembali' }}
                        </p>
                        @if($dispensasi->sampai_pulang)
                            <p class="font-bold text-purple-900 text-sm">Sampai Pulang</p>
                            <p class="text-purple-700 text-xs mt-1 font-semibold flex items-center gap-1"><i class="ti ti-circle-check text-xs"></i>Selesai KBM</p>
                        @else
                            <p class="font-bold text-amber-900 text-sm">{{ $dispensasi->jam_kembali }}</p>
                            @php $waktuKembali = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali, $dispensasi->tanggal_izin?->dayOfWeek ?? $dispensasi->created_at?->dayOfWeek); @endphp
                            @if($waktuKembali !== '-' && $waktuKembali !== $dispensasi->jam_kembali)
                                <p class="text-amber-700 text-xs mt-1 flex items-center gap-1"><i class="ti ti-clock text-[11px]"></i>{{ $waktuKembali }}</p>
                            @endif
                        @endif
                    </div>
                    @if($dispensasi->waktu_kembali_aktual)
                        <div class="col-span-2 sm:col-span-1 {{ $dispensasi->isReturnedLate() ? 'bg-rose-50/60 border-rose-100' : 'bg-emerald-50/60 border-emerald-100' }} rounded-xl p-3 sm:p-3.5 border">
                            <p class="text-[10px] font-bold {{ $dispensasi->isReturnedLate() ? 'text-rose-700' : 'text-emerald-700' }} uppercase mb-1 flex items-center gap-1">
                                <i class="ti ti-door-enter"></i> Kembali Aktual
                            </p>
                            <p class="font-bold {{ $dispensasi->isReturnedLate() ? 'text-rose-900' : 'text-emerald-900' }} text-sm">
                                {{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->format('H:i') }} WIB
                            </p>
                            @if($dispensasi->isReturnedLate())
                                <p class="text-rose-700 text-xs mt-1 flex items-center gap-1"><i class="ti ti-alert-triangle text-[11px]"></i>Terlambat {{ $dispensasi->getLateDurationText(true) }}</p>
                            @elseif($dispensasi->isReturnedEarly())
                                <p class="text-emerald-700 text-xs mt-1 flex items-center gap-1"><i class="ti ti-clock-check text-[11px]"></i>Kembali Lebih Awal</p>
                            @else
                                <p class="text-emerald-700 text-xs mt-1 flex items-center gap-1"><i class="ti ti-check text-[11px]"></i>Tepat Waktu</p>
                            @endif
                        </div>
                    @endif
                </div>

                @if($dispensasi->dibuat_manual_oleh_guru)
                    <div class="mt-4 bg-purple-50 border border-purple-200 rounded-xl p-3.5">
                        <p class="text-purple-700 text-[10px] font-bold uppercase mb-1 flex items-center gap-1.5"><i class="ti ti-user-circle"></i>Dibuat Manual oleh Guru Piket</p>
                        <p class="text-purple-900 text-xs sm:text-sm font-medium">Pengajuan dispensasi ini dibuat langsung oleh {{ $dispensasi->guru?->nama_lengkap ?? 'Guru Piket' }} untuk Anda.</p>
                    </div>
                @endif
                @if($dispensasi->catatan_admin)
                    <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl p-3.5">
                        <p class="text-amber-800 text-[10px] font-bold uppercase mb-1 flex items-center gap-1.5">
                            <i class="ti ti-message-dots text-amber-600"></i> Catatan Guru Piket / Keterangan Sistem
                        </p>
                        <p class="text-amber-950 text-xs sm:text-sm font-medium leading-relaxed">{{ $dispensasi->catatan_admin }}</p>
                    </div>
                @endif
            </div>

            {{-- Status QR Code & Cetak --}}
            <div id="qr-code-section" class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i class="ti ti-qrcode text-slate-400 text-base"></i>Status QR Code & Cetak
                </h3>

                {{-- Info QR Code --}}
                @if($dispensasi->status === 'disetujui')
                    @if($dispensasi->qr_code)
                        <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-xl text-center">
                            <div class="w-12 h-12 mx-auto rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-3 border border-emerald-200">
                                <i class="ti ti-check text-2xl"></i>
                            </div>
                            <p class="text-emerald-900 font-bold text-sm mb-1">QR Code Aktif</p>
                            <p class="text-emerald-700 text-xs mb-3">Tunjukkan QR Code ini kepada petugas Satpam di pos gerbang untuk konfirmasi keluar.</p>
                            <div class="mb-3 py-1.5 px-3 bg-white/80 border border-emerald-200 rounded-lg text-xs text-emerald-800 flex items-center justify-center gap-1.5">
                                <i class="ti ti-info-circle text-emerald-600"></i>
                                <span>Scan keluar sebelum batas izin berakhir (<strong>{{ $dispensasi->getBatasWaktuExpiredText() }}</strong>).</span>
                            </div>
                            <div class="bg-white p-3 rounded-2xl inline-block border border-slate-200 shadow-sm">
                                <img src="{{ asset('storage/' . $dispensasi->qr_code) }}" alt="QR Code" class="w-44 h-44 object-contain mx-auto">
                            </div>
                            <p class="text-[11px] text-slate-500 mt-3 font-mono font-bold">{{ $dispensasi->nomor_surat }}</p>

                            @if(empty($dispensasi->waktu_keluar_aktual))
                            <div class="mt-4 pt-3 border-t border-emerald-200/60 text-center">
                                <p class="text-xs text-slate-500 mb-2">Tidak jadi izin keluar sekolah?</p>
                                <button type="button" onclick="confirmBatalKeluar('{{ route('siswa.pengajuan.batal', $dispensasi) }}')"
                                        class="w-full inline-flex items-center justify-center min-h-[44px] px-4 py-2.5 rounded-xl text-xs font-bold text-rose-600 bg-white hover:bg-rose-50 active:bg-rose-100 border border-rose-200 transition-colors gap-1.5">
                                    <i class="ti ti-ban text-sm"></i> Batalkan Dispensasi Ini
                                </button>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-xl text-center">
                            <div class="w-12 h-12 mx-auto rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mb-3 border border-amber-200">
                                <i class="ti ti-clock text-2xl"></i>
                            </div>
                            <p class="text-amber-900 font-bold text-sm mb-1">QR Code Belum Tersedia</p>
                            <p class="text-amber-700 text-xs">Sedang di-generate. Silakan refresh halaman.</p>
                        </div>
                    @endif
                @elseif($dispensasi->status === 'keluar')
                    @if($dispensasi->isSampaiPulang())
                        <div class="p-4 sm:p-5 bg-emerald-50/70 border border-emerald-200 rounded-xl text-center">
                            <div class="w-12 h-12 mx-auto rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-3 border border-emerald-200">
                                <i class="ti ti-door-exit text-2xl"></i>
                            </div>
                            <p class="text-emerald-900 font-bold text-sm mb-1">Dispensasi Sedang Berjalan (Sampai Pulang)</p>
                            <p class="text-emerald-700 text-xs max-w-sm mx-auto leading-relaxed">
                                Anda telah diverifikasi keluar sekolah oleh Satpam. Karena izin dispensasi ini <strong>sampai jam pulang sekolah</strong>, Anda <strong>tidak perlu melakukan scan kembali</strong> ke Satpam.
                            </p>
                            <div class="mt-3.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 border border-emerald-300 text-emerald-800 text-[11px] font-bold">
                                <i class="ti ti-check"></i> Bebas Kewajiban Scan Kembali
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-sky-50/70 border border-sky-200 rounded-xl text-center">
                            <div class="w-12 h-12 mx-auto rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center mb-3 border border-sky-200">
                                <i class="ti ti-walk text-2xl"></i>
                            </div>
                            <p class="text-sky-900 font-bold text-sm mb-1">Sedang Keluar</p>
                            <p class="text-sky-700 text-xs mb-4">Tunjukkan QR Code yang sama kepada Satpam saat Anda kembali.</p>
                            @if($dispensasi->qr_code)
                                <div class="bg-white p-3 rounded-2xl inline-block border border-slate-200 shadow-sm">
                                    <img src="{{ asset('storage/' . $dispensasi->qr_code) }}" alt="QR Code" class="w-44 h-44 object-contain mx-auto">
                                </div>
                                <p class="text-[11px] text-slate-500 mt-3 font-mono font-bold">{{ $dispensasi->nomor_surat }}</p>
                            @endif
                        </div>
                    @endif
                @elseif($dispensasi->status === 'selesai')
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center">
                        <div class="w-12 h-12 mx-auto rounded-xl bg-slate-200 text-slate-600 flex items-center justify-center mb-3">
                            <i class="ti ti-checks text-2xl"></i>
                        </div>
                        <p class="text-slate-900 font-bold text-sm mb-1">QR Code Tidak Aktif</p>
                        <p class="text-slate-600 text-xs">Dispensasi ini sudah selesai diproses.</p>
                    </div>
                @elseif($dispensasi->status === 'dibatalkan')
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center">
                        <div class="w-12 h-12 mx-auto rounded-xl bg-slate-200 text-slate-600 flex items-center justify-center mb-3">
                            <i class="ti ti-ban text-2xl"></i>
                        </div>
                        <p class="text-slate-900 font-bold text-sm mb-1">Dispensasi Dibatalkan</p>
                        <p class="text-slate-600 text-xs">Permohonan dispensasi ini telah dibatalkan.</p>
                    </div>
                @elseif($dispensasi->status === 'kadaluarsa')
                    <div class="p-4 bg-orange-50 border border-orange-200 rounded-xl text-center">
                        <div class="w-12 h-12 mx-auto rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center mb-3 border border-orange-200">
                            <i class="ti ti-calendar-off text-2xl"></i>
                        </div>
                        <p class="text-orange-900 font-bold text-sm mb-1">Dispensasi Kadaluarsa</p>
                        <p class="text-orange-700 text-xs leading-relaxed">
                            {{ $dispensasi->catatan_admin ?? 'Permohonan ini kadaluarsa karena tidak dikonfirmasikan hingga batas waktu jam pelajaran berakhir.' }}
                        </p>
                    </div>
                @elseif($dispensasi->status === 'ditolak')
                    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-center">
                        <div class="w-12 h-12 mx-auto rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center mb-3 border border-rose-200">
                            <i class="ti ti-x text-2xl"></i>
                        </div>
                        <p class="text-rose-900 font-bold text-sm mb-1">Pengajuan Ditolak</p>
                        <p class="text-rose-700 text-xs">Pengajuan telah ditolak oleh Guru Piket.</p>
                    </div>
                @else
                    <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-xl text-center">
                        <div class="w-12 h-12 mx-auto rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mb-3 border border-amber-200">
                            <i class="ti ti-clock text-2xl"></i>
                        </div>
                        <p class="text-amber-900 font-bold text-sm mb-1">Menunggu Persetujuan</p>
                        <p class="text-amber-700 text-xs mb-2">QR Code akan tersedia setelah disetujui Guru Piket.</p>
                        <div class="mt-2 py-1.5 px-3 bg-amber-100/70 border border-amber-200 rounded-lg text-xs text-amber-800 flex items-center justify-center gap-1">
                            <i class="ti ti-hourglass-empty text-xs"></i>
                            <span>Kadaluarsa pukul <strong>{{ $dispensasi->getBatasWaktuExpiredText() }}</strong> jika belum disetujui.</span>
                        </div>
                    </div>
                @endif

                {{-- Info Batas Cetak --}}
                @php
                    $maxPrint = \App\Helpers\PrintHelper::maxStudentLimit();
                    $currentPrint = $dispensasi->student_print_count ?? 0;
                    $sisaCetak = $maxPrint - $currentPrint;
                    $currentTime = \App\Helpers\PrintHelper::currentTime();
                    $startTime = \App\Helpers\PrintHelper::startTime();
                    $endTime = \App\Helpers\PrintHelper::endTime();
                    $isWithinTime = \App\Helpers\PrintHelper::isWithinOperatingHours($currentTime);
                @endphp
                <div class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-slate-900 text-xs sm:text-sm font-bold flex items-center gap-1.5">
                            <i class="ti ti-printer text-slate-400"></i>Status Pencetakan
                        </p>
                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold {{ $sisaCetak > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $currentPrint }} / {{ $maxPrint }}
                        </span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 mb-3">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: {{ min(($currentPrint / $maxPrint) * 100, 100) }}%"></div>
                    </div>
                    <div class="bg-white rounded-xl p-3 border border-slate-200 mb-3 text-xs text-slate-600">
                        <p class="flex items-center gap-1">
                            <i class="ti ti-clock text-slate-400"></i>
                            <strong>Jam Cetak:</strong> {{ $startTime }} - {{ $endTime }} WIB
                        </p>
                        <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                            <span>Saat ini: {{ $currentTime }} WIB -</span>
                            @if($isWithinTime)
                                <span class="text-emerald-700 font-bold">Dalam jam operasional</span>
                            @else
                                <span class="text-rose-700 font-bold">Di luar jam operasional</span>
                            @endif
                        </p>
                    </div>
                    @if($sisaCetak > 0)
                        <p class="text-slate-700 text-xs flex items-center gap-1"><i class="ti ti-circle-check text-emerald-600"></i>Sisa cetak: <strong>{{ $sisaCetak }} kali</strong> lagi</p>
                    @else
                        <p class="text-rose-700 text-xs font-bold flex items-center gap-1"><i class="ti ti-alert-triangle"></i>Batas cetak tercapai. Hubungi Guru Piket.</p>
                    @endif
                </div>

                {{-- Tombol Cetak --}}
                <div class="mt-4">
                    @if(in_array($dispensasi->status, ['disetujui', 'keluar']))
                        @if($sisaCetak > 0 && $isWithinTime)
                            <a href="{{ route('siswa.cetak', $dispensasi) }}" target="_blank" onclick="handleAfterPrintSiswa()" id="btnCetakSiswa"
                               class="w-full inline-flex justify-center items-center gap-2 min-h-[48px] px-5 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 transition-colors shadow-sm">
                                <i class="ti ti-printer text-base"></i>Cetak Surat Dispensasi
                            </a>
                        @else
                            <button disabled class="w-full inline-flex justify-center items-center gap-2 min-h-[48px] px-5 py-3 rounded-xl text-sm font-bold text-slate-400 bg-slate-100 cursor-not-allowed border border-slate-200">
                                <i class="ti ti-lock text-base"></i>
                                @if(!$isWithinTime) Di Luar Jam Cetak @else Batas Cetak Tercapai @endif
                            </button>
                            <p class="text-center text-xs text-slate-500 mt-2 flex items-center justify-center gap-1"><i class="ti ti-info-circle"></i>Hubungi Guru Piket jika butuh bantuan</p>
                        @endif
                    @else
                        <button disabled class="w-full inline-flex justify-center items-center gap-2 min-h-[48px] px-5 py-3 rounded-xl text-sm font-bold text-slate-400 bg-slate-100 cursor-not-allowed border border-slate-200">
                            <i class="ti ti-lock text-base"></i>Tidak Dapat Dicetak
                        </button>
                        <p class="text-center text-xs text-slate-500 mt-2 flex items-center justify-center gap-1"><i class="ti ti-shield-check"></i>Dispensasi selesai tidak dapat dicetak ulang.</p>
                    @endif
                </div>
            </div>

            {{-- Foto Verifikasi & Foto Bukti --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <i class="ti ti-camera text-slate-400 text-base"></i>Foto Verifikasi & Bukti
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Foto Verifikasi --}}
                    <div>
                        <p class="text-xs font-bold text-slate-700 mb-2 flex items-center gap-1.5">
                            <i class="ti ti-user-check text-blue-600"></i>Foto Verifikasi (Selfie)
                        </p>
                        @php
                            $hasFotoVerif = !empty($dispensasi->foto_verifikasi) && \Illuminate\Support\Facades\Storage::disk('public')->exists($dispensasi->foto_verifikasi);
                        @endphp
                        @if($hasFotoVerif)
                            <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50 aspect-video max-h-48 flex items-center justify-center cursor-pointer hover:shadow-md transition-shadow" onclick="openPhotoModal('{{ asset('storage/' . $dispensasi->foto_verifikasi) }}', 'Foto Verifikasi (Selfie)')">
                                <img src="{{ asset('storage/' . $dispensasi->foto_verifikasi) }}" alt="Foto Verifikasi" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="p-5 rounded-xl bg-slate-50 border border-dashed border-slate-300 text-center">
                                <i class="ti ti-camera text-slate-400 text-2xl mb-2 block"></i>
                                <p class="text-xs text-slate-500 font-medium">
                                    @if($dispensasi->status === 'selesai')
                                        Foto verifikasi sudah dibersihkan karena dispensasi telah selesai.
                                    @else
                                        Foto verifikasi tidak tersedia.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Foto Bukti --}}
                    <div>
                        <p class="text-xs font-bold text-slate-700 mb-2 flex items-center gap-1.5">
                            <i class="ti ti-photo text-emerald-600"></i>Foto Bukti Kegiatan
                        </p>
                        @php
                            $hasFotoBukti = !empty($dispensasi->foto_bukti) && \Illuminate\Support\Facades\Storage::disk('public')->exists($dispensasi->foto_bukti);
                        @endphp
                        @if($hasFotoBukti)
                            <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50 aspect-video max-h-48 flex items-center justify-center cursor-pointer hover:shadow-md transition-shadow" onclick="openPhotoModal('{{ asset('storage/' . $dispensasi->foto_bukti) }}', 'Foto Bukti Kegiatan')">
                                <img src="{{ asset('storage/' . $dispensasi->foto_bukti) }}" alt="Foto Bukti" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="p-5 rounded-xl bg-slate-50 border border-dashed border-slate-300 text-center">
                                <i class="ti ti-photo text-slate-400 text-2xl mb-2 block"></i>
                                <p class="text-xs text-slate-500 font-medium">
                                    @if($dispensasi->status === 'selesai')
                                        Foto bukti sudah dibersihkan karena dispensasi telah selesai.
                                    @else
                                        Foto bukti belum diunggah.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Data Siswa & Guru & Timeline --}}
        <div class="space-y-4">
            {{-- Data Siswa / Pemohon --}}
            @if($dispensasi->isRombongan())
                {{-- Card 1: Data Pemohon (PIC) --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="flex items-center gap-1.5"><i class="ti ti-crown text-amber-600"></i>Data Pemohon (PIC)</span>
                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Pemohon</span>
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5">Nama Lengkap</p>
                            <p class="font-bold text-slate-900 text-sm">{{ $dispensasi->siswa->nama_lengkap }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5">NIS / NISN</p>
                            <p class="font-mono font-semibold text-slate-800 text-xs sm:text-sm">{{ $dispensasi->siswa->user->nis_nip ?? $dispensasi->siswa->nis_nip ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5">Kelas</p>
                            <p class="font-semibold text-slate-800 text-xs sm:text-sm">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5">Jurusan</p>
                            <p class="text-slate-700 text-xs sm:text-sm">{{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                        </div>
                        @if(!empty($dispensasi->siswa->no_telepon))
                        @php
                            $rawSiswaPhone = preg_replace('/[^0-9]/', '', $dispensasi->siswa->no_telepon);
                            $waSiswaPhone = str_starts_with($rawSiswaPhone, '0') ? '62' . substr($rawSiswaPhone, 1) : $rawSiswaPhone;
                        @endphp
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5 flex items-center gap-1">
                                <i class="ti ti-brand-whatsapp text-emerald-600 text-xs"></i> Kontak WhatsApp
                            </p>
                            <a href="https://wa.me/{{ $waSiswaPhone }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 font-mono font-semibold text-emerald-700 hover:text-emerald-800 text-xs sm:text-sm hover:underline">
                                {{ $dispensasi->siswa->no_telepon }}
                                <i class="ti ti-external-link text-[11px] text-emerald-500"></i>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Card 2: Data Anda (Jika login sebagai Anggota Rombongan) --}}
                @if(auth()->user()->siswa && auth()->user()->siswa->id !== $dispensasi->siswa_id)
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center justify-between border-b border-slate-200 pb-3">
                        <span class="flex items-center gap-1.5"><i class="ti ti-user-check text-blue-600"></i>Data Anda (Anggota)</span>
                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">Anggota</span>
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5">Nama Lengkap</p>
                            <p class="font-bold text-slate-900 text-sm">{{ auth()->user()->siswa->nama_lengkap }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5">NIS / NISN</p>
                            <p class="font-mono font-semibold text-slate-800 text-xs sm:text-sm">{{ auth()->user()->siswa->user->nis_nip ?? auth()->user()->siswa->nis_nip ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5">Kelas</p>
                            <p class="font-semibold text-slate-800 text-xs sm:text-sm">{{ auth()->user()->siswa->kelas?->nama_kelas ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5">Jurusan</p>
                            <p class="text-slate-700 text-xs sm:text-sm">{{ auth()->user()->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                @endif
            @else
                {{-- Data Siswa Individu --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-1.5 border-b border-slate-100 pb-3">
                        <i class="ti ti-user-circle text-slate-400 text-base"></i>Data Siswa
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5">Nama Lengkap</p>
                            <p class="font-bold text-slate-900 text-sm">{{ $dispensasi->siswa->nama_lengkap }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5">NIS / NISN</p>
                            <p class="font-mono font-semibold text-slate-800 text-xs sm:text-sm">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5">Kelas</p>
                            <p class="font-semibold text-slate-800 text-xs sm:text-sm">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5">Jurusan</p>
                            <p class="text-slate-700 text-xs sm:text-sm">{{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                        </div>
                        @if(!empty($dispensasi->siswa->no_telepon))
                        @php
                            $rawSiswaPhone = preg_replace('/[^0-9]/', '', $dispensasi->siswa->no_telepon);
                            $waSiswaPhone = str_starts_with($rawSiswaPhone, '0') ? '62' . substr($rawSiswaPhone, 1) : $rawSiswaPhone;
                        @endphp
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5 flex items-center gap-1">
                                <i class="ti ti-brand-whatsapp text-emerald-600 text-xs"></i> Kontak WhatsApp
                            </p>
                            <a href="https://wa.me/{{ $waSiswaPhone }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 font-mono font-semibold text-emerald-700 hover:text-emerald-800 text-xs sm:text-sm hover:underline">
                                {{ $dispensasi->siswa->no_telepon }}
                                <i class="ti ti-external-link text-[11px] text-emerald-500"></i>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Guru Piket --}}
            @if($dispensasi->guru)
            <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-1.5 border-b border-slate-100 pb-3">
                    <i class="ti ti-user-circle text-blue-600 text-base"></i>Guru Piket Menyetujui
                </h3>
                <div class="space-y-3 text-xs">
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5">Nama Guru</p>
                        <p class="font-bold text-slate-900 text-sm">{{ $dispensasi->guru->nama_lengkap }}</p>
                        @if($dispensasi->guru->nip)
                            <p class="text-[11px] text-slate-500 font-mono mt-0.5">NIP: {{ $dispensasi->guru->nip }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 uppercase mb-0.5">Waktu Persetujuan</p>
                        <p class="font-semibold text-slate-800 text-xs">{{ $dispensasi->updated_at->isoFormat('dddd, D MMMM Y, HH:mm') }} WIB</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- TIMELINE STATUS --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-1.5 border-b border-slate-100 pb-3">
                    <i class="ti ti-history text-slate-400 text-base"></i>Alur & Riwayat Status
                </h3>
                <div class="space-y-4">
                    {{-- 1. Diajukan --}}
                    <div class="flex items-start gap-3">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-600 mt-1 flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-900">Pengajuan Dibuat</p>
                            <p class="text-[11px] text-slate-500">{{ $dispensasi->created_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                        </div>
                    </div>

                    {{-- 2. Disetujui / Ditolak --}}
                    @if($dispensasi->status === 'ditolak')
                        <div class="flex items-start gap-3">
                            <div class="w-2.5 h-2.5 rounded-full bg-rose-500 mt-1 flex-shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-rose-600">Ditolak oleh Guru Piket</p>
                                <p class="text-[11px] text-slate-500">{{ $dispensasi->updated_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                            </div>
                        </div>
                    @elseif($dispensasi->guru)
                        <div class="flex items-start gap-3">
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1 flex-shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-900">Disetujui oleh Guru Piket</p>
                                <p class="text-[11px] text-slate-500">{{ $dispensasi->updated_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                                <p class="text-[11px] text-slate-600 mt-0.5">Oleh: {{ $dispensasi->guru->nama_lengkap }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- 3. Menunggu Scan Keluar --}}
                    @if($dispensasi->status === 'disetujui')
                        <div class="flex items-start gap-3">
                            <div class="w-2.5 h-2.5 rounded-full bg-amber-500 mt-1 flex-shrink-0 animate-pulse"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-amber-600">Menunggu Konfirmasi Satpam (Keluar)</p>
                                <p class="text-[11px] text-slate-500">Silakan tunjukkan QR Code kepada petugas satpam di pos.</p>
                            </div>
                        </div>
                    @endif

                    {{-- 4. Konfirmasi Keluar --}}
                    @if($dispensasi->waktu_keluar_aktual)
                        <div class="flex items-start gap-3">
                            <div class="w-2.5 h-2.5 rounded-full bg-sky-500 mt-1 flex-shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-900">Dikonfirmasi Keluar oleh Satpam</p>
                                <p class="text-[11px] text-slate-500">{{ \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                            </div>
                        </div>
                    @endif

                    {{-- 5. Menunggu Scan Kembali / Info Sampai Pulang --}}
                    @if($dispensasi->status === 'keluar')
                        @if($dispensasi->isSampaiPulang())
                            <div class="flex items-start gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1 flex-shrink-0"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-emerald-700">Dispensasi Sampai Jam Pulang</p>
                                    <p class="text-[11px] text-slate-500">Tidak perlu scan kembali ke sekolah. Otomatis selesai di akhir jadwal KBM.</p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-start gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-amber-500 mt-1 flex-shrink-0 animate-pulse"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-amber-600">Menunggu Konfirmasi Satpam (Kembali)</p>
                                    <p class="text-[11px] text-slate-500">Tunjukkan QR Code yang sama saat kembali ke sekolah.</p>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- 6. Konfirmasi Kembali --}}
                    @if($dispensasi->waktu_kembali_aktual)
                        <div class="flex items-start gap-3">
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1 flex-shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-900">Dikonfirmasi Kembali oleh Satpam</p>
                                <p class="text-[11px] text-slate-500">{{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                                @if($dispensasi->is_warned || $dispensasi->isReturnedLate())
                                    <p class="text-[11px] text-rose-600 font-bold mt-0.5 flex items-center gap-1"><i class="ti ti-alert-circle text-xs"></i>Tercatat Terlambat {{ $dispensasi->getLateDurationText(true) ? '(' . $dispensasi->getLateDurationText(true) . ')' : '' }}</p>
                                @elseif($dispensasi->isReturnedEarly())
                                    <p class="text-[11px] text-emerald-600 font-bold mt-0.5 flex items-center gap-1"><i class="ti ti-clock-check text-xs"></i>Kembali Lebih Awal</p>
                                @else
                                    <p class="text-[11px] text-emerald-600 font-bold mt-0.5 flex items-center gap-1"><i class="ti ti-circle-check text-xs"></i>Tepat Waktu</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <a href="{{ route('siswa.pengajuan.index') }}" class="w-full inline-flex items-center justify-center min-h-[44px] px-4 py-3 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 active:bg-slate-100 transition-colors gap-2 shadow-xs">
                <i class="ti ti-arrow-left text-sm"></i>Kembali ke Riwayat
            </a>
        </div>
    </div>

</div>

@push('modals')
    {{-- Modal Preview Foto --}}
    <div id="photoModal"
         class="hidden fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6 bg-slate-950/85 backdrop-blur-md"
         onclick="closePhotoModal(event)"
         role="dialog"
         aria-modal="true"
         aria-labelledby="photoModalTitle">
        
        <div class="relative flex max-h-[92dvh] w-full max-w-xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl border border-slate-700/30"
             onclick="event.stopPropagation()">
            
            {{-- Header Modal: Selalu terlihat & sticky di atas --}}
            <div class="w-full bg-white px-4 py-3 sm:px-5 sm:py-3.5 flex items-center justify-between border-b border-slate-200 shrink-0">
                <h3 id="photoModalTitle" class="text-sm font-bold text-slate-900 flex items-center gap-2 truncate pr-2">
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="ti ti-photo text-base"></i>
                    </span>
                    <span class="truncate">Preview Foto</span>
                </h3>
                <button type="button"
                        onclick="closePhotoModal()"
                        aria-label="Tutup Preview Foto"
                        class="w-9 h-9 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 active:bg-slate-200 flex items-center justify-center transition-colors shrink-0">
                    <i class="ti ti-x text-lg"></i>
                </button>
            </div>

            {{-- Body Modal: Area Foto dengan latar gelap kontras tinggi --}}
            <div class="relative p-2 sm:p-4 bg-slate-950 flex items-center justify-center overflow-auto flex-1 min-h-[200px] max-h-[calc(92dvh-115px)]">
                <img id="photoModalImage"
                     src=""
                     alt="Preview Foto"
                     class="max-w-full max-h-[70dvh] w-auto h-auto object-contain rounded-xl shadow-lg">
            </div>

            {{-- Footer Modal: Tombol Tutup & Panduan --}}
            <div class="px-4 py-2.5 sm:px-5 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0">
                <span class="text-[11px] text-slate-500 font-medium truncate pr-2 flex items-center gap-1">
                    <i class="ti ti-info-circle text-xs text-slate-400"></i> Ketuk luar untuk menutup
                </span>
                <button type="button"
                        onclick="closePhotoModal()"
                        class="px-4 py-2 min-h-[36px] rounded-xl bg-white border border-slate-300 hover:bg-slate-100 active:bg-slate-200 text-slate-700 text-xs font-bold transition-colors shadow-2xs">
                    Tutup
                </button>
            </div>
        </div>
    </div>
@endpush

@if($popupEligible)
@push('modals')
{{-- ============================================================ --}}
{{-- MODAL: HUBUNGI GURU PIKET --}}
{{-- ============================================================ --}}
<div id="piketModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
        {{-- Header --}}
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center flex-shrink-0 text-emerald-600">
                    <i class="ti ti-brand-whatsapp text-2xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-slate-900">
                        Hubungi Guru Piket
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Pilih guru piket yang sedang bertugas untuk dihubungi
                    </p>
                </div>
            </div>

            <button type="button"
                    onclick="closePiketPopup()"
                    class="w-9 h-9 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors flex-shrink-0">
                <i class="ti ti-x text-base"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-4 sm:p-5 space-y-4 max-h-[75vh] overflow-y-auto">
            {{-- Daftar Guru Piket Sesi Ini --}}
            @if(!empty($daftarGuruPiket) && $daftarGuruPiket->isNotEmpty())
                <div class="space-y-2.5">
                    @foreach($daftarGuruPiket as $item)
                        @php
                            $guru = $item['guru'];
                            $status = $item['status'] ?? 'Sedang Bertugas';
                            $hasPhone = !empty($guru->no_telepon);
                            $badgeClass = match($status) {
                                'Sedang Bertugas' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'Sedang Keluar' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'Sudah Kembali' => 'bg-blue-50 text-blue-700 border-blue-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <div class="p-3 sm:p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-slate-300 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 border border-blue-100 flex items-center justify-center text-sm flex-shrink-0 font-bold">
                                    <i class="ti ti-user-circle text-xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <p class="text-xs sm:text-sm font-bold text-slate-900 truncate">{{ $guru->nama_lengkap }}</p>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $badgeClass }}">
                                            {{ $status }}
                                        </span>
                                    </div>
                                    @if($hasPhone)
                                        <p class="text-[11px] text-slate-500 font-mono mt-0.5 flex items-center gap-1">
                                            <i class="ti ti-brand-whatsapp text-emerald-600 text-xs"></i>{{ $guru->no_telepon }}
                                        </p>
                                    @else
                                        <p class="text-[11px] text-slate-400 italic mt-0.5">Nomor WA belum diatur</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-stretch sm:self-center flex-shrink-0">
                                @if($hasPhone)
                                    <a href="{{ route('siswa.pengajuan.hubungi-guru-piket', ['dispensasi' => $dispensasi, 'guru_id' => $guru->id]) }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       onclick="onWaClick()"
                                       class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 min-h-[44px] px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold shadow-xs active:scale-95 transition-all"
                                       title="Hubungi {{ $guru->nama_lengkap }} via WhatsApp">
                                        <i class="ti ti-brand-whatsapp text-sm"></i>
                                        <span>Hubungi WA</span>
                                    </a>
                                @else
                                    <button type="button" disabled class="w-full sm:w-auto min-h-[44px] px-3.5 py-2 rounded-xl bg-slate-200 text-slate-400 text-xs font-bold cursor-not-allowed">
                                        WA Belum Ada
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif(!empty($hubungiGuru))
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 border border-blue-100 flex items-center justify-center text-sm flex-shrink-0 font-bold">
                            <i class="ti ti-user-circle text-xl"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs sm:text-sm font-bold text-slate-900 truncate">{{ $hubungiGuru->nama_lengkap }}</p>
                            @if(!empty($hubungiGuru->no_telepon))
                                <p class="text-[11px] text-slate-500 font-mono mt-0.5 flex items-center gap-1">
                                    <i class="ti ti-brand-whatsapp text-emerald-600 text-xs"></i>{{ $hubungiGuru->no_telepon }}
                                </p>
                            @else
                                <p class="text-[11px] text-slate-400 italic mt-0.5">Nomor WA belum diatur</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 self-stretch sm:self-center flex-shrink-0">
                        @if(!empty($hubungiGuru->no_telepon))
                            <a href="{{ route('siswa.pengajuan.hubungi-guru-piket', ['dispensasi' => $dispensasi, 'guru_id' => $hubungiGuru->id]) }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               onclick="onWaClick()"
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 min-h-[44px] px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold shadow-xs active:scale-95 transition-all">
                                <i class="ti ti-brand-whatsapp text-sm"></i>
                                <span>Hubungi WA</span>
                            </a>
                        @else
                            <button type="button" disabled class="w-full sm:w-auto min-h-[44px] px-3.5 py-2 rounded-xl bg-slate-200 text-slate-400 text-xs font-bold cursor-not-allowed">
                                WA Belum Ada
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Information --}}
            <div class="rounded-xl border border-blue-100 bg-blue-50/70 p-3.5">
                <div class="flex items-start gap-2.5">
                    <i class="ti ti-info-circle text-blue-600 mt-0.5 text-base"></i>
                    <div class="text-xs text-blue-900 leading-relaxed">
                        <p class="font-bold">Pesan WhatsApp sudah disiapkan oleh sistem.</p>
                        <p class="mt-0.5 text-blue-700">Silakan klik tombol WhatsApp pada guru piket yang ingin Anda hubungi.</p>
                    </div>
                </div>
            </div>

            {{-- Error --}}
            @if($hubungiError)
            <div class="flex items-start gap-2.5 bg-amber-50 border border-amber-200 rounded-xl p-3.5">
                <i class="ti ti-alert-triangle text-amber-600 mt-0.5 text-base"></i>
                <p class="text-xs text-amber-900 leading-relaxed">{{ $hubungiError }}</p>
            </div>
            @endif

            {{-- Countdown --}}
            <div class="flex items-center justify-between px-3.5 py-3 rounded-xl bg-slate-50 border border-slate-200">
                <div class="flex items-center gap-2">
                    <i class="ti ti-clock text-slate-400 text-sm"></i>
                    <span class="text-xs text-slate-500 font-medium">Waktu tersedia</span>
                </div>
                <span id="modalCountdownDisplay" class="font-mono text-xs sm:text-sm font-bold text-slate-900">
                    --:--
                </span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end">
            <button type="button"
                    onclick="closePiketPopup()"
                    class="min-h-[40px] py-2 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 active:bg-slate-200 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>
@endpush
@endif

@push('scripts')
<script>
function handleAfterPrintSiswa() {
    const btn = document.getElementById('btnCetakSiswa');
    if(btn) {
        btn.innerHTML = '<i class="ti ti-loader-2 animate-spin mr-2"></i> Mencetak...';
        btn.classList.add('opacity-75', 'cursor-not-allowed');
    }
    setTimeout(function() { location.reload(); }, 2500);
}

function openPhotoModal(imageUrl, title = 'Preview Foto') {
    const modal = document.getElementById('photoModal');
    const modalImg = document.getElementById('photoModalImage');
    const modalTitle = document.getElementById('photoModalTitle');

    if (modal && modalImg) {
        modalImg.src = imageUrl;
        if (modalTitle) {
            const spanText = modalTitle.querySelector('span:last-child') || modalTitle.querySelector('span');
            if (spanText) spanText.textContent = title;
        }
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Sembunyikan bottom navigation bar agar tidak menutupi modal preview foto
        const bottomNav = document.getElementById('siswaBottomNav');
        if (bottomNav) {
            bottomNav.classList.add('!hidden');
        }
    }
}

function closePhotoModal(e) {
    if (e && e.target && e.target.id !== 'photoModal' && !e.target.closest('button')) {
        return;
    }

    const modal = document.getElementById('photoModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        const modalImg = document.getElementById('photoModalImage');
        if (modalImg) modalImg.src = '';

        // Tampilkan kembali bottom navigation bar
        const bottomNav = document.getElementById('siswaBottomNav');
        if (bottomNav) {
            bottomNav.classList.remove('!hidden');
        }
    }
}

// Tutup preview foto dengan tombol Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const photoModal = document.getElementById('photoModal');
        if (photoModal && !photoModal.classList.contains('hidden')) {
            closePhotoModal();
        }
    }
});

// ============================================================
// HUBUNGI GURU PIKET — Countdown & Popup Logic
// ============================================================
@if($popupEligible)
let _piketSecondsLeft = Math.max(0, Math.floor({{ $popupSecondsLeft }}));
let _piketTimerInterval = null;

function _formatTime(seconds) {
    seconds = Math.max(0, Math.floor(seconds));
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return String(minutes).padStart(2, '0') + ':' + String(remainingSeconds).padStart(2, '0');
}

function _updatePiketCountdown() {
    const formatted = _formatTime(_piketSecondsLeft);
    const display = document.getElementById('countdownDisplay');
    const modalDisplay = document.getElementById('modalCountdownDisplay');

    if (display) {
        display.textContent = formatted;
    }
    if (modalDisplay) {
        modalDisplay.textContent = formatted;
    }
}

function _tickPiket() {
    _piketSecondsLeft--;

    if (_piketSecondsLeft <= 0) {
        _piketSecondsLeft = 0;
        clearInterval(_piketTimerInterval);
        _piketTimerInterval = null;

        const banner = document.getElementById('piketBanner');
        const modal = document.getElementById('piketModal');

        if (banner) {
            banner.remove();
        }
        if (modal) {
            modal.remove();
        }
        return;
    }

    _updatePiketCountdown();
}

function openPiketPopup() {
    if (_piketSecondsLeft <= 0) {
        return;
    }

    const modal = document.getElementById('piketModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        const bottomNav = document.getElementById('siswaBottomNav');
        if (bottomNav) bottomNav.classList.add('!hidden');
        _updatePiketCountdown();
    }
}

function closePiketPopup() {
    const modal = document.getElementById('piketModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        const bottomNav = document.getElementById('siswaBottomNav');
        if (bottomNav) bottomNav.classList.remove('!hidden');
    }
}

function onWaClick() {
    setTimeout(closePiketPopup, 300);
}

document.addEventListener('DOMContentLoaded', function () {
    _updatePiketCountdown();

    if (_piketSecondsLeft > 0) {
        _piketTimerInterval = setInterval(_tickPiket, 1000);
    }

    const modal = document.getElementById('piketModal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closePiketPopup();
            }
        });
    }
});
@endif

function confirmBatalKeluar(url) {
    Swal.fire({
        title: 'Batalkan Dispensasi?',
        text: 'Apakah Anda yakin tidak jadi keluar sekolah? Status dispensasi akan diubah menjadi Dibatalkan dan QR Code tidak lagi dapat digunakan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="ti ti-ban mr-1.5"></i> Ya, Batalkan Dispensasi',
        cancelButtonText: 'Tidak Jadi',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl shadow-xl border border-slate-200',
            confirmButton: 'rounded-xl text-xs font-bold px-4 py-2.5',
            cancelButton: 'rounded-xl text-xs font-semibold px-4 py-2.5'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection
