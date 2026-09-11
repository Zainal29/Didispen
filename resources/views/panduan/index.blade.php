@php
    $role = auth()->user()->role ?? 'siswa';
    $layout = match($role) {
        'guru' => 'guru.layouts.app',
        'satpam' => 'satpam.layouts.app',
        'admin' => 'admin.layouts.app',
        default => 'siswa.layouts.app',
    };

    // Simplified color mapping for consistency
    $theme = match($role) {
        'satpam' => 'red',
        'guru' => 'indigo',
        default => 'blue',
    };
@endphp

@extends($layout)

@section('title', 'Panduan Penggunaan')
@section('page-title', 'Panduan Penggunaan Sistem')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- HERO HEADER (Clean & Professional) --}}
    <div class="relative overflow-hidden rounded-xl bg-{{ $theme }}-600 p-6 sm:p-8 text-white shadow-sm">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-white/10 border border-white/20 text-xs font-semibold uppercase tracking-wider text-white mb-2">
                    <i class="fas fa-book-open mr-1.5"></i>Pusat Bantuan & Panduan
                </span>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight leading-tight">
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
                <p class="text-sm text-white/90 mt-2 max-w-xl leading-relaxed">
                    Petunjuk langkah demi langkah penggunaan sistem dispensasi digital SMKN 1 Bangsri.
                </p>
            </div>
            <div class="flex-shrink-0">
                <a href="#tanya-jawab" class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20 text-sm font-semibold text-white transition-colors">
                    <i class="fas fa-question-circle mr-2"></i>Pertanyaan Umum
                </a>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- PANDUAN KHUSUS SISWA                       --}}
    {{-- ========================================== --}}
    @if($role === 'siswa' || $role === 'admin')
    <div class="space-y-4">
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center flex-shrink-0">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-bold text-blue-900">Alur Dispensasi Siswa</h3>
                <p class="text-xs text-blue-700 mt-1 leading-relaxed">
                    Isi Form (termasuk Foto Verifikasi) &rarr; Diverifikasi Guru Piket &rarr; QR Code terbit &rarr; Di-scan Satpam saat keluar &rarr; Dikonfirmasi Satpam saat kembali.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Langkah 1 --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col justify-between space-y-4 hover:border-blue-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold text-sm flex items-center justify-center">1</span>
                        <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-xs font-semibold">Status: Menunggu</span>
                    </div>
                    <h4 class="font-bold text-gray-900 text-sm">Buat Pengajuan</h4>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Klik tombol <strong>"Buat Pengajuan"</strong>. Isi alasan, tujuan, dan <strong>wajib upload Foto Verifikasi</strong> diri Anda. Pilih jam keluar & kembali yang realistis.
                    </p>
                </div>
                <a href="{{ route('siswa.pengajuan.create') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold transition-colors">
                    Buat Pengajuan Sekarang <i class="fas fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
            </div>

            {{-- Langkah 2 --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col justify-between space-y-4 hover:border-blue-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold text-sm flex items-center justify-center">2</span>
                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-xs font-semibold">Status: Disetujui</span>
                    </div>
                    <h4 class="font-bold text-gray-900 text-sm">Dapatkan QR Code</h4>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Setelah Guru Piket menyetujui, buka menu <strong>"Riwayat"</strong>. Klik pengajuan Anda untuk menampilkan <strong>Kode QR Aktif</strong> yang siap di-scan.
                    </p>
                </div>
                <a href="{{ route('siswa.pengajuan.index') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold transition-colors">
                    Lihat Riwayat & QR <i class="fas fa-qrcode ml-1.5 text-[10px]"></i>
                </a>
            </div>

            {{-- Langkah 3 --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col justify-between space-y-4 hover:border-blue-300 transition-colors md:col-span-2 lg:col-span-1">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold text-sm flex items-center justify-center">3</span>
                        <span class="px-2 py-0.5 rounded-md bg-sky-100 text-sky-800 text-xs font-semibold">Status: Keluar & Selesai</span>
                    </div>
                    <h4 class="font-bold text-gray-900 text-sm">Verifikasi di Pos Satpam</h4>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Tunjukkan QR Code ke Satpam saat keluar. Satpam akan mencocokkan wajah Anda dengan foto verifikasi. Saat kembali, laporkan diri untuk di-scan/dikonfirmasi <strong>Selesai</strong>.
                    </p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg text-xs text-gray-600 font-medium text-center border border-gray-200">
                    <i class="fas fa-shield-alt text-amber-500 mr-1"></i>QR hanya dapat di-scan 1x untuk keluar
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- PANDUAN KHUSUS GURU PIKET                  --}}
    {{-- ========================================== --}}
    @if($role === 'guru' || $role === 'admin')
    <div class="space-y-4">
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5 flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-indigo-600 text-white flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-bold text-indigo-900">Tugas Guru Piket</h3>
                <p class="text-xs text-indigo-700 mt-1 leading-relaxed">
                    Memverifikasi permohonan, membuat dispensasi manual untuk keadaan darurat, serta memantau status siswa secara real-time.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Verifikasi --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm"><i class="fas fa-file-signature"></i></div>
                        <h4 class="font-bold text-gray-900 text-sm">1. Verifikasi Pengajuan</h4>
                    </div>
                    <ol class="space-y-2 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Buka menu <strong>"Verifikasi"</strong>.</li>
                        <li>Periksa alasan, jam, dan tujuan siswa.</li>
                        <li>Klik <strong>"Setujui"</strong> (QR terbit) atau <strong>"Tolak"</strong>.</li>
                    </ol>
                </div>
                <a href="{{ route('guru.pengajuan.index') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition-colors">
                    Buka Menu Verifikasi <i class="fas fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
            </div>

            {{-- Buat Manual --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm"><i class="fas fa-plus-circle"></i></div>
                        <h4 class="font-bold text-gray-900 text-sm">2. Buat Dispensasi Manual</h4>
                    </div>
                    <ol class="space-y-2 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Gunakan fitur ini jika siswa <strong>tidak punya HP</strong> atau keadaan darurat.</li>
                        <li>Cari siswa via NIS/Nama, isi data, dan <strong>upload foto siswa</strong>.</li>
                        <li>Sistem akan <strong>langsung menyetujui</strong> dan menerbitkan QR Code.</li>
                    </ol>
                </div>
                <a href="{{ route('guru.pengajuan.create') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-purple-600 text-white text-xs font-semibold hover:bg-purple-700 transition-colors">
                    Buat Pengajuan Manual <i class="fas fa-plus ml-1.5 text-[10px]"></i>
                </a>
            </div>

            {{-- Scan Backup & Filter --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm"><i class="fas fa-qrcode"></i></div>
                        <h4 class="font-bold text-gray-900 text-sm">3. Scan Backup & Pantau</h4>
                    </div>
                    <ol class="space-y-2 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Gunakan menu <strong>"Scan QR"</strong> jika Satpam sedang berhalangan.</li>
                        <li>Gunakan <strong>Filter Dashboard</strong> (Keluar, Terlambat, Selesai) untuk memantau siswa.</li>
                    </ol>
                </div>
                <a href="{{ route('guru.scan') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition-colors">
                    Buka Scanner QR <i class="fas fa-camera ml-1.5 text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- PANDUAN KHUSUS SATPAM                      --}}
    {{-- ========================================== --}}
    @if($role === 'satpam' || $role === 'admin')
    <div class="space-y-4">
        <div class="bg-red-50 border border-red-200 rounded-xl p-5 flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-red-600 text-white flex items-center justify-center flex-shrink-0">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-bold text-red-900">Tugas Satpam / Pos Gerbang</h3>
                <p class="text-xs text-red-700 mt-1 leading-relaxed">
                    Memindai QR Code, melakukan verifikasi manual, mencocokkan foto wajah siswa, dan mengonfirmasi kepulangan siswa secara akurat.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Scan QR --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center font-bold text-sm"><i class="fas fa-qrcode"></i></div>
                        <h4 class="font-bold text-gray-900 text-sm">1. Scan QR Code</h4>
                    </div>
                    <ol class="space-y-2 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Buka menu <strong>"Scan QR"</strong> di perangkat.</li>
                        <li>Arahkan kamera ke QR Code pada HP siswa.</li>
                        <li><strong>Penting:</strong> Cocokkan wajah siswa dengan <em>Foto Verifikasi</em> yang muncul di dashboard.</li>
                        <li>Scan pertama = <strong>Keluar</strong>. Scan kedua = <strong>Kembali (Selesai)</strong>.</li>
                    </ol>
                </div>
                <a href="{{ route('satpam.scan') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700 transition-colors">
                    Buka Scanner QR <i class="fas fa-camera ml-1.5 text-[10px]"></i>
                </a>
            </div>

            {{-- Verifikasi Manual --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm"><i class="fas fa-search"></i></div>
                        <h4 class="font-bold text-gray-900 text-sm">2. Verifikasi Manual</h4>
                    </div>
                    <ol class="space-y-2 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Jika QR tidak bisa discan, gunakan kolom <strong>"Verifikasi Manual"</strong> di halaman Scan.</li>
                        <li>Ketik <strong>Nomor Surat, NIS, atau Nama Siswa</strong>.</li>
                        <li>Sistem akan menampilkan data. Klik tombol <strong>"Konfirmasi Keluar"</strong> atau <strong>"Kembali"</strong> secara manual.</li>
                    </ol>
                </div>
                <a href="{{ route('satpam.scan') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-amber-600 text-white text-xs font-semibold hover:bg-amber-700 transition-colors">
                    Coba Verifikasi Manual <i class="fas fa-search ml-1.5 text-[10px]"></i>
                </a>
            </div>

            {{-- Kontak & Auto-Complete --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-sm"><i class="fas fa-bell"></i></div>
                        <h4 class="font-bold text-gray-900 text-sm">3. Kontak & Auto-Selesai</h4>
                    </div>
                    <ol class="space-y-2 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Gunakan tombol <strong>WhatsApp</strong> di dashboard untuk menghubungi siswa terlambat (otomatis tercatat "Sudah Dihubungi").</li>
                        <li>Siswa yang dispensasi "Sampai Pulang" tidak wajib scan kembali. Sistem akan <strong>otomatis menyelesaikan</strong> dispensasi mereka setelah jam pulang.</li>
                    </ol>
                </div>
                <a href="{{ route('satpam.dashboard') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-gray-800 text-white text-xs font-semibold hover:bg-gray-900 transition-colors">
                    Dashboard Satpam <i class="fas fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- PERTANYAAN UMUM (FAQ)                      --}}
    {{-- ========================================== --}}
    <div id="tanya-jawab" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
        <h3 class="text-base font-bold text-gray-900 flex items-center">
            <i class="fas fa-question-circle text-blue-600 mr-2"></i>Pertanyaan Umum (FAQ)
        </h3>

        <div class="space-y-3 text-xs" x-data="{ openFaq: null }">

            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button @click="openFaq = openFaq === 1 ? null : 1" class="w-full p-4 text-left font-semibold text-gray-800 flex justify-between items-center bg-gray-50 hover:bg-gray-100 transition-colors">
                    <span>Apakah Kode QR bisa digunakan dua kali?</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform flex-shrink-0" :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 1" class="p-4 bg-white border-t border-gray-200 text-gray-600 leading-relaxed">
                    Ya, tapi untuk tujuan berbeda. Scan <strong>pertama</strong> akan mencatat status <em>Keluar</em>. Scan <strong>kedua</strong> saat siswa kembali akan mencatat status <em>Selesai</em>. Setelah itu, QR tidak bisa digunakan lagi.
                </div>
            </div>

            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button @click="openFaq = openFaq === 2 ? null : 2" class="w-full p-4 text-left font-semibold text-gray-800 flex justify-between items-center bg-gray-50 hover:bg-gray-100 transition-colors">
                    <span>Bagaimana jika HP siswa mati/kehabisan baterai saat di pintu gerbang?</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform flex-shrink-0" :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 2" class="p-4 bg-white border-t border-gray-200 text-gray-600 leading-relaxed">
                    Siswa dapat menyebutkan Nomor Surat Dispensasi, NIS, atau Nama Lengkap kepada Satpam. Satpam akan menggunakan fitur <strong>"Verifikasi Manual"</strong> di halaman Scan untuk mencari data dan mengonfirmasi keluar/masuk secara manual.
                </div>
            </div>

            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button @click="openFaq = openFaq === 3 ? null : 3" class="w-full p-4 text-left font-semibold text-gray-800 flex justify-between items-center bg-gray-50 hover:bg-gray-100 transition-colors">
                    <span>Mengapa siswa wajib upload Foto Verifikasi saat mengajukan?</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform flex-shrink-0" :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 3" class="p-4 bg-white border-t border-gray-200 text-gray-600 leading-relaxed">
                    Untuk memastikan keamanan dan mencegah penyalahgunaan dispensasi oleh orang yang tidak berhak. Satpam akan mencocokkan wajah siswa dengan foto tersebut. <strong>Foto akan dihapus otomatis</strong> dari server setelah siswa dikonfirmasi kembali.
                </div>
            </div>

            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button @click="openFaq = openFaq === 4 ? null : 4" class="w-full p-4 text-left font-semibold text-gray-800 flex justify-between items-center bg-gray-50 hover:bg-gray-100 transition-colors">
                    <span>Apa yang terjadi jika siswa tidak kembali sebelum jam pulang sekolah?</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform flex-shrink-0" :class="openFaq === 4 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 4" class="p-4 bg-white border-t border-gray-200 text-gray-600 leading-relaxed">
                    Untuk dispensasi yang berlaku "Sampai Pulang", sistem memiliki fitur <strong>Auto-Complete</strong>. Sistem akan otomatis mengubah status menjadi <em>Selesai</em> setelah jam pulang sekolah agar data tidak menumpuk, namun siswa tetap diwajibkan melapor ke Satpam saat tiba di sekolah.
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
