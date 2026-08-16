@php
    $role = auth()->user()->role ?? 'siswa';
    $layout = match($role) {
        'guru' => 'guru.layouts.app',
        'satpam' => 'satpam.layouts.app',
        'admin' => 'admin.layouts.app',
        default => 'siswa.layouts.app',
    };
@endphp

@extends($layout)

@section('title', 'Panduan Penggunaan')
@section('page-title', 'Panduan Penggunaan Sistem')

@section('content')
<div class="max-w-5xl mx-auto space-y-4 sm:space-y-6">

    {{-- HERO HEADER (RESPONSIVE) --}}
    <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-r {{ $role === 'satpam' ? 'from-red-700 via-rose-700 to-amber-700' : 'from-blue-700 via-indigo-700 to-purple-700' }} p-4 sm:p-6 md:p-8 text-white shadow-xl">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 sm:w-60 sm:h-60 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white/15 border border-white/20 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-white mb-1.5 sm:mb-2">
                    <i class="fas fa-book-open mr-1.5 text-amber-300"></i>Pusat Bantuan & Panduan
                </span>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black tracking-tight leading-tight">
                    @if($role === 'siswa')
                        Panduan Penggunaan Siswa
                    @elseif($role === 'guru')
                        Panduan Penggunaan Guru Piket
                    @elseif($role === 'satpam')
                        Panduan Penggunaan Satpam
                    @else
                        Panduan Penggunaan DIDISPEN
                    @endif
                </h1>
                <p class="text-xs sm:text-sm text-white/90 mt-1 max-w-xl leading-relaxed">
                    Petunjuk langkah demi langkah penggunaan sistem dispensasi digital SMKN 1 Bangsri.
                </p>
            </div>
            <div class="flex-shrink-0 pt-1 sm:pt-0">
                <a href="#tanya-jawab" class="w-full sm:w-auto inline-flex items-center justify-center px-3.5 py-2 sm:py-2.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/20 text-xs font-bold text-white transition-all active:scale-95">
                    <i class="fas fa-question-circle mr-2"></i>Pertanyaan Umum
                </a>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- PANDUAN KHUSUS SISWA                       --}}
    {{-- ========================================== --}}
    @if($role === 'siswa' || $role === 'admin')
    <div class="space-y-3 sm:space-y-4">
        
        {{-- Banner Alur --}}
        <div class="bg-blue-50 border border-blue-200/80 rounded-xl sm:rounded-2xl p-3.5 sm:p-5 flex items-start gap-3 sm:gap-3.5">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0 text-base sm:text-lg shadow-md shadow-blue-500/30">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-xs sm:text-sm font-bold text-blue-900">Alur Dispensasi Siswa</h3>
                <p class="text-[11px] sm:text-xs text-blue-700/90 mt-0.5 leading-relaxed">
                    Pengajuan dibuat &rarr; Diverifikasi Guru Piket &rarr; QR Code terbit &rarr; Di-scan Satpam saat keluar gerbang &rarr; Dikonfirmasi Satpam saat kembali.
                </p>
            </div>
        </div>

        {{-- Grid Langkah --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            
            {{-- Langkah 1 --}}
            <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5 flex flex-col justify-between space-y-3 sm:space-y-4 hover:border-blue-200 transition-all">
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-100 text-blue-700 font-black text-xs sm:text-sm flex items-center justify-center">1</span>
                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">Status: Menunggu</span>
                    </div>
                    <h4 class="font-bold text-gray-900 text-xs sm:text-sm">Buat Pengajuan</h4>
                    <p class="text-[11px] sm:text-xs text-gray-600 leading-relaxed">
                        Klik tombol <strong>"+" (Buat Pengajuan)</strong>. Pilih kategori izin, lokasi tujuan, alasan lengkap, serta jam keluar dan jam kembali.
                    </p>
                </div>
                <a href="{{ route('siswa.pengajuan.create') }}" class="w-full inline-flex items-center justify-center px-3.5 py-2.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition-colors active:scale-[0.98]">
                    Buat Pengajuan Sekarang <i class="fas fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
            </div>

            {{-- Langkah 2 --}}
            <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5 flex flex-col justify-between space-y-3 sm:space-y-4 hover:border-blue-200 transition-all">
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-100 text-blue-700 font-black text-xs sm:text-sm flex items-center justify-center">2</span>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Status: Disetujui</span>
                    </div>
                    <h4 class="font-bold text-gray-900 text-xs sm:text-sm">Dapatkan QR Code</h4>
                    <p class="text-[11px] sm:text-xs text-gray-600 leading-relaxed">
                        Setelah Guru Piket menyetujui, buka menu <strong>"Riwayat"</strong>. Klik pengajuan Anda untuk menampilkan <strong>Kode QR Aktif</strong>.
                    </p>
                </div>
                <a href="{{ route('siswa.pengajuan.index') }}" class="w-full inline-flex items-center justify-center px-3.5 py-2.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition-colors active:scale-[0.98]">
                    Lihat Riwayat & QR <i class="fas fa-qrcode ml-1.5 text-[10px]"></i>
                </a>
            </div>

            {{-- Langkah 3 --}}
            <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5 flex flex-col justify-between space-y-3 sm:space-y-4 hover:border-blue-200 transition-all sm:col-span-2 lg:col-span-1">
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-100 text-blue-700 font-black text-xs sm:text-sm flex items-center justify-center">3</span>
                        <span class="px-2 py-0.5 rounded-full bg-sky-100 text-sky-800 text-[10px] font-bold">Status: Keluar & Selesai</span>
                    </div>
                    <h4 class="font-bold text-gray-900 text-xs sm:text-sm">Scan di Pos Satpam</h4>
                    <p class="text-[11px] sm:text-xs text-gray-600 leading-relaxed">
                        Tunjukkan Kode QR ke Satpam saat keluar gerbang. Saat kembali ke sekolah, laporkan diri ke Satpam untuk dikonfirmasi <strong>Selesai</strong>.
                    </p>
                </div>
                <div class="p-2 bg-gray-50 rounded-xl text-[10px] sm:text-[11px] text-gray-500 font-medium text-center border border-gray-100">
                    <i class="fas fa-shield-alt text-amber-500 mr-1"></i>QR hanya dapat di-scan 1x
                </div>
            </div>

        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- PANDUAN KHUSUS GURU PIKET                   --}}
    {{-- ========================================== --}}
    @if($role === 'guru' || $role === 'admin')
    <div class="space-y-3 sm:space-y-4">
        
        {{-- Banner Deskripsi --}}
        <div class="bg-indigo-50 border border-indigo-200/80 rounded-xl sm:rounded-2xl p-3.5 sm:p-5 flex items-start gap-3 sm:gap-3.5">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 text-base sm:text-lg shadow-md shadow-indigo-500/30">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-xs sm:text-sm font-bold text-indigo-900">Tugas Guru Piket</h3>
                <p class="text-[11px] sm:text-xs text-indigo-700/90 mt-0.5 leading-relaxed">
                    Memverifikasi permohonan dispensasi siswa yang masuk, memberikan persetujuan/penolakan, serta memantau laporan rekapan harian dispensasi.
                </p>
            </div>
        </div>

        {{-- Grid Fitur --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
            
            {{-- Verifikasi --}}
            <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5 space-y-3 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs sm:text-sm">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <h4 class="font-bold text-gray-900 text-xs sm:text-sm">1. Memverifikasi Pengajuan Siswa</h4>
                    </div>
                    <ol class="space-y-2 text-[11px] sm:text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Buka menu <strong>"Verifikasi"</strong> di sidebar kiri.</li>
                        <li>Periksa angka badge notifikasi untuk pengajuan baru (`Menunggu`).</li>
                        <li>Klik <strong>"Proses"</strong> pada siswa yang mengajukan dispensasi.</li>
                        <li>Periksa alasan, jam keluar, jam kembali, dan lokasi tujuan.</li>
                        <li>Klik <strong>"Setujui"</strong> untuk menerbitkan QR Code, atau <strong>"Tolak"</strong> jika permohonan tidak sesuai.</li>
                    </ol>
                </div>
                <div class="pt-2">
                    <a href="{{ route('guru.pengajuan.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition-colors shadow-md shadow-blue-500/20 active:scale-95">
                        Buka Menu Verifikasi <i class="fas fa-arrow-right ml-1.5 text-[10px]"></i>
                    </a>
                </div>
            </div>

            {{-- Laporan --}}
            <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5 space-y-3 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs sm:text-sm">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h4 class="font-bold text-gray-900 text-xs sm:text-sm">2. Memantau & Export Laporan</h4>
                    </div>
                    <ol class="space-y-2 text-[11px] sm:text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Buka menu <strong>"Laporan"</strong> di sidebar.</li>
                        <li>Gunakan filter tanggal dari / sampai dan filter status pengajuan.</li>
                        <li>Klik tombol <strong>"Export PDF"</strong> untuk mengunduh laporan format cetak resmi (lengkap dengan format tanda tangan).</li>
                        <li>Klik <strong>"Export Excel"</strong> untuk mengunduh rekap spreadsheet.</li>
                    </ol>
                </div>
                <div class="pt-2">
                    <a href="{{ route('guru.laporan.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition-colors shadow-md shadow-emerald-500/20 active:scale-95">
                        Buka Menu Laporan <i class="fas fa-file-excel ml-1.5 text-[10px]"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- PANDUAN KHUSUS SATPAM                      --}}
    {{-- ========================================== --}}
    @if($role === 'satpam' || $role === 'admin')
    <div class="space-y-3 sm:space-y-4">
        
        {{-- Banner Deskripsi --}}
        <div class="bg-red-50 border border-red-200/80 rounded-xl sm:rounded-2xl p-3.5 sm:p-5 flex items-start gap-3 sm:gap-3.5">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-red-600 text-white flex items-center justify-center flex-shrink-0 text-base sm:text-lg shadow-md shadow-red-500/30">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-xs sm:text-sm font-bold text-red-900">Tugas Satpam / Pos Gerbang</h3>
                <p class="text-[11px] sm:text-xs text-red-700/90 mt-0.5 leading-relaxed">
                    Memindai (scan) QR Code dispensasi siswa yang disetujui saat izin keluar gerbang, dan mengonfirmasi siswa yang telah kembali ke lingkungan sekolah.
                </p>
            </div>
        </div>

        {{-- Grid Fitur --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
            
            {{-- Scan QR --}}
            <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5 space-y-3 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold text-xs sm:text-sm">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <h4 class="font-bold text-gray-900 text-xs sm:text-sm">1. Scan QR Code Siswa Keluar</h4>
                    </div>
                    <ol class="space-y-2 text-[11px] sm:text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Buka menu <strong>"Scan QR"</strong> di perangkat HP/Tablet pos satpam.</li>
                        <li>Izinkan akses kamera perangkat jika diminta.</li>
                        <li>Arahkan kamera ke <strong>Kode QR</strong> yang ada pada HP siswa.</li>
                        <li>Jika valid, muncul pesan <strong>"Siswa Keluar Tercatat"</strong> (Status berubah menjadi `Keluar`).</li>
                        <li>Jika QR sudah pernah digunakan, sistem akan menolak otomatis.</li>
                    </ol>
                </div>
                <div class="pt-2">
                    <a href="{{ route('satpam.scan') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700 transition-colors shadow-md shadow-red-500/20 active:scale-95">
                        Buka Scanner QR <i class="fas fa-camera ml-1.5 text-[10px]"></i>
                    </a>
                </div>
            </div>

            {{-- Konfirmasi Kembali --}}
            <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5 space-y-3 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs sm:text-sm">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h4 class="font-bold text-gray-900 text-xs sm:text-sm">2. Konfirmasi Siswa Kembali</h4>
                    </div>
                    <ol class="space-y-2 text-[11px] sm:text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Buka <strong>Dashboard Satpam</strong>.</li>
                        <li>Lihat bagian tabel <strong>"Daftar Siswa Sedang Keluar"</strong>.</li>
                        <li>Saat siswa kembali ke sekolah, cari nama siswa tersebut.</li>
                        <li>Klik tombol <strong>"Konfirmasi Kembali"</strong>.</li>
                        <li>Waktu kembali aktual siswa tersimpan dan status berubah menjadi `Selesai`.</li>
                    </ol>
                </div>
                <div class="pt-2">
                    <a href="{{ route('satpam.dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-gray-800 text-white text-xs font-bold hover:bg-gray-900 transition-colors active:scale-95">
                        Dashboard Satpam <i class="fas fa-arrow-right ml-1.5 text-[10px]"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- PERTANYAAN UMUM (FAQ)                      --}}
    {{-- ========================================== --}}
    <div id="tanya-jawab" class="bg-white rounded-xl sm:rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-6 space-y-3 sm:space-y-4">
        <h3 class="text-sm sm:text-base font-bold text-gray-900 flex items-center">
            <i class="fas fa-question-circle text-blue-600 mr-2"></i>Pertanyaan Umum (FAQ)
        </h3>

        <div class="space-y-2.5 sm:space-y-3 text-xs" x-data="{ openFaq: null }">
            
            <div class="border border-gray-100 rounded-xl overflow-hidden">
                <button @click="openFaq = openFaq === 1 ? null : 1" class="w-full p-3 sm:p-3.5 text-left font-bold text-gray-800 flex justify-between items-center bg-gray-50/50 hover:bg-gray-100/50 transition-colors">
                    <span class="pr-2">Apakah Kode QR bisa digunakan dua kali?</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform flex-shrink-0" :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 1" class="p-3 sm:p-3.5 bg-white border-t border-gray-100 text-gray-600 leading-relaxed text-[11px] sm:text-xs">
                    Tidak bisa. Setelah di-scan oleh Satpam saat siswa keluar gerbang, Kode QR akan otomatis dinonaktifkan (Status berubah dari <em>Disetujui</em> menjadi <em>Keluar</em>) sehingga tidak bisa di-scan ulang.
                </div>
            </div>

            <div class="border border-gray-100 rounded-xl overflow-hidden">
                <button @click="openFaq = openFaq === 2 ? null : 2" class="w-full p-3 sm:p-3.5 text-left font-bold text-gray-800 flex justify-between items-center bg-gray-50/50 hover:bg-gray-100/50 transition-colors">
                    <span class="pr-2">Bagaimana jika HP siswa mati/kehabisan baterai saat di pintu gerbang?</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform flex-shrink-0" :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 2" class="p-3 sm:p-3.5 bg-white border-t border-gray-100 text-gray-600 leading-relaxed text-[11px] sm:text-xs">
                    Siswa dapat menyebutkan Nomor Surat Dispensasi atau Nama Lengkap & Kelas kepada Satpam. Satpam dapat melakukan verifikasi manual dari sistem.
                </div>
            </div>

            <div class="border border-gray-100 rounded-xl overflow-hidden">
                <button @click="openFaq = openFaq === 3 ? null : 3" class="w-full p-3 sm:p-3.5 text-left font-bold text-gray-800 flex justify-between items-center bg-gray-50/50 hover:bg-gray-100/50 transition-colors">
                    <span class="pr-2">Siapa yang bertugas menyetujui dispensasi?</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform flex-shrink-0" :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 3" class="p-3 sm:p-3.5 bg-white border-t border-gray-100 text-gray-600 leading-relaxed text-[11px] sm:text-xs">
                    Dispensasi diverifikasi dan disetujui oleh Guru Piket Utama yang sedang bertugas pada hari tersebut.
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
