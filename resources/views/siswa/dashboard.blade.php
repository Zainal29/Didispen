@extends('siswa.layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard Siswa')
@section('content')

{{-- ============ MODAL & BANNER PERINGATAN KEAMANAN AKUN ============ --}}
@include('components.security-modal')

{{-- ============ HERO BANNER (CLEAN TABLER BLUE, RESPONSIVE) ============ --}}
<div class="relative overflow-hidden bg-blue-600 rounded-2xl p-4 sm:p-5 mb-4 shadow-xs text-white border border-blue-700">
    <div class="relative z-10">
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 text-[11px] font-semibold text-blue-100 uppercase tracking-wide mb-2 border border-white/10">
            <i class="ti ti-calendar text-blue-200"></i>
            {{ now()->isoFormat('dddd, D MMMM Y') }}
        </div>
        <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight leading-snug">
            Halo, {{ auth()->user()->name }}!
        </h2>
        <p class="text-xs sm:text-sm text-blue-100/90 mt-1 max-w-xl leading-relaxed">
            Sistem Digital Dispensasi SMKN 1 Bangsri. Ajukan dan pantau izin dispensasi Anda secara real-time.
        </p>
        <div class="mt-3.5 flex flex-wrap items-center gap-2">
            <a href="{{ route('siswa.notifikasi.index') }}"
               class="inline-flex items-center px-3 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 text-white text-xs font-semibold border border-white/20 transition-colors">
                <i class="ti ti-bell mr-1.5 text-amber-300"></i>{{ $notifikasiBelumDibaca ?? 0 }} Notifikasi Baru
            </a>
            <a href="{{ route('siswa.pengajuan.create') }}"
               class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-white text-blue-700 hover:bg-blue-50 text-xs font-bold shadow-xs active:scale-95 transition-all">
                <i class="ti ti-plus mr-1.5"></i>Buat Pengajuan
            </a>
        </div>
    </div>
</div>

{{-- ============ BANNER HUBUNGI GURU PIKET & PERINGATAN KADALUARSA ============ --}}
@if(isset($dispensasiMenunggu))
<div id="piketDashboardBanner" class="bg-amber-50/80 border border-amber-200 rounded-2xl p-4 sm:p-5 mb-4 shadow-xs transition-all">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-white border border-amber-200 text-amber-600 flex items-center justify-center flex-shrink-0 text-base shadow-xs">
                <i class="ti ti-alert-triangle text-xl"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">
                        Pengajuan Menunggu Persetujuan Guru Piket
                    </h3>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold border border-amber-200">
                        <i class="ti ti-hourglass-low text-[11px]"></i> Kadaluarsa: {{ $dispensasiMenunggu->getBatasWaktuExpiredText() }}
                    </span>
                    @if(!empty($piketEligible))
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold border border-emerald-200">
                        <i class="ti ti-clock text-[11px]"></i>
                        <span id="dashboardCountdownDisplay" class="font-mono">
                            @php
                                $mins = intdiv($popupSecondsLeft ?? 0, 60);
                                $secs = ($popupSecondsLeft ?? 0) % 60;
                            @endphp
                            {{ sprintf('%02d:%02d', $mins, $secs) }}
                        </span>
                    </div>
                    @endif
                </div>
                <p class="text-xs text-amber-900 leading-relaxed">
                    Pengajuan <span class="font-mono font-semibold text-slate-800">#{{ $dispensasiMenunggu->nomor_surat }}</span> Anda <strong>akan segera kadaluarsa</strong> jika tidak disetujui hingga {{ $dispensasiMenunggu->sampai_pulang ? 'jam KBM usai' : 'batas izin berakhir' }} (pukul <strong>{{ $dispensasiMenunggu->getBatasWaktuExpiredText() }}</strong>). Segera temui Guru Piket di ruang piket untuk menyelesaikan prosesnya.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-stretch sm:self-center flex-shrink-0">
            @if(!empty($piketEligible))
            <button type="button"
                    onclick="openPiketDashboardModal()"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all">
                <i class="ti ti-brand-whatsapp text-sm"></i>
                <span>Hubungi Guru Piket Via WhatsApp</span>
            </button>
            @endif
            <a href="{{ route('siswa.pengajuan.show', $dispensasiMenunggu) }}"
               class="inline-flex items-center justify-center px-3.5 py-2.5 rounded-xl bg-white hover:bg-amber-50 text-amber-900 border border-amber-300 text-xs font-bold transition-all shadow-xs gap-1.5"
               title="Lihat Detail Pengajuan">
                <span>Lihat Detail</span>
                <i class="ti ti-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- MODAL HUBUNGI GURU PIKET DI DASHBOARD --}}
<div id="piketDashboardModal"
     class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 flex items-center justify-center p-4">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-150">
        {{-- Header Modal --}}
        <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">
                    <i class="ti ti-brand-whatsapp text-base"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Hubungi Guru Piket Via WhatsApp</h3>
                    <p class="text-[11px] text-slate-500">Daftar Guru Piket yang sedang bertugas pada sesi ini</p>
                </div>
            </div>
            <button type="button" onclick="closePiketDashboardModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors">
                <i class="ti ti-x text-sm"></i>
            </button>
        </div>

        {{-- Body Modal --}}
        <div class="p-5 space-y-4">
            @if(!empty($daftarGuruPiket) && $daftarGuruPiket->isNotEmpty())
                <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                    @foreach($daftarGuruPiket as $item)
                        @php
                            $guru = $item['guru'];
                            $status = $item['status'] ?? 'Sedang Bertugas';
                            $hasPhone = !empty($guru->no_telepon);
                            $badgeClass = match($status) {
                                'Sedang Bertugas' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'Sedang Keluar' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'Sudah Kembali' => 'bg-blue-100 text-blue-800 border-blue-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-slate-300 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 text-blue-600 flex items-center justify-center text-sm flex-shrink-0 font-bold">
                                    <i class="ti ti-user text-base"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <p class="text-xs sm:text-sm font-bold text-slate-900 truncate">{{ $guru->nama_lengkap }}</p>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $badgeClass }}">
                                            {{ $status }}
                                        </span>
                                    </div>
                                    @if($hasPhone)
                                        <p class="text-[11px] text-slate-500 font-mono mt-0.5">
                                            <i class="ti ti-brand-whatsapp text-emerald-600 mr-1"></i>{{ $guru->no_telepon }}
                                        </p>
                                    @else
                                        <p class="text-[11px] text-slate-400 italic mt-0.5">Nomor WA belum diatur</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0">
                                @if($hasPhone)
                                    <a href="{{ route('siswa.pengajuan.hubungi-guru-piket', ['dispensasi' => $dispensasiMenunggu, 'guru_id' => $guru->id]) }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all"
                                       title="Hubungi {{ $guru->nama_lengkap }} via WhatsApp">
                                        <i class="ti ti-brand-whatsapp text-sm"></i>
                                        <span>Hubungi WA</span>
                                    </a>
                                @else
                                    <button type="button" disabled class="px-3 py-2 rounded-xl bg-slate-200 text-slate-400 text-xs font-semibold cursor-not-allowed">
                                        WA Belum Ada
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif(!empty($hubungiGuru))
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-200 text-blue-600 flex items-center justify-center text-base flex-shrink-0 font-bold">
                            <i class="ti ti-user text-base"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Petugas Guru Piket</p>
                            <p class="text-sm font-bold text-slate-900 truncate">{{ $hubungiGuru->nama_lengkap }}</p>
                            @if($hubungiGuru->no_telepon)
                                <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $hubungiGuru->no_telepon }}</p>
                            @endif
                        </div>
                    </div>
                    @if($hubungiGuru->no_telepon)
                        <a href="{{ route('siswa.pengajuan.hubungi-guru-piket', ['dispensasi' => $dispensasiMenunggu, 'guru_id' => $hubungiGuru->id]) }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-all">
                            <i class="ti ti-brand-whatsapp text-sm"></i>
                            <span>Hubungi WA</span>
                        </a>
                    @endif
                </div>
            @endif

            <div class="p-3.5 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-2.5">
                <i class="ti ti-info-circle text-blue-600 mt-0.5 text-base flex-shrink-0"></i>
                <div class="text-xs text-blue-900 leading-relaxed">
                    <p class="font-semibold">Format pesan WhatsApp sudah disiapkan sistem.</p>
                    <p class="mt-0.5 text-blue-700">Silakan kirim pesan tersebut kepada salah satu Guru Piket di atas untuk mempercepat verifikasi pengajuan dispensasi Anda.</p>
                </div>
            </div>

            @if(!empty($hubungiError))
            <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2.5">
                <i class="ti ti-alert-circle text-amber-600 mt-0.5 text-base flex-shrink-0"></i>
                <p class="text-xs text-amber-800 leading-relaxed">{{ $hubungiError }}</p>
            </div>
            @endif

            <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                <span class="text-slate-500 font-medium flex items-center gap-1"><i class="ti ti-hourglass-low text-amber-500"></i>Waktu Hubungi Tersisa:</span>
                <span id="modalDashboardCountdownDisplay" class="font-mono font-bold text-amber-700">--:--</span>
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="px-5 py-4 bg-slate-50 border-t border-slate-200 flex justify-end">
            <button type="button" onclick="closePiketDashboardModal()" class="w-full sm:w-auto py-2 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>
@endif

{{-- Banner Terlambat (Hanya muncul jika terlambat) --}}
@if($isTerlambat)
<div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 sm:p-5 mb-4 shadow-xs">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0">
            <i class="ti ti-alert-triangle text-xl"></i>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-rose-900 font-bold text-sm mb-1">Peringatan: Anda Terlambat!</h3>
            <p class="text-rose-700 text-xs sm:text-sm mb-2 leading-relaxed">
                Anda telah melewati batas waktu kembali dispensasi
                <strong class="text-rose-900">({{ $dispensasiAktif->nomor_surat }})</strong>
                selama
                <span class="bg-rose-100 text-rose-800 px-2 py-0.5 rounded-md font-semibold">
                    @if($terlambatJam > 0)
                        {{ $terlambatJam }} jam {{ $terlambatMenit }} menit
                    @else
                        {{ $terlambatMenit }} menit
                    @endif
                </span>
            </p>
            <p class="text-xs text-rose-600 mb-3 flex items-center gap-1">
                <i class="ti ti-clock"></i>
                Batas kembali: <strong>{{ \Carbon\Carbon::parse($dispensasiAktif->batas_waktu_kembali)->format('H:i') }} WIB</strong>
            </p>
            <a href="{{ route('siswa.pengajuan.show', $dispensasiAktif) }}"
               class="inline-flex items-center px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <i class="ti ti-qrcode mr-1.5"></i>Lihat QR Code
            </a>
        </div>
    </div>
</div>
@endif

{{-- ============ STATUS DISPENSASI AKTIF ============ --}}
@if(isset($dispensasiAktif))
    {{-- KONDISI 1: DISETUJUI --}}
    @if($dispensasiAktif->status === 'disetujui')
    <div class="bg-white border border-slate-200 rounded-2xl p-4 mb-4 shadow-xs">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Aktif
                </span>
                <h3 class="text-sm font-bold text-slate-900 flex items-center">
                    <i class="ti ti-circle-check text-emerald-600 mr-1.5 text-base"></i>Dispensasi Disetujui
                </h3>
                @if($dispensasiAktif->isOrganisasi())
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        <i class="ti ti-users mr-1"></i>{{ $dispensasiAktif->nama_organisasi }}
                    </span>
                    @if(auth()->user()->siswa && auth()->user()->siswa->id !== $dispensasiAktif->siswa_id)
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            Anggota Rombongan
                        </span>
                    @endif
                @else
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                        <i class="ti ti-user mr-1"></i>Individu
                    </span>
                @endif
                @if($dispensasiAktif->tanggal_izin && $dispensasiAktif->tanggal_izin->isFuture())
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        <i class="ti ti-calendar-event mr-1"></i>Jadwal {{ $dispensasiAktif->tanggal_izin->translatedFormat('d M Y') }}
                    </span>
                @endif
            </div>
        </div>
        <div class="space-y-2 text-xs">
            <div class="bg-slate-50 border border-slate-200/80 rounded-xl px-3.5 py-2.5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">No. Surat</p>
                <p class="font-mono font-semibold text-slate-900 truncate">{{ $dispensasiAktif->nomor_surat }}</p>
            </div>
            <div class="bg-slate-50 border border-slate-200/80 rounded-xl px-3.5 py-2.5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Waktu</p>
                <p class="font-semibold text-slate-900">
                    {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasiAktif->jam_keluar, $dispensasiAktif->created_at?->dayOfWeek) }} – 
                    @if($dispensasiAktif->isSampaiPulang())
                        <span class="text-emerald-700 font-bold">Sampai Pulang Sekolah</span>
                    @else
                        {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasiAktif->jam_kembali, $dispensasiAktif->created_at?->dayOfWeek) }}
                    @endif
                </p>
            </div>
        </div>
        <div class="mt-3.5 flex gap-2">
            <a href="{{ route('siswa.pengajuan.show', $dispensasiAktif) }}"
               class="flex-1 inline-flex items-center justify-center px-3.5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <i class="ti ti-qrcode mr-1.5 text-sm"></i>Lihat QR Code
            </a>
            @php
                $maxLimitAktif = \App\Helpers\PrintHelper::maxStudentLimit();
                $isWithinPrintTime = \App\Helpers\PrintHelper::isWithinOperatingHours();
            @endphp
            @if(($dispensasiAktif->student_print_count ?? 0) < $maxLimitAktif && $isWithinPrintTime)
            <a href="{{ route('siswa.cetak', $dispensasiAktif) }}"
               target="_blank"
               class="flex-1 inline-flex items-center justify-center px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <i class="ti ti-printer mr-1.5 text-sm"></i>Cetak
            </a>
            @endif
        </div>

        @if(empty($dispensasiAktif->waktu_keluar_aktual) && (!auth()->user()->siswa || auth()->user()->siswa->id === $dispensasiAktif->siswa_id))
        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
            <span class="text-[11px] text-slate-500">Tidak jadi izin keluar?</span>
            <button type="button" onclick="confirmBatalKeluar('{{ route('siswa.pengajuan.batal', $dispensasiAktif) }}')"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 px-2.5 py-1.5 rounded-lg transition-colors">
                <i class="ti ti-ban text-[11px]"></i> Batalkan Dispensasi
            </button>
        </div>
        @endif
    </div>
    @endif

    {{-- KONDISI 2: SUDAH KELUAR --}}
    @if($dispensasiAktif->status === 'keluar')
    <div class="bg-blue-50/70 border border-blue-200 rounded-2xl p-4 sm:p-5 mb-4 shadow-xs">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center flex-shrink-0">
                <i class="ti ti-walk text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="font-bold text-slate-900 text-sm">Anda Sedang di Luar Sekolah</h3>
                    @if($dispensasiAktif->isOrganisasi())
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            <i class="ti ti-users mr-1"></i>{{ $dispensasiAktif->nama_organisasi }}
                        </span>
                    @endif
                </div>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">
                    Keluar pukul: <strong class="text-slate-900">{{ $dispensasiAktif->waktu_keluar_aktual ? \Carbon\Carbon::parse($dispensasiAktif->waktu_keluar_aktual)->format('H:i') : '-' }} WIB</strong>
                </p>
                @if($dispensasiAktif->isSampaiPulang())
                    <p class="text-xs text-emerald-700 font-semibold mt-1 flex items-center gap-1">
                        <i class="ti ti-circle-check text-emerald-600 text-sm"></i>
                        Izin sampai jam pulang sekolah (Tidak perlu scan kembali & bebas peringatan terlambat).
                    </p>
                @else
                    <p class="text-xs text-slate-600 mt-1 flex items-center gap-1">
                        <i class="ti ti-info-circle text-blue-500"></i>Harap kembali sebelum pukul {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasiAktif->jam_kembali) }}
                    </p>
                @endif
            </div>
        </div>

        {{-- BAGIAN FOTO BUKTI (DENGAN KAMERA LANGSUNG) --}}
        <div class="mt-4 pt-4 border-t border-blue-200/80">
            <p class="text-xs font-bold text-slate-800 mb-2 flex items-center gap-1.5">
                <i class="ti ti-camera text-blue-600 text-sm"></i> Foto Bukti Kegiatan
            </p>

            @if($dispensasiAktif->foto_bukti)
                {{-- FOTO SUDAH DIUPLOAD --}}
                <div class="flex items-center justify-between bg-white rounded-xl p-3 border border-emerald-200 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('storage/' . $dispensasiAktif->foto_bukti) }}"
                             class="w-12 h-12 object-cover rounded-xl border border-slate-200 cursor-pointer shadow-xs"
                             onclick="showPreview('{{ asset('storage/' . $dispensasiAktif->foto_bukti) }}', '{{ $dispensasiAktif->nomor_surat }}')">
                        <div>
                            <p class="text-xs font-bold text-emerald-700 flex items-center gap-1">
                                <i class="ti ti-circle-check text-emerald-600"></i> Foto Bukti Terkirim
                            </p>
                            <p class="text-[10px] text-slate-500">
                                Diupload: {{ $dispensasiAktif->foto_bukti_uploaded_at ? \Carbon\Carbon::parse($dispensasiAktif->foto_bukti_uploaded_at)->format('H:i') : '-' }} WIB
                            </p>
                        </div>
                    </div>
                    <button onclick="hapusFoto({{ $dispensasiAktif->id }})"
                            class="text-rose-500 hover:text-rose-700 p-2 rounded-lg hover:bg-rose-50 transition-colors"
                            title="Hapus Foto">
                        <i class="ti ti-trash text-sm"></i>
                    </button>
                </div>
            @else
                {{-- FOTO BELUM DIUPLOAD --}}
                <div class="bg-white rounded-xl p-3.5 border {{ $isTerlambat ? 'border-rose-300 bg-rose-50/50' : 'border-amber-200 bg-amber-50/50' }} shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                    <div>
                        <p class="text-xs font-bold {{ $isTerlambat ? 'text-rose-700' : 'text-amber-800' }} flex items-center gap-1.5">
                            <i class="ti ti-alert-circle text-sm"></i>
                            {{ $isTerlambat ? 'Wajib Upload Foto Bukti!' : 'Belum ada foto bukti' }}
                        </p>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Ambil foto langsung menggunakan kamera sebagai bukti kegiatan
                        </p>
                    </div>
                    <button onclick="openUploadModal({{ $dispensasiAktif->id }})"
                            class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors inline-flex items-center justify-center gap-1.5 self-start sm:self-auto">
                        <i class="ti ti-camera text-sm"></i> Ambil Foto
                    </button>
                </div>
            @endif

            {{-- Aksi / QR Code Berdasarkan Tipe Kepulangan --}}
            @if($dispensasiAktif->isSampaiPulang())
                <div class="mt-3 p-3 rounded-xl bg-white border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shrink-0">
                            <i class="ti ti-door-exit text-base"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-emerald-950">Dispensasi Sampai Pulang Sekolah</p>
                            <p class="text-[11px] text-slate-500">Tidak perlu scan kembali ke Satpam. Otomatis selesai di akhir jadwal KBM.</p>
                        </div>
                    </div>
                    <a href="{{ route('siswa.pengajuan.show', $dispensasiAktif) }}"
                       class="inline-flex items-center justify-center px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors shrink-0">
                        <i class="ti ti-file-text mr-1.5 text-sm"></i>Detail Surat
                    </a>
                </div>
            @else
                <div class="mt-3">
                    <a href="{{ route('siswa.pengajuan.show', $dispensasiAktif) }}"
                       class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                        <i class="ti ti-qrcode mr-1.5 text-sm"></i>Lihat QR Code (Untuk Scan Kembali)
                    </a>
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- KONDISI 3: SUDAH SELESAI --}}
    @if($dispensasiAktif->status === 'selesai')
    <div class="bg-emerald-50/80 border border-emerald-200 rounded-2xl p-4 sm:p-5 mb-4 shadow-xs">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                <i class="ti ti-circle-check text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-bold text-slate-900 text-sm">Dispensasi Selesai</h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-1 leading-relaxed">
                    Anda telah kembali ke sekolah pukul {{ $dispensasiAktif->waktu_kembali_aktual ? \Carbon\Carbon::parse($dispensasiAktif->waktu_kembali_aktual)->format('H:i') : '-' }} WIB.
                </p>
            </div>
        </div>
    </div>
    @endif
@endif

{{-- ============ GURU PIKET HARI INI ============ --}}
<div class="bg-white border border-slate-200 rounded-2xl p-4 mb-4 shadow-xs">
    <div class="border-b border-slate-100 pb-3 mb-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
            <div class="flex items-start gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 text-sm mt-0.5 sm:mt-0">
                    <i class="ti ti-user-shield text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <h3 class="text-sm font-bold text-slate-900 whitespace-nowrap">Guru Piket Hari Ini</h3>
                        @if(!empty($infoPiket['jadwal']))
                            @if(!empty($infoPiket['jadwal']->nama_sesi))
                                <span class="text-[10px] sm:text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100 whitespace-nowrap">
                                    {{ $infoPiket['jadwal']->nama_sesi }}
                                </span>
                            @endif
                            <span class="text-[11px] sm:text-xs font-mono font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md whitespace-nowrap">
                                <i class="ti ti-clock text-[11px] text-slate-400 mr-1"></i>{{ substr($infoPiket['jadwal']->jam_mulai, 0, 5) }} – {{ substr($infoPiket['jadwal']->jam_selesai, 0, 5) }} WIB
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Daftar Bapak/Ibu Guru yang bertugas piket pada sesi ini.</p>
                </div>
            </div>
            @if(!empty($infoPiket['jadwal']))
                <div class="flex items-center sm:self-center self-start pl-10 sm:pl-0">
                    @if($infoPiket['status_sesi'] === 'Berlangsung')
                        <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs whitespace-nowrap">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                            Sedang Berlangsung
                        </span>
                    @elseif($infoPiket['status_sesi'] === 'Akan Datang')
                        <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-800 border border-slate-200 shadow-2xs whitespace-nowrap">
                            <i class="ti ti-clock mr-1 text-[11px]"></i>
                            Akan Datang
                        </span>
                    @elseif($infoPiket['status_sesi'] === 'Sesi Selesai')
                        <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200 whitespace-nowrap">
                            Sesi Selesai
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- KONDISI 1: CONFLICT REPLACEMENT --}}
    @if(!empty($infoPiket['conflict']))
        <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-center gap-2.5">
            <i class="ti ti-info-circle text-base text-amber-600 flex-shrink-0"></i>
            <span class="font-medium">Data Guru Piket sedang diperiksa.</span>
        </div>

    {{-- KONDISI 2: TIDAK ADA JADWAL SESI --}}
    @elseif(empty($infoPiket['jadwal']))
        <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-500 flex items-center gap-2">
            <i class="ti ti-info-circle text-base text-slate-400 flex-shrink-0"></i>
            <span>{{ empty($adaJadwalHariIni) ? 'Belum ada jadwal Guru Piket untuk hari ini.' : 'Tidak ada sesi berikutnya hari ini.' }}</span>
        </div>

     {{-- KONDISI 3: ADA SESI & PETUGAS AKTUAL --}}
    @else
        @if(empty($infoPiket['petugas']))
            <p class="text-xs text-slate-500 italic">Belum ada petugas piket yang ditentukan untuk sesi ini.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                @foreach($infoPiket['petugas'] as $p)
                    @php
                        $statusClass = match($p['status']) {
                            'Sedang Bertugas' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                            'Sedang Keluar'   => 'bg-amber-50 text-amber-800 border-amber-200',
                            'Sudah Kembali'   => 'bg-blue-50 text-blue-800 border-blue-200',
                            'Akan Bertugas'   => 'bg-slate-100 text-slate-800 border-slate-200',
                            default           => 'bg-slate-50 text-slate-700 border-slate-200',
                        };
                        $dotColor = match($p['status']) {
                            'Sedang Bertugas' => 'bg-emerald-500',
                            'Sedang Keluar'   => 'bg-amber-500',
                            'Sudah Kembali'   => 'bg-blue-500',
                            'Akan Bertugas'   => 'bg-slate-500',
                            default           => 'bg-slate-400',
                        };
                        $initials = collect(explode(' ', $p['guru']->nama_lengkap ?? 'G'))
                            ->filter()
                            ->take(2)
                            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                            ->join('');
                    @endphp
                    <div class="p-3.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition-all flex flex-col justify-between gap-2.5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ $initials ?: 'GP' }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-xs sm:text-sm text-slate-900 truncate">{{ $p['guru']->nama_lengkap }}</p>
                                @if(!empty($p['is_pengganti']) && !empty($p['guru_resmi']))
                                    <p class="text-[11px] text-amber-700 font-medium mt-0.5 flex items-center gap-1">
                                        <i class="ti ti-arrows-exchange text-xs"></i>
                                        <span class="truncate">Menggantikan: {{ $p['guru_resmi']->nama_lengkap }}</span>
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-1.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10px] sm:text-[11px] font-bold border {{ $statusClass }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }} {{ $p['status'] === 'Sedang Bertugas' ? 'animate-pulse' : '' }}"></span>
                                {{ $p['status'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>

{{-- ============ STATISTIK UTAMA (MODERN, SOLID SURFACE, CLEAN SHADOW) ============ --}}
@php
$cards = [
    'menunggu'  => ['Menunggu',  $stats['menunggu'] ?? 0,  'ti-clock-pause', 'bg-amber-50 text-amber-600 border border-amber-200/80'],
    'disetujui' => ['Disetujui', $stats['disetujui'] ?? 0, 'ti-circle-check', 'bg-emerald-50 text-emerald-600 border border-emerald-200/80'],
    'ditolak'   => ['Ditolak',   $stats['ditolak'] ?? 0,   'ti-circle-x',     'bg-rose-50 text-rose-600 border border-rose-200/80'],
    'selesai'   => ['Selesai',   $stats['selesai'] ?? 0,   'ti-checks',       'bg-blue-50 text-blue-600 border border-blue-200/80'],
];
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 mb-3">
    @foreach($cards as $key => [$label, $value, $icon, $iconStyle])
    <a href="{{ route('siswa.pengajuan.index', ['status' => $key]) }}"
       class="stat-card-btn text-left rounded-2xl border border-slate-200 bg-white p-3.5 sm:p-4 min-h-[44px] shadow-xs hover:border-slate-300 active:scale-95 transition-all w-full block">
        <div class="flex items-center justify-between mb-2">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $iconStyle }}">
                <i class="ti {{ $icon }} text-lg"></i>
            </div>
            <span class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">{{ $value }}</span>
        </div>
        <p class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $label }}</p>
    </a>
    @endforeach
</div>

{{-- SECONDARY BUTTON: SEMUA PENGAJUAN --}}
<div class="mb-4">
    <a href="{{ route('siswa.pengajuan.index') }}"
       class="filter-btn w-full px-3 py-2.5 min-h-[44px] inline-flex items-center justify-center rounded-xl text-xs font-semibold border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition-all text-center shadow-xs">
        <i class="ti ti-layers-intersect mr-1.5 text-blue-600 text-sm"></i>Tampilkan Semua Pengajuan ({{ $stats['total'] ?? 0 }})
    </a>
</div>

{{-- ============ PENGAJUAN TERBARU ============ --}}
<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
    <div class="px-4 py-3 sm:px-5 sm:py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
        <h3 class="text-sm font-bold text-slate-900 flex items-center">
            <i class="ti ti-history mr-1.5 text-slate-400 text-base"></i>Pengajuan Terbaru
        </h3>
        <a href="{{ route('siswa.pengajuan.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1">
            Lihat Semua <i class="ti ti-arrow-right text-xs"></i>
        </a>
    </div>

    @if(isset($pengajuanTerbaru) && $pengajuanTerbaru->count() > 0)
        <div class="divide-y divide-slate-100">
            @foreach($pengajuanTerbaru as $pengajuan)
                @php
                    $badge = $pengajuan->status_badge;
                    $maxPrint = \App\Helpers\PrintHelper::maxStudentLimit();
                    $studentPrintCount = $pengajuan->student_print_count ?? 0;
                    $currentTime = \App\Helpers\PrintHelper::currentTime();
                    $startTime = \App\Helpers\PrintHelper::startTime();
                    $endTime = \App\Helpers\PrintHelper::endTime();
                    $isWithinTime = \App\Helpers\PrintHelper::isWithinOperatingHours($currentTime);
                    $canPrint = in_array($pengajuan->status, \App\Helpers\PrintHelper::PRINTABLE_STATUSES) &&
                                $studentPrintCount < $maxPrint && $isWithinTime;
                @endphp
                <div class="p-4 sm:p-5 hover:bg-slate-50/70 transition-colors">
                    <div class="flex justify-between items-start gap-3 mb-2">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <h4 class="font-mono font-bold text-slate-900 text-xs">{{ $pengajuan->nomor_surat }}</h4>
                                @if($pengajuan->isOrganisasi())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <i class="ti ti-users text-[10px]"></i> {{ $pengajuan->nama_organisasi }}
                                    </span>
                                    @if(auth()->user()->siswa && auth()->user()->siswa->id !== $pengajuan->siswa_id)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            Anggota Rombongan
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        <i class="ti ti-user text-[10px]"></i> Individu
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ $pengajuan->created_at->format('d M Y, H:i') }} •
                                <span class="capitalize">{{ str_replace('_', ' ', $pengajuan->kategori) }}</span>
                            </p>
                            <p class="text-xs text-slate-600 mt-1 truncate">
                                <span class="font-medium text-slate-700">Tujuan:</span> {{ $pengajuan->tujuan }}
                            </p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold flex-shrink-0 border {{ $badge['class'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                            {{ $badge['text'] }}
                        </span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('siswa.pengajuan.show', $pengajuan) }}"
                           class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                            <i class="ti ti-eye mr-1.5 text-sm"></i>Detail
                        </a>
                        @if(in_array($pengajuan->status, ['disetujui', 'keluar']))
                            @if($canPrint)
                                <a href="{{ route('siswa.cetak', $pengajuan) }}" target="_blank"
                                   class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition-colors">
                                    <i class="ti ti-printer mr-1.5 text-sm"></i>Cetak
                                </a>
                            @else
                                <button disabled
                                        class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-400 bg-slate-100 cursor-not-allowed"
                                        title="{{ $studentPrintCount >= $maxPrint ? 'Batas cetak tercapai (' . $maxPrint . ' kali)' : 'Di luar jam cetak (' . $startTime . ' - ' . $endTime . ' WIB)' }}">
                                    <i class="ti ti-lock mr-1.5 text-sm"></i>Cetak
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-8 sm:p-12 text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-3">
                <i class="ti ti-inbox text-3xl"></i>
            </div>
            <p class="text-slate-800 font-bold text-sm mb-1">Belum ada pengajuan dispensasi</p>
            <p class="text-xs text-slate-500 mb-4">Buat pengajuan pertama Anda untuk memulai.</p>
            <a href="{{ route('siswa.pengajuan.create') }}"
               class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                <i class="ti ti-plus mr-1.5 text-sm"></i>Buat Pengajuan Pertama
            </a>
        </div>
    @endif
</div>

{{-- MODAL AMBIL FOTO BUKTI LANGSUNG KAMERA --}}
<div id="uploadModal" class="hidden fixed inset-0 bg-slate-900/70 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden flex flex-col">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="ti ti-camera text-base"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Ambil Foto Bukti</h3>
                    <p class="text-[11px] text-slate-500">Kamera langsung aktif saat ini</p>
                </div>
            </div>
            <button type="button" onclick="closeUploadModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors">
                <i class="ti ti-x text-sm"></i>
            </button>
        </div>

        <form id="uploadForm" class="p-5 flex flex-col gap-4">
            @csrf
            <input type="hidden" id="dispensasiId">

            <div class="relative w-full rounded-2xl overflow-hidden bg-black aspect-[4/3] flex items-center justify-center border border-slate-300 shadow-inner">
                <video id="videoBukti" autoplay playsinline muted class="w-full h-full object-cover"></video>
                <img id="previewImgBukti" class="w-full h-full object-cover hidden" alt="Preview Foto Bukti">
                <canvas id="canvasBukti" class="hidden"></canvas>

                <div id="liveBadgeBukti" class="absolute top-3 left-3 bg-rose-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full flex items-center gap-1.5 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-white animate-ping"></span> LIVE KAMERA
                </div>

                <button type="button" id="btnFlipBukti" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/60 hover:bg-black/80 text-white flex items-center justify-center text-xs transition-colors shadow" title="Putar Kamera">
                    <i class="ti ti-camera-rotate text-sm"></i>
                </button>

                <div id="cameraLoadingBukti" class="absolute inset-0 bg-slate-900 flex flex-col items-center justify-center text-white hidden">
                    <i class="ti ti-loader-2 animate-spin text-2xl mb-2 text-emerald-400"></i>
                    <p class="text-xs font-semibold">Mengaktifkan kamera...</p>
                </div>

                <div id="cameraErrorBukti" class="absolute inset-0 bg-slate-950/95 p-5 flex flex-col items-center justify-center text-center text-white hidden">
                    <div class="w-12 h-12 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl mb-2">
                        <i class="ti ti-alert-triangle text-2xl"></i>
                    </div>
                    <p class="text-xs font-bold mb-1">Kamera Tidak Dapat Diakses</p>
                    <p class="text-[11px] text-slate-300 mb-3 max-w-xs leading-relaxed">Pastikan izin akses kamera telah diizinkan pada peramban Anda.</p>
                    <button type="button" onclick="retryBuktiCamera()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors inline-flex items-center gap-1">
                        <i class="ti ti-rotate text-sm"></i> Coba Lagi
                    </button>
                </div>
            </div>

            <div class="text-[11px] text-slate-500 bg-slate-50 border border-slate-200 rounded-xl p-2.5 flex items-center gap-2">
                <i class="ti ti-shield-check text-emerald-600 text-base flex-shrink-0"></i>
                <span>Foto diambil seketika dengan stempel waktu otomatis untuk mencegah penggunaan foto lama.</span>
            </div>

            <div id="controlsCameraBukti" class="flex gap-2">
                <button type="button" onclick="closeUploadModal()" class="w-1/3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold text-xs transition-colors">Batal</button>
                <button type="button" id="btnSnapBukti" class="w-2/3 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white rounded-xl font-bold text-xs shadow-xs flex items-center justify-center gap-2 transition-all">
                    <i class="ti ti-camera text-base"></i> Jepret Foto Bukti
                </button>
            </div>

            <div id="controlsPreviewBukti" class="hidden flex gap-2">
                <button type="button" id="btnRetakeBukti" class="w-1/2 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold text-xs transition-colors flex items-center justify-center gap-1.5">
                    <i class="ti ti-rotate text-sm"></i> Foto Ulang
                </button>
                <button type="submit" id="btnSubmitBukti" class="w-1/2 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white rounded-xl font-bold text-xs shadow-xs flex items-center justify-center gap-1.5 transition-colors">
                    <i class="ti ti-upload text-sm"></i> Upload Foto
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>

let buktiStream = null;
let currentFacingModeBukti = 'environment';
let capturedBuktiBlob = null;

const videoBukti = document.getElementById('videoBukti');
const previewImgBukti = document.getElementById('previewImgBukti');
const canvasBukti = document.getElementById('canvasBukti');
const liveBadgeBukti = document.getElementById('liveBadgeBukti');
const btnFlipBukti = document.getElementById('btnFlipBukti');
const cameraLoadingBukti = document.getElementById('cameraLoadingBukti');
const cameraErrorBukti = document.getElementById('cameraErrorBukti');
const controlsCameraBukti = document.getElementById('controlsCameraBukti');
const controlsPreviewBukti = document.getElementById('controlsPreviewBukti');
const btnSnapBukti = document.getElementById('btnSnapBukti');
const btnRetakeBukti = document.getElementById('btnRetakeBukti');
const btnSubmitBukti = document.getElementById('btnSubmitBukti');

function stopBuktiCamera() {
    if (buktiStream) { buktiStream.getTracks().forEach(track => track.stop()); buktiStream = null; }
}

async function startBuktiCamera(facingMode = 'environment') {
    stopBuktiCamera();
    cameraErrorBukti.classList.add('hidden');
    previewImgBukti.classList.add('hidden');
    videoBukti.classList.remove('hidden');
    liveBadgeBukti.classList.remove('hidden');
    controlsPreviewBukti.classList.add('hidden');
    controlsCameraBukti.classList.remove('hidden');
    cameraLoadingBukti.classList.remove('hidden');

    try {
        buktiStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: facingMode }, width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false });
        videoBukti.srcObject = buktiStream;
        await videoBukti.play();
        cameraLoadingBukti.classList.add('hidden');
    } catch (err) {
        try {
            buktiStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            videoBukti.srcObject = buktiStream;
            await videoBukti.play();
            cameraLoadingBukti.classList.add('hidden');
        } catch (fallbackErr) {
            cameraLoadingBukti.classList.add('hidden');
            cameraErrorBukti.classList.remove('hidden');
        }
    }
}

function retryBuktiCamera() { startBuktiCamera(currentFacingModeBukti); }

function openUploadModal(id) {
    document.getElementById('dispensasiId').value = id;
    capturedBuktiBlob = null;
    document.getElementById('uploadModal').classList.remove('hidden');
    startBuktiCamera(currentFacingModeBukti);
}

function closeUploadModal() {
    stopBuktiCamera();
    capturedBuktiBlob = null;
    document.getElementById('uploadModal').classList.add('hidden');
}

btnFlipBukti.addEventListener('click', function() {
    currentFacingModeBukti = (currentFacingModeBukti === 'environment') ? 'user' : 'environment';
    startBuktiCamera(currentFacingModeBukti);
});

btnSnapBukti.addEventListener('click', function() {
    if (!videoBukti.videoWidth) return;
    const width = videoBukti.videoWidth, height = videoBukti.videoHeight;
    canvasBukti.width = width; canvasBukti.height = height;
    const ctx = canvasBukti.getContext('2d');

    if (currentFacingModeBukti === 'user') {
        ctx.translate(width, 0); ctx.scale(-1, 1); ctx.drawImage(videoBukti, 0, 0, width, height); ctx.setTransform(1, 0, 0, 1, 0, 0);
    } else { ctx.drawImage(videoBukti, 0, 0, width, height); }

    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    const dateStr = `${pad(now.getDate())}/${pad(now.getMonth()+1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())} WIB`;
    const stampText = `DIGIPEN • BUKTI • ${dateStr}`;
    const fontSize = Math.max(13, Math.round(width / 42));
    ctx.font = `bold ${fontSize}px sans-serif`;
    const textWidth = ctx.measureText(stampText).width;
    const boxWidth = textWidth + 24, boxHeight = fontSize + 16;
    const posX = width - boxWidth - 14, posY = height - boxHeight - 14;

    ctx.fillStyle = 'rgba(0, 0, 0, 0.72)'; ctx.beginPath(); ctx.roundRect(posX, posY, boxWidth, boxHeight, 6); ctx.fill();
    ctx.fillStyle = '#10b981'; ctx.beginPath(); ctx.arc(posX + 12, posY + boxHeight / 2, 4, 0, Math.PI * 2); ctx.fill();
    ctx.fillStyle = '#ffffff'; ctx.fillText(stampText, posX + 22, posY + boxHeight / 2 + fontSize / 3);

    canvasBukti.toBlob(function(blob) {
        capturedBuktiBlob = blob;
        previewImgBukti.src = URL.createObjectURL(blob);
        previewImgBukti.classList.remove('hidden');
        videoBukti.classList.add('hidden');
        liveBadgeBukti.classList.add('hidden');
        controlsCameraBukti.classList.add('hidden');
        controlsPreviewBukti.classList.remove('hidden');
        stopBuktiCamera();
    }, 'image/jpeg', 0.85);
});

btnRetakeBukti.addEventListener('click', function() { capturedBuktiBlob = null; startBuktiCamera(currentFacingModeBukti); });

document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('dispensasiId').value;
    if (!capturedBuktiBlob) return;

    const formData = new FormData();
    formData.append('foto_bukti', capturedBuktiBlob, `bukti-${Date.now()}.jpg`);
    btnSubmitBukti.innerHTML = '<i class="ti ti-loader-2 animate-spin mr-1"></i> Mengupload...';
    btnSubmitBukti.disabled = true;

    try {
        const res = await fetch(`/siswa/pengajuan/${id}/upload-foto-bukti`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: formData
        });
        const data = await res.json();
        if (data.success) { closeUploadModal(); location.reload(); }
        else { alert('Gagal: ' + (data.message || 'Terjadi kesalahan')); }
    } catch (err) { alert('Error jaringan'); }
    finally { btnSubmitBukti.innerHTML = '<i class="ti ti-upload"></i> Upload Foto'; btnSubmitBukti.disabled = false; }
});

async function hapusFoto(id) {
    const result = await Swal.fire({
        title: 'Hapus Foto Bukti?',
        text: 'Foto bukti akan dihapus permanen. Anda harus upload ulang sebelum kembali.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-xl',
            confirmButton: 'rounded-l-lg',
            cancelButton: 'rounded-r-lg'
        }
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`/siswa/pengajuan/${id}/hapus-foto-bukti`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Foto bukti berhasil dihapus.',
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message || 'Terjadi kesalahan saat menghapus foto.', 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Gagal menghapus foto. Periksa koneksi Anda.', 'error');
    }
}

function showPreview(url, nomorSurat) {
    // Jika SweetAlert (Swal) tersedia, gunakan modal gambar, jika tidak buka di tab baru
    if (typeof Swal !== 'undefined') {
        Swal.fire({ imageUrl: url, imageAlt: 'Foto Bukti ' + nomorSurat, showConfirmButton: false, background: '#fff', padding: '1rem' });
    } else {
        window.open(url, '_blank');
    }
}

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
            popup: 'rounded-2xl shadow-xl border border-gray-100',
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

@if(!empty($piketEligible) && isset($dispensasiMenunggu))
let _dashboardPiketSecondsLeft = Math.max(0, Math.floor({{ $popupSecondsLeft ?? 0 }}));
let _dashboardPiketTimer = null;

function _formatDashboardTime(seconds) {
    seconds = Math.max(0, Math.floor(seconds));
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
}

function _updateDashboardCountdown() {
    const formatted = _formatDashboardTime(_dashboardPiketSecondsLeft);
    const display = document.getElementById('dashboardCountdownDisplay');
    const modalDisplay = document.getElementById('modalDashboardCountdownDisplay');
    if (display) display.textContent = formatted;
    if (modalDisplay) modalDisplay.textContent = formatted;
}

function _tickDashboardPiket() {
    _dashboardPiketSecondsLeft--;
    if (_dashboardPiketSecondsLeft <= 0) {
        _dashboardPiketSecondsLeft = 0;
        clearInterval(_dashboardPiketTimer);
        _dashboardPiketTimer = null;
        const banner = document.getElementById('piketDashboardBanner');
        const modal = document.getElementById('piketDashboardModal');
        if (banner) banner.remove();
        if (modal) modal.remove();
        return;
    }
    _updateDashboardCountdown();
}

function openPiketDashboardModal() {
    if (_dashboardPiketSecondsLeft <= 0) return;
    const modal = document.getElementById('piketDashboardModal');
    if (modal) {
        modal.classList.remove('hidden');
        _updateDashboardCountdown();
    }
}

function closePiketDashboardModal() {
    const modal = document.getElementById('piketDashboardModal');
    if (modal) modal.classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
    _updateDashboardCountdown();
    if (_dashboardPiketSecondsLeft > 0) {
        _dashboardPiketTimer = setInterval(_tickDashboardPiket, 1000);
    }
    const modal = document.getElementById('piketDashboardModal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closePiketDashboardModal();
        });
    }
});
@endif
</script>
@endpush


@endsection
