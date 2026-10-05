@extends('siswa.layouts.app')
@section('title', 'Detail Pengajuan Dispensasi')
@section('page-title', 'Detail Pengajuan Dispensasi')
@section('content')

<div class="max-w-5xl mx-auto space-y-4">

    {{-- Header Card --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Nomor Surat</p>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 font-mono">{{ $dispensasi->nomor_surat }}</h1>
                <p class="text-xs text-gray-500 mt-1 flex items-center">
                    <i class="far fa-calendar-alt mr-1.5"></i>
                    Diajukan: {{ $dispensasi->created_at->isoFormat('dddd, D MMMM Y, HH:mm') }} WIB
                </p>
            </div>
                @php
                    $badge = $dispensasi->status_badge;
                @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border {{ $badge['class'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                    {{ $badge['text'] }}
                </span>
            </div>
        </div>
    </div>

  {{-- ============================================================ --}}
{{-- HUBUNGI GURU PIKET --}}
{{-- ============================================================ --}}
@if($popupEligible)
<div id="piketBanner"
     class="bg-white border border-amber-200 rounded-xl shadow-sm overflow-hidden">

    <div class="p-4 sm:p-5">
        <div class="flex items-start gap-3 sm:gap-4">

            {{-- Icon --}}
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-amber-50 border border-amber-100
                        flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-clock text-amber-600"></i>
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">
                            Guru Piket Belum Merespons
                        </h3>

                        <p class="text-xs text-gray-500 mt-0.5">
                            Anda dapat menghubungi Guru Piket melalui WhatsApp.
                        </p>
                    </div>

                    {{-- Countdown --}}
                    <div class="inline-flex items-center self-start sm:self-auto
                                gap-1.5 px-2.5 py-1 rounded-lg
                                bg-amber-50 border border-amber-100">
                        <i class="fas fa-clock text-amber-500 text-[10px]"></i>
                        <span id="countdownDisplay"
                              class="text-[11px] font-bold font-mono text-amber-700">
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
                            class="inline-flex items-center justify-center gap-2
                                   px-4 py-2.5 rounded-lg
                                   bg-green-600 hover:bg-green-700
                                   text-white text-xs font-bold
                                   shadow-sm transition-colors">
                        <i class="fab fa-whatsapp text-sm"></i>
                        Hubungi Guru Piket
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
        <div class="bg-red-50 border border-red-200 rounded-xl p-5">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-red-900 font-bold text-sm mb-1">PERINGATAN: ANDA TERLAMBAT!</h3>
                    <p class="text-red-700 text-sm mb-3">
                        Anda telah melewati batas waktu kembali selama
                        <span class="bg-red-100 px-2 py-0.5 rounded font-semibold text-red-900">{{ $terlambatText }}</span>
                    </p>
                    <div class="bg-white/50 rounded-lg p-3 mb-4 border border-red-100">
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <p class="text-red-600 text-[10px] font-bold uppercase mb-0.5">Batas Kembali</p>
                                <p class="font-bold text-red-900">
                                    <i class="far fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($dispensasi->batas_waktu_kembali)->format('H:i') }} WIB
                                </p>
                            </div>
                            <div>
                                <p class="text-red-600 text-[10px] font-bold uppercase mb-0.5">Status</p>
                                <p class="font-bold text-red-900">
                                    <i class="fas fa-walking mr-1"></i>Belum Kembali
                                </p>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('siswa.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-red-200 text-red-700 hover:bg-red-50 font-semibold rounded-lg transition-colors text-sm">
                        <i class="fas fa-home mr-2"></i>Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    @elseif($dispensasi->status === 'keluar' && $dispensasi->batas_waktu_kembali)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-clock text-amber-600 text-lg"></i>
            </div>
            <div class="flex-1">
                <p class="text-amber-900 font-semibold text-sm">Sedang Keluar - Harap Kembali Tepat Waktu</p>
                <p class="text-amber-700 text-xs">
                    Batas kembali: <strong>{{ \Carbon\Carbon::parse($dispensasi->batas_waktu_kembali)->format('H:i') }} WIB</strong>
                </p>
            </div>
        </div>
    @elseif($dispensasi->isNotReturned())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 sm:p-5">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-user-xmark text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-red-900">Dispensasi Ditutup: Tidak Kembali ke Sekolah</h3>
                    <p class="text-xs text-red-700 mt-1 leading-relaxed">
                        Anda tercatat keluar dari sekolah namun <strong>tidak melakukan scan kembali di pos gerbang Satpam</strong> hingga kegiatan belajar mengajar berakhir. Status ditutup otomatis dengan catatan peringatan.
                    </p>
                </div>
            </div>
        </div>
    @elseif($dispensasi->isReturnedLate())
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 sm:p-5">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-clock-rotate-left text-lg"></i>
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
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 sm:p-5">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="w-10 h-10 rounded-xl bg-gray-200 text-gray-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-ban text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-gray-900">Dispensasi Dibatalkan</h3>
                    <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                        Permohonan dispensasi ini telah dibatalkan (siswa tidak jadi keluar sekolah) dan QR Code dinonaktifkan.
                    </p>
                </div>
            </div>
        </div>
    @elseif($dispensasi->status === 'kadaluarsa')
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 sm:p-5">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-calendar-xmark text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-orange-900">Dispensasi Kadaluarsa</h3>
                    <p class="text-xs text-orange-800 mt-1 leading-relaxed">
                        Permohonan ini kadaluarsa secara otomatis karena tidak dikonfirmasikan ke Guru Piket hingga jam pelajaran sekolah hari tersebut berakhir.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Kolom Kiri: Informasi Dispensasi --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Informasi Dispensasi --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center border-b border-gray-100 pb-3">
                    <i class="fas fa-file-alt text-gray-400 mr-2"></i>Informasi Dispensasi
                </h3>
                <div class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-[10px] font-bold text-gray-500 uppercase mb-1">Kategori</p>
                            <p class="font-semibold text-gray-900 capitalize text-sm">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-[10px] font-bold text-gray-500 uppercase mb-1">Tujuan</p>
                            <p class="font-semibold text-gray-900 text-sm">{{ $dispensasi->tujuan }}</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-[10px] font-bold text-gray-500 uppercase mb-1">Alasan</p>
                        <p class="font-medium text-gray-800 text-sm leading-relaxed">{{ $dispensasi->alasan }}</p>
                    </div>
                    @if($dispensasi->lokasi)
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-[10px] font-bold text-gray-500 uppercase mb-1">Lokasi</p>
                        <p class="font-semibold text-gray-900 text-sm">{{ $dispensasi->lokasi }}</p>
                    </div>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-100">
                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                        <p class="text-[10px] font-bold text-blue-600 uppercase mb-1">Jam Keluar</p>
                        <p class="font-bold text-blue-900 text-sm">{{ $dispensasi->jam_keluar }}</p>
                        @php $waktuKeluar = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar); @endphp
                        @if($waktuKeluar !== '-')
                            <p class="text-blue-700 text-xs mt-1"><i class="far fa-clock mr-1"></i>{{ $waktuKeluar }}</p>
                        @endif
                    </div>
                    <div class="bg-amber-50 rounded-lg p-3 border border-amber-100">
                        <p class="text-[10px] font-bold text-amber-600 uppercase mb-1">Jam Kembali</p>
                        <p class="font-bold text-amber-900 text-sm">{{ $dispensasi->jam_kembali }}</p>
                        @php $waktuKembali = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali); @endphp
                        @if($waktuKembali !== '-')
                            <p class="text-amber-700 text-xs mt-1"><i class="far fa-clock mr-1"></i>{{ $waktuKembali }}</p>
                        @endif
                    </div>
                </div>
                @if($dispensasi->dibuat_manual_oleh_guru)
                    <div class="mt-4 bg-violet-50 border border-violet-200 rounded-lg p-3">
                        <p class="text-violet-700 text-[10px] font-bold uppercase mb-1"><i class="fas fa-user-tie mr-1"></i>Dibuat Manual oleh Guru Piket</p>
                        <p class="text-violet-900 text-sm font-medium">Pengajuan dispensasi ini dibuat langsung oleh {{ $dispensasi->guru?->nama_lengkap ?? 'Guru Piket' }} untuk Anda.</p>
                    </div>
                @endif
                @if($dispensasi->catatan_admin)
                    <div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-3.5">
                        <p class="text-amber-800 text-[10px] font-bold uppercase mb-1 flex items-center gap-1.5">
                            <i class="fas fa-comment-dots text-amber-600"></i> Catatan Guru Piket / Keterangan Sistem
                        </p>
                        <p class="text-amber-950 text-xs sm:text-sm font-medium leading-relaxed">{{ $dispensasi->catatan_admin }}</p>
                    </div>
                @endif
            </div>

            {{-- Foto Verifikasi & Foto Bukti --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center">
                    <i class="fas fa-camera text-gray-400 mr-2"></i>Foto Verifikasi & Bukti
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Foto Verifikasi --}}
                    <div>
                        <p class="text-xs font-bold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-user-check text-blue-600 mr-1.5"></i>Foto Verifikasi (Selfie)
                        </p>
                        @php
                            $hasFotoVerif = !empty($dispensasi->foto_verifikasi) && \Illuminate\Support\Facades\Storage::disk('public')->exists($dispensasi->foto_verifikasi);
                        @endphp
                        @if($hasFotoVerif)
                            <div class="rounded-lg overflow-hidden border border-gray-200 bg-gray-50 aspect-video max-h-48 flex items-center justify-center cursor-pointer hover:shadow-md transition-shadow" onclick="openPhotoModal('{{ asset('storage/' . $dispensasi->foto_verifikasi) }}', 'Foto Verifikasi (Selfie)')">
                                <img src="{{ asset('storage/' . $dispensasi->foto_verifikasi) }}" alt="Foto Verifikasi" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="p-5 rounded-lg bg-gray-50 border border-dashed border-gray-300 text-center">
                                <i class="fas fa-camera text-gray-400 text-2xl mb-2 block"></i>
                                <p class="text-xs text-gray-600 font-medium">
                                    @if($dispensasi->status === 'selesai')
                                        Foto verifikasi sudah dihapus karena dispensasi telah diselesaikan.
                                    @else
                                        Foto verifikasi tidak tersedia.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Foto Bukti --}}
                    <div>
                        <p class="text-xs font-bold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-image text-emerald-600 mr-1.5"></i>Foto Bukti Kegiatan
                        </p>
                        @php
                            $hasFotoBukti = !empty($dispensasi->foto_bukti) && \Illuminate\Support\Facades\Storage::disk('public')->exists($dispensasi->foto_bukti);
                        @endphp
                        @if($hasFotoBukti)
                            <div class="rounded-lg overflow-hidden border border-gray-200 bg-gray-50 aspect-video max-h-48 flex items-center justify-center cursor-pointer hover:shadow-md transition-shadow" onclick="openPhotoModal('{{ asset('storage/' . $dispensasi->foto_bukti) }}', 'Foto Bukti Kegiatan')">
                                <img src="{{ asset('storage/' . $dispensasi->foto_bukti) }}" alt="Foto Bukti" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="p-5 rounded-lg bg-gray-50 border border-dashed border-gray-300 text-center">
                                <i class="fas fa-image text-gray-400 text-2xl mb-2 block"></i>
                                <p class="text-xs text-gray-600 font-medium">
                                    @if($dispensasi->status === 'selesai')
                                        Foto bukti sudah dihapus karena dispensasi telah diselesaikan.
                                    @else
                                        Foto bukti belum diupload.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Status QR Code & Cetak --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center border-b border-gray-100 pb-3">
                    <i class="fas fa-qrcode text-gray-400 mr-2"></i>Status QR Code & Cetak
                </h3>

                {{-- Info QR Code --}}
                @if($dispensasi->status === 'disetujui')
                    @if($dispensasi->qr_code)
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-center">
                            <div class="w-12 h-12 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3">
                                <i class="fas fa-check text-xl"></i>
                            </div>
                            <p class="text-emerald-900 font-bold text-sm mb-1">QR Code Aktif</p>
                            <p class="text-emerald-700 text-xs mb-4">Tunjukkan QR Code ini kepada petugas Satpam untuk konfirmasi keluar.</p>
                            <div class="bg-white p-3 rounded-lg inline-block border border-gray-200">
                                <img src="{{ asset('storage/' . $dispensasi->qr_code) }}" alt="QR Code" class="w-40 h-40 object-contain">
                            </div>
                            <p class="text-[10px] text-gray-500 mt-3 font-mono">{{ $dispensasi->nomor_surat }}</p>

                            @if(empty($dispensasi->waktu_keluar_aktual))
                            <div class="mt-4 pt-3 border-t border-emerald-200/60 text-center">
                                <p class="text-[11px] text-gray-500 mb-2">Tidak jadi izin keluar?</p>
                                <button type="button" onclick="confirmBatalKeluar('{{ route('siswa.pengajuan.batal', $dispensasi) }}')"
                                        class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-xs font-semibold text-rose-600 bg-white hover:bg-rose-50 border border-rose-200 transition-colors gap-1.5">
                                    <i class="fas fa-ban"></i> Batalkan Dispensasi Ini
                                </button>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-center">
                            <div class="w-12 h-12 mx-auto rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mb-3">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                            <p class="text-amber-900 font-bold text-sm mb-1">QR Code Belum Tersedia</p>
                            <p class="text-amber-700 text-xs">Sedang di-generate. Silakan refresh halaman.</p>
                        </div>
                    @endif
                @elseif($dispensasi->status === 'keluar')
                    <div class="p-4 bg-sky-50 border border-sky-200 rounded-lg text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-sky-100 text-sky-600 flex items-center justify-center mb-3">
                            <i class="fas fa-walking text-xl"></i>
                        </div>
                        <p class="text-sky-900 font-bold text-sm mb-1">Sedang Keluar</p>
                        <p class="text-sky-700 text-xs mb-4">Tunjukkan QR Code yang sama kepada Satpam saat Anda kembali.</p>
                        @if($dispensasi->qr_code)
                            <div class="bg-white p-3 rounded-lg inline-block border border-gray-200">
                                <img src="{{ asset('storage/' . $dispensasi->qr_code) }}" alt="QR Code" class="w-40 h-40 object-contain">
                            </div>
                            <p class="text-[10px] text-gray-500 mt-3 font-mono">{{ $dispensasi->nomor_surat }}</p>
                        @endif
                    </div>
                @elseif($dispensasi->status === 'selesai')
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-gray-200 text-gray-500 flex items-center justify-center mb-3">
                            <i class="fas fa-check-double text-xl"></i>
                        </div>
                        <p class="text-gray-900 font-bold text-sm mb-1">QR Code Tidak Aktif</p>
                        <p class="text-gray-600 text-xs">Dispensasi ini sudah selesai diproses.</p>
                    </div>
                @elseif($dispensasi->status === 'dibatalkan')
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-gray-200 text-gray-600 flex items-center justify-center mb-3">
                            <i class="fas fa-ban text-xl"></i>
                        </div>
                        <p class="text-gray-900 font-bold text-sm mb-1">Dispensasi Dibatalkan</p>
                        <p class="text-gray-600 text-xs">Permohonan dispensasi ini telah dibatalkan.</p>
                    </div>
                @elseif($dispensasi->status === 'kadaluarsa')
                    <div class="p-4 bg-orange-50 border border-orange-200 rounded-lg text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mb-3">
                            <i class="fas fa-calendar-xmark text-xl"></i>
                        </div>
                        <p class="text-orange-900 font-bold text-sm mb-1">Dispensasi Kadaluarsa</p>
                        <p class="text-orange-700 text-xs">Permohonan ini kadaluarsa karena tidak dikonfirmasikan ke Guru Piket.</p>
                    </div>
                @elseif($dispensasi->status === 'ditolak')
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-red-100 text-red-600 flex items-center justify-center mb-3">
                            <i class="fas fa-times text-xl"></i>
                        </div>
                        <p class="text-red-900 font-bold text-sm mb-1">Pengajuan Ditolak</p>
                        <p class="text-red-700 text-xs">Pengajuan telah ditolak oleh Guru Piket.</p>
                    </div>
                @else
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mb-3">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        <p class="text-amber-900 font-bold text-sm mb-1">Menunggu Persetujuan</p>
                        <p class="text-amber-700 text-xs">QR Code akan tersedia setelah disetujui Guru Piket.</p>
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
                <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-gray-900 text-sm font-bold flex items-center">
                            <i class="fas fa-print mr-2 text-gray-400"></i>Status Pencetakan
                        </p>
                        <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $sisaCetak > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $currentPrint }} / {{ $maxPrint }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mb-3">
                        <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-500" style="width: {{ min(($currentPrint / $maxPrint) * 100, 100) }}%"></div>
                    </div>
                    <div class="bg-white rounded-md p-2.5 border border-gray-200 mb-3">
                        <p class="text-gray-700 text-xs">
                            <i class="fas fa-clock mr-1 text-gray-400"></i>
                            <strong>Jam Cetak:</strong> {{ $startTime }} - {{ $endTime }} WIB
                        </p>
                        <p class="text-gray-500 text-[10px] mt-1">
                            Saat ini: {{ $currentTime }} WIB -
                            @if($isWithinTime)
                                <span class="text-emerald-600 font-bold">Dalam jam operasional</span>
                            @else
                                <span class="text-red-600 font-bold">Di luar jam operasional</span>
                            @endif
                        </p>
                    </div>
                    @if($sisaCetak > 0)
                        <p class="text-gray-700 text-xs"><i class="fas fa-check-circle mr-1 text-emerald-500"></i>Sisa cetak: <strong>{{ $sisaCetak }} kali</strong> lagi</p>
                    @else
                        <p class="text-red-700 text-xs font-bold"><i class="fas fa-exclamation-triangle mr-1"></i>Batas cetak tercapai. Hubungi Guru Piket.</p>
                    @endif
                </div>

                {{-- Tombol Cetak --}}
                <div class="mt-4">
                    @if(in_array($dispensasi->status, ['disetujui', 'keluar']))
                        @if($sisaCetak > 0 && $isWithinTime)
                            <a href="{{ route('siswa.cetak', $dispensasi) }}" target="_blank" onclick="handleAfterPrintSiswa()" id="btnCetakSiswa"
                               class="w-full inline-flex justify-center items-center px-5 py-3 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                                <i class="fas fa-print mr-2"></i>Cetak Surat Dispensasi
                            </a>
                        @else
                            <button disabled class="w-full inline-flex justify-center items-center px-5 py-3 rounded-lg text-sm font-semibold text-gray-400 bg-gray-100 cursor-not-allowed border border-gray-200">
                                <i class="fas fa-lock mr-2"></i>
                                @if(!$isWithinTime) Di Luar Jam Cetak @else Batas Cetak Tercapai @endif
                            </button>
                            <p class="text-center text-xs text-gray-500 mt-2"><i class="fas fa-info-circle mr-1"></i>Hubungi Guru Piket jika butuh bantuan</p>
                        @endif
                    @else
                        <button disabled class="w-full inline-flex justify-center items-center px-5 py-3 rounded-lg text-sm font-semibold text-gray-400 bg-gray-100 cursor-not-allowed border border-gray-200">
                            <i class="fas fa-lock mr-2"></i>Tidak Dapat Dicetak
                        </button>
                        <p class="text-center text-xs text-gray-500 mt-2"><i class="fas fa-shield-alt mr-1"></i>Dispensasi selesai tidak dapat dicetak ulang.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Data Siswa & Guru --}}
        <div class="space-y-4">
            {{-- Data Siswa --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center border-b border-gray-100 pb-3">
                    <i class="fas fa-user-graduate text-gray-400 mr-2"></i>Data Siswa
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase mb-0.5">Nama Lengkap</p>
                        <p class="font-semibold text-gray-900 text-sm">{{ $dispensasi->siswa->nama_lengkap }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase mb-0.5">NIS / NISN</p>
                        <p class="font-mono font-semibold text-gray-900 text-sm">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase mb-0.5">Kelas</p>
                        <p class="font-semibold text-gray-900 text-sm">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase mb-0.5">Jurusan</p>
                        <p class="text-gray-700 text-sm">{{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                    </div>
                    @if(!empty($dispensasi->siswa->no_telepon))
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase mb-0.5">No. Telepon</p>
                        <p class="font-mono font-semibold text-gray-900 text-sm">{{ $dispensasi->siswa->no_telepon }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Guru Piket --}}
            @if($dispensasi->guru)
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center border-b border-gray-100 pb-3">
                    <i class="fas fa-user-tie text-gray-400 mr-2"></i>Guru Piket
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase mb-0.5">Nama Guru</p>
                        <p class="font-semibold text-gray-900 text-sm">{{ $dispensasi->guru->nama_lengkap }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase mb-0.5">Tanggal Persetujuan</p>
                        <p class="text-gray-700 text-sm">{{ $dispensasi->updated_at->isoFormat('D MMMM Y') }}</p>
                    </div>
                </div>
            </div>
            @endif

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
            <p class="text-xs font-bold text-gray-900">
                Disetujui oleh Guru Piket
            </p>

            <p class="text-[10px] text-gray-500">
                {{ $dispensasi->updated_at->isoFormat('D MMM Y, HH:mm') }} WIB
            </p>

            <p class="text-[11px] text-gray-600 mt-0.5">
                Oleh: {{ $dispensasi->guru->nama_lengkap }}
            </p>
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

            <a href="{{ route('siswa.pengajuan.index') }}" class="w-full inline-flex items-center justify-center px-4 py-3 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Riwayat
            </a>
        </div>
    </div>
    {{-- Modal Preview Foto --}}
    <div id="photoModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-3 sm:p-6 bg-black/80 backdrop-blur-sm" onclick="closePhotoModal(event)">
        <div class="relative flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" onclick="event.stopPropagation()">
            <div class="w-full bg-white px-4 py-3 flex items-center justify-between border-b border-gray-200">
                <h3 id="photoModalTitle" class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-image text-blue-600"></i>
                    <span>Preview Foto</span>
                </h3>
                <button type="button" onclick="closePhotoModal()" class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>
            <div class="p-3 bg-gray-950 flex items-center justify-center overflow-hidden">
                <img id="photoModalImage" src="" alt="Preview Foto" class="max-w-full max-h-[70vh] w-auto h-auto object-contain rounded-lg">
            </div>
        </div>
    </div>
</div>

@if($popupEligible)
{{-- ============================================================ --}}
{{-- MODAL: HUBUNGI GURU PIKET --}}
{{-- ============================================================ --}}
<div id="piketModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center
            bg-black/50 backdrop-blur-sm p-4">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl
                border border-gray-200 overflow-hidden">

        {{-- Header --}}
        <div class="px-5 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between gap-3">

                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-green-50 border border-green-100
                                flex items-center justify-center flex-shrink-0">
                        <i class="fab fa-whatsapp text-green-600 text-xl"></i>
                    </div>

                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900">
                            Hubungi Guru Piket
                        </h3>

                        <p class="text-[11px] text-gray-500 mt-0.5">
                            Kirim pesan melalui WhatsApp
                        </p>
                    </div>
                </div>

                <button type="button"
                        onclick="closePiketPopup()"
                        class="w-8 h-8 rounded-lg
                               text-gray-400 hover:text-gray-600
                               hover:bg-gray-100
                               flex items-center justify-center
                               transition-colors flex-shrink-0">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-5 space-y-4">

            {{-- Guru --}}
            @if($hubungiGuru)
            <div class="flex items-center gap-3 p-3.5
                        bg-gray-50 border border-gray-200 rounded-xl">

                <div class="w-10 h-10 rounded-full bg-blue-50
                            flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user-tie text-blue-600"></i>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-[10px] uppercase tracking-wide
                              font-bold text-gray-400">
                        Guru Piket
                    </p>

                    <p class="text-sm font-bold text-gray-900 truncate">
                        {{ $hubungiGuru->nama_lengkap }}
                    </p>

                    @if($hubungiGuru->no_telepon)
                        <p class="text-[11px] text-gray-500 font-mono mt-0.5">
                            {{ $hubungiGuru->no_telepon }}
                        </p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Information --}}
            <div class="rounded-xl border border-blue-100 bg-blue-50 p-3.5">
                <div class="flex items-start gap-2.5">
                    <i class="fas fa-info-circle text-blue-500 mt-0.5 text-sm"></i>

                    <div class="text-xs text-blue-800 leading-relaxed">
                        <p class="font-semibold">
                            Pesan WhatsApp sudah disiapkan oleh sistem.
                        </p>
                        <p class="mt-0.5 text-blue-700">
                            Anda hanya perlu membuka WhatsApp dan mengirim pesan tersebut
                            kepada Guru Piket.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Error --}}
            @if($hubungiError)
            <div class="flex items-start gap-2.5
                        bg-amber-50 border border-amber-200
                        rounded-xl p-3.5">
                <i class="fas fa-exclamation-circle
                          text-amber-500 mt-0.5 text-sm"></i>

                <p class="text-xs text-amber-800 leading-relaxed">
                    {{ $hubungiError }}
                </p>
            </div>
            @endif

            {{-- Countdown --}}
            <div class="flex items-center justify-between
                        px-3.5 py-3 rounded-xl
                        bg-gray-50 border border-gray-200">

                <div class="flex items-center gap-2">
                    <i class="fas fa-clock text-gray-400 text-xs"></i>
                    <span class="text-xs text-gray-500">
                        Waktu tersedia
                    </span>
                </div>

                <span id="modalCountdownDisplay"
                      class="font-mono text-sm font-bold text-gray-900">
                    --:--
                </span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-4 bg-gray-50 border-t border-gray-100
                    flex flex-col-reverse sm:flex-row gap-2">

            <button type="button"
                    onclick="closePiketPopup()"
                    class="flex-1 py-2.5 px-4
                           rounded-xl
                           bg-white border border-gray-200
                           text-gray-700 text-xs font-semibold
                           hover:bg-gray-100
                           transition-colors">
                Tutup
            </button>

            @if($hubungiGuru && !$hubungiError)

                <a href="{{ route('siswa.pengajuan.hubungi-guru-piket', $dispensasi) }}"
                   id="btnHubungiWa"
                   target="_blank"
                   rel="noopener noreferrer"
                   onclick="onWaClick()"
                   class="flex-1 py-2.5 px-4
                          rounded-xl
                          bg-green-600 hover:bg-green-700
                          text-white text-xs font-bold
                          flex items-center justify-center gap-2
                          shadow-sm
                          transition-colors">

                    <i class="fab fa-whatsapp text-base"></i>
                    Buka WhatsApp
                </a>

            @else

                <button type="button"
                        disabled
                        class="flex-1 py-2.5 px-4
                               rounded-xl
                               bg-gray-200
                               text-gray-400 text-xs font-semibold
                               cursor-not-allowed">
                    WhatsApp Tidak Tersedia
                </button>

            @endif
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
function handleAfterPrintSiswa() {
    const btn = document.getElementById('btnCetakSiswa');
    if(btn) {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mencetak...';
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
        if (modalTitle) modalTitle.querySelector('span').textContent = title;
        modal.classList.remove('hidden');
    }
}

function closePhotoModal(e) {
    const modal = document.getElementById('photoModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

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

    return String(minutes).padStart(2, '0') + ':' +
           String(remainingSeconds).padStart(2, '0');
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
        _updatePiketCountdown();
    }
}

function closePiketPopup() {
    const modal = document.getElementById('piketModal');

    if (modal) {
        modal.classList.add('hidden');
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
        confirmButtonText: '<i class="fas fa-ban mr-1.5"></i> Ya, Batalkan Dispensasi',
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
</script>
@endpush
@endsection
