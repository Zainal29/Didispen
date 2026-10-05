@php
    $role = auth()->user()->role ?? 'siswa';
    $layout = match($role) {
        'guru' => 'guru.layouts.app',
        'satpam' => 'satpam.layouts.app',
        'admin' => 'admin.layouts.app',
        default => 'siswa.layouts.app',
    };

    // Ambil video dari database sesuai role aktif
    $video = \App\Models\TutorialVideo::where('role', $role)->where('is_active', true)->first();
@endphp

@extends($layout)

@section('title', 'Panduan Penggunaan')
@section('page-title', 'Panduan Penggunaan')

@section('content')
<div class="max-w-5xl mx-auto space-y-5" x-data="{ openFaq: null }">

    {{-- HEADER BERSIH & FORMAL (TANPA GRADIENT) --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-gray-100 border border-gray-200 text-gray-700 flex items-center justify-center flex-shrink-0 text-lg">
                @if($role === 'siswa')
                    <i class="fas fa-user-graduate text-blue-600"></i>
                @elseif($role === 'guru')
                    <i class="fas fa-user-shield text-indigo-600"></i>
                @elseif($role === 'satpam')
                    <i class="fas fa-shield-alt text-red-600"></i>
                @else
                    <i class="fas fa-user-cog text-slate-800"></i>
                @endif
            </div>
            <div>
                <h1 class="text-base sm:text-xl font-bold text-gray-900">
                    @if($role === 'siswa')
                        Panduan Penggunaan Siswa
                    @elseif($role === 'guru')
                        Panduan Penggunaan Guru Piket
                    @elseif($role === 'satpam')
                        Panduan Penggunaan Satpam (Pos Gerbang)
                    @else
                        Panduan Penggunaan Administrator
                    @endif
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    Petunjuk langkah demi langkah pengelolaan dispensasi digital di SMKN 1 Bangsri.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            @if($video)
            <button type="button" onclick="openVideoModal('{{ $video->youtube_url }}', '{{ $video->title }}')"
               class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-semibold transition-colors active:scale-95 shadow-xs">
                <i class="fab fa-youtube mr-1.5 text-sm"></i> Video Tutorial
            </button>
            @endif
            <a href="#tanya-jawab" class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition-colors">
                <i class="fas fa-question-circle mr-1.5 text-gray-500"></i> FAQ
            </a>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- 1. PANDUAN KHUSUS ROLE: SISWA                            --}}
    {{-- ======================================================== --}}
    @if($role === 'siswa')
        {{-- Ringkasan Alur --}}
        <div class="bg-blue-50/70 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center flex-shrink-0 text-sm mt-0.5">
                <i class="fas fa-route"></i>
            </div>
            <div class="text-xs text-blue-900 leading-relaxed">
                <span class="font-bold">Alur Pengajuan Siswa:</span>
                <span class="text-blue-800">1. Isi Form Pengajuan (Foto Bukti) &rarr; 2. Verifikasi Guru Piket &rarr; 3. QR Code Terbit &rarr; 4. Satpam Scan Keluar & Ambil Foto di Pos Gerbang &rarr; 5. Satpam Scan Masuk saat Siswa Kembali (Selesai).</span>
            </div>
        </div>

        {{-- Kartu Langkah-langkah --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Langkah 1 --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 flex flex-col justify-between space-y-4 hover:border-gray-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                        <span class="w-7 h-7 rounded-lg bg-gray-100 text-gray-800 font-bold text-xs flex items-center justify-center">1</span>
                        <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-bold">Status: Menunggu</span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm">Buat Pengajuan Dispensasi</h3>
                    <ul class="text-xs text-gray-600 space-y-1.5 leading-relaxed">
                        <li>• Pilih Kategori: <strong>Sakit, Izin Pribadi, Tugas Dinas, atau Lomba</strong>.</li>
                        <li>• Tentukan <strong>Jam Keluar</strong> dan perkiraan <strong>Jam Kembali</strong>.</li>
                        <li>• Tuliskan alasan dispensasi dan lokasi tujuan secara jelas.</li>
                        <li>• <strong>Unggah Foto Bukti</strong> (surat izin orang tua / surat tugas sekolah).</li>
                        <li>• Untuk izin bersama lebih dari 1 siswa, gunakan opsi <strong>Rombongan</strong>.</li>
                    </ul>
                </div>
                <a href="{{ route('siswa.pengajuan.create') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-colors shadow-xs">
                    Buat Pengajuan <i class="fas fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
            </div>

            {{-- Langkah 2 --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 flex flex-col justify-between space-y-4 hover:border-gray-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                        <span class="w-7 h-7 rounded-lg bg-gray-100 text-gray-800 font-bold text-xs flex items-center justify-center">2</span>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold">Status: Disetujui</span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm">Pantau Status & Ambil QR Code</h3>
                    <ul class="text-xs text-gray-600 space-y-1.5 leading-relaxed">
                        <li>• Guru Piket yang sedang bertugas hari ini akan memvalidasi pengajuan.</li>
                        <li>• Jika mendesak, gunakan tombol <strong>"Hubungi Guru Piket via WhatsApp"</strong> di Dashboard.</li>
                        <li>• Jika disetujui, buka menu <strong>Riwayat</strong> & klik <strong>"Lihat Tiket / QR Code"</strong>.</li>
                        <li>• Jika ditolak, Anda dapat membaca <strong>Catatan Alasan Penolakan</strong> dari guru piket.</li>
                    </ul>
                </div>
                <a href="{{ route('siswa.pengajuan.index') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold transition-colors">
                    Lihat Riwayat & QR <i class="fas fa-qrcode ml-1.5 text-[10px]"></i>
                </a>
            </div>

            {{-- Langkah 3 --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 flex flex-col justify-between space-y-4 hover:border-gray-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                        <span class="w-7 h-7 rounded-lg bg-gray-100 text-gray-800 font-bold text-xs flex items-center justify-center">3</span>
                        <span class="px-2 py-0.5 rounded-full bg-sky-50 text-sky-800 border border-sky-200 text-[10px] font-bold">Status: Keluar & Selesai</span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm">Validasi di Pos Gerbang Satpam</h3>
                    <ul class="text-xs text-gray-600 space-y-1.5 leading-relaxed">
                        <li>• <strong>Saat Keluar</strong>: Tunjukkan QR Code di layar HP ke Satpam. Satpam akan mengambil foto verifikasi di pos gerbang.</li>
                        <li>• <strong>Saat Kembali</strong>: Pindai kembali QR Code ke Satpam agar status ditandai <em>Selesai</em>.</li>
                        <li>• ⚠️ Harap kembali tepat waktu agar tidak terdeteksi <strong>Overdue (Terlambat)</strong> oleh sistem.</li>
                    </ul>
                </div>
                <div class="p-2.5 bg-gray-50 rounded-xl text-[11px] text-gray-600 font-medium border border-gray-200 text-center">
                    <i class="fas fa-shield-alt text-amber-600 mr-1"></i>QR Code hanya berlaku untuk 1x sesi izin
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- 2. PANDUAN KHUSUS ROLE: GURU PIKET                       --}}
    {{-- ======================================================== --}}
    @if($role === 'guru')
        {{-- Ringkasan Alur --}}
        <div class="bg-indigo-50/70 border border-indigo-200 rounded-xl p-4 flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center flex-shrink-0 text-sm mt-0.5">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="text-xs text-indigo-900 leading-relaxed">
                <span class="font-bold">Alur Kerja Guru Piket:</span>
                <span class="text-indigo-800">1. Review Antrean Izin &rarr; 2. Setujui atau Tolak Berargumen &rarr; 3. Opsi Cetak Struk Bluetooth 58mm / PDF &rarr; 4. Guru Checklog Tugas Luar &rarr; 5. Tukar Jadwal Piket (Shift Swap).</span>
            </div>
        </div>

        {{-- Kartu Langkah-langkah Guru --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- 1. Verifikasi --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 flex flex-col justify-between space-y-4 hover:border-gray-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-7 h-7 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs"><i class="fas fa-file-signature"></i></div>
                        <h3 class="font-bold text-gray-900 text-sm">1. Verifikasi Izin</h3>
                    </div>
                    <ol class="space-y-1.5 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Buka menu <strong>"Verifikasi"</strong> di antrean status <em>Menunggu</em>.</li>
                        <li>Periksa alasan, jam KBM, dan foto surat pendukung.</li>
                        <li>Klik <strong>"Setujui"</strong> (sistem otomatis menerbitkan QR token & notif WA) atau <strong>"Tolak"</strong> (wajib isi alasan).</li>
                    </ol>
                </div>
                <a href="{{ route('guru.pengajuan.index') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-colors shadow-xs">
                    Menu Verifikasi <i class="fas fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
            </div>

            {{-- 2. Cetak Struk --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 flex flex-col justify-between space-y-4 hover:border-gray-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-7 h-7 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs"><i class="fas fa-print"></i></div>
                        <h3 class="font-bold text-gray-900 text-sm">2. Cetak Struk 58mm</h3>
                    </div>
                    <ol class="space-y-1.5 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Untuk siswa tanpa HP atau arsip meja pos gerbang.</li>
                        <li><strong>Cetak Bluetooth (BLE)</strong>: Kirim data ESC/POS langsung ke printer thermal portable.</li>
                        <li><strong>Unduh PDF</strong>: Berformat 58mm untuk dicetak via komputer.</li>
                    </ol>
                </div>
                <div class="p-2.5 bg-gray-50 rounded-xl text-[11px] text-gray-600 font-medium text-center border border-gray-200">
                    <i class="fab fa-bluetooth-b mr-1 text-blue-600"></i>Mendukung Mini Printer Kasir 58mm
                </div>
            </div>

            {{-- 3. Dispensasi Mandiri --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 flex flex-col justify-between space-y-4 hover:border-gray-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-7 h-7 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs"><i class="fas fa-plus-circle"></i></div>
                        <h3 class="font-bold text-gray-900 text-sm">3. Dispensasi Mandiri</h3>
                    </div>
                    <ol class="space-y-1.5 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Digunakan jika guru membawa siswa keluar untuk tugas dinas mendadak.</li>
                        <li>Cari siswa via NIS/Nama, tentukan alasan dan jam izin.</li>
                        <li>Pengajuan <strong>langsung Disetujui</strong> otomatis dan siap di-scan Satpam.</li>
                    </ol>
                </div>
                <a href="{{ route('guru.pengajuan.create') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold transition-colors">
                    Buat Mandiri <i class="fas fa-plus ml-1.5 text-[10px]"></i>
                </a>
            </div>

            {{-- 4. Checklog & Tukar Jadwal --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 flex flex-col justify-between space-y-4 hover:border-gray-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-7 h-7 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs"><i class="fas fa-exchange-alt"></i></div>
                        <h3 class="font-bold text-gray-900 text-sm">4. Checklog & Tukar Jadwal</h3>
                    </div>
                    <ol class="space-y-1.5 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li><strong>Guru Checklog</strong>: Catat saat keluar tugas luar & konfirmasi kembali standby.</li>
                        <li><strong>Tukar Jadwal Piket</strong>: Ajukan swap jadwal ke guru pengganti jika berhalangan piket.</li>
                    </ol>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('guru.checklog.index') }}" class="flex-1 inline-flex items-center justify-center px-2 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-colors">Checklog</a>
                    <a href="{{ route('guru.piket.swap.create') }}" class="flex-1 inline-flex items-center justify-center px-2 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-colors">Tukar Piket</a>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- 3. PANDUAN KHUSUS ROLE: SATPAM (POS GERBANG)              --}}
    {{-- ======================================================== --}}
    @if($role === 'satpam')
        {{-- Ringkasan Alur --}}
        <div class="bg-red-50/70 border border-red-200 rounded-xl p-4 flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-red-100 text-red-700 flex items-center justify-center flex-shrink-0 text-sm mt-0.5">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="text-xs text-red-900 leading-relaxed">
                <span class="font-bold">Alur Pemeriksaan Pos Gerbang:</span>
                <span class="text-red-800">1. Pindai QR Siswa Keluar &rarr; 2. Ambil Foto Verifikasi Gerbang &rarr; 3. Konfirmasi Keluar &rarr; 4. Pindai QR Kembali saat Siswa Tiba &rarr; 5. Deteksi Otomatis Keterlambatan (Overdue).</span>
            </div>
        </div>

        {{-- Kartu Langkah-langkah Satpam --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- 1. Scan Keluar & Foto --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 flex flex-col justify-between space-y-4 hover:border-gray-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-7 h-7 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs"><i class="fas fa-qrcode"></i></div>
                        <h3 class="font-bold text-gray-900 text-sm">1. Scan Keluar & Foto Gerbang</h3>
                    </div>
                    <ol class="space-y-1.5 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Buka menu <strong>"Scan QR"</strong> (pastikan izin kamera aktif).</li>
                        <li>Arahkan kamera ke QR Code di HP siswa atau struk thermal.</li>
                        <li>Periksa data siswa dan batas jam kembali.</li>
                        <li><strong>Ambil Foto Siswa di Pos</strong> sebagai bukti otentik keberangkatan.</li>
                        <li>Klik <strong>"Konfirmasi Keluar"</strong> & izinkan siswa lewat gerbang.</li>
                    </ol>
                </div>
                <a href="{{ route('satpam.scan') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition-colors shadow-xs">
                    Buka Scanner QR <i class="fas fa-camera ml-1.5 text-[10px]"></i>
                </a>
            </div>

            {{-- 2. Scan Kembali & Overdue --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 flex flex-col justify-between space-y-4 hover:border-gray-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-7 h-7 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs"><i class="fas fa-history"></i></div>
                        <h3 class="font-bold text-gray-900 text-sm">2. Scan Kembali & Cek Overdue</h3>
                    </div>
                    <ol class="space-y-1.5 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Saat siswa kembali ke sekolah, pindai kembali QR Code siswa.</li>
                        <li><strong>Tepat Waktu</strong>: Layar hijau &rarr; klik <em>"Konfirmasi Masuk"</em> (status selesai).</li>
                        <li><strong>Terlambat (Overdue)</strong>: Layar merah/kuning &rarr; sistem menghitung menit telat & otomatis mengirim alert WhatsApp ke Guru Piket.</li>
                    </ol>
                </div>
                <a href="{{ route('satpam.dashboard') }}#tab-keluar" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold transition-colors">
                    Pantau Siswa Sedang Keluar <i class="fas fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
            </div>

            {{-- 3. Verifikasi Manual & Guru Piket --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 flex flex-col justify-between space-y-4 hover:border-gray-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-7 h-7 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs"><i class="fas fa-search"></i></div>
                        <h3 class="font-bold text-gray-900 text-sm">3. Verifikasi Manual & Kontak</h3>
                    </div>
                    <ol class="space-y-1.5 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Jika kamera bermasalah atau HP siswa habis baterai, gunakan tab <strong>"Verifikasi Manual"</strong> (cari via Nomor Surat / NIS / Nama).</li>
                        <li>Gunakan kartu <strong>"Guru Piket Hari Ini"</strong> di Dashboard untuk melihat petugas piket sesi aktif guna koordinasi gerbang.</li>
                    </ol>
                </div>
                <div class="p-2.5 bg-gray-50 rounded-xl text-[11px] text-gray-600 font-medium border border-gray-200 text-center">
                    <i class="fas fa-user-shield text-gray-500 mr-1"></i>Koordinasi Meja Piket & Pos Gerbang
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- 4. PANDUAN KHUSUS ROLE: ADMINISTRATOR                    --}}
    {{-- ======================================================== --}}
    @if($role === 'admin')
        {{-- Ringkasan Alur --}}
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-slate-800 text-white flex items-center justify-center flex-shrink-0 text-sm mt-0.5">
                <i class="fas fa-user-cog"></i>
            </div>
            <div class="text-xs text-slate-800 leading-relaxed">
                <span class="font-bold">Ruang Lingkup Administrator:</span>
                <span class="text-slate-700">1. Master Data & Penjadwalan Sesi Piket &rarr; 2. Sinkronisasi SiPintu Gateway &rarr; 3. WhatsApp Gateway & Template Pesan &rarr; 4. Audit Trail Keamanan & Ekspor Laporan.</span>
            </div>
        </div>

        {{-- Kartu Langkah-langkah Admin --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- 1. Master Data --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 flex flex-col justify-between space-y-4 hover:border-gray-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-7 h-7 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs"><i class="fas fa-database"></i></div>
                        <h3 class="font-bold text-gray-900 text-sm">1. Master Data & Sesi</h3>
                    </div>
                    <ol class="space-y-1.5 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Kelola akun <strong>Siswa, Guru, Satpam, Kelas, & Jurusan</strong>.</li>
                        <li>Atur <strong>Jadwal Guru Piket</strong> mingguan (Senin-Jumat) beserta sesi jam tugas & guru koordinator.</li>
                    </ol>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.siswa.index') }}" class="flex-1 inline-flex items-center justify-center px-2 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold transition-colors">Siswa</a>
                    <a href="{{ route('admin.guru.index') }}" class="flex-1 inline-flex items-center justify-center px-2 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold transition-colors">Guru</a>
                </div>
            </div>

            {{-- 2. SiPintu --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 flex flex-col justify-between space-y-4 hover:border-gray-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-7 h-7 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs"><i class="fas fa-cloud-arrow-down"></i></div>
                        <h3 class="font-bold text-gray-900 text-sm">2. SiPintu Gateway</h3>
                    </div>
                    <ol class="space-y-1.5 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Sistem terintegrasi secara <strong>SSO Hybrid</strong> dengan SiPintu SMKN 1 Bangsri.</li>
                        <li>Gunakan menu sinkronisasi untuk menarik data siswa dan guru terbaru secara otomatis.</li>
                    </ol>
                </div>
                <a href="{{ route('admin.settings.index') }}" class="w-full inline-flex items-center justify-center px-3 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold transition-colors">
                    Pengaturan SiPintu <i class="fas fa-cog ml-1.5 text-[10px]"></i>
                </a>
            </div>

            {{-- 3. WhatsApp Template --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 flex flex-col justify-between space-y-4 hover:border-gray-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-7 h-7 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs"><i class="fab fa-whatsapp"></i></div>
                        <h3 class="font-bold text-gray-900 text-sm">3. WhatsApp Template</h3>
                    </div>
                    <ol class="space-y-1.5 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Konfigurasi token Fonnte WhatsApp Gateway.</li>
                        <li>Kustomisasi template pesan otomatis: <em>Terlambat</em>, <em>Disetujui</em>, <em>Ditolak</em>, dan <em>Keluar</em>.</li>
                    </ol>
                </div>
                <a href="{{ route('admin.whatsapp-templates.index') }}" class="w-full inline-flex items-center justify-center px-3 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold transition-colors">
                    Template WhatsApp <i class="fab fa-whatsapp ml-1.5 text-xs"></i>
                </a>
            </div>

            {{-- 4. Audit & Laporan --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 flex flex-col justify-between space-y-4 hover:border-gray-300 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center space-x-2.5 border-b border-gray-100 pb-2.5">
                        <div class="w-7 h-7 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs"><i class="fas fa-shield-halved"></i></div>
                        <h3 class="font-bold text-gray-900 text-sm">4. Audit Log & Rekap</h3>
                    </div>
                    <ol class="space-y-1.5 text-xs text-gray-600 list-decimal list-inside leading-relaxed">
                        <li>Pantau rekam jejak aktivitas, IP address & proteksi gagal login brute force.</li>
                        <li>Ekspor data rekapitulasi dispensasi ke format <strong>Excel (.xlsx)</strong> atau <strong>PDF</strong>.</li>
                    </ol>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.semua.pengajuan') }}" class="flex-1 inline-flex items-center justify-center px-2 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold transition-colors">Laporan</a>
                    <a href="{{ route('admin.audit.index') }}" class="flex-1 inline-flex items-center justify-center px-2 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold transition-colors">Audit</a>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- PERTANYAAN UMUM (FAQ) — BERSIH TANPA GRADIENT            --}}
    {{-- ======================================================== --}}
    <div id="tanya-jawab" class="bg-white rounded-2xl border border-gray-200 shadow-xs p-5 sm:p-6 space-y-3">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-sm sm:text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-question-circle text-gray-500"></i>Pertanyaan Umum (FAQ)
            </h3>
            <span class="text-[11px] text-gray-400 font-medium">Bantuan Cepat</span>
        </div>

        <div class="space-y-2 text-xs">
            {{-- Q1 --}}
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <button type="button" @click="openFaq = openFaq === 1 ? null : 1" class="w-full p-3.5 text-left font-semibold text-gray-800 flex justify-between items-center bg-gray-50/70 hover:bg-gray-100 transition-colors">
                    <span>Apakah Kode QR dispensasi bisa digunakan berulang kali?</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform flex-shrink-0" :class="openFaq === 1 ? 'rotate-180 text-gray-700' : ''"></i>
                </button>
                <div x-show="openFaq === 1" x-cloak class="p-3.5 bg-white border-t border-gray-100 text-gray-600 leading-relaxed">
                    Tidak bisa. Kode QR didesain dengan prinsip <strong>Single-Use Lifecycle</strong>. Scan pertama di pos Satpam mencatat status <em>Keluar</em>. Scan kedua saat siswa tiba kembali mencatat status <em>Selesai</em>. Setelah berstatus selesai, kode QR tidak dapat digunakan lagi.
                </div>
            </div>

            {{-- Q2 --}}
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <button type="button" @click="openFaq = openFaq === 2 ? null : 2" class="w-full p-3.5 text-left font-semibold text-gray-800 flex justify-between items-center bg-gray-50/70 hover:bg-gray-100 transition-colors">
                    <span>Bagaimana jika HP siswa mati / baterai habis saat di pos gerbang Satpam?</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform flex-shrink-0" :class="openFaq === 2 ? 'rotate-180 text-gray-700' : ''"></i>
                </button>
                <div x-show="openFaq === 2" x-cloak class="p-3.5 bg-white border-t border-gray-100 text-gray-600 leading-relaxed">
                    Siswa cukup menyebutkan <strong>Nomor Surat Dispensasi, NIS, atau Nama Lengkap</strong> kepada petugas Satpam. Satpam memiliki fitur <strong>"Verifikasi Manual"</strong> di menu scan untuk mencari berkas izin dan mengonfirmasi keluar/kembali secara langsung. Selain itu, siswa juga bisa membawa struk thermal 58mm yang dicetak oleh Guru Piket.
                </div>
            </div>

            {{-- Q3 --}}
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <button type="button" @click="openFaq = openFaq === 3 ? null : 3" class="w-full p-3.5 text-left font-semibold text-gray-800 flex justify-between items-center bg-gray-50/70 hover:bg-gray-100 transition-colors">
                    <span>Mengapa Satpam mengambil foto siswa di pos gerbang saat keluar?</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform flex-shrink-0" :class="openFaq === 3 ? 'rotate-180 text-gray-700' : ''"></i>
                </button>
                <div x-show="openFaq === 3" x-cloak class="p-3.5 bg-white border-t border-gray-100 text-gray-600 leading-relaxed">
                    Pengambilan foto fisik di pos gerbang berfungsi sebagai validasi keamanan otentik (<em>anti-fraud</em>). Foto ini membuktikan bahwa siswa yang keluar benar-benar pemilik izin dengan atribut seragam lengkap, sekaligus mencegah peminjaman tiket QR oleh orang lain.
                </div>
            </div>

            {{-- Q4 --}}
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <button type="button" @click="openFaq = openFaq === 4 ? null : 4" class="w-full p-3.5 text-left font-semibold text-gray-800 flex justify-between items-center bg-gray-50/70 hover:bg-gray-100 transition-colors">
                    <span>Bagaimana sistem mendeteksi siswa yang terlambat kembali (Overdue)?</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform flex-shrink-0" :class="openFaq === 4 ? 'rotate-180 text-gray-700' : ''"></i>
                </button>
                <div x-show="openFaq === 4" x-cloak class="p-3.5 bg-white border-t border-gray-100 text-gray-600 leading-relaxed">
                    Sistem secara otomatis menghitung selisih waktu antara batas waktu kembali dengan jam aktual scan di gerbang. Jika melewati batas waktu, layar scanner Satpam menampilkan warna merah dengan rincian total menit keterlambatan, menandai dispensasi sebagai *terlambat*, dan mengirimkan notifikasi peringatan via WhatsApp.
                </div>
            </div>

            {{-- Q5 --}}
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <button type="button" @click="openFaq = openFaq === 5 ? null : 5" class="w-full p-3.5 text-left font-semibold text-gray-800 flex justify-between items-center bg-gray-50/70 hover:bg-gray-100 transition-colors">
                    <span>Bagaimana cara mencetak bukti izin ke Printer Bluetooth Thermal 58mm?</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform flex-shrink-0" :class="openFaq === 5 ? 'rotate-180 text-gray-700' : ''"></i>
                </button>
                <div x-show="openFaq === 5" x-cloak class="p-3.5 bg-white border-t border-gray-100 text-gray-600 leading-relaxed">
                    Guru Piket cukup membuka permohonan yang telah disetujui, menyalakan Bluetooth di laptop/HP, lalu mengklik tombol <strong>"Cetak Bluetooth"</strong>. Browser Chrome/Edge akan memindai printer thermal kasir (ESC/POS 58mm) dan mencetak struk fisik lengkap dengan barcode/QR dalam hitungan detik tanpa perlu instalasi driver tambahan.
                </div>
            </div>
        </div>
    </div>

</div>

{{-- MODAL VIDEO TUTORIAL --}}
<div id="videoModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="video-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:block sm:p-0">
        {{-- Overlay --}}
        <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeVideoModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        {{-- Modal Panel --}}
        <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full">
            {{-- Header --}}
            <div class="bg-gray-50 px-4 py-3 flex items-center justify-between border-b border-gray-200">
                <h3 class="text-base font-bold text-gray-900" id="videoModalTitle">Video Tutorial</h3>
                <button type="button" onclick="closeVideoModal()" class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-gray-600 rounded-xl hover:bg-gray-100 transition-colors" aria-label="Tutup Modal">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            {{-- Video Container --}}
            <div class="relative pb-[56.25%] bg-black">
                <iframe id="videoFrame" class="absolute top-0 left-0 w-full h-full"
                        src=""
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                </iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openVideoModal(youtubeUrl, title) {
    const modal = document.getElementById('videoModal');
    const frame = document.getElementById('videoFrame');
    const titleEl = document.getElementById('videoModalTitle');

    const videoId = extractVideoId(youtubeUrl);

    if (videoId) {
        titleEl.textContent = title;
        frame.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0`;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } else {
        alert('Link video YouTube tidak valid');
    }
}

function closeVideoModal() {
    const modal = document.getElementById('videoModal');
    const frame = document.getElementById('videoFrame');

    frame.src = '';
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

function extractVideoId(url) {
    const match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/);
    return match ? match[1] : null;
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeVideoModal();
    }
});
</script>
@endpush
