@extends('siswa.layouts.app')

@section('title', 'Detail Pengajuan Dispensasi')
@section('page-title', 'Detail Pengajuan Dispensasi')

@section('content')

{{-- <i class="fas fa-check-circle"></i> HANYA TAMPILKAN ALERT SATU KALI --}}
@include('components.alert')

<div class="max-w-4xl mx-auto">
    {{-- Header Card --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl p-5 mb-4 text-white shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-blue-100 text-xs font-bold uppercase mb-1">Surat Dispensasi</p>
                <h1 class="text-2xl font-black font-mono">{{ $dispensasi->nomor_surat }}</h1>
                <p class="text-blue-100 text-xs mt-1">
                    <i class="far fa-calendar-alt mr-1"></i>
                    Diajukan: {{ $dispensasi->created_at->isoFormat('dddd, D MMMM Y, HH:mm') }} WIB
                </p>
            </div>
            <div>
                @php
                    $statusBadges = [
                        'menunggu' => ['bg-amber-100', 'text-amber-700', 'Menunggu'],
                        'disetujui' => ['bg-emerald-100', 'text-emerald-700', 'Disetujui'],
                        'ditolak' => ['bg-red-100', 'text-red-700', 'Ditolak'],
                        'keluar' => ['bg-sky-100', 'text-sky-700', 'Sedang Keluar'],
                        'selesai' => ['bg-gray-100', 'text-gray-700', 'Selesai'],
                    ];
                    $badge = $statusBadges[$dispensasi->status] ?? $statusBadges['menunggu'];
                @endphp
                <span class="px-4 py-2 rounded-full text-sm font-bold {{ $badge[0] }} {{ $badge[1] }}">
                    {{ $badge[2] }}
                </span>
            </div>
        </div>
    </div>
    {{-- PERINGATAN TERLAMBAT --}}
    @php
        // 1. Cek apakah siswa sedang keluar dan sudah lewat batas waktu
        $isTerlambatDetail = $dispensasi->status === 'keluar' &&
                             $dispensasi->batas_waktu_kembali &&
                             now()->greaterThan($dispensasi->batas_waktu_kembali);

        // 2. Inisialisasi variabel default agar TIDAK PERNAH undefined
        $terlambatJam = 0;
        $terlambatMenit = 0;
        $terlambatText = '0 menit';

        // 3. Hitung jika benar-benar terlambat
        if ($isTerlambatDetail) {
            $totalMenit = \App\Helpers\DispensasiTimeHelper::hitungMenitTerlambat($dispensasi->batas_waktu_kembali);
            $terlambatJam = floor($totalMenit / 60);
            $terlambatMenit = $totalMenit % 60; // Sisa menit

            if ($terlambatJam > 0) {
                $terlambatText = "{$terlambatJam} jam {$terlambatMenit} menit";
            } else {
                $terlambatText = "{$terlambatMenit} menit";
            }
        }
    @endphp

    @if($isTerlambatDetail)
    <div class="bg-gradient-to-r from-red-500 to-rose-600 rounded-2xl shadow-lg shadow-red-500/30 p-5 mb-6 text-white">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0 animate-pulse">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-white font-black text-lg mb-2">
                    ⚠️ ANDA TERLAMBAT KEMBALI!
                </h3>
                <p class="text-red-100 text-sm mb-3">
                    Anda telah melewati batas waktu kembali selama
                    <span class="bg-white/20 px-3 py-1 rounded-lg font-bold">
                        {{ $terlambatText }}
                    </span>
                </p>
                <div class="bg-white/10 rounded-xl p-3 mb-4">
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <p class="text-red-200 text-[10px] font-bold uppercase mb-0.5">Batas Kembali</p>
                            <p class="font-bold text-white">
                                <i class="far fa-clock mr-1"></i>
                                {{ \Carbon\Carbon::parse($dispensasi->batas_waktu_kembali)->format('H:i') }} WIB
                            </p>
                        </div>
                        <div>
                            <p class="text-red-200 text-[10px] font-bold uppercase mb-0.5">Status</p>
                            <p class="font-bold text-white">
                                <i class="fas fa-walking mr-1"></i>
                                Belum Kembali
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <!--@if($dispensasi->qr_code)
                    <a href="#qr-section"
                       class="inline-flex items-center px-4 py-2 bg-white text-red-600 hover:bg-red-50 font-bold rounded-xl transition-colors text-sm">
                        <i class="fas fa-qrcode mr-2"></i>Lihat QR Code
                    </a>
                    @endif-->
                    <a href="{{ route('siswa.dashboard') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl transition-colors text-sm">
                        <i class="fas fa-home mr-2"></i>Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
    @else
        {{-- Info normal jika belum terlambat --}}
        @if($dispensasi->status === 'keluar' && $dispensasi->batas_waktu_kembali)
        <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl shadow-lg shadow-amber-500/20 p-4 mb-6 text-white">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="text-white font-bold text-sm">Sedang Keluar - Harap Kembali Tepat Waktu</p>
                    <p class="text-amber-100 text-xs">
                        Batas kembali: <strong>{{ \Carbon\Carbon::parse($dispensasi->batas_waktu_kembali)->format('H:i') }} WIB</strong>
                    </p>
                </div>
            </div>
        </div>
        @endif
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Kolom Kiri: Informasi Dispensasi --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Informasi Dispensasi --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-file-alt text-blue-600 mr-2"></i>Informasi Dispensasi
                </h3>

                <div class="space-y-3">
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Kategori</p>
                        <p class="font-bold text-gray-800 capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Alasan</p>
                        <p class="font-semibold text-gray-800">{{ $dispensasi->alasan }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Tujuan</p>
                        <p class="font-semibold text-gray-800">{{ $dispensasi->tujuan }}</p>
                    </div>

                    @if($dispensasi->lokasi)
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Lokasi</p>
                        <p class="font-semibold text-gray-800">{{ $dispensasi->lokasi }}</p>
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="bg-blue-50 rounded-xl p-3 border border-blue-100">
                        <p class="text-blue-400 text-[10px] font-bold uppercase mb-1">Jam Keluar</p>
                        <p class="font-bold text-blue-700">{{ $dispensasi->jam_keluar }}</p>
                        @php
                            $waktuKeluar = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar);
                        @endphp
                        @if($waktuKeluar !== '-')
                            <p class="text-blue-600 text-xs mt-1">
                                <i class="far fa-clock mr-1"></i>{{ $waktuKeluar }}
                            </p>
                        @endif
                    </div>
                    <div class="bg-amber-50 rounded-xl p-3 border border-amber-100">
                        <p class="text-amber-400 text-[10px] font-bold uppercase mb-1">Jam Kembali</p>
                        <p class="font-bold text-amber-700">{{ $dispensasi->jam_kembali }}</p>
                        @php
                            $waktuKembali = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali);
                        @endphp
                        @if($waktuKembali !== '-')
                            <p class="text-amber-600 text-xs mt-1">
                                <i class="far fa-clock mr-1"></i>{{ $waktuKembali }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Status QR Code & Cetak --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-qrcode text-blue-600 mr-2"></i>Status QR Code & Cetak
                </h3>

                {{-- Info QR Code --}}
                @if($dispensasi->status === 'disetujui')
                    @if($dispensasi->qr_code)
                    <div class="p-4 bg-emerald-50 border-2 border-emerald-200 rounded-xl text-center">
                        <div class="w-16 h-16 mx-auto rounded-full bg-emerald-500 text-white flex items-center justify-center mb-3">
                            <i class="fas fa-check text-2xl"></i>
                        </div>
                        <p class="text-emerald-700 font-bold text-sm mb-1">QR Code Aktif</p>
                        <p class="text-emerald-600 text-xs mb-4">Tunjukkan QR Code ini kepada petugas Satpam saat keluar dan kembali.</p>

                        {{-- Tampilkan QR Code --}}
                        <div class="bg-white p-4 rounded-xl inline-block shadow-lg">
                            <img src="{{ asset('storage/' . $dispensasi->qr_code) }}"
                                 alt="QR Code"
                                 class="w-48 h-48 object-contain">
                        </div>

                        <p class="text-[10px] text-gray-500 mt-3">No. Surat: {{ $dispensasi->nomor_surat }}</p>
                    </div>
                    @else
                    <div class="p-4 bg-amber-50 border-2 border-amber-200 rounded-xl text-center">
                        <div class="w-16 h-16 mx-auto rounded-full bg-amber-500 text-white flex items-center justify-center mb-3">
                            <i class="fas fa-clock text-2xl"></i>
                        </div>
                        <p class="text-amber-700 font-bold text-sm mb-1">QR Code Belum Tersedia</p>
                        <p class="text-amber-600 text-xs">QR Code sedang di-generate. Silakan refresh halaman ini.</p>
                    </div>
                    @endif

                    @elseif($dispensasi->status === 'keluar')
                        <div class="p-4 bg-sky-50 border-2 border-sky-200 rounded-xl text-center">
                            <div class="w-16 h-16 mx-auto rounded-full bg-sky-500 text-white flex items-center justify-center mb-3">
                                <i class="fas fa-walking text-2xl"></i>
                            </div>

                            <p class="text-sky-700 font-bold text-sm mb-1">Sedang Keluar</p>

                            <p class="text-sky-600 text-xs mb-4">
                                Tunjukkan QR Code yang sama kepada petugas Satpam saat Anda kembali ke sekolah.
                            </p>

                            @if($dispensasi->qr_code)
                                <div class="bg-white p-4 rounded-xl inline-block shadow-lg">
                                    <img src="{{ asset('storage/' . $dispensasi->qr_code) }}"
                                         alt="QR Code Dispensasi"
                                         class="w-48 h-48 object-contain">
                                </div>

                                <p class="text-[10px] text-gray-500 mt-3">
                                    No. Surat: {{ $dispensasi->nomor_surat }}
                                </p>
                            @else
                                <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                    <p class="text-amber-700 text-xs font-semibold">
                                        QR Code belum tersedia. Silakan refresh halaman.
                                    </p>
                                </div>
                            @endif
                        </div>

                @elseif($dispensasi->status === 'selesai')
                <div class="p-4 bg-gray-50 border-2 border-gray-200 rounded-xl text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-gray-400 text-white flex items-center justify-center mb-3">
                        <i class="fas fa-check-double text-2xl"></i>
                    </div>
                    <p class="text-gray-700 font-bold text-sm mb-1">QR Code Sudah Di-Scan (Tidak Aktif)</p>
                    <p class="text-gray-500 text-xs">Dispensasi ini sudah pernah di-scan oleh Satpam.</p>
                </div>

                @else
                <div class="p-4 bg-amber-50 border-2 border-amber-200 rounded-xl text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-amber-500 text-white flex items-center justify-center mb-3">
                        <i class="fas fa-clock text-2xl"></i>
                    </div>
                    <p class="text-amber-700 font-bold text-sm mb-1">Menunggu Persetujuan</p>
                    <p class="text-amber-600 text-xs">QR Code akan tersedia setelah disetujui guru piket.</p>
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

                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-blue-800 text-sm font-bold">
                            <i class="fas fa-print mr-2"></i>Status Pencetakan Anda
                        </p>
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $sisaCetak > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $currentPrint }} / {{ $maxPrint }} kali
                        </span>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="w-full bg-blue-200 rounded-full h-2 mb-3">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                             style="width: {{ min(($currentPrint / $maxPrint) * 100, 100) }}%">
                        </div>
                    </div>

                    {{-- Info Jam Cetak --}}
                    <div class="bg-white rounded-lg p-2.5 mb-3 border border-blue-100">
                        <p class="text-blue-700 text-xs">
                            <i class="fas fa-clock mr-1"></i>
                            <strong>Jam Cetak:</strong> {{ $startTime }} - {{ $endTime }} WIB
                        </p>
                        <p class="text-blue-600 text-[10px] mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Saat ini: {{ $currentTime }} WIB -
                            @if($isWithinTime)
                                <span class="text-emerald-600 font-bold"><i class="fas fa-check mr-1"></i>Dalam jam operasional</span>
                            @else
                                <span class="text-red-600 font-bold"><i class="fas fa-times mr-1"></i>Di luar jam operasional</span>
                            @endif
                        </p>
                    </div>

                    @if($sisaCetak > 0)
                        <p class="text-blue-700 text-xs">
                            <i class="fas fa-check-circle mr-1"></i>Sisa cetak Anda: <strong>{{ $sisaCetak }} kali</strong> lagi
                        </p>
                    @else
                        <p class="text-red-700 text-xs font-bold">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Batas cetak Anda telah tercapai. Silakan hubungi Guru Piket untuk mencetak ulang.
                        </p>
                    @endif
                </div>

                {{-- Tombol Cetak --}}
                <div class="mt-4">
                    {{-- ✅ PERBAIKAN: Hapus 'selesai' agar tidak bisa dicetak setelah selesai --}}
                    @if(in_array($dispensasi->status, ['disetujui', 'keluar']))
                        @if($sisaCetak > 0 && $isWithinTime)
                            <a href="{{ route('siswa.cetak', $dispensasi) }}"
                               target="_blank"
                               onclick="handleAfterPrintSiswa()"
                               id="btnCetakSiswa"
                               class="w-full inline-flex justify-center items-center px-5 py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 shadow-lg shadow-emerald-500/30 active:scale-[0.98] transition-all">
                                <i class="fas fa-print mr-2"></i>Cetak Surat Dispensasi
                            </a>
                        @else
                            <button disabled
                                    class="w-full inline-flex justify-center items-center px-5 py-3 rounded-xl text-sm font-bold text-gray-400 bg-gray-200 cursor-not-allowed">
                                <i class="fas fa-lock mr-2"></i>
                                @if(!$isWithinTime)
                                    Di Luar Jam Cetak
                                @else
                                    Batas Cetak Tercapai
                                @endif
                            </button>
                            <p class="text-center text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>Hubungi Guru Piket jika membutuhkan bantuan
                            </p>
                        @endif
                    @else
                        <button disabled
                                class="w-full inline-flex justify-center items-center px-5 py-3 rounded-xl text-sm font-bold text-gray-400 bg-gray-200 cursor-not-allowed">
                            <i class="fas fa-lock mr-2"></i>Tidak Dapat Dicetak
                        </button>
                        <p class="text-center text-xs text-gray-500 mt-2">
                            <i class="fas fa-shield-alt mr-1"></i>Dispensasi yang sudah selesai tidak dapat dicetak untuk mencegah penyalahgunaan.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Data Siswa & Guru --}}
        <div class="space-y-4">

            {{-- Data Siswa --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user-graduate text-blue-600 mr-2"></i>Data Siswa
                </h3>

                <div class="space-y-3">
                    <div>
                        <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Nama Lengkap</p>
                        <p class="font-bold text-gray-800">{{ $dispensasi->siswa->nama_lengkap }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">NIS / NISN</p>
                        <p class="font-mono font-bold text-gray-800">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Kelas</p>
                        <p class="font-bold text-gray-800">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Jurusan</p>
                        <p class="text-gray-700">{{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                    </div>
                    @if(!empty($dispensasi->siswa->no_telepon))
                    <div>
                        <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">No. Telepon</p>
                        <p class="font-mono font-bold text-gray-800">{{ $dispensasi->siswa->no_telepon }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Guru Piket --}}
            @if($dispensasi->guru)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user-tie text-amber-600 mr-2"></i>Guru Piket Penanggung Jawab
                </h3>

                <div class="space-y-3">
                    <div>
                        <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Nama Guru</p>
                        <p class="font-bold text-gray-800">{{ $dispensasi->guru->nama_lengkap }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Tanggal Persetujuan</p>
                        <p class="text-gray-700">{{ $dispensasi->updated_at->isoFormat('D MMMM Y') }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Timeline Status --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-history text-purple-600 mr-2"></i>Riwayat Status
                </h3>

                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-gray-800">Pengajuan Dibuat</p>
                            <p class="text-[10px] text-gray-500">{{ $dispensasi->created_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                        </div>
                    </div>

                    @if($dispensasi->guru)
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 mt-2"></div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-gray-800">Disetujui Guru Piket</p>
                            <p class="text-[10px] text-gray-500">{{ $dispensasi->updated_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                        </div>
                    </div>
                    @endif

                    @if($dispensasi->waktu_keluar_aktual)
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-sky-500 mt-2"></div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-gray-800">Konfirmasi Keluar (Scan QR)</p>
                            <p class="text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                        </div>
                    </div>
                    @endif

                    @if($dispensasi->waktu_kembali_aktual)
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 mt-2"></div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-gray-800">Konfirmasi Kembali</p>
                            <p class="text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Tombol Kembali --}}
            <a href="{{ route('siswa.pengajuan.index') }}"
               class="w-full inline-flex items-center justify-center px-4 py-3 rounded-xl text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-all">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Riwayat
            </a>
        </div>
    </div>
</div>


@push('scripts')
<script>
function handleAfterPrintSiswa() {
    const btn = document.getElementById('btnCetakSiswa');
    if(btn) {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mencetak...';
        btn.classList.add('opacity-75', 'cursor-not-allowed');
    }

    // Refresh halaman setelah 2.5 detik agar progress bar terupdate dari database
    setTimeout(function() {
        location.reload();
    }, 2500);
}
</script>
@endpush

@endsection
