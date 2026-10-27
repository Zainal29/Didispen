@extends('admin.layouts.app')

@section('title', 'Bot & Log WhatsApp')

@section('content')
<div class="space-y-6">

    {{-- Header & Status Utama --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl shadow-xs border border-slate-200">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200/70 text-blue-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ti ti-brand-whatsapp"></i>
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
            {{-- Tombol Cepat ON / OFF Bot --}}
            <form action="{{ route('admin.whatsapp-bot.toggle') }}" method="POST">
                @csrf
                @if($settings['wa_bot_enabled'])
                    <button type="submit" class="uiverse-btn-tactile px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold flex items-center gap-1.5 transition" title="Klik untuk mematikan bot">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <i class="ti ti-power"></i>
                        <span>Bot: ON (Matikan)</span>
                    </button>
                @else
                    <button type="submit" class="uiverse-btn-tactile px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs font-bold flex items-center gap-1.5 transition" title="Klik untuk menghidupkan bot">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        <i class="ti ti-power"></i>
                        <span>Bot: OFF (Aktifkan)</span>
                    </button>
                @endif
            </form>

            <form action="{{ route('admin.whatsapp-bot.restart') }}" method="POST">
                @csrf
                <button type="submit" class="uiverse-btn-tactile px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition" title="Sinkronkan status koneksi SiPintu Gateway">
                    <i class="ti ti-refresh text-slate-500"></i>
                    <span>Sinkron Status Gateway</span>
                </button>
            </form>

            <button type="button" onclick="document.getElementById('sectionSettings').scrollIntoView({ behavior: 'smooth' })"
                class="uiverse-btn-tactile px-3.5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-white text-xs font-semibold flex items-center gap-1.5 transition shadow-sm shadow-blue-500/25">
                <i class="ti ti-settings"></i>
                <span>Pengaturan</span>
            </button>
        </div>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
            <i class="ti ti-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-2">
            <i class="ti ti-alert-circle text-rose-600 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- CARD UTAMA: KONEKSI PERANGKAT & SCAN QR CODE --}}
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden" id="qrContainerWrapper">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="ti ti-qrcode text-blue-600 text-lg"></i>
                    Koneksi Perangkat WhatsApp (QR Code Scanner)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Tautkan akun WhatsApp sekolah untuk mengirim notifikasi secara otomatis.</p>
            </div>

            <div id="liveStatusBadge" class="flex items-center gap-2 flex-wrap">
                @if(!$settings['wa_bot_enabled'])
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                        <i class="ti ti-player-pause text-amber-500"></i>
                        Status: Dijeda (OFF)
                    </span>
                @endif
                @if(!empty($botStatus['connected']))
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Terhubung ke WhatsApp
                    </span>
                @elseif(!empty($botStatus['online']))
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
            <div id="stateConnected" class="{{ !empty($botStatus['connected']) ? '' : 'hidden' }}">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6 p-6 rounded-2xl bg-emerald-50/60 border border-emerald-200">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-3xl shadow-sm shrink-0">
                            <i class="ti ti-brand-whatsapp"></i>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-200 text-emerald-900 mb-1">
                                <i class="ti ti-shield-check"></i> Sesi Aktif & Siap Mengirim
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900" id="connectedPhoneText">
                                {{ $botStatus['user']['phone'] ?? 'WhatsApp Terhubung' }}
                            </h3>
                            <p class="text-xs text-slate-600 mt-0.5" id="connectedNameText">
                                {{ $botStatus['user']['name'] ?? 'Akun Bot Sekolah' }} &bull; Gateway: {{ config('services.sipintu.url') ?: 'https://sipintu.smkn1bangsri.sch.id' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0">
                        @if(($botStatus['gateway_type'] ?? '') === 'sipintu')
                            <a href="{{ rtrim(config('services.sipintu.url') ?: 'https://sipintu.smkn1bangsri.sch.id', '/') }}/admin/whatsapp-gateway" target="_blank"
                                class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center gap-2 shadow-xs">
                                <i class="ti ti-external-link"></i>
                                <span>Kelola Nomor di SiPintu</span>
                            </a>
                        @else
                            <form action="{{ route('admin.whatsapp-bot.disconnect') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan perangkat WhatsApp ini? Anda perlu melakukan scan QR ulang.')">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-xs">
                                    <i class="ti ti-logout"></i>
                                    <span>Putuskan Sesi / Ganti Nomor</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- KONDISI 2: MENUNGGU SCAN QR CODE (WAITING QR) --}}
            <div id="stateWaitingQr" class="{{ (empty($botStatus['connected']) && !empty($botStatus['online'])) ? '' : 'hidden' }}">
                @if(($botStatus['gateway_type'] ?? '') === 'sipintu')
                    <div class="p-6 rounded-2xl bg-gradient-to-r from-amber-50/70 via-white to-blue-50/50 border border-amber-200">
                        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                            <div class="flex items-start gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-2xl shadow-sm shrink-0">
                                    <i class="ti ti-qrcode"></i>
                                </div>
                                <div class="space-y-1.5 max-w-xl">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-200 text-amber-900">
                                            <i class="ti ti-clock"></i> Menunggu Scan QR di SiPintu
                                        </span>
                                        @if($botStatus['auto_fallback'] ?? true)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <i class="ti ti-shield-check"></i> Auto-Fallback ke Nomor Pusat SiPintu: AKTIF
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-base font-bold text-slate-900">
                                        Tautkan Nomor WhatsApp Khusus DiDispen di Dashboard SiPintu
                                    </h3>
                                    <p class="text-xs text-slate-600 leading-relaxed">
                                        DiDispen telah terhubung ke <strong>Multi-Session WhatsApp Gateway SiPintu</strong>. Untuk memasangkan nomor WhatsApp khusus aplikasi ini, silakan scan QR Code langsung dari menu WhatsApp Gateway di SiPintu.
                                    </p>
                                    @if($botStatus['auto_fallback'] ?? true)
                                        <div class="text-[11px] text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200 flex items-center gap-1.5">
                                            <i class="ti ti-info-circle text-emerald-600 text-sm"></i>
                                            <span><strong>Pesan Tetap Terkirim:</strong> Selama nomor khusus belum discan, seluruh pesan WhatsApp otomatis dialihkan dan terkirim via <strong>Nomor WhatsApp Pusat SiPintu</strong>.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0 w-full lg:w-auto">
                                <a href="{{ rtrim(config('services.sipintu.url') ?: 'https://sipintu.smkn1bangsri.sch.id', '/') }}/admin/whatsapp-gateway" target="_blank"
                                    class="px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm shadow-blue-500/20 active:scale-95">
                                    <i class="ti ti-external-link text-sm"></i>
                                    <span>Buka Menu Scan QR di SiPintu</span>
                                </a>
                                <button type="button" onclick="pollBotStatus(true)" id="btnRefreshQr" class="px-4 py-3 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs">
                                    <i class="ti ti-refresh text-slate-500" id="iconRefreshQr"></i>
                                    <span>Cek Status</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                        <div class="md:col-span-5 flex flex-col items-center justify-center p-4 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="relative w-64 h-64 bg-white rounded-xl shadow-xs border border-slate-200 flex items-center justify-center overflow-hidden p-2">
                                <img id="qrImageElement" src="{{ $botStatus['qr_image'] ?? '' }}" alt="QR Code WhatsApp" class="w-full h-full object-contain {{ empty($botStatus['qr_image']) ? 'hidden' : '' }}">
                                <div id="qrLoadingPlaceholder" class="{{ !empty($botStatus['qr_image']) ? 'hidden' : '' }} text-center p-4">
                                    <i class="ti ti-loader-2 animate-spin text-3xl text-blue-600 mb-2"></i>
                                    <p class="text-xs text-slate-500">Mempersiapkan QR Code...</p>
                                </div>
                            </div>
                            <div class="mt-2.5 flex items-center justify-between w-64 px-1 text-[11px] text-slate-500">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                    <span>Auto-refresh aktif</span>
                                </span>
                                <button type="button" onclick="pollBotStatus(true)" id="btnRefreshQr" class="text-blue-700 hover:text-blue-800 font-semibold flex items-center gap-1 transition">
                                    <i class="ti ti-refresh" id="iconRefreshQr"></i>
                                    <span>Muat Ulang QR</span>
                                </button>
                            </div>
                            <div class="mt-2 w-64 text-center">
                                <form action="{{ route('admin.whatsapp-bot.disconnect') }}" method="POST" onsubmit="return confirm('QR Code belum muncul? Klik OK untuk menghapus cache sesi lama dan memaksa WhatsApp server mengirimkan QR Code baru.')">
                                    @csrf
                                    <button type="submit" class="text-[11px] text-slate-400 hover:text-rose-600 underline transition inline-flex items-center gap-1">
                                        <i class="ti ti-refresh text-[10px]"></i>
                                        <span>QR tidak kunjung muncul? Reset & Buat QR Baru</span>
                                    </button>
                                </form>
                            </div>
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
                @endif
            </div>

            {{-- KONDISI 3: SERVER BOT OFFLINE --}}
            <div id="stateOffline" class="{{ empty($botStatus['online']) ? '' : 'hidden' }}">
                <div class="p-6 rounded-2xl bg-rose-50 border border-rose-200 text-center sm:text-left flex flex-col sm:flex-row items-center justify-between gap-5">
                    <div class="flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl shrink-0">
                            <i class="ti ti-alert-triangle text-2xl"></i>
                        </div>
                        <div>
                            @if(($botStatus['gateway_type'] ?? '') === 'sipintu' || !empty(config('services.sipintu.client_id')))
                                <h3 class="text-sm sm:text-base font-bold text-rose-900">Koneksi ke SiPintu WhatsApp Gateway Terputus</h3>
                                <p class="text-xs text-rose-700 mt-1 leading-relaxed">
                                    DiDispen tidak dapat menghubungi server SiPintu di <code class="bg-rose-100 px-1 py-0.5 rounded font-mono">{{ config('services.sipintu.url') ?: 'https://sipintu.smkn1bangsri.sch.id' }}</code>.
                                    Pastikan server SiPintu aktif dan jaringan server terhubung ke internet.
                                </p>
                            @else
                                <h3 class="text-sm sm:text-base font-bold text-rose-900">Server Microservice WhatsApp Bot Sedang Offline</h3>
                                <p class="text-xs text-rose-700 mt-1 leading-relaxed">
                                    Aplikasi tidak dapat menghubungi server bot di <code class="bg-rose-100 px-1 py-0.5 rounded font-mono">{{ $settings['wa_bot_url'] }}</code>.
                                </p>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('admin.whatsapp-bot.restart') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shrink-0">
                            <i class="ti ti-refresh"></i> Cek Ulang Koneksi
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
                <i class="ti ti-adjustments text-blue-600"></i>
                Pengaturan Aturan Notifikasi WhatsApp DiDispen
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Atur jadwal pengingat piket, nomor WhatsApp satpam gerbang, dan pilih jenis notifikasi otomatis yang aktif.</p>
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

            {{-- 2. Informasi Engine Gateway Terpusat (SiPintu Cloud Gateway) --}}
            @if(!empty(config('services.sipintu.client_id')))
                <div class="p-4 rounded-xl border border-blue-200 bg-gradient-to-r from-blue-50/70 via-white to-indigo-50/50 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xl shrink-0 shadow-xs">
                                <i class="ti ti-cloud-lock"></i>
                            </div>
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                                    Engine: SiPintu Multi-Session WhatsApp Gateway
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Aktif (Terpusat)
                                    </span>
                                </h4>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Infrastruktur bot, sesi WhatsApp, dan koneksi socket ditangani oleh SiPintu. DiDiSpen hanya menjalankan aturan bisnis notifikasi di bawah.
                                </p>
                            </div>
                        </div>

                        <a href="{{ rtrim(config('services.sipintu.url') ?: 'https://sipintu.smkn1bangsri.sch.id', '/') }}/admin/whatsapp-gateway" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-blue-200 text-blue-700 hover:bg-blue-50 text-xs font-semibold transition shrink-0 shadow-2xs">
                            <i class="ti ti-external-link"></i>
                            <span>Buka Gateway SiPintu</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1 text-xs">
                        <div class="p-2.5 rounded-lg bg-white border border-slate-200/80">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Gateway Endpoint</span>
                            <span class="font-mono font-semibold text-slate-800 text-[11px] truncate block mt-0.5">{{ config('services.sipintu.url') ?: 'https://sipintu.smkn1bangsri.sch.id' }}</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-white border border-slate-200/80">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Client ID Aplikasi</span>
                            <span class="font-mono font-semibold text-slate-800 text-[11px] truncate block mt-0.5">{{ config('services.sipintu.client_id') }}</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-white border border-slate-200/80">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Nomor Bot Terpasang</span>
                            <span class="font-bold text-emerald-700 text-[11px] truncate block mt-0.5">
                                {{ !empty($botStatus['assigned_phone']) ? ('+' . $botStatus['assigned_phone']) : ($botStatus['user']['phone'] ?? 'Belum Ditautkan') }}
                            </span>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="wa_bot_url" class="block text-xs font-bold text-slate-700 mb-1.5">
                            URL Server Microservice Baileys
                        </label>
                        <input type="url" name="wa_bot_url" id="wa_bot_url" value="{{ old('wa_bot_url', $settings['wa_bot_url']) }}"
                            placeholder="http://127.0.0.1:3789"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition">
                        <p class="text-[11px] text-slate-500 mt-1">Default lokal: <code>http://127.0.0.1:3789</code></p>
                    </div>

                    <div>
                        <label for="wa_bot_api_key" class="block text-xs font-bold text-slate-700 mb-1.5">
                            API Key Server Bot
                        </label>
                        <input type="text" name="wa_bot_api_key" id="wa_bot_api_key" value="{{ old('wa_bot_api_key', $settings['wa_bot_api_key']) }}"
                            placeholder="sipintu_wa_secret_key_2026"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition">
                    </div>
                </div>
            @endif

            {{-- 3. Pengaturan Waktu Pengingat Sesi Piket (Per Sesi) --}}
            <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40 space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <label for="wa_piket_reminder_minutes" class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1.5 cursor-pointer">
                            <i class="ti ti-clock text-blue-600"></i>
                            Waktu Pengingat Sebelum Sesi Piket Dimulai
                        </label>
                        <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                            Notifikasi WhatsApp dikirimkan per sesi sebelum jam mulai sesi piket (contoh: diisi <strong>15</strong> menit, maka pesan Sesi 1 dikirim jam 06:45, Sesi 2 dikirim jam 09:15, dst).
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <input type="number" name="wa_piket_reminder_minutes" id="wa_piket_reminder_minutes"
                            value="{{ old('wa_piket_reminder_minutes', $settings['wa_piket_reminder_minutes'] ?? 15) }}"
                            min="0" max="120" required
                            class="w-24 px-3 py-2 rounded-xl border border-slate-300 text-sm font-bold text-center focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition bg-white shadow-2xs">
                        <span class="text-xs font-bold text-slate-700">Menit sebelum sesi</span>
                    </div>
                </div>
            </div>

            {{-- 3b. Nomor WhatsApp Pos Satpam --}}
            <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/40 space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <label for="wa_satpam_phone" class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1.5 cursor-pointer">
                            <i class="ti ti-shield-check text-blue-600"></i>
                            Nomor WhatsApp Pos Satpam / Gerbang Utama
                        </label>
                        <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                            Nomor WhatsApp HP dinas / pos gerbang utama untuk menerima pengingat kesiapsiagaan memindai QR siswa setiap sesi piket.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <input type="text" name="wa_satpam_phone" id="wa_satpam_phone"
                            value="{{ old('wa_satpam_phone', $settings['wa_satpam_phone'] ?? '') }}"
                            placeholder="Contoh: 081234567890"
                            class="w-48 sm:w-56 px-3 py-2 rounded-xl border border-slate-300 text-sm font-semibold focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition bg-white shadow-2xs font-mono">
                    </div>
                </div>
            </div>

            {{-- 4. Saklar Notifikasi Otomatis Spesifik --}}
            <div class="space-y-3 pt-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Pilihan Notifikasi Otomatis</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    {{-- Opsi A: Guru Piket Bertugas --}}
                    <label class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer transition flex items-start gap-3">
                        <input type="checkbox" name="wa_notify_piket_bertugas" value="1" class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300" @checked($settings['wa_notify_piket_bertugas'])>
                        <div class="space-y-1">
                            <span class="block text-xs font-bold text-slate-900">Guru Piket Bertugas (Per Sesi)</span>
                            <span class="block text-[11px] text-slate-500 leading-normal">Kirim WA otomatis beberapa menit sebelum sesi dimulai ke guru yang bertugas aktif.</span>
                        </div>
                    </label>

                    {{-- Opsi B: Koordinator Guru Piket --}}
                    <label class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer transition flex items-start gap-3">
                        <input type="checkbox" name="wa_notify_koordinator_piket" value="1" class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300" @checked($settings['wa_notify_koordinator_piket'])>
                        <div class="space-y-1">
                            <span class="block text-xs font-bold text-slate-900">Koordinator Guru Piket</span>
                            <span class="block text-[11px] text-slate-500 leading-normal">Kirim WA otomatis ke Koordinator Piket 1x sehari di sesi awal untuk mengecek kehadiran pos.</span>
                        </div>
                    </label>

                    {{-- Opsi C: Permintaan Tukar Piket (ke Guru Pengganti) --}}
                    <label class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer transition flex items-start gap-3">
                        <input type="checkbox" name="wa_notify_piket_swap" value="1" class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300" @checked($settings['wa_notify_piket_swap'])>
                        <div class="space-y-1">
                            <span class="block text-xs font-bold text-slate-900">Permintaan Tukar Piket</span>
                            <span class="block text-[11px] text-slate-500 leading-normal">Kirim WA otomatis ke Guru Pengganti saat ada guru lain mengajukan pertukaran jadwal piket.</span>
                        </div>
                    </label>

                    {{-- Opsi D: Hasil Respon Tukar Piket (ke Guru Asal) --}}
                    <label class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer transition flex items-start gap-3">
                        <input type="checkbox" name="wa_notify_piket_swap_result" value="1" class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300" @checked($settings['wa_notify_piket_swap_result'])>
                        <div class="space-y-1">
                            <span class="block text-xs font-bold text-slate-900">Hasil Respon Tukar Piket</span>
                            <span class="block text-[11px] text-slate-500 leading-normal">Kirim WA otomatis hasil persetujuan/penolakan ke Guru Asal saat pertukaran direspon.</span>
                        </div>
                    </label>

                    {{-- Opsi E: Peringatan Siswa Terlambat --}}
                    <label class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer transition flex items-start gap-3">
                        <input type="checkbox" name="wa_notify_siswa_terlambat" value="1" class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300" @checked($settings['wa_notify_siswa_terlambat'])>
                        <div class="space-y-1">
                            <span class="block text-xs font-bold text-slate-900">Peringatan Siswa Terlambat</span>
                            <span class="block text-[11px] text-slate-500 leading-normal">Kirim WA peringatan langsung ke siswa yang melewati batas waktu kembali izin.</span>
                        </div>
                    </label>

                    {{-- Opsi F: Laporan Rekap ke Guru Piket --}}
                    <label class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer transition flex items-start gap-3">
                        <input type="checkbox" name="wa_notify_laporan_guru_piket" value="1" class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300" @checked($settings['wa_notify_laporan_guru_piket'])>
                        <div class="space-y-1">
                            <span class="block text-xs font-bold text-slate-900">Laporan Rekap ke Guru Piket</span>
                            <span class="block text-[11px] text-slate-500 leading-normal">Kirim rekap data siswa terlambat ke nomor Guru Piket untuk ditindaklanjuti.</span>
                        </div>
                    </label>

                    {{-- Opsi G: Pengingat Siaga Pos Satpam --}}
                    <label class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer transition flex items-start gap-3">
                        <input type="checkbox" name="wa_notify_satpam_sesi_piket" value="1" class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300" @checked($settings['wa_notify_satpam_sesi_piket'])>
                        <div class="space-y-1">
                            <span class="block text-xs font-bold text-slate-900">Siaga Pos Satpam (Per Sesi)</span>
                            <span class="block text-[11px] text-slate-500 leading-normal">Kirim WA otomatis ke Pos Satpam setiap sesi guru piket dimulai agar standby scan QR gerbang.</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- 4. Hari Operasional Aktif Pengiriman Bot --}}
            <div class="space-y-2 pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Hari Operasional Pengiriman Bot</h3>
                        <p class="text-[11px] text-slate-500">Pilih hari kerja apa saja notifikasi otomatis guru piket & koordinator boleh dikirim.</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2.5">
                    @php
                        $dayMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                        $activeDays = $settings['wa_active_days'] ?? [1, 2, 3, 4, 5];
                    @endphp
                    @foreach($dayMap as $dayNum => $dayName)
                        <label class="p-3 rounded-xl border {{ in_array($dayNum, $activeDays) ? 'border-emerald-300 bg-emerald-50/50' : 'border-slate-200 bg-white' }} hover:border-slate-300 cursor-pointer transition flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800">{{ $dayName }}</span>
                            <input type="checkbox" name="wa_active_days[]" value="{{ $dayNum }}" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300" @checked(in_array($dayNum, $activeDays))>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold transition flex items-center gap-2 shadow-sm shadow-blue-500/25">
                    <i class="ti ti-device-floppy"></i>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </form>
    </div>

    {{-- MATRIKS AGENDA PENGIRIMAN BOT GURU PIKET (FORMAT RESMI SK SMK N 1 BANGSRI) --}}
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden" id="sectionMatriksPiket">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="ti ti-calendar-check text-blue-600"></i>
                    Matriks Agenda Pengiriman Bot Guru Piket
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Jadwal penugasan guru piket harian dan alur pengiriman notifikasi otomatis WhatsApp per sesi.
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200/60">
                    <i class="ti ti-circle-check text-blue-600"></i>
                    Format Matriks SK SMK N 1 Bangsri
                </span>
                <a href="{{ route('admin.jadwal-piket.index', ['view' => 'matriks']) }}" class="px-3 py-1 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-semibold flex items-center gap-1 transition">
                    <i class="ti ti-external-link text-[10px]"></i>
                    <span>Kelola Jadwal Piket</span>
                </a>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            @php
                $hariNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                $todayIso = now('Asia/Jakarta')->dayOfWeekIso;
                $hariCounter = 1;
                $waServiceHelper = app(\App\Services\WhatsappMessageService::class);
            @endphp

            @if(empty($matriksData) || $matriksData->isEmpty())
                <div class="text-center py-8 text-slate-500 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                    <i class="ti ti-calendar-off text-3xl text-slate-400 mb-2"></i>
                    <p class="text-sm font-semibold text-slate-700">Belum Ada Data Jadwal Piket Aktif</p>
                    <p class="text-xs text-slate-500 mt-0.5">Silakan tambahkan data sesi dan penugasan guru piket terlebih dahulu.</p>
                </div>
            @else
                <div class="w-full overflow-x-auto border border-slate-200 rounded-xl bg-white shadow-2xs touch-pan-x">
                    <table class="w-full min-w-[960px] text-left text-xs text-slate-800 border-collapse">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                                <th class="py-3 px-3 border-r border-slate-200 w-12 text-center whitespace-nowrap">No.</th>
                                <th class="py-3 px-3 border-r border-slate-200 w-28 whitespace-nowrap">Hari</th>
                                <th class="py-3 px-4 border-r border-slate-200 w-64 whitespace-nowrap">Koordinator Guru Piket</th>
                                <th class="py-3 px-4 border-r border-slate-200 w-56 whitespace-nowrap">Pengaturan Sesi Piket</th>
                                <th class="py-3 px-4 border-r border-slate-200 min-w-[200px]">Guru Piket Bertugas</th>
                                <th class="py-3 px-4 border-r border-slate-200 w-48 whitespace-nowrap">No. HP Guru</th>
                                <th class="py-3 px-3 text-center w-36 whitespace-nowrap bg-slate-100 z-10 border-l border-slate-200">Alur Notifikasi Bot</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($matriksData as $hariNum => $sesiList)
                                @php
                                    $firstSesi = $sesiList->first();
                                    $koordinator = $sesiList->pluck('koordinator')->filter()->first();
                                    $totalSesi = $sesiList->count();
                                    $isToday = ($hariNum == $todayIso);
                                    $isDayActive = in_array($hariNum, $settings['wa_active_days'] ?? [1, 2, 3, 4, 5]);
                                @endphp
                                @foreach($sesiList as $idx => $sesi)
                                    <tr class="hover:bg-slate-50/70 transition-colors {{ $idx === 0 ? 'border-t-2 border-slate-300' : '' }} {{ $isToday ? 'bg-emerald-50/30' : '' }}">
                                        {{-- No. (Rowspan) --}}
                                        @if($idx === 0)
                                            <td rowspan="{{ $totalSesi }}" class="py-3 px-3 border-r border-slate-200 text-center font-bold align-top bg-slate-50/60 whitespace-nowrap">
                                                {{ $hariCounter++ }}
                                            </td>
                                            {{-- Hari (Rowspan) --}}
                                            <td rowspan="{{ $totalSesi }}" class="py-3 px-3 border-r border-slate-200 font-bold text-slate-900 align-top bg-slate-50/60 whitespace-nowrap">
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-xs font-bold">{{ $hariNames[$hariNum] ?? 'Hari '.$hariNum }}</span>
                                                    @if($isToday)
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-600 text-white shadow-2xs">
                                                            <i class="ti ti-calendar text-[10px]"></i> Hari Ini
                                                        </span>
                                                    @endif
                                                    @if(!$isDayActive)
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-200 text-slate-600">
                                                            Bot Nonaktif
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            {{-- Koordinator (Rowspan) --}}
                                            <td rowspan="{{ $totalSesi }}" class="py-3 px-4 border-r border-slate-200 align-top bg-slate-50/60">
                                                @if($koordinator)
                                                    <div class="font-bold text-slate-900">{{ $koordinator->nama_lengkap }}</div>
                                                    @php
                                                        $koorWaLink = $waServiceHelper->generatePiketWaLink($koordinator, $firstSesi);
                                                    @endphp
                                                    @if($koorWaLink)
                                                        <a href="{{ $koorWaLink }}" target="_blank" rel="noopener noreferrer"
                                                           class="inline-flex items-center gap-1 text-[11px] text-blue-700 hover:text-blue-900 font-mono mt-1 px-2 py-0.5 rounded bg-blue-50 hover:bg-blue-100 border border-blue-200 transition-colors whitespace-nowrap"
                                                           title="Kirim pesan WhatsApp pengingat ke Koordinator">
                                                            <i class="ti ti-brand-whatsapp text-blue-600"></i>
                                                            <span class="font-semibold">{{ $koordinator->no_telepon }}</span>
                                                        </a>
                                                    @else
                                                        <div class="text-[11px] text-slate-400 font-mono mt-0.5 flex items-center whitespace-nowrap">
                                                            <i class="ti ti-brand-whatsapp text-slate-300 mr-1 text-xs"></i> {{ $koordinator->no_telepon ?? '-' }}
                                                        </div>
                                                    @endif
                                                    <div class="mt-1.5 text-[10px] text-blue-700 font-medium flex items-center gap-1">
                                                        <i class="ti ti-shield-check text-blue-600 text-xs"></i>
                                                        <span>Notifikasi: 1x sehari di sesi awal</span>
                                                    </div>
                                                @else
                                                    <span class="text-slate-400 italic">Belum disetel</span>
                                                @endif
                                            </td>
                                        @endif

                                        {{-- Pengaturan Sesi Piket --}}
                                        <td class="py-2.5 px-4 border-r border-slate-200 font-medium">
                                            <div class="flex items-center gap-1.5 whitespace-nowrap">
                                                <span class="font-bold text-blue-800">{{ $sesi->nama_sesi ?? 'Sesi' }}</span>
                                                <span class="text-[11px] text-slate-500 font-mono">({{ substr($sesi->jam_mulai, 0, 5) }} - {{ substr($sesi->jam_selesai, 0, 5) }})</span>
                                            </div>
                                            <div class="text-[10px] mt-0.5">
                                                @if($sesi->is_active)
                                                    <span class="text-emerald-700 font-semibold inline-flex items-center">
                                                        <i class="ti ti-circle-check text-emerald-500 mr-1 text-xs"></i> Sesi Aktif
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 font-semibold inline-flex items-center">
                                                        <i class="ti ti-circle-x text-slate-400 mr-1 text-xs"></i> Nonaktif
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Guru Piket Bertugas --}}
                                        <td class="py-2.5 px-4 border-r border-slate-200">
                                            @if($sesi->guru->isEmpty())
                                                <span class="text-xs text-amber-600 italic">Belum ada guru piket</span>
                                            @else
                                                <ul class="space-y-1">
                                                    @foreach($sesi->guru as $guru)
                                                        <li class="flex items-center gap-1.5">
                                                            <i class="ti ti-user text-xs text-blue-600"></i>
                                                            <span class="font-medium text-slate-800">{{ $guru->nama_lengkap }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>

                                        {{-- No. HP --}}
                                        <td class="py-2.5 px-4 border-r border-slate-200 whitespace-nowrap">
                                            @if($sesi->guru->isEmpty())
                                                <span class="text-slate-400">-</span>
                                            @else
                                                <ul class="space-y-1">
                                                    @foreach($sesi->guru as $guru)
                                                        @php
                                                            $waLink = $waServiceHelper->generatePiketWaLink($guru, $sesi);
                                                        @endphp
                                                        <li>
                                                            @if($waLink)
                                                                <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer"
                                                                   class="inline-flex items-center gap-1 text-[11px] text-emerald-700 hover:text-emerald-950 font-mono px-2 py-0.5 rounded bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors"
                                                                   title="Hubungi Guru Piket via WhatsApp">
                                                                    <i class="ti ti-brand-whatsapp text-emerald-600"></i>
                                                                    <span>{{ $guru->no_telepon }}</span>
                                                                </a>
                                                            @else
                                                                <span class="text-[11px] font-mono text-slate-400">
                                                                    <i class="ti ti-brand-whatsapp text-slate-300 mr-0.5"></i> {{ $guru->no_telepon ?? '-' }}
                                                                </span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>

                                        {{-- Alur Notifikasi Bot --}}
                                        <td class="py-2.5 px-3 text-center border-l border-slate-200">
                                            @if(!$isDayActive)
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-[10px] font-bold bg-slate-100 text-slate-500">
                                                    <i class="ti ti-player-pause text-[10px]"></i> Hari Nonaktif
                                                </span>
                                            @elseif(!$settings['wa_notify_piket_bertugas'])
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    <i class="ti ti-ban text-[10px]"></i> Notifikasi OFF
                                                </span>
                                            @else
                                                <div class="flex flex-col items-center gap-0.5">
                                                    @php
                                                        $remMinutes = (int) ($settings['wa_piket_reminder_minutes'] ?? 15);
                                                        $waktuKirim = \Carbon\Carbon::parse($sesi->jam_mulai)->subMinutes($remMinutes)->format('H:i');
                                                    @endphp
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200/60">
                                                        <i class="ti ti-send text-[10px] text-blue-600"></i>
                                                        Otomatis {{ $waktuKirim }} WIB
                                                    </span>
                                                    <span class="text-[10px] text-slate-500 font-mono">
                                                        @if($remMinutes > 0)
                                                             H-{{ $remMinutes }} mnt sebelum sesi
                                                        @else
                                                            Saat sesi dimulai
                                                        @endif
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
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
            <i class="ti ti-send text-blue-600"></i>
            Uji Coba Kirim Pesan WhatsApp Langsung
        </h2>
        <form action="{{ route('admin.whatsapp-bot.send-test') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-3">
            @csrf
            <div class="md:col-span-3">
                <input type="text" name="phone" placeholder="No. WA (cth: 08123456789)" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition">
            </div>
            <div class="md:col-span-7">
                <input type="text" name="message" placeholder="Isi pesan uji coba..." value="Halo, ini pesan uji coba otomatis dari Bot WhatsApp DIDISPEN SMKN 1 Bangsri." required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-sm shadow-blue-500/25">
                    <i class="ti ti-send"></i> Kirim Tes
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
                    <option value="">Semua Kategori</option>
                    <option value="piket_bertugas" @selected(request('context') === 'piket_bertugas')>Guru Piket Bertugas</option>
                    <option value="piket_koordinator" @selected(request('context') === 'piket_koordinator')>Koordinator Guru Piket</option>
                    <option value="overdue" @selected(request('context') === 'overdue')>Peringatan Siswa Terlambat</option>
                    <option value="laporan_piket_overdue" @selected(request('context') === 'laporan_piket_overdue')>Laporan ke Guru Piket</option>
                    <option value="returned_late" @selected(request('context') === 'returned_late')>Siswa Kembali Terlambat</option>
                    <option value="test_admin" @selected(request('context') === 'test_admin')>Uji Coba Admin</option>
                </select>
                <button type="submit" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition">
                    <i class="ti ti-filter mr-1"></i> Filter
                </button>
            </form>

            @if($stats['failed'] > 0)
                <form action="{{ route('admin.whatsapp-bot.retry-all') }}" method="POST" onsubmit="return confirm('Kirim ulang semua pesan gagal?')">
                    @csrf
                    <button type="submit" class="uiverse-btn-tactile px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 transition">
                        <i class="ti ti-refresh"></i> Kirim Ulang Gagal ({{ $stats['failed'] }})
                    </button>
                </form>
            @endif
        </div>

        <div class="overflow-x-auto min-w-0 w-full touch-pan-x">
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
                                @if($log->context === 'piket_bertugas')
                                    <span class="px-2 py-0.5 text-[11px] rounded-md bg-blue-50 text-blue-700 border border-blue-200/60 font-semibold inline-flex items-center gap-1">
                                        <i class="ti ti-calendar-check text-[11px]"></i> Piket Bertugas
                                    </span>
                                @elseif($log->context === 'piket_koordinator')
                                    <span class="px-2 py-0.5 text-[11px] rounded-md bg-blue-50 text-blue-700 border border-blue-200/60 font-semibold inline-flex items-center gap-1">
                                        <i class="ti ti-shield-check text-[11px]"></i> Koordinator Piket
                                    </span>
                                @elseif($log->context === 'overdue' || str_contains($log->context, 'overdue'))
                                    <span class="px-2 py-0.5 text-[11px] rounded-md bg-rose-50 text-rose-700 border border-rose-200/60 font-semibold inline-flex items-center gap-1">
                                        <i class="ti ti-clock text-[11px]"></i> Siswa Terlambat
                                    </span>
                                @elseif($log->context === 'laporan_piket_overdue')
                                    <span class="px-2 py-0.5 text-[11px] rounded-md bg-amber-50 text-amber-700 border border-amber-200/60 font-semibold inline-flex items-center gap-1">
                                        <i class="ti ti-alert-triangle text-[11px]"></i> Laporan Guru Piket
                                    </span>
                                @elseif($log->context === 'test_admin')
                                    <span class="px-2 py-0.5 text-[11px] rounded-md bg-slate-100 text-slate-700 font-semibold inline-flex items-center gap-1">
                                        <i class="ti ti-flask text-[11px]"></i> Tes Admin
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-[11px] rounded-md bg-slate-100 text-slate-700 font-mono font-semibold">
                                        {{ $log->context }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 max-w-md">
                                <div class="text-xs text-slate-700 whitespace-pre-line line-clamp-2" title="{{ $log->message }}">
                                    {{ \Illuminate\Support\Str::limit($log->message, 120) }}
                                </div>
                                @if($log->error_message)
                                    <div class="text-xs text-rose-600 mt-1 font-semibold flex items-start gap-1">
                                        <i class="ti ti-alert-circle mt-0.5"></i>
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
                                        <button type="submit" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-bold transition">
                                            <i class="ti ti-refresh mr-1"></i>Retry
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
    let isCurrentlyConnected = {{ !empty($botStatus['connected']) ? 'true' : 'false' }};
    let pollTimer = null;

    function scheduleNextPoll() {
        if (pollTimer) clearTimeout(pollTimer);
        // Polling adaptif: 30 detik saat terhubung, 6 detik saat menunggu scan QR / offline
        const delay = isCurrentlyConnected ? 30000 : 6000;
        pollTimer = setTimeout(async () => {
            if (!document.hidden && isPolling) {
                await window.pollBotStatus(false);
            }
            scheduleNextPoll();
        }, delay);
    }

    window.pollBotStatus = async function(manual = false) {
        if (!isPolling && !manual) return;

        const icon = document.getElementById('iconRefreshQr');
        if (manual && icon) icon.classList.add('animate-spin');

        try {
            const url = manual ? `${statusUrl}?refresh=1` : statusUrl;
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) {
                isCurrentlyConnected = false;
                renderOffline();
                return;
            }

            const data = await res.json();

            if (!data.online) {
                isCurrentlyConnected = false;
                renderOffline();
            } else if (data.connected) {
                isCurrentlyConnected = true;
                renderConnected(data.user);
            } else {
                isCurrentlyConnected = false;
                renderWaitingQr(data.qr_image);
            }
        } catch (e) {
            isCurrentlyConnected = false;
            renderOffline();
        } finally {
            if (manual && icon) {
                setTimeout(() => icon.classList.remove('animate-spin'), 600);
            }
        }
    };

    function renderConnected(user) {
        stateConnected.classList.remove('hidden');
        stateWaitingQr.classList.add('hidden');
        stateOffline.classList.add('hidden');

        if (user) {
            connectedPhoneText.textContent = user.phone ? ((user.phone.startsWith('+') || user.phone.startsWith('Nomor')) ? user.phone : ('+' + user.phone)) : 'WhatsApp Terhubung';
            connectedNameText.textContent = (user.name || 'Akun Bot') + ' • Sesi Gateway Aktif';
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

    // Jalankan timer polling adaptif
    scheduleNextPoll();
});
</script>
@endpush
@endsection