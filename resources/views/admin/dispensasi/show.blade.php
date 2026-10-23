@extends('admin.layouts.app')

@section('title', 'Detail Pengajuan Dispensasi')
@section('page-title', 'Detail Pengajuan Dispensasi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header Card --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Nomor Surat</span>
                    @if($dispensasi->isRombongan())
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200 flex items-center gap-1">
                            <i class="ti ti-users text-xs"></i> Rombongan ({{ $dispensasi->anggotaSiswa->count() + 1 }} Siswa)
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 flex items-center gap-1">
                            <i class="ti ti-user text-xs"></i> Individu
                        </span>
                    @endif
                </div>
                <h3 class="text-xl sm:text-2xl font-black text-slate-900 font-mono tracking-tight break-all">
                    {{ $dispensasi->nomor_surat }}
                </h3>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                    <i class="ti ti-clock text-slate-400"></i>
                    Diajukan: {{ $dispensasi->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i:s') }} WIB
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @if($dispensasi->trashed())
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300 flex items-center gap-1">
                        <i class="ti ti-archive text-xs"></i> Diarsipkan
                    </span>
                @endif
                <span class="px-3.5 py-1 rounded-full text-xs sm:text-sm font-bold flex-shrink-0 {{ $statusColors[$dispensasi->status] ?? 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                    {{ ucfirst($dispensasi->status) }}
                </span>
            </div>
        </div>

        {{-- Soft Delete Warning Banner --}}
        @if($dispensasi->trashed())
            <div class="mt-5 p-4 rounded-xl bg-amber-50/80 border border-amber-200 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <i class="ti ti-archive text-lg"></i>
                </div>
                <div class="text-xs sm:text-sm text-amber-900 min-w-0">
                    <p class="font-bold">Dispensasi ini berada di arsip (Soft Deleted)</p>
                    <p class="text-amber-700 text-xs mt-0.5">Dihapus dari daftar aktif pada: {{ $dispensasi->deleted_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB. Anda dapat memulihkannya kapan saja.</p>
                </div>
            </div>
        @endif

        {{-- Pending Alert --}}
        @if($dispensasi->status === 'menunggu')
            <div class="mt-5 p-4 rounded-xl bg-amber-50/80 border border-amber-300 flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <i class="ti ti-clock-pause text-xl animate-pulse"></i>
                </div>
                <div class="text-xs sm:text-sm text-amber-900 flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <p class="font-bold">Pengajuan Menunggu Persetujuan Guru Piket</p>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-200/80 text-amber-900 border border-amber-300">
                            <i class="ti ti-hourglass-low text-xs"></i> Kadaluarsa: {{ $dispensasi->getBatasWaktuExpiredText() }}
                        </span>
                    </div>
                    <p class="text-amber-800 text-xs mt-1">Pengajuan ini belum disetujui dan akan otomatis berubah menjadi <strong>Kadaluarsa</strong> jika tidak disetujui hingga {{ $dispensasi->sampai_pulang ? 'jam KBM sekolah hari ini berakhir' : 'batas waktu izin' }} (pukul {{ $dispensasi->getBatasWaktuExpiredText() }}).</p>
                </div>
            </div>
        @elseif($dispensasi->status === 'kadaluarsa')
            <div class="mt-5 p-4 rounded-xl bg-orange-50 border border-orange-200 flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-700 flex items-center justify-center shrink-0">
                    <i class="ti ti-calendar-off text-xl"></i>
                </div>
                <div class="text-xs sm:text-sm text-orange-900 flex-1 min-w-0">
                    <p class="font-bold">Dispensasi Kadaluarsa</p>
                    <p class="text-orange-800 text-xs mt-0.5">{{ $dispensasi->catatan_admin ?? 'Permohonan dispensasi ini telah kadaluarsa karena melewati batas waktu operasional KBM.' }}</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Foto Bukti & Verifikasi --}}
    @if($dispensasi->foto_verifikasi || $dispensasi->foto_bukti)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Foto Verifikasi Siswa --}}
            @if($dispensasi->foto_verifikasi)
                <div class="bg-white rounded-2xl border border-blue-200/80 p-4 shadow-2xs">
                    <div class="flex items-start gap-3.5">
                        <div class="w-20 h-20 rounded-xl overflow-hidden border border-blue-200 shrink-0 bg-slate-900 cursor-pointer hover:shadow-lg transition-all group"
                             onclick="openPhotoModal('{{ Storage::url($dispensasi->foto_verifikasi) }}', 'Foto Verifikasi - {{ $dispensasi->siswa?->nama_lengkap ?? '-' }}')">
                            <img src="{{ Storage::url($dispensasi->foto_verifikasi) }}" alt="Foto {{ $dispensasi->siswa?->nama_lengkap ?? '-' }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 mb-1">
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1">
                                    <i class="ti ti-camera text-blue-600"></i> Foto Verifikasi
                                </h4>
                                <span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100 cursor-pointer" onclick="openPhotoModal('{{ Storage::url($dispensasi->foto_verifikasi) }}', 'Foto Verifikasi - {{ $dispensasi->siswa?->nama_lengkap ?? '-' }}')">
                                    Perbesar
                                </span>
                            </div>
                            <p class="text-xs text-slate-500">Verifikasi visual identitas siswa saat pengajuan izin.</p>
                            <p class="text-[10px] text-slate-400 mt-2 flex items-center gap-1">
                                <i class="ti ti-info-circle"></i> Dihapus otomatis setelah siswa kembali.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Foto Bukti Kedatangan --}}
            @if($dispensasi->foto_bukti)
                <div class="bg-white rounded-2xl border border-emerald-200/80 p-4 shadow-2xs">
                    <div class="flex items-start gap-3.5">
                        <div class="w-20 h-20 rounded-xl overflow-hidden border border-emerald-200 shrink-0 bg-slate-900 cursor-pointer hover:shadow-lg transition-all group"
                             onclick="openPhotoModal('{{ Storage::url($dispensasi->foto_bukti) }}', 'Foto Bukti Kedatangan - {{ $dispensasi->siswa?->nama_lengkap ?? '-' }}')">
                            <img src="{{ Storage::url($dispensasi->foto_bukti) }}" alt="Foto Bukti {{ $dispensasi->siswa?->nama_lengkap ?? '-' }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 mb-1">
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1">
                                    <i class="ti ti-photo-check text-emerald-600"></i> Bukti Kedatangan
                                </h4>
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100 cursor-pointer" onclick="openPhotoModal('{{ Storage::url($dispensasi->foto_bukti) }}', 'Foto Bukti Kedatangan - {{ $dispensasi->siswa?->nama_lengkap ?? '-' }}')">
                                    Perbesar
                                </span>
                            </div>
                            <p class="text-xs text-slate-500">Foto bukti yang diunggah siswa di lokasi tujuan.</p>
                            @if($dispensasi->foto_bukti_uploaded_at)
                                <p class="text-[10px] text-emerald-700 mt-2 font-mono flex items-center gap-1">
                                    <i class="ti ti-clock"></i> {{ $dispensasi->foto_bukti_uploaded_at->isoFormat('D MMM Y, HH:mm') }} WIB
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Main Details Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        {{-- Data Siswa --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5">
            <h4 class="font-bold text-slate-900 text-sm mb-4 border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="ti ti-user text-blue-600"></i> Data Pemohon Siswa
            </h4>
            <div class="space-y-3 text-xs sm:text-sm">
                <div>
                    <span class="text-slate-400 font-medium text-[11px] uppercase tracking-wider block">Nama Lengkap</span>
                    <p class="font-bold text-slate-900 mt-0.5">{{ $dispensasi->siswa?->nama_lengkap ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-slate-400 font-medium text-[11px] uppercase tracking-wider block">NIS</span>
                    <p class="font-mono font-bold text-slate-800 mt-0.5">{{ $dispensasi->siswa?->user?->nis_nip ?? '-' }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <span class="text-slate-400 font-medium text-[11px] uppercase tracking-wider block">Kelas</span>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ $dispensasi->siswa?->kelas?->nama_kelas ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium text-[11px] uppercase tracking-wider block">Jurusan</span>
                        <p class="font-semibold text-slate-800 mt-0.5 truncate">{{ $dispensasi->siswa?->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Pengajuan --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5">
            <h4 class="font-bold text-slate-900 text-sm mb-4 border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="ti ti-file-description text-blue-600"></i> Detail Permohonan
            </h4>
            <div class="space-y-3 text-xs sm:text-sm">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <span class="text-slate-400 font-medium text-[11px] uppercase tracking-wider block">Kategori</span>
                        <p class="font-bold text-slate-900 capitalize mt-0.5">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium text-[11px] uppercase tracking-wider block">Lokasi</span>
                        <p class="font-semibold text-slate-800 mt-0.5 truncate">{{ $dispensasi->lokasi ?? '-' }}</p>
                    </div>
                </div>
                <div>
                    <span class="text-slate-400 font-medium text-[11px] uppercase tracking-wider block">Tujuan Dispensasi</span>
                    <p class="font-semibold text-slate-800 mt-0.5">{{ $dispensasi->tujuan }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                    <div>
                        <span class="text-slate-400 font-medium text-[11px] uppercase tracking-wider block">Rencana Keluar</span>
                        <p class="font-bold text-blue-700 font-mono mt-0.5">
                            {{ $dispensasi->jam_keluar }}
                            <span class="text-[11px] text-slate-500 block font-normal">
                                {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium text-[11px] uppercase tracking-wider block">Rencana Kembali</span>
                        <p class="font-bold text-blue-700 font-mono mt-0.5">
                            {{ $dispensasi->jam_kembali }}
                            <span class="text-[11px] text-slate-500 block font-normal">
                                {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alasan & Catatan --}}
        <div class="md:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5">
            <h4 class="font-bold text-slate-900 text-sm mb-4 border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="ti ti-notes text-blue-600"></i> Alasan & Catatan Tambahan
            </h4>
            <div class="space-y-3 text-xs sm:text-sm">
                @if($dispensasi->dibuat_manual_oleh_guru)
                    <div class="bg-indigo-50/70 p-3.5 rounded-xl border border-indigo-200">
                        <span class="text-indigo-800 font-bold text-xs uppercase flex items-center gap-1.5">
                            <i class="ti ti-user-check text-base"></i> Dibuat Manual oleh Guru Piket
                        </span>
                        <p class="text-indigo-900 mt-1 text-xs">Pengajuan ini dibuat langsung oleh {{ $dispensasi->guru?->nama_lengkap ?? 'Guru Piket' }} untuk siswa bersangkutan.</p>
                    </div>
                @endif
                <div>
                    <span class="text-slate-400 font-medium text-[11px] uppercase tracking-wider block mb-1">Alasan Pengajuan Siswa</span>
                    <p class="font-medium text-slate-800 bg-slate-50/80 p-3 rounded-xl border border-slate-200/60 leading-relaxed">{{ $dispensasi->alasan }}</p>
                </div>
                @if($dispensasi->catatan_admin && !$dispensasi->dibuat_manual_oleh_guru)
                    <div>
                        <span class="text-slate-400 font-medium text-[11px] uppercase tracking-wider block mb-1">Catatan Admin / Guru</span>
                        <p class="font-medium text-slate-800 bg-amber-50/70 p-3 rounded-xl border border-amber-200 leading-relaxed">{{ $dispensasi->catatan_admin }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Guru Piket Penanggung Jawab --}}
        <div class="md:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5">
            <h4 class="font-bold text-slate-900 text-sm mb-4 border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="ti ti-user-shield text-blue-600"></i> Guru Piket Penanggung Jawab
            </h4>
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 border border-blue-100 rounded-xl flex items-center justify-center font-bold text-lg shrink-0">
                    {{ strtoupper(substr($dispensasi->guru?->nama_lengkap ?? 'G', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-slate-900 text-sm sm:text-base">{{ $dispensasi->guru?->nama_lengkap ?? 'Menunggu persetujuan guru piket' }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Tanggal Pengajuan: {{ $dispensasi->created_at ? $dispensasi->created_at->format('d M Y') : '-' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Anggota Rombongan --}}
        @if($dispensasi->isRombongan() && $dispensasi->anggotaSiswa->count() > 0)
            <div class="md:col-span-2 bg-white rounded-2xl border border-purple-200/80 shadow-2xs p-5">
                <div class="flex items-center justify-between mb-4 border-b border-purple-100 pb-3">
                    <h4 class="font-bold text-purple-900 flex items-center gap-2 text-sm">
                        <i class="ti ti-users text-purple-600"></i> Anggota Siswa Rombongan
                    </h4>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                        {{ $dispensasi->anggotaSiswa->count() + 1 }} Siswa
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div class="p-3 rounded-xl bg-purple-50/50 border border-purple-200 flex items-center justify-between text-xs">
                        <div class="min-w-0 pr-2">
                            <p class="font-bold text-slate-900 truncate">1. {{ $dispensasi->siswa?->nama_lengkap }}</p>
                            <p class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $dispensasi->siswa?->kelas?->nama_kelas ?? '-' }} • NIS: {{ $dispensasi->siswa?->user?->nis_nip ?? '-' }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-200 text-purple-800 shrink-0">Ketua</span>
                    </div>
                    @foreach($dispensasi->anggotaSiswa as $idx => $anggota)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                            <div class="min-w-0 pr-2">
                                <p class="font-bold text-slate-900 truncate">{{ $idx + 2 }}. {{ $anggota->nama_lengkap }}</p>
                                <p class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $anggota->kelas?->nama_kelas ?? '-' }} • NIS: {{ $anggota->user?->nis_nip ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-200 text-slate-700 shrink-0">Anggota</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Timeline Status Stepper --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6">
        <h3 class="text-sm font-bold text-slate-900 mb-5 flex items-center border-b border-slate-100 pb-3 gap-2">
            <i class="ti ti-timeline text-blue-600"></i> Alur & Riwayat Status Dispensasi
        </h3>
        <div class="space-y-4">
            {{-- 1. Diajukan --}}
            <div class="flex items-start gap-3.5">
                <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">
                    <i class="ti ti-file-plus"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm font-bold text-slate-900">Pengajuan Dibuat</p>
                    <p class="text-[11px] text-slate-400 font-mono">{{ $dispensasi->created_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                </div>
            </div>

            {{-- 2. Disetujui / Ditolak --}}
            @if($dispensasi->status === 'ditolak')
                <div class="flex items-start gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">
                        <i class="ti ti-x"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-bold text-rose-600">Ditolak oleh Guru Piket</p>
                        <p class="text-[11px] text-slate-400 font-mono">{{ ($dispensasi->rejected_at ?? $dispensasi->updated_at)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                    </div>
                </div>
            @elseif($dispensasi->guru)
                <div class="flex items-start gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">
                        <i class="ti ti-check"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-bold text-slate-900">Disetujui oleh Guru Piket</p>
                        <p class="text-[11px] text-slate-400 font-mono">{{ ($dispensasi->approved_at ?? $dispensasi->created_at)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                    </div>
                </div>
            @endif

            {{-- 3. Menunggu Scan Keluar --}}
            @if($dispensasi->status === 'disetujui')
                <div class="flex items-start gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold animate-pulse">
                        <i class="ti ti-qrcode"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-bold text-amber-700">Menunggu Konfirmasi Satpam (Gerbang Keluar)</p>
                        <p class="text-[11px] text-slate-400">Silakan tunjukkan QR Code kepada petugas satpam di pos.</p>
                    </div>
                </div>
            @endif

            {{-- 4. Konfirmasi Keluar --}}
            @if($dispensasi->waktu_keluar_aktual)
                <div class="flex items-start gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">
                        <i class="ti ti-door-exit"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-bold text-slate-900">Dikonfirmasi Keluar oleh Satpam</p>
                        <p class="text-[11px] text-slate-400 font-mono">{{ \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                    </div>
                </div>
            @endif

            {{-- 5. Menunggu Scan Kembali --}}
            @if($dispensasi->status === 'keluar')
                <div class="flex items-start gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold animate-pulse">
                        <i class="ti ti-door-enter"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-bold text-amber-700">Menunggu Konfirmasi Satpam (Gerbang Masuk)</p>
                        <p class="text-[11px] text-slate-400">Tunjukkan QR Code yang sama saat kembali ke sekolah.</p>
                    </div>
                </div>
            @endif

            {{-- 6. Konfirmasi Kembali --}}
            @if($dispensasi->waktu_kembali_aktual)
                <div class="flex items-start gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">
                        <i class="ti ti-circle-check"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-bold text-slate-900">Dikonfirmasi Selesai & Kembali oleh Satpam</p>
                        <p class="text-[11px] text-slate-400 font-mono">{{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                        @if($dispensasi->isReturnedLate())
                            <p class="text-[11px] text-amber-700 font-bold mt-1 flex items-center gap-1 flex-wrap">
                                <i class="ti ti-alert-triangle"></i> Tercatat Terlambat {{ $dispensasi->getLateDurationText(true) ? '(' . $dispensasi->getLateDurationText(true) . ')' : '' }}
                                @if($dispensasi->isWithinTolerance())
                                    <span class="text-[10px] font-medium text-amber-700 bg-amber-100/80 px-2 py-0.5 rounded border border-amber-300">
                                        {{ $dispensasi->getKeteranganToleransiKembaliText() }}
                                    </span>
                                @else
                                    <span class="text-[10px] font-medium text-rose-700 bg-rose-100/80 px-2 py-0.5 rounded border border-rose-300">
                                        Melewati batas toleransi gerbang
                                    </span>
                                @endif
                            </p>
                        @elseif($dispensasi->isReturnedEarly())
                            <p class="text-[11px] text-emerald-600 font-bold mt-1 flex items-center gap-1">
                                <i class="ti ti-clock-check"></i> Kembali Lebih Awal
                            </p>
                        @else
                            <p class="text-[11px] text-emerald-600 font-bold mt-1 flex items-center gap-1">
                                <i class="ti ti-circle-check"></i> Tepat Waktu Sesuai Izin
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Bottom Action Buttons --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-4 sm:p-5 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <a href="{{ route('admin.semua.pengajuan') }}" class="min-h-[44px] inline-flex items-center justify-center px-4 py-2.5 border border-slate-200 rounded-xl text-slate-700 text-xs sm:text-sm font-bold hover:bg-slate-50 transition-colors gap-2">
            <i class="ti ti-arrow-left text-base"></i>
            <span>Kembali ke Daftar</span>
        </a>

        <div class="flex items-center gap-2 flex-wrap">
            @if($dispensasi->trashed())
                <button type="button"
                        onclick="confirmRestore({{ $dispensasi->id }}, '{{ $dispensasi->nomor_surat }}')"
                        class="min-h-[44px] px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center justify-center gap-2 shadow-xs">
                    <i class="ti ti-rotate-clockwise text-base"></i>
                    <span>Pulihkan Dispensasi</span>
                </button>
                <button type="button"
                        onclick="confirmForceDelete({{ $dispensasi->id }}, '{{ $dispensasi->nomor_surat }}', '{{ addslashes($dispensasi->siswa?->nama_lengkap ?? '-') }}')"
                        class="min-h-[44px] px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center justify-center gap-2 shadow-xs">
                    <i class="ti ti-trash-x text-base"></i>
                    <span>Hapus Permanen</span>
                </button>
            @else
                <button type="button"
                        onclick="confirmSoftDelete({{ $dispensasi->id }}, '{{ $dispensasi->nomor_surat }}', '{{ addslashes($dispensasi->siswa?->nama_lengkap ?? '-') }}')"
                        class="min-h-[44px] px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center justify-center gap-2 shadow-xs">
                    <i class="ti ti-archive text-base"></i>
                    <span>Pindahkan ke Arsip</span>
                </button>
            @endif
        </div>
    </div>
</div>

{{-- MODAL PREVIEW FOTO --}}
<div id="photoModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6 bg-slate-950/85 backdrop-blur-md" onclick="closePhotoModal(event)">
    <div class="relative flex max-h-[92dvh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl border border-slate-700/30" onclick="event.stopPropagation()">
        {{-- Header Modal --}}
        <div class="w-full bg-white px-4 py-3 sm:px-5 sm:py-3.5 flex items-center justify-between border-b border-slate-200 shrink-0">
            <h3 id="photoModalTitle" class="min-w-0 text-sm font-bold text-slate-900 flex items-center gap-2 truncate pr-2">
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <i class="ti ti-photo text-base"></i>
                </span>
                <span class="truncate">Preview Foto</span>
            </h3>
            <button onclick="closePhotoModal()" class="w-9 h-9 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 active:bg-slate-200 flex items-center justify-center transition-colors shrink-0" aria-label="Tutup preview foto">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>

        {{-- Body Modal --}}
        <div class="min-h-0 flex-1 w-full bg-slate-950 p-2 sm:p-4 flex items-center justify-center overflow-auto max-h-[calc(92dvh-115px)]">
            <img id="photoModalImage" src="" alt="Preview" class="max-w-full max-h-[70dvh] w-auto h-auto object-contain rounded-xl shadow-lg">
        </div>

        {{-- Footer Modal --}}
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
                <p class="mb-2 text-sm text-slate-600">Data dispensasi ini akan dipindahkan ke <strong>Arsip</strong> (Soft Delete).</p>
                <div class="bg-slate-50 p-3 rounded-xl text-xs border border-slate-200 font-medium text-slate-700">
                    <p><strong>No. Surat:</strong> ${nomorSurat}</p>
                    <p><strong>Siswa:</strong> ${namaSiswa}</p>
                </div>
                <p class="text-amber-700 text-xs mt-3 flex items-center gap-1">
                    <i class="ti ti-info-circle"></i> Data tetap tersimpan aman dan dapat dipulihkan kapan saja.
                </p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="ti ti-archive mr-1"></i> Ya, Arsipkan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl shadow-xl border border-slate-200',
            confirmButton: 'rounded-xl text-xs font-bold px-4 py-2.5',
            cancelButton: 'rounded-xl text-xs font-semibold px-4 py-2.5'
        }
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
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="ti ti-rotate-clockwise mr-1"></i> Ya, Pulihkan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl shadow-xl border border-slate-200',
            confirmButton: 'rounded-xl text-xs font-bold px-4 py-2.5',
            cancelButton: 'rounded-xl text-xs font-semibold px-4 py-2.5'
        }
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
                <p class="mb-2 text-sm text-slate-700">Hapus data surat ini secara <strong>permanen</strong>?</p>
                <div class="bg-rose-50 p-3 rounded-xl text-xs border border-rose-200 text-rose-800">
                    <p class="font-bold flex items-center gap-1"><i class="ti ti-alert-triangle"></i> Peringatan:</p>
                    <p class="mt-1">File foto bukti dan QR code akan terhapus permanen dari server. Tindakan ini tidak dapat dibatalkan!</p>
                </div>
            </div>
        `,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="ti ti-trash mr-1"></i> Hapus Permanen',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl shadow-xl border border-slate-200',
            confirmButton: 'rounded-xl text-xs font-bold px-4 py-2.5',
            cancelButton: 'rounded-xl text-xs font-semibold px-4 py-2.5'
        }
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
