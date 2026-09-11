@extends('siswa.layouts.app')
@section('title', 'Detail Pengajuan Dispensasi')
@section('page-title', 'Detail Pengajuan Dispensasi')
@section('content')
@include('components.alert')

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
            <div>
                @php
                    $statusBadges = [
                        'menunggu'  => ['bg-amber-100', 'text-amber-700', 'Menunggu Persetujuan'],
                        'disetujui' => ['bg-emerald-100', 'text-emerald-700', 'Disetujui'],
                        'ditolak'   => ['bg-red-100', 'text-red-700', 'Ditolak'],
                        'keluar'    => ['bg-sky-100', 'text-sky-700', 'Sedang Keluar'],
                        'selesai'   => ['bg-gray-100', 'text-gray-700', 'Selesai'],
                    ];
                    $badge = $statusBadges[$dispensasi->status] ?? $statusBadges['menunggu'];
                @endphp
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold {{ $badge[0] }} {{ $badge[1] }}">
                    {{ $badge[2] }}
                </span>
            </div>
        </div>
    </div>

    {{-- PERINGATAN TERLAMBAT --}}
    @php
        $isTerlambatDetail = $dispensasi->status === 'keluar' && $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);
        $terlambatJam = 0;
        $terlambatMenit = 0;
        $terlambatText = '0 menit';
        if ($isTerlambatDetail) {
            $totalMenit = \App\Helpers\DispensasiTimeHelper::hitungMenitTerlambat($dispensasi->batas_waktu_kembali);
            $terlambatJam = floor($totalMenit / 60);
            $terlambatMenit = $totalMenit % 60;
            $terlambatText = $terlambatJam > 0 ? "{$terlambatJam} jam {$terlambatMenit} menit" : "{$terlambatMenit} menit";
        }
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
    @else
        @if($dispensasi->status === 'keluar' && $dispensasi->batas_waktu_kembali)
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
        @endif
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
                        <p class="text-gray-600 text-xs">Dispensasi ini sudah selesai di-scan oleh Satpam.</p>
                    </div>
                @else
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mb-3">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        <p class="text-amber-900 font-bold text-sm mb-1">Menunggu Persetujuan</p>
                        <p class="text-amber-700 text-xs">QR Code akan tersedia setelah disetujui guru piket.</p>
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

            <a href="{{ route('siswa.pengajuan.index') }}" class="w-full inline-flex items-center justify-center px-4 py-3 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">
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
    setTimeout(function() { location.reload(); }, 2500);
}
</script>
@endpush
@endsection
