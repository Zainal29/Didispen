{{-- ============================================================
     PRINTER THERMAL BLE (OPTIMIZED FOR RPP02 & ALL PLATFORMS)
     ============================================================ --}}

<div class="mt-4 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

    {{-- HEADER --}}
    <div class="p-4 sm:p-5 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
        <div class="flex items-start justify-between gap-3">

            {{-- Identitas Printer --}}
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 shrink-0 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="ti ti-printer text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-slate-800 tracking-tight">
                        Printer Thermal BLE (RPP02 / POS-58)
                    </h3>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span id="printer-dot" class="w-2 h-2 shrink-0 rounded-full bg-slate-300"></span>
                        <p id="printer-status" class="text-xs font-medium text-slate-500 truncate">
                            Belum terhubung
                        </p>
                    </div>
                </div>
            </div>

            {{-- CONNECT / DISCONNECT --}}
            <button
                type="button"
                id="btn-connect-printer"
                class="group relative overflow-hidden inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl transition-all shadow-sm hover:shadow-md active:scale-95 shrink-0"
            >
                <i class="ti ti-bluetooth text-sm transition-transform duration-200 group-hover:scale-110"></i>
                <span>Hubungkan</span>
            </button>
        </div>

        {{-- STATUS PENCETAKAN --}}
        <div class="mt-3">
            @if(in_array($dispensasi->status, ['disetujui', 'keluar', 'selesai']))
                <div
                    id="printer-print-status"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1.5 rounded-lg border border-emerald-100"
                >
                    <i id="printer-print-status-icon" class="ti ti-circle-check text-xs"></i>
                    <span id="printer-print-status-text">Dispensasi disetujui, siap cetak</span>
                </div>
            @else
                <div
                    id="printer-print-status"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-1.5 rounded-lg border border-amber-100"
                >
                    <i id="printer-print-status-icon" class="ti ti-alert-circle text-xs"></i>
                    <span id="printer-print-status-text">Pencetakan hanya aktif jika status disetujui</span>
                </div>
            @endif
        </div>
    </div>

    {{-- AKSI CETAK --}}
    <div class="p-4 sm:p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            {{-- CETAK BUKTI BLE LANGSUNG (Uiverse-inspired Tactile Button) --}}
            <button
                type="button"
                id="btn-print-bluetooth"
                disabled
                class="group relative overflow-hidden inline-flex items-center justify-center gap-2 px-4 py-3 min-h-[48px] bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-500 hover:to-teal-600 disabled:from-slate-100 disabled:via-slate-100 disabled:to-slate-100 disabled:text-slate-400 disabled:border disabled:border-slate-200 disabled:shadow-none disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl transition-all shadow-sm hover:shadow-emerald-500/25 active:scale-[0.98]"
            >
                <span class="absolute inset-0 w-full h-full bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></span>
                <i class="ti ti-receipt text-base transition-transform duration-200 group-hover:scale-110"></i>
                <span class="tracking-wide">Cetak Bukti (Bluetooth Langsung)</span>
            </button>

            {{-- TEST PRINT BLE --}}
            <button
                type="button"
                id="btn-test-print"
                disabled
                class="group relative inline-flex items-center justify-center gap-2 px-4 py-3 min-h-[48px] bg-slate-100 hover:bg-slate-200/80 disabled:bg-slate-50 disabled:text-slate-300 disabled:border disabled:border-slate-100 disabled:cursor-not-allowed text-slate-700 text-xs font-semibold rounded-xl border border-slate-200/70 transition-all active:scale-[0.98]"
            >
                <i class="ti ti-test-pipe text-base transition-transform duration-200 group-hover:rotate-12"></i>
                <span>Test Print BLE</span>
            </button>
        </div>

        {{-- PANDUAN DEVICE --}}
        <div class="mt-4 space-y-2">
            <div class="flex items-start gap-2.5 px-3.5 py-2.5 rounded-xl bg-blue-50/70 border border-blue-100">
                <i class="ti ti-brand-apple text-blue-600 text-base mt-0.5 shrink-0"></i>
                <div class="text-[11px] leading-relaxed text-slate-600 min-w-0 flex-1">
                    <p>
                        <strong class="font-bold text-slate-800">Pengguna iPhone / iPad:</strong> Safari & Chrome iOS memblokir Bluetooth. Buka web ini via browser <strong class="font-bold text-blue-700">Bluefy (App Store)</strong>. <em>Penting:</em> Pastikan printer sudah di-<strong>Forget Device (Lupakan Perangkat)</strong> dari Settings Bluetooth iPhone, dan jika muncul pop-up PIN saat connect, tekan <strong>Cancel</strong>.
                    </p>
                </div>
            </div>
            <div class="flex items-start gap-2 px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200/70">
                <i class="ti ti-brand-android text-slate-500 text-sm mt-0.5 shrink-0"></i>
                <p class="text-[11px] leading-relaxed text-slate-600">
                    <strong class="font-bold text-slate-700">Pengguna Android:</strong> Wajib aktifkan <strong class="font-semibold text-slate-700">Bluetooth</strong> dan <strong class="font-semibold text-slate-700">GPS / Lokasi HP</strong>. Jika printer berstatus <em>BONDED/Tersimpan</em> di HP tapi gagal connect, klik <em>Unpair / Hapus</em> dari menu Bluetooth HP dulu.
                </p>
            </div>
        </div>
    </div>

    {{-- DIAGNOSTIK --}}
    <div class="border-t border-slate-100 bg-slate-50/60">
        <details class="group">
            <summary class="flex items-center justify-between px-4 py-3 text-[11px] font-semibold text-slate-500 cursor-pointer hover:text-slate-700 select-none">
                <span class="flex items-center gap-1.5">
                    <i class="ti ti-cpu text-slate-400 text-sm"></i>
                    <span>Diagnostik Web Bluetooth</span>
                </span>
                <i class="ti ti-chevron-down text-xs text-slate-400 transition-transform duration-200 group-open:rotate-180"></i>
            </summary>
            <div class="px-4 pb-4 text-[11px] font-mono text-slate-600">
                <div class="flex items-center justify-between gap-3 py-2 border-b border-slate-200/70">
                    <span class="text-slate-500">HTTPS Protocol</span>
                    <span id="ble-https" class="font-bold text-slate-700">checking...</span>
                </div>
                <div class="flex items-center justify-between gap-3 py-2 border-b border-slate-200/70">
                    <span class="text-slate-500">Web Bluetooth API</span>
                    <span id="ble-api" class="font-bold text-slate-700">checking...</span>
                </div>
                <div class="pt-2">
                    <div class="text-slate-400 mb-1">User Agent</div>
                    <div id="ble-browser" class="text-[10px] leading-relaxed text-slate-500 break-all">checking...</div>
                </div>
            </div>
        </details>
    </div>
</div>

@php
    $extractTime = function ($value) {
        if (empty($value)) return null;
        preg_match('/\b(\d{1,2}[:.]\d{2})\b/', (string) $value, $matches);
        return $matches[1] ?? null;
    };

    $waktuKeluar = $extractTime(\App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar));
    $waktuKembali = $extractTime(\App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali));

    $namaGuru = $dispensasi->guru?->nama_lengkap
        ?? $dispensasi->guru?->user?->name
        ?? $dispensasi->user?->name
        ?? 'Guru Piket';

    $nipGuru = $dispensasi->guru?->nip
        ?? $dispensasi->guru?->user?->nis_nip
        ?? $dispensasi->user?->nis_nip
        ?? null;

    $isRombongan = method_exists($dispensasi, 'isRombongan') ? $dispensasi->isRombongan() : ($dispensasi->anggotaSiswa->count() > 0);
    $totalAnggota = $isRombongan ? $dispensasi->anggotaSiswa->count() : 0;

    $receiptData = [
        'nomor_surat' => $dispensasi->nomor_surat ?? '-',
        'is_rombongan' => $isRombongan,
        'total_siswa' => $totalAnggota + 1,
        'nis' => $dispensasi->siswa?->user?->nis_nip ?? '-',
        'nama' => $dispensasi->siswa?->nama_lengkap ?? '-',
        'kelas' => $dispensasi->siswa?->kelas?->nama_kelas ?? '-',
        'tujuan' => $dispensasi->tujuan ?? '-',
        'lokasi' => $dispensasi->lokasi ?? '-',
        'jam_keluar' => $waktuKeluar ?? '-',
        'jam_kembali' => (method_exists($dispensasi, 'isSampaiPulang') && $dispensasi->isSampaiPulang()) ? 'Selesai KBM' : ($waktuKembali ?? '-'),
        'nama_guru' => $namaGuru,
        'nip_guru' => $nipGuru,
        'tanggal' => now()->format('d/m/Y'),
        'dicetak' => now()->format('d/m/Y H:i'),
        'qr_token' => $dispensasi->qr_token ?? '',
        'anggota' => $isRombongan ? $dispensasi->anggotaSiswa->map(fn($a) => [
            'nama' => $a->nama_lengkap,
            'nis' => $a->user?->nis_nip ?? '-',
            'kelas' => $a->kelas?->nama_kelas ?? '-',
        ])->values()->all() : [],
        'print_enabled' => in_array($dispensasi->status, ['disetujui', 'keluar', 'selesai']),
    ];
@endphp

<script>
(function () {
    'use strict';

    /*
     * ============================================================
     * ESC/POS CONSTANTS & HELPER FUNCTIONS
     * ============================================================
     */
    const ESC = 0x1B;
    const GS  = 0x1D;

    function bytes(...args) {
        return new Uint8Array(args.flat(Infinity));
    }

    function concatBytes(...arrays) {
        const validArrays = arrays
            .filter(a => a != null)
            .map(a => a instanceof Uint8Array ? a : (Array.isArray(a) ? new Uint8Array(a) : new Uint8Array()));
        const totalLength = validArrays.reduce((sum, arr) => sum + arr.length, 0);
        const result = new Uint8Array(totalLength);
        let offset = 0;
        for (const arr of validArrays) {
            result.set(arr, offset);
            offset += arr.length;
        }
        return result;
    }

    function textBytes(text) {
        return new TextEncoder().encode(String(text ?? ''));
    }

    function alignLeft() { return bytes(ESC, 0x61, 0x00); }
    function alignCenter() { return bytes(ESC, 0x61, 0x01); }
    function bold(enable = true) { return bytes(ESC, 0x45, enable ? 0x01 : 0x00); }
    function feed(lines = 1) { return bytes(ESC, 0x64, lines); }
    function cut() { return bytes(GS, 0x56, 0x42, 0x00); }

    function qrCodeBytes(text, size = 6) {
        if (!text) return new Uint8Array();
        const data = new TextEncoder().encode(String(text));
        const len = data.length + 3;
        const pL = len & 0xFF;
        const pH = (len >> 8) & 0xFF;
        return concatBytes(
            bytes(GS, 0x28, 0x6B, 0x04, 0x00, 0x31, 0x41, 0x32, 0x00),
            bytes(GS, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x43, Math.min(Math.max(size, 1), 8)),
            bytes(GS, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x45, 0x31),
            bytes(GS, 0x28, 0x6B, pL, pH, 0x31, 0x50, 0x30),
            data,
            bytes(GS, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x51, 0x30)
        );
    }

    /*
     * ============================================================
     * PROFIL GATT RPP02 (DARI DUMP nRF CONNECT)
     * ============================================================
     */
    const RPP02_GATT_PROFILES = [
        // Target Utama 1: 16-bit Standard Thermal POS (0x18F0 / 0x2AF1) - Khas RPP02 di Bluefy iOS
        {
            service: '000018f0-0000-1000-8000-00805f9b34fb',
            writeChar: '00002af1-0000-1000-8000-00805f9b34fb'
        },
        {
            service: 0x18f0,
            writeChar: 0x2af1
        },
        // Target Utama 2: Microchip ISSC Transparent UART (RPP02 UART)
        {
            service: '49535343-fe7d-4ae5-8fa9-9fafd205e455',
            writeChar: '49535343-8841-43f4-a8d4-ecbe34729bb3'
        },
        // Target Alternatif 3: Custom POS-58
        {
            service: 'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
            writeChar: 'bef8d6c9-9c21-4c9e-b632-bd58c1009f9f'
        },
        // Target Alternatif 4: Standard 0xFFE0 / 0xFFE1
        {
            service: '0000ffe0-0000-1000-8000-00805f9b34fb',
            writeChar: '0000ffe1-0000-1000-8000-00805f9b34fb'
        },
        {
            service: 0xffe0,
            writeChar: 0xffe1
        }
    ];

    const OPTIONAL_SERVICES = [
        '000018f0-0000-1000-8000-00805f9b34fb',
        0x18f0,
        '49535343-fe7d-4ae5-8fa9-9fafd205e455',
        'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
        '0000ffe0-0000-1000-8000-00805f9b34fb',
        0xffe0,
        '0000180a-0000-1000-8000-00805f9b34fb',
        0x180a
    ];

    const CHUNK_SIZE = 20;  // 20 byte aman untuk MTU BLE 4.0 standar
    const CHUNK_DELAY = 20; // 20ms memberi jeda buffer printer RPP02
    const CONNECT_RETRIES = 3;
    const CONNECT_RETRY_DELAY = 800; // 800ms jeda antar percobaan gatt.connect()
    const PRINTER_HAS_CUTTER = false; // RPP02 POS-58 umumnya tanpa auto-cutter

    let printerDevice = null;
    let writeCharacteristic = null;
    let isPrinting = false;

    const receiptData = @js($receiptData);

    const statusEl = document.getElementById('printer-status');
    const printStatusEl = document.getElementById('printer-print-status');
    const connectBtn = document.getElementById('btn-connect-printer');
    const printBtn = document.getElementById('btn-print-bluetooth');
    const testBtn = document.getElementById('btn-test-print');
    const statusDot = document.getElementById('printer-dot');

    function setStatus(text) {
        if (statusEl) statusEl.textContent = text;
    }

    function setPrintStatus(text, type = 'info') {
        const textEl = document.getElementById('printer-print-status-text');
        const iconEl = document.getElementById('printer-print-status-icon');
        if (!printStatusEl) return;

        const configs = {
            success: {
                wrapper: 'inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1.5 rounded-lg border border-emerald-100',
                icon: 'ti ti-circle-check text-xs'
            },
            warning: {
                wrapper: 'inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-1.5 rounded-lg border border-amber-100',
                icon: 'ti ti-alert-circle text-xs'
            },
            error: {
                wrapper: 'inline-flex items-center gap-1.5 text-xs font-medium text-rose-700 bg-rose-50 px-2.5 py-1.5 rounded-lg border border-rose-100',
                icon: 'ti ti-circle-x text-xs'
            },
            info: {
                wrapper: 'inline-flex items-center gap-1.5 text-xs font-medium text-blue-700 bg-blue-50 px-2.5 py-1.5 rounded-lg border border-blue-100',
                icon: 'ti ti-info-circle text-xs'
            }
        };

        const config = configs[type] || configs.info;
        printStatusEl.className = config.wrapper;
        if (textEl) textEl.textContent = text;
        if (iconEl) iconEl.className = config.icon;
    }

    function isPrintAllowed() {
        return receiptData.print_enabled === true;
    }

    function setConnectedState(connected) {
        const canPrint = connected && isPrintAllowed();

        if (printBtn) printBtn.disabled = !canPrint;
        if (testBtn) testBtn.disabled = !canPrint;

        if (statusDot) {
            statusDot.className = connected
                ? 'w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50 animate-pulse'
                : 'w-2.5 h-2.5 rounded-full bg-slate-300';
        }

        if (!connectBtn) return;

        if (connected) {
            connectBtn.innerHTML = '<i class="ti ti-unlink text-sm"></i><span>Putuskan</span>';
            connectBtn.className = 'group relative overflow-hidden inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold rounded-xl transition-all active:scale-95 shrink-0';
        } else {
            connectBtn.innerHTML = '<i class="ti ti-bluetooth text-sm transition-transform duration-200 group-hover:scale-110"></i><span>Hubungkan</span>';
            connectBtn.className = 'group relative overflow-hidden inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl transition-all shadow-sm hover:shadow-md active:scale-95 shrink-0';
        }
    }

    /*
     * ============================================================
     * BLE CONNECTION & DISCOVERY
     * ============================================================
     */
    async function connectWithRetry(device, retries = CONNECT_RETRIES) {
        let lastErr;
        for (let i = 0; i < retries; i++) {
            try {
                return await device.gatt.connect();
            } catch (e) {
                lastErr = e;
                console.warn(`[RPP02] gatt.connect() gagal (${i + 1}/${retries}):`, e.message);
                if (i < retries - 1) await new Promise(r => setTimeout(r, CONNECT_RETRY_DELAY));
            }
        }
        throw lastErr;
    }

    async function connectToDevice(device) {
        printerDevice = device;

        printerDevice.removeEventListener('gattserverdisconnected', handleDisconnect);
        printerDevice.addEventListener('gattserverdisconnected', handleDisconnect);

        const devName = printerDevice.name || 'RPP02';
        setStatus('Menghubungkan ke ' + devName + '...');
        setPrintStatus('Menghubungkan ke ' + devName + '...', 'info');

        const server = await connectWithRetry(printerDevice);
        let foundChar = null;

        // Cari profile GATT RPP02 secara berurutan
        for (const profile of RPP02_GATT_PROFILES) {
            try {
                const service = await server.getPrimaryService(profile.service);
                if (service) {
                    const char = await service.getCharacteristic(profile.writeChar);
                    if (char && (char.properties.write || char.properties.writeWithoutResponse)) {
                        foundChar = char;
                        console.log(`[RPP02] Terhubung ke Service: ${profile.service}, Char: ${profile.writeChar}`);
                        break;
                    }
                }
            } catch (e) {
                // Lewati jika bukan profile ini
            }
        }

        // Fallback dinamis jika UUID di atas berubah
        if (!foundChar && typeof server.getPrimaryServices === 'function') {
            try {
                const services = await server.getPrimaryServices();
                for (const s of services) {
                    try {
                        const chars = await s.getCharacteristics();
                        for (const c of chars) {
                            if (c.properties.write || c.properties.writeWithoutResponse) {
                                foundChar = c;
                                break;
                            }
                        }
                    } catch (err) {}
                    if (foundChar) break;
                }
            } catch (err) {}
        }

        if (!foundChar) {
            throw new Error('Karakteristik write printer tidak dapat diakses.');
        }

        writeCharacteristic = foundChar;
        setConnectedState(true);
        setStatus('Terhubung: ' + devName);
        setPrintStatus('Printer terhubung (' + devName + '). Siap cetak bukti.', 'success');
    }

    async function handleConnectClick() {
        if (printerDevice && printerDevice.gatt && printerDevice.gatt.connected) {
            if (isPrinting) {
                setPrintStatus('Sedang mencetak, tunggu sebentar sebelum memutuskan.', 'warning');
                return;
            }
            try {
                printerDevice.gatt.disconnect();
            } catch (e) {}
            handleDisconnect();
            return;
        }

        if (!window.isSecureContext) {
            setStatus('Gagal: Butuh HTTPS.');
            setPrintStatus('Web Bluetooth membutuhkan koneksi aman HTTPS.', 'error');
            return;
        }

        // Deteksi iOS Safari/Chrome vs Web Bluetooth
        if (!('bluetooth' in navigator)) {
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || 
                          (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

            if (isIOS) {
                const currentUrl = window.location.href;
                const bluefyUrl = 'bluefy://' + currentUrl;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Akses Bluetooth di iPhone / iOS',
                        html: `
                            <p class="text-xs text-slate-600 mb-3 text-left leading-relaxed">
                                Apple membatasi akses Web Bluetooth pada Safari & Chrome iOS. Agar printer <strong>RPP02</strong> dapat terhubung langsung:
                            </p>
                            <div class="text-xs text-left space-y-2 mb-3 bg-blue-50/80 p-3 rounded-xl border border-blue-200">
                                <p class="text-blue-900 font-bold flex items-center gap-1.5">
                                    <i class="ti ti-sparkles text-blue-600"></i> Cara Termudah (Browser Bluefy):
                                </p>
                                <ol class="list-decimal list-inside space-y-1 text-slate-600">
                                    <li>Install aplikasi gratis <strong>Bluefy</strong> di App Store.</li>
                                    <li>Pastikan RPP02 sudah di-<strong>Forget Device</strong> dari Settings Bluetooth iPhone.</li>
                                    <li>Buka web ini di Bluefy lalu klik Hubungkan (jika muncul pop-up PIN, klik <strong>Cancel</strong>).</li>
                                </ol>
                            </div>
                            <div class="flex flex-col gap-2 pt-1">
                                <a href="${bluefyUrl}" class="w-full py-2.5 px-3 rounded-xl bg-blue-600 text-white font-bold text-xs flex items-center justify-center gap-1.5 no-underline hover:bg-blue-700 transition shadow-xs">
                                    <i class="ti ti-external-link"></i> Buka Langsung di Aplikasi Bluefy
                                </a>
                                <a href="https://apps.apple.com/app/bluefy-web-ble-browser/id1492822055" target="_blank" class="w-full py-2 px-3 rounded-xl bg-slate-100 text-slate-700 font-semibold text-xs flex items-center justify-center gap-1.5 no-underline hover:bg-slate-200 transition border border-slate-200">
                                    <i class="ti ti-download"></i> Belum Punya? Unduh Bluefy di App Store
                                </a>
                            </div>
                        `,
                        showCancelButton: true,
                        showConfirmButton: true,
                        confirmButtonText: '<i class="ti ti-printer mr-1"></i> Cetak Alternatif (AirPrint)',
                        cancelButtonText: 'Tutup',
                        confirmButtonColor: '#475569',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open("{{ route('guru.cetak-struk', $dispensasi) }}?autoprint=1", "_blank");
                        }
                    });
                } else {
                    alert('iPhone membatasi Bluetooth di Safari & Chrome. Buka melalui aplikasi Bluefy untuk koneksi Bluetooth langsung.');
                }
            } else {
                setStatus('Browser tidak didukung.');
                setPrintStatus('Gunakan Google Chrome di HP Android atau Laptop.', 'error');
            }
            return;
        }

        try {
            setStatus('Memindai printer Bluetooth...');
            setPrintStatus('Silakan pilih RPP02 di dialog pemindai...', 'info');

            // Gunakan acceptAllDevices agar printer RPP02 tidak disaring oleh browser
            const device = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: OPTIONAL_SERVICES
            });

            await connectToDevice(device);

        } catch (error) {
            console.error('BLE connection error:', error);
            writeCharacteristic = null;
            printerDevice = null;
            setConnectedState(false);

            if (error.name === 'NotFoundError') {
                setStatus('Pemilihan printer dibatalkan.');
                setPrintStatus('Pemilihan printer dibatalkan atau waktu habis. Pastikan GPS & Bluetooth HP aktif.', 'warning');
            } else if (error.name === 'SecurityError') {
                setStatus('Izin Bluetooth ditolak.');
                setPrintStatus('Izin Bluetooth ditolak oleh browser.', 'error');
            } else {
                setStatus('Gagal terhubung: ' + error.message);
                setPrintStatus('Gagal terhubung: ' + error.message + '. PENTING di iPhone/Android: Lupakan/Forget Device printer dari menu Bluetooth HP, dan jika muncul pop-up PIN tekan Cancel.', 'error');
            }
        }
    }

    function handleDisconnect() {
        writeCharacteristic = null;
        printerDevice = null;
        setConnectedState(false);
        setStatus('Belum terhubung');
        setPrintStatus('Printer terputus. Silakan hubungkan kembali.', 'warning');
    }

    /*
     * ============================================================
     * ESC/POS TRANSMISSION (DENGAN CHUNKING & ROBUST WRITER)
     * ============================================================
     */
    async function sendEscPos(data) {
        if (!writeCharacteristic) {
            throw new Error('Printer belum terhubung.');
        }

        const props = writeCharacteristic.properties || {};

        // Pilih writer sesuai properti karakteristik + ketersediaan method
        let writer;
        if (props.writeWithoutResponse && typeof writeCharacteristic.writeValueWithoutResponse === 'function') {
            writer = (chunk) => writeCharacteristic.writeValueWithoutResponse(chunk);
        } else if (props.write && typeof writeCharacteristic.writeValueWithResponse === 'function') {
            writer = (chunk) => writeCharacteristic.writeValueWithResponse(chunk);
        } else if (typeof writeCharacteristic.writeValue === 'function') {
            writer = (chunk) => writeCharacteristic.writeValue(chunk);
        } else {
            throw new Error('Printer tidak mendukung method write yang dikenali.');
        }

        for (let i = 0; i < data.length; i += CHUNK_SIZE) {
            const chunk = data.slice(i, i + CHUNK_SIZE);
            try {
                await writer(chunk);
            } catch (e) {
                // Fallback sekali ke writeValue legacy jika method khusus gagal
                console.warn('[RPP02] Writer utama gagal, fallback ke writeValue():', e.message);
                if (typeof writeCharacteristic.writeValue === 'function') {
                    await writeCharacteristic.writeValue(chunk);
                } else {
                    throw e;
                }
            }
            if (CHUNK_DELAY > 0) {
                await new Promise(resolve => setTimeout(resolve, CHUNK_DELAY));
            }
        }
    }

    /*
     * ============================================================
     * FORMAT TEKS & PENCETAKAN STRUK
     * ============================================================
     */
    function wrapText(value, width = 32) {
        const text = String(value ?? '-').trim();
        if (!text) return ['-'];

        const words = text.split(/\s+/);
        const lines = [];
        let current = '';

        for (const word of words) {
            if (word.length > width) {
                if (current) {
                    lines.push(current);
                    current = '';
                }
                for (let i = 0; i < word.length; i += width) {
                    lines.push(word.substring(i, i + width));
                }
                continue;
            }

            const candidate = current ? current + ' ' + word : word;
            if (candidate.length <= width) {
                current = candidate;
            } else {
                if (current) lines.push(current);
                current = word;
            }
        }

        if (current) lines.push(current);
        return lines.length ? lines : ['-'];
    }

    function printField(label, value) {
        const prefix = label.padEnd(8, ' ') + ': ';
        const available = 32 - prefix.length;
        const lines = wrapText(value, Math.max(10, available));

        let result = textBytes(prefix + lines[0] + '\n');
        for (let i = 1; i < lines.length; i++) {
            result = concatBytes(
                result,
                textBytes(' '.repeat(prefix.length) + lines[i] + '\n')
            );
        }
        return result;
    }

    function buildReceipt() {
        const parts = [];

        // 1. Inisialisasi printer
        parts.push(bytes(ESC, 0x40));

        // 2. Kop Surat
        parts.push(
            alignCenter(),
            bold(true),
            textBytes('SMKN 1 BANGSRI\n'),
            bold(false),
            textBytes('Sistem Informasi Dispensasi\n'),
            textBytes('--------------------------------\n'),
            bold(true),
            textBytes('BUKTI DISPENSASI\n'),
            bold(false),
            textBytes('--------------------------------\n'),
            alignLeft(),
            printField('No.Surat', receiptData.nomor_surat),
            ...(receiptData.is_rombongan ? [
                printField('Tipe', 'Rombongan (' + receiptData.total_siswa + ' Siswa)'),
                printField('Ketua', receiptData.nama),
            ] : [
                printField('Nama', receiptData.nama),
            ]),
            printField('NIS', receiptData.nis),
            printField('Kelas', receiptData.kelas),
            printField('Tujuan', receiptData.tujuan),
            printField('Lokasi', receiptData.lokasi),
            printField('Waktu', receiptData.jam_keluar + ' - ' + receiptData.jam_kembali),
            textBytes('--------------------------------\n')
        );

        // 3. Rombongan
        if (receiptData.is_rombongan && receiptData.anggota && receiptData.anggota.length > 0) {
            parts.push(
                bold(true),
                textBytes('ANGGOTA ROMBONGAN (' + receiptData.total_siswa + ' SISWA):\n'),
                bold(false),
                textBytes('1. ' + receiptData.nama + ' (Ketua)\n   ' + receiptData.kelas + ' - NIS: ' + receiptData.nis + '\n')
            );
            receiptData.anggota.forEach((ang, idx) => {
                parts.push(
                    textBytes((idx + 2) + '. ' + ang.nama + '\n   ' + ang.kelas + ' - NIS: ' + ang.nis + '\n')
                );
            });
            parts.push(textBytes('--------------------------------\n'));
        }

        // 4. QR Code Validasi Pos Satpam
        if (receiptData.qr_token) {
            const qrPayload = JSON.stringify({ token: receiptData.qr_token });
            parts.push(
                alignCenter(),
                bold(true),
                textBytes('[ QR CODE VALIDASI ]\n\n'),
                bold(false),
                qrCodeBytes(qrPayload, 6),
                textBytes('\n\nScan di Pos Satpam\n'),
                textBytes('--------------------------------\n')
            );
        }

        // 5. Pengesahan Guru Piket
        parts.push(
            alignCenter(),
            textBytes('Bangsri, ' + receiptData.tanggal + '\n'),
            textBytes('Guru Piket,\n\n\n\n'),
            bold(true),
            textBytes(receiptData.nama_guru + '\n'),
            bold(false)
        );

        if (receiptData.nip_guru) {
            parts.push(textBytes('NIP. ' + receiptData.nip_guru + '\n'));
        }

        // 6. Penutup & Feed
        parts.push(
            textBytes('--------------------------------\n'),
            textBytes('Dicetak: ' + receiptData.dicetak + ' WIB\n'),
            textBytes('STRUK INI SAH JIKA \n'),
            textBytes('DITANDATANGANI \n'),
            textBytes('OLEH GURU PIKET.\n'),
            bold(true),
            textBytes('- TERIMA KASIH -\n'),
            bold(false),
            feed(4),
            ...(PRINTER_HAS_CUTTER ? [cut()] : [feed(2)])
        );

        return concatBytes(...parts);
    }

    /*
     * ============================================================
     * PRINT ACTIONS
     * ============================================================
     */
    async function printReceipt() {
        if (!isPrintAllowed()) {
            setStatus('Pencetakan hanya untuk status disetujui.');
            return;
        }

        if (!writeCharacteristic) {
            setStatus('Printer belum terhubung.');
            return;
        }

        isPrinting = true;
        try {
            printBtn.disabled = true;
            testBtn.disabled = true;
            printBtn.innerHTML = '<i class="ti ti-loader-2 animate-spin text-base"></i><span>Mencetak Bukti...</span>';

            setStatus('Mengirim ke printer...');
            setPrintStatus('Mengirim data bukti dispensasi...', 'info');

            const data = buildReceipt();
            await sendEscPos(data);

            setStatus('Struk berhasil dicetak.');
            setPrintStatus('Struk berhasil dicetak di RPP02.', 'success');

        } catch (error) {
            console.error('Print error:', error);
            setStatus('Gagal mencetak: ' + error.message);
            setPrintStatus('Gagal mencetak: ' + error.message, 'error');
        } finally {
            isPrinting = false;
            printBtn.innerHTML = '<i class="ti ti-receipt text-base transition-transform duration-200 group-hover:scale-110"></i><span class="tracking-wide">Cetak Bukti (Bluetooth Langsung)</span>';
            const connected = !!(printerDevice && printerDevice.gatt && printerDevice.gatt.connected && writeCharacteristic);
            setConnectedState(connected);
        }
    }

    async function testPrint() {
        if (!writeCharacteristic) {
            setStatus('Printer belum terhubung.');
            return;
        }

        isPrinting = true;
        try {
            printBtn.disabled = true;
            testBtn.disabled = true;
            testBtn.innerHTML = '<i class="ti ti-loader-2 animate-spin text-base"></i><span>Mengirim Test...</span>';

            setStatus('Mengirim test print...');
            setPrintStatus('Mengirim data test print...', 'info');

            const devName = printerDevice?.name || 'RPP02';
            const data = concatBytes(
                bytes(ESC, 0x40),
                alignCenter(),
                bold(true),
                textBytes('SMKN 1 BANGSRI\n'),
                textBytes('DIDISPEN TEST PRINT\n'),
                bold(false),
                textBytes('--------------------------------\n'),
                textBytes('Koneksi BLE RPP02 Berhasil\n'),
                textBytes('Target  : ISSC Microchip UART\n'),
                textBytes('Printer : ' + devName + '\n'),
                textBytes('Waktu   : ' + receiptData.dicetak + ' WIB\n'),
                textBytes('--------------------------------\n'),
                textBytes('Printer siap digunakan!\n'),
                feed(4),
                ...(PRINTER_HAS_CUTTER ? [cut()] : [feed(2)])
            );

            await sendEscPos(data);
            setStatus('Test print berhasil.');
            setPrintStatus('Test print RPP02 berhasil dicetak.', 'success');

        } catch (error) {
            console.error('Test print error:', error);
            setStatus('Gagal test print: ' + error.message);
            setPrintStatus('Gagal test print: ' + error.message, 'error');
        } finally {
            isPrinting = false;
            testBtn.innerHTML = '<i class="ti ti-test-pipe text-base transition-transform duration-200 group-hover:rotate-12"></i><span>Test Print BLE</span>';
            const connected = !!(printerDevice && printerDevice.gatt && printerDevice.gatt.connected && writeCharacteristic);
            setConnectedState(connected);
        }
    }

    if (connectBtn) connectBtn.addEventListener('click', handleConnectClick);
    if (printBtn) printBtn.addEventListener('click', printReceipt);
    if (testBtn) testBtn.addEventListener('click', testPrint);

    // Diagnostik UI
    const httpsEl = document.getElementById('ble-https');
    const apiEl = document.getElementById('ble-api');
    const browserEl = document.getElementById('ble-browser');

    if (httpsEl) httpsEl.textContent = window.isSecureContext ? 'YES (Aman)' : 'NO (Perlu HTTPS)';
    if (apiEl) apiEl.textContent = ('bluetooth' in navigator) ? 'AVAILABLE (Didukung)' : 'NOT AVAILABLE';
    if (browserEl) browserEl.textContent = navigator.userAgent;

})();
</script>