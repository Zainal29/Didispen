@extends('admin.layouts.app')

@section('title', 'Bot & Log WhatsApp')

@section('content')
<div class="space-y-6">

    {{-- Header & Status Utama --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl shadow-xs border border-slate-200">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center gap-2">
                        WhatsApp Bot & Notifikasi Otomatis
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Kelola integrasi microservice Baileys, QR scanner, preferensi notifikasi, dan antrean pengiriman pesan.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <form action="{{ route('admin.whatsapp-bot.restart') }}" method="POST" onsubmit="return confirm('Restart socket Baileys?')">
                @csrf
                <button type="submit" class="px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition">
                    <i class="fas fa-sync-alt text-slate-500"></i>
                    <span>Restart Bot</span>
                </button>
            </form>

            <button type="button" onclick="document.getElementById('sectionSettings').scrollIntoView({ behavior: 'smooth' })"
                class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold flex items-center gap-1.5 transition shadow-xs">
                <i class="fas fa-cog"></i>
                <span>Pengaturan Bot</span>
            </button>
        </div>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- CARD UTAMA: KONEKSI PERANGKAT & SCAN QR CODE --}}
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden" id="qrContainerWrapper">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fas fa-qrcode text-emerald-600"></i>
                    Koneksi Perangkat WhatsApp (QR Code Scanner)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Tautkan akun WhatsApp sekolah untuk mengirim notifikasi secara otomatis.</p>
            </div>

            <div id="liveStatusBadge">
                @if($botStatus['connected'])
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Terhubung ke WhatsApp
                    </span>
                @elseif($botStatus['online'])
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Menunggu Scan QR
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Server Bot Offline
                    </span>
                @endif
            </div>
        </div>

        <div class="p-5 sm:p-6" id="qrContentArea">
            {{-- KONDISI 1: BOT SUDAH TERHUBUNG (CONNECTED) --}}
            <div id="stateConnected" class="{{ $botStatus['connected'] ? '' : 'hidden' }}">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6 p-6 rounded-2xl bg-emerald-50/60 border border-emerald-200">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-3xl shadow-sm shrink-0">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-200 text-emerald-900 mb-1">
                                <i class="fas fa-shield-alt"></i> Sesi Aktif & Siap Mengirim
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900" id="connectedPhoneText">
                                {{ $botStatus['user']['phone'] ?? 'WhatsApp Terhubung' }}
                            </h3>
                            <p class="text-xs text-slate-600 mt-0.5" id="connectedNameText">
                                {{ $botStatus['user']['name'] ?? 'Akun Bot Sekolah' }} &bull; Microservice: {{ $settings['wa_bot_url'] }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0">
                        <form action="{{ route('admin.whatsapp-bot.disconnect') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan perangkat WhatsApp ini? Anda perlu melakukan scan QR ulang.')">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-xs">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Putuskan Sesi / Ganti Nomor</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- KONDISI 2: MENUNGGU SCAN QR CODE (WAITING QR) --}}
            <div id="stateWaitingQr" class="{{ (!$botStatus['connected'] && $botStatus['online']) ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                    <div class="md:col-span-5 flex flex-col items-center justify-center p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="relative w-64 h-64 bg-white rounded-xl shadow-xs border border-slate-200 flex items-center justify-center overflow-hidden p-2">
                            <img id="qrImageElement" src="{{ $botStatus['qr_image'] ?? '' }}" alt="QR Code WhatsApp" class="w-full h-full object-contain {{ empty($botStatus['qr_image']) ? 'hidden' : '' }}">
                            <div id="qrLoadingPlaceholder" class="{{ !empty($botStatus['qr_image']) ? 'hidden' : '' }} text-center p-4">
                                <i class="fas fa-spinner fa-spin text-3xl text-emerald-600 mb-2"></i>
                                <p class="text-xs text-slate-500">Mempersiapkan QR Code...</p>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-2.5 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>QR Code auto-refresh setiap beberapa detik</span>
                        </p>
                    </div>

                    <div class="md:col-span-7 space-y-4">
                        <div class="space-y-1">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Langkah Menghubungkan</span>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Scan QR Code dengan WhatsApp di HP Anda</h3>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                Setelah terhubung, bot akan bekerja di latar belakang untuk mengirimkan notifikasi ke Guru Piket dan Siswa.
                            </p>
                        </div>

                        <ol class="space-y-2.5 text-xs text-slate-700 bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <li class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center shrink-0 text-[11px]">1</span>
                                <span>Buka aplikasi <strong>WhatsApp</strong> di smartphone Anda.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center shrink-0 text-[11px]">2</span>
                                <span>Ketuk menu titik tiga (<strong>⋮</strong>) di Android atau tab <strong>Pengaturan</strong> di iPhone.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center shrink-0 text-[11px]">3</span>
                                <span>Pilih <strong>Perangkat Tertaut (Linked Devices)</strong>, lalu ketuk tombol <strong>Tautkan Perangkat</strong>.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center shrink-0 text-[11px]">4</span>
                                <span>Arahkan kamera ke kotak QR Code di samping. Halaman ini akan otomatis berganti status saat berhasil tersambung.</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            {{-- KONDISI 3: SERVER BOT OFFLINE --}}
            <div id="stateOffline" class="{{ !$botStatus['online'] ? '' : 'hidden' }}">
                <div class="p-6 rounded-2xl bg-rose-50 border border-rose-200 text-center sm:text-left flex flex-col sm:flex-row items-center justify-between gap-5">
                    <div class="flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl shrink-0">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-rose-900">Server Microservice WhatsApp Bot Sedang Offline</h3>
                            <p class="text-xs text-rose-700 mt-1 leading-relaxed">
                                Aplikasi tidak dapat menghubungi server Baileys di <code class="bg-rose-100 px-1 py-0.5 rounded font-mono">{{ $settings['wa_bot_url'] }}</code>.
                                Pastikan server bot dijalankan menggunakan terminal.
                            </p>
                            <div class="mt-3 p-2.5 rounded-lg bg-slate-900 text-slate-100 text-xs font-mono inline-block">
                                cd whatsapp-bot && npm start
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.whatsapp-bot.restart') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shrink-0">
                            <i class="fas fa-redo"></i> Coba Hubungkan Ulang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- KARTU PENGATURAN BOT LENGKAP ("BISA DISETTING DI ADMIN SESUAI KEINGINAN") --}}
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden" id="sectionSettings">
        <div class="p-5 border-b border-slate-100">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="fas fa-sliders-h text-emerald-600"></i>
                Pengaturan Konfigurasi & Notifikasi Bot
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Atur preferensi server, kredensial API, dan pilih jenis notifikasi otomatis yang aktif.</p>
        </div>

        <form action="{{ route('admin.whatsapp-bot.settings') }}" method="POST" class="p-5 sm:p-6 space-y-6">
            @csrf

            {{-- 1. Saklar Master Bot --}}
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 flex items-center justify-between gap-4">
                <div class="space-y-0.5">
                    <label for="wa_bot_enabled" class="text-xs sm:text-sm font-bold text-slate-900 cursor-pointer">
                        Status Master Bot WhatsApp
                    </label>
                    <p class="text-xs text-slate-500">
                        Jika dimatikan, semua proses pengiriman pesan WhatsApp otomatis akan diabaikan tanpa error.
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" name="wa_bot_enabled" id="wa_bot_enabled" value="1" class="sr-only peer" @checked($settings['wa_bot_enabled'])>
                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                </label>
            </div>

            {{-- 2. Konfigurasi Server & Kredensial --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="wa_bot_url" class="block text-xs font-bold text-slate-700 mb-1.5">
                        URL Server Microservice Baileys <span class="text-rose-500">*</span>
                    </label>
                    <input type="url" name="wa_bot_url" id="wa_bot_url" value="{{ old('wa_bot_url', $settings['wa_bot_url']) }}" required
                        placeholder="http://127.0.0.1:3789"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition">
                    <p class="text-[11px] text-slate-500 mt-1">Default lokal: <code>http://127.0.0.1:3789</code></p>
                </div>

                <div>
                    <label for="wa_bot_api_key" class="block text-xs font-bold text-slate-700 mb-1.5">
                        API Key Server Bot <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="wa_bot_api_key" id="wa_bot_api_key" value="{{ old('wa_bot_api_key', $settings['wa_bot_api_key']) }}" required
                        placeholder="sipintu_wa_secret_key_2026"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition">
                    <p class="text-[11px] text-slate-500 mt-1">Harus sama persis dengan yang dikonfigurasi di file <code>whatsapp-bot/.env</code></p>
                </div>
            </div>

            {{-- 3. Saklar Notifikasi Otomatis Spesifik --}}
            <div class="space-y-3 pt-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Pilihan Notifikasi Otomatis</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    {{-- Opsi A: Guru Piket Bertugas --}}
                    <label class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer transition flex items-start gap-3">
                        <input type="checkbox" name="wa_notify_piket_bertugas" value="1" class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300" @checked($settings['wa_notify_piket_bertugas'])>
                        <div class="space-y-1">
                            <span class="block text-xs font-bold text-slate-900">Guru Piket Sedang Bertugas</span>
                            <span class="block text-[11px] text-slate-500 leading-normal">Kirim WA otomatis saat sesi piket dimulai ke nomor guru yang bertugas aktif.</span>
                        </div>
                    </label>

                    {{-- Opsi B: Peringatan Siswa Terlambat --}}
                    <label class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer transition flex items-start gap-3">
                        <input type="checkbox" name="wa_notify_siswa_terlambat" value="1" class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300" @checked($settings['wa_notify_siswa_terlambat'])>
                        <div class="space-y-1">
                            <span class="block text-xs font-bold text-slate-900">Peringatan Siswa Terlambat</span>
                            <span class="block text-[11px] text-slate-500 leading-normal">Kirim WA peringatan langsung ke siswa yang melewati batas waktu kembali izin.</span>
                        </div>
                    </label>

                    {{-- Opsi C: Laporan ke Guru Piket --}}
                    <label class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer transition flex items-start gap-3">
                        <input type="checkbox" name="wa_notify_laporan_guru_piket" value="1" class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300" @checked($settings['wa_notify_laporan_guru_piket'])>
                        <div class="space-y-1">
                            <span class="block text-xs font-bold text-slate-900">Laporan Rekap ke Guru Piket</span>
                            <span class="block text-[11px] text-slate-500 leading-normal">Kirim rekap data siswa terlambat ke nomor Guru Piket untuk ditindaklanjuti.</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold transition flex items-center gap-2 shadow-xs">
                    <i class="fas fa-save"></i>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </form>
    </div>

    {{-- KARTU STATISTIK PENGIRIMAN --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Total Notifikasi</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-emerald-200 shadow-xs bg-emerald-50/20">
            <p class="text-xs text-emerald-700 uppercase font-bold tracking-wider">Terkirim (Sent)</p>
            <p class="text-2xl font-bold text-emerald-700 mt-1">{{ number_format($stats['sent']) }}</p>
        </div>
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-amber-200 shadow-xs bg-amber-50/20">
            <p class="text-xs text-amber-700 uppercase font-bold tracking-wider">Antrean (Pending)</p>
            <p class="text-2xl font-bold text-amber-700 mt-1">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-rose-200 shadow-xs bg-rose-50/20">
            <p class="text-xs text-rose-700 uppercase font-bold tracking-wider">Gagal (Failed)</p>
            <p class="text-2xl font-bold text-rose-700 mt-1">{{ number_format($stats['failed']) }}</p>
        </div>
    </div>

    {{-- FORM TES KIRIM PESAN WA --}}
    <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200">
        <h2 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
            <i class="fas fa-paper-plane text-emerald-600"></i>
            Uji Coba Kirim Pesan WhatsApp Langsung
        </h2>
        <form action="{{ route('admin.whatsapp-bot.send-test') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-3">
            @csrf
            <div class="md:col-span-3">
                <input type="text" name="phone" placeholder="No. WA (cth: 08123456789)" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition">
            </div>
            <div class="md:col-span-7">
                <input type="text" name="message" placeholder="Isi pesan uji coba..." value="Halo, ini pesan uji coba otomatis dari Bot WhatsApp DIDISPEN SMKN 1 Bangsri." required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="w-full px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-2">
                    <i class="fas fa-paper-plane"></i> Kirim Tes
                </button>
            </div>
        </form>
    </div>

    {{-- FILTER & TABEL RIWAYAT PENGIRIMAN --}}
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / nomor HP..."
                    class="px-3.5 py-1.5 rounded-xl border border-slate-300 text-xs">
                <select name="status" class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
                    <option value="">Semua Status</option>
                    <option value="sent" @selected(request('status') === 'sent')>Terkirim (Sent)</option>
                    <option value="pending" @selected(request('status') === 'pending')>Antrean (Pending)</option>
                    <option value="failed" @selected(request('status') === 'failed')>Gagal (Failed)</option>
                </select>
                <select name="context" class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
                    <option value="">Semua Konteks</option>
                    <option value="piket_aktif" @selected(request('context') === 'piket_aktif')>Guru Piket Aktif</option>
                    <option value="siswa_terlambat_overdue" @selected(request('context') === 'siswa_terlambat_overdue')>Siswa Overdue</option>
                    <option value="siswa_terlambat_returned_late" @selected(request('context') === 'siswa_terlambat_returned_late')>Siswa Kembali Terlambat</option>
                    <option value="siswa_terlambat_not_returned" @selected(request('context') === 'siswa_terlambat_not_returned')>Siswa Tidak Kembali</option>
                    <option value="laporan_piket_overdue" @selected(request('context') === 'laporan_piket_overdue')>Laporan ke Guru Piket</option>
                    <option value="test_admin" @selected(request('context') === 'test_admin')>Uji Coba Admin</option>
                </select>
                <button type="submit" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </form>

            @if($stats['failed'] > 0)
                <form action="{{ route('admin.whatsapp-bot.retry-all') }}" method="POST" onsubmit="return confirm('Kirim ulang semua pesan gagal?')">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 transition">
                        <i class="fas fa-redo"></i> Kirim Ulang Gagal ({{ $stats['failed'] }})
                    </button>
                </form>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-xs sm:text-sm">
                <thead class="bg-slate-50/70">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold text-slate-700">Waktu</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-700">Penerima</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-700">Kategori</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-700">Isi Pesan / Keterangan</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-700">Status</th>
                        <th class="px-4 py-3 text-right font-bold text-slate-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-500">
                                <div class="font-medium text-slate-700">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                                @if($log->sent_at)
                                    <div class="text-[11px] text-emerald-600 font-semibold">Terkirim: {{ $log->sent_at->format('H:i:s') }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $log->recipient_name ?? '-' }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $log->phone ?? 'Nomor Kosong' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 text-[11px] rounded-md bg-slate-100 text-slate-700 font-mono font-semibold">
                                    {{ $log->context }}
                                </span>
                            </td>
                            <td class="px-4 py-3 max-w-md">
                                <div class="text-xs text-slate-700 whitespace-pre-line line-clamp-2" title="{{ $log->message }}">
                                    {{ \Illuminate\Support\Str::limit($log->message, 120) }}
                                </div>
                                @if($log->error_message)
                                    <div class="text-xs text-rose-600 mt-1 font-semibold flex items-start gap-1">
                                        <i class="fas fa-exclamation-circle mt-0.5"></i>
                                        <span>{{ $log->error_message }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($log->status === 'sent')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">Terkirim</span>
                                @elseif($log->status === 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">Antrean</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">Gagal</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @if($log->status !== 'sent' && $log->phone)
                                    <form action="{{ route('admin.whatsapp-bot.retry', $log) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition">
                                            <i class="fas fa-redo mr-1"></i>Retry
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs sm:text-sm">
                                Belum ada riwayat pengiriman pesan WhatsApp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>
</div>

{{-- SCRIPT LIVE POLLING STATUS & QR CODE BAILEYS --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusUrl = "{{ route('admin.whatsapp-bot.status') }}";
    const stateConnected = document.getElementById('stateConnected');
    const stateWaitingQr = document.getElementById('stateWaitingQr');
    const stateOffline = document.getElementById('stateOffline');
    const qrImageElement = document.getElementById('qrImageElement');
    const qrLoadingPlaceholder = document.getElementById('qrLoadingPlaceholder');
    const connectedPhoneText = document.getElementById('connectedPhoneText');
    const connectedNameText = document.getElementById('connectedNameText');
    const liveStatusBadge = document.getElementById('liveStatusBadge');

    let isPolling = true;

    async function pollBotStatus() {
        if (!isPolling) return;

        try {
            const res = await fetch(statusUrl, {
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) {
                renderOffline();
                return;
            }

            const data = await res.json();

            if (!data.online) {
                renderOffline();
            } else if (data.connected) {
                renderConnected(data.user);
            } else {
                renderWaitingQr(data.qr_image);
            }
        } catch (e) {
            renderOffline();
        }
    }

    function renderConnected(user) {
        stateConnected.classList.remove('hidden');
        stateWaitingQr.classList.add('hidden');
        stateOffline.classList.add('hidden');

        if (user) {
            connectedPhoneText.textContent = user.phone ? ('+' + user.phone) : 'WhatsApp Terhubung';
            connectedNameText.textContent = (user.name || 'Akun Bot') + ' • Sesi Baileys Aktif';
        }

        liveStatusBadge.innerHTML = `
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Terhubung ke WhatsApp
            </span>
        `;
    }

    function renderWaitingQr(qrImage) {
        stateConnected.classList.add('hidden');
        stateWaitingQr.classList.remove('hidden');
        stateOffline.classList.add('hidden');

        if (qrImage) {
            qrImageElement.src = qrImage;
            qrImageElement.classList.remove('hidden');
            qrLoadingPlaceholder.classList.add('hidden');
        } else {
            qrImageElement.classList.add('hidden');
            qrLoadingPlaceholder.classList.remove('hidden');
        }

        liveStatusBadge.innerHTML = `
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                Menunggu Scan QR
            </span>
        `;
    }

    function renderOffline() {
        stateConnected.classList.add('hidden');
        stateWaitingQr.classList.add('hidden');
        stateOffline.classList.remove('hidden');

        liveStatusBadge.innerHTML = `
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                Server Bot Offline
            </span>
        `;
    }

    // Interval polling setiap 4 detik
    setInterval(pollBotStatus, 4000);
});
</script>
@endpush
@endsection