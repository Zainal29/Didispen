@extends('satpam.layouts.app')

@section('title', 'Verifikasi Dispensasi')
@section('page-title', 'Verifikasi Dispensasi')

@section('content')
<div class="max-w-2xl mx-auto space-y-4 w-full min-w-0">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5">
        <div class="flex items-center justify-between mb-4 gap-2 flex-wrap">
            <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center">
                <i class="ti ti-qrcode text-rose-600 mr-2 text-xl"></i>
                Verifikasi Dispensasi
            </h2>
            <div class="flex items-center gap-2 flex-wrap">
                @if($dispensasi->isRombongan())
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                        <i class="ti ti-users text-xs"></i>
                        <span>Rombongan</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        <i class="ti ti-user text-xs"></i>
                        <span>Individu</span>
                    </span>
                @endif
                <span class="px-3 py-1 rounded-xl text-xs font-bold border
                    @if($dispensasi->status === 'disetujui') bg-emerald-50 text-emerald-700 border-emerald-200
                    @elseif($dispensasi->status === 'keluar') bg-sky-50 text-sky-700 border-sky-200
                    @else bg-slate-100 text-slate-700 border-slate-200
                    @endif">
                    {{ strtoupper($dispensasi->status) }}
                </span>
            </div>
        </div>

        {{-- Data Dispensasi --}}
        <div class="bg-rose-50/40 border border-rose-100 rounded-xl p-3.5 sm:p-4 mb-4 space-y-3">
            <div>
                <p class="text-[10px] font-bold text-rose-600 uppercase">Nomor Surat</p>
                <p class="font-mono font-bold text-slate-900 break-all text-sm sm:text-base">{{ $dispensasi->nomor_surat }}</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="text-[10px] font-bold text-rose-600 uppercase">ID Dispensasi</p>
                    <p class="font-semibold text-slate-900 text-xs sm:text-sm">#{{ $dispensasi->id }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-rose-600 uppercase">Tanggal Pengajuan</p>
                    <p class="font-semibold text-slate-900 text-xs sm:text-sm">{{ $dispensasi->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- Data Siswa --}}
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 sm:p-4 mb-4">
            <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center">
                <i class="ti ti-school text-rose-600 mr-2 text-base"></i>Data Siswa
            </h3>

            <div class="space-y-2 text-xs sm:text-sm">
                <div class="flex justify-between gap-2">
                    <span class="text-slate-500 flex-shrink-0">Nama Lengkap:</span>
                    <span class="font-bold text-slate-900 text-right break-words">{{ $dispensasi->siswa->nama_lengkap }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-slate-500 flex-shrink-0">NIS/NISN:</span>
                    <span class="font-mono font-semibold text-slate-900 text-right break-all">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-slate-500 flex-shrink-0">Kelas:</span>
                    <span class="font-semibold text-slate-900 text-right">{{ $dispensasi->siswa->kelas->nama_kelas ?? '-' }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-slate-500 flex-shrink-0">Jurusan:</span>
                    <span class="text-slate-700 text-right break-words">{{ $dispensasi->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</span>
                </div>
            </div>
        </div>

        @if($dispensasi->isRombongan())
        <div class="bg-purple-50/50 border border-purple-200 rounded-xl p-3.5 sm:p-4 mb-4">
            <div class="flex items-center justify-between mb-2.5">
                <h3 class="text-xs sm:text-sm font-bold text-purple-950 flex items-center">
                    <i class="ti ti-users text-purple-600 mr-2 text-base"></i>Anggota Rombongan
                </h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-200 text-purple-800">
                    {{ $dispensasi->anggotaSiswa->count() + 1 }} Siswa
                </span>
            </div>
            <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-purple-100 text-xs">
                    <span class="font-bold text-slate-800">1. {{ $dispensasi->siswa->nama_lengkap }} (Pemohon)</span>
                    <span class="text-slate-500 font-mono text-[11px]">{{ $dispensasi->siswa->kelas->nama_kelas ?? '-' }}</span>
                </div>
                @foreach($dispensasi->anggotaSiswa as $idx => $anggota)
                <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-slate-200 text-xs">
                    <span class="font-medium text-slate-700">{{ $idx + 2 }}. {{ $anggota->nama_lengkap }}</span>
                    <span class="text-slate-500 font-mono text-[11px]">{{ $anggota->kelas->nama_kelas ?? '-' }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Waktu Keluar & Kembali --}}
        @php
            $dayOfWeek = $dispensasi->created_at ? $dispensasi->created_at->dayOfWeek : null;
            $waktuKeluar = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar, $dayOfWeek);
            $jamMulai = str_contains($waktuKeluar, ' - ') ? explode(' - ', $waktuKeluar)[0] : ($waktuKeluar !== '-' ? $waktuKeluar : null);

            $waktuKembali = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali, $dayOfWeek);
            $jamSelesai = str_contains($waktuKembali, ' - ') ? explode(' - ', $waktuKembali)[1] : ($waktuKembali !== '-' ? $waktuKembali : null);
            if (!$jamSelesai && $dispensasi->batas_waktu_kembali) {
                $jamSelesai = $dispensasi->batas_waktu_kembali->format('H:i');
            }
        @endphp
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="bg-rose-50/40 border border-rose-100 rounded-xl p-3">
                <p class="text-[10px] font-bold text-rose-600 uppercase mb-1">Jam Keluar</p>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <p class="font-bold text-slate-900 text-sm">{{ $jamMulai ? $jamMulai . ' WIB' : \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar, $dayOfWeek) }}</p>
                    @if(str_contains((string)$dispensasi->jam_keluar, 'Jam Pelajaran'))
                        <span class="inline-flex items-center text-[11px] font-semibold px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 border border-rose-200">
                            {{ $dispensasi->jam_keluar }}
                        </span>
                    @endif
                </div>
                @if($dispensasi->waktu_keluar_aktual)
                <p class="text-xs text-sky-700 mt-1 flex items-center">
                    <i class="ti ti-circle-check mr-1 text-xs"></i>
                    Aktual: {{ \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->format('H:i') }}
                </p>
                @endif
            </div>

            <div class="bg-amber-50/50 border border-amber-200 rounded-xl p-3">
                <p class="text-[10px] font-bold text-amber-700 uppercase mb-1">Jam Kembali</p>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <p class="font-bold text-slate-900 text-sm">{{ $jamSelesai ? $jamSelesai . ' WIB' : \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali, $dayOfWeek) }}</p>
                    @if(str_contains((string)$dispensasi->jam_kembali, 'Jam Pelajaran'))
                        <span class="inline-flex items-center text-[11px] font-semibold px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 border border-amber-200">
                            {{ $dispensasi->jam_kembali }}
                        </span>
                    @endif
                </div>
                @if($dispensasi->waktu_kembali_aktual)
                <p class="text-xs text-emerald-700 mt-1 flex items-center">
                    <i class="ti ti-circle-check mr-1 text-xs"></i>
                    Aktual: {{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->format('H:i') }}
                </p>
                @endif
            </div>
        </div>

        {{-- Tujuan & Alasan --}}
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 mb-4">
            <div class="mb-2">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Tujuan</p>
                <p class="text-xs sm:text-sm font-semibold text-slate-900 break-words">{{ $dispensasi->tujuan }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase">Alasan</p>
                <p class="text-xs sm:text-sm text-slate-700 break-words">{{ $dispensasi->alasan }}</p>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        @if($dispensasi->status === 'disetujui')
        <form action="{{ route('satpam.scan.verify') }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="qr_data" value='@json(['token' => $dispensasi->qr_token])'>
            <input type="hidden" name="action" value="keluar">

            <button type="submit" class="w-full py-3 min-h-[44px] bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-xs transition-colors flex items-center justify-center text-sm">
                <i class="ti ti-door-exit mr-2 text-lg"></i>Konfirmasi Siswa KELUAR
            </button>
        </form>

        @elseif($dispensasi->status === 'keluar')
        <form action="{{ route('satpam.scan.verify') }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="qr_data" value='@json(['token' => $dispensasi->qr_token])'>
            <input type="hidden" name="action" value="kembali">

            <button type="submit" class="w-full py-3 min-h-[44px] bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition-colors flex items-center justify-center text-sm">
                <i class="ti ti-door-enter mr-2 text-lg"></i>Konfirmasi Siswa KEMBALI
            </button>
        </form>
        @endif

        {{-- Tombol Kembali --}}
        <a href="{{ route('satpam.scan') }}" class="inline-flex items-center justify-center w-full min-h-[44px] mt-3 text-slate-600 hover:text-slate-900 font-semibold text-sm">
            <i class="ti ti-reload mr-1.5 text-base"></i>Scan QR Code Lainnya
        </a>
    </div>
</div>
@endsection
