
{{-- ============================================================
     PRINTER THERMAL BLE
     ============================================================ --}}

<div class="mt-4 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

    {{-- HEADER --}}
    <div class="p-4 sm:p-5 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">

        <div class="flex items-start justify-between gap-3">

            {{-- Identitas Printer --}}
            <div class="flex items-center gap-3 min-w-0">

                <div
                    class="w-10 h-10 shrink-0 rounded-xl bg-blue-50 text-blue-600
                           flex items-center justify-center"
                >
                    <i class="fas fa-print text-lg"></i>
                </div>

                <div class="min-w-0">

                    <h3 class="text-sm font-bold text-slate-800 tracking-tight">
                        Printer Thermal BLE
                    </h3>

                    <div class="flex items-center gap-1.5 mt-0.5">

                        <span
                            id="printer-dot"
                            class="w-2 h-2 shrink-0 rounded-full bg-slate-300"
                        ></span>

                        <p
                            id="printer-status"
                            class="text-xs font-medium text-slate-500 truncate"
                        >
                            Belum terhubung
                        </p>

                    </div>

                </div>

            </div>


            {{-- CONNECT / DISCONNECT --}}
            <button
                type="button"
                id="btn-connect-printer"
                class="inline-flex items-center justify-center gap-1.5
                       px-3.5 py-2 min-h-[38px]
                       bg-slate-900 hover:bg-slate-800
                       text-white text-xs font-semibold
                       rounded-xl transition-all shadow-sm
                       active:scale-95 shrink-0"
            >
                <i class="fas fa-bluetooth-b"></i>
                <span>Hubungkan</span>
            </button>

        </div>


        {{-- STATUS PENCETAKAN --}}
        <div class="mt-3">

            @if(in_array($dispensasi->status, ['disetujui', 'keluar', 'selesai']))

                <div
                    id="printer-print-status"
                    class="inline-flex items-center gap-1.5
                           text-xs font-medium
                           text-emerald-700
                           bg-emerald-50
                           px-2.5 py-1.5
                           rounded-lg
                           border border-emerald-100"
                >

                    <i
                        id="printer-print-status-icon"
                        class="fas fa-circle-check text-[10px]"
                    ></i>

                    <span id="printer-print-status-text">
                        Dispensasi disetujui, siap cetak
                    </span>

                </div>

            @else

                <div
                    id="printer-print-status"
                    class="inline-flex items-center gap-1.5
                           text-xs font-medium
                           text-amber-700
                           bg-amber-50
                           px-2.5 py-1.5
                           rounded-lg
                           border border-amber-100"
                >

                    <i
                        id="printer-print-status-icon"
                        class="fas fa-circle-exclamation text-[10px]"
                    ></i>

                    <span id="printer-print-status-text">
                        Pencetakan hanya aktif jika status disetujui
                    </span>

                </div>

            @endif

        </div>

    </div>


    {{-- AKSI CETAK --}}
    <div class="p-4 sm:p-5">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

            {{-- CETAK BUKTI --}}
            <button
                type="button"
                id="btn-print-bluetooth"
                disabled
                class="group inline-flex items-center justify-center
                       gap-2 px-4 py-3 min-h-[46px]
                       bg-emerald-600 hover:bg-emerald-700
                       disabled:bg-slate-100
                       disabled:text-slate-400
                       disabled:border
                       disabled:border-slate-200
                       disabled:cursor-not-allowed
                       text-white text-xs font-semibold
                       rounded-xl transition-all
                       shadow-sm
                       active:scale-[0.98]"
            >
                <i class="fas fa-file-invoice text-sm"></i>

                <span>
                    Cetak Bukti Dispensasi
                </span>

            </button>


            {{-- TEST PRINT --}}
            <button
                type="button"
                id="btn-test-print"
                disabled
                class="group inline-flex items-center justify-center
                       gap-2 px-4 py-3 min-h-[46px]
                       bg-slate-100
                       hover:bg-slate-200
                       disabled:bg-slate-50
                       disabled:text-slate-300
                       disabled:border
                       disabled:border-slate-100
                       disabled:cursor-not-allowed
                       text-slate-700
                       text-xs font-semibold
                       rounded-xl transition-all
                       active:scale-[0.98]"
            >
                <i class="fas fa-vial text-sm"></i>

                <span>
                    Test Print
                </span>

            </button>

        </div>


        {{-- INFO ANDROID --}}
        <div
            class="mt-3 flex items-start gap-2
                   px-3 py-2.5
                   rounded-xl
                   bg-slate-50
                   border border-slate-100"
        >

            <i
                class="fas fa-circle-info
                       text-slate-400
                       text-[11px]
                       mt-0.5 shrink-0"
            ></i>

            <p class="text-[11px] leading-relaxed text-slate-500">
                Jika gagal terhubung di HP Android, pastikan
                <strong class="font-semibold text-slate-600">
                    Bluetooth
                </strong>
                dan
                <strong class="font-semibold text-slate-600">
                    Lokasi/GPS
                </strong>
                sudah aktif.
            </p>

        </div>

    </div>


    {{-- DIAGNOSTIK --}}
    <div class="border-t border-slate-100 bg-slate-50/60">

        <details class="group">

            <summary
                class="flex items-center justify-between
                       px-4 py-3
                       text-[11px] font-semibold
                       text-slate-500
                       cursor-pointer
                       hover:text-slate-700
                       select-none"
            >

                <span class="flex items-center gap-1.5">

                    <i class="fas fa-microchip text-slate-400"></i>

                    <span>
                        Diagnostik Web Bluetooth
                    </span>

                </span>

                <i
                    class="fas fa-chevron-down
                           text-[10px]
                           text-slate-400
                           transition-transform
                           duration-200
                           group-open:rotate-180"
                ></i>

            </summary>


            <div
                class="px-4 pb-4
                       text-[11px]
                       font-mono
                       text-slate-600"
            >

                {{-- HTTPS --}}
                <div
                    class="flex items-center justify-between
                           gap-3
                           py-2
                           border-b border-slate-200/70"
                >

                    <span class="text-slate-500">
                        HTTPS
                    </span>

                    <span
                        id="ble-https"
                        class="font-bold text-slate-700"
                    >
                        checking...
                    </span>

                </div>


                {{-- WEB BLUETOOTH --}}
                <div
                    class="flex items-center justify-between
                           gap-3
                           py-2
                           border-b border-slate-200/70"
                >

                    <span class="text-slate-500">
                        Web Bluetooth
                    </span>

                    <span
                        id="ble-api"
                        class="font-bold text-slate-700"
                    >
                        checking...
                    </span>

                </div>


                {{-- BROWSER --}}
                <div class="pt-2">

                    <div class="text-slate-400 mb-1">
                        Browser
                    </div>

                    <div
                        id="ble-browser"
                        class="text-[10px]
                               leading-relaxed
                               text-slate-500
                               break-all"
                    >
                        checking...
                    </div>

                </div>

            </div>

        </details>

    </div>

</div>



@php
    $extractTime = function ($value) {
        if (empty($value)) {
            return null;
        }

        preg_match(
            '/\b(\d{1,2}[:.]\d{2})\b/',
            (string) $value,
            $matches
        );

        return $matches[1] ?? null;
    };

    $waktuKeluar = $extractTime(
        \App\Helpers\TimeHelper::getWaktuAktual(
            $dispensasi->jam_keluar
        )
    );

    $waktuKembali = $extractTime(
        \App\Helpers\TimeHelper::getWaktuAktual(
            $dispensasi->jam_kembali
        )
    );

    $namaGuru = $dispensasi->guru?->nama_lengkap
        ?? $dispensasi->guru?->user?->name
        ?? $dispensasi->user?->name
        ?? 'Guru Piket';

    $nipGuru = $dispensasi->guru?->nip
        ?? $dispensasi->guru?->user?->nis_nip
        ?? $dispensasi->user?->nis_nip
        ?? null;

       $receiptData = [
        'nomor_surat' => $dispensasi->nomor_surat ?? '-',
        'nis' => $dispensasi->siswa?->user?->nis_nip ?? '-',
        'nama' => $dispensasi->siswa?->nama_lengkap ?? '-',
        'kelas' => $dispensasi->siswa?->kelas?->nama_kelas ?? '-',
        'tujuan' => $dispensasi->tujuan ?? '-',
        'lokasi' => $dispensasi->lokasi ?? '-',
        'jam_keluar' => $waktuKeluar ?? '-',
        'jam_kembali' => $waktuKembali ?? '-',
        'nama_guru' => $namaGuru,
        'nip_guru' => $nipGuru,
        'tanggal' => now()->format('d/m/Y'),
        'dicetak' => now()->format('d/m/Y H:i'),
        'qr_token' => $dispensasi->qr_token ?? '',

        'logo_url' => asset('images/logo-didispen1.png'),
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

    /**
     * Membentuk Uint8Array dari argumen byte / array byte
     */
    function bytes(...args) {
        return new Uint8Array(args.flat(Infinity));
    }

    /**
     * Menggabungkan beberapa Uint8Array menjadi satu
     */
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

    /**
     * Mengonversi string ke Uint8Array (UTF-8)
     */
    function textBytes(text) {
        return new TextEncoder().encode(String(text ?? ''));
    }

    /**
     * ESC/POS Text Alignment
     */
    function alignLeft() {
        return bytes(ESC, 0x61, 0x00);
    }

    function alignCenter() {
        return bytes(ESC, 0x61, 0x01);
    }

    function alignRight() {
        return bytes(ESC, 0x61, 0x02);
    }

    /**
     * ESC/POS Font Bold
     */
    function bold(enable = true) {
        return bytes(ESC, 0x45, enable ? 0x01 : 0x00);
    }

    /**
     * ESC/POS Feed Lines
     */
    function feed(lines = 1) {
        return bytes(ESC, 0x64, lines);
    }

    /**
     * ESC/POS Partial Cut
     */
    function cut() {
        return bytes(GS, 0x56, 0x42, 0x00);
    }

    /**
     * ESC/POS Native 2D Barcode (QR Code Model 2)
     */
    function qrCodeBytes(text, size = 6) {
        if (!text) return new Uint8Array();
        const data = new TextEncoder().encode(String(text));
        const len = data.length + 3;
        const pL = len & 0xFF;
        const pH = (len >> 8) & 0xFF;
        return concatBytes(
            // Model 2
            bytes(GS, 0x28, 0x6B, 0x04, 0x00, 0x31, 0x41, 0x32, 0x00),
            // Size (1 - 8)
            bytes(GS, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x43, Math.min(Math.max(size, 1), 8)),
            // Error correction level M (49)
            bytes(GS, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x45, 0x31),
            // Store data
            bytes(GS, 0x28, 0x6B, pL, pH, 0x31, 0x50, 0x30),
            data,
            // Print symbol
            bytes(GS, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x51, 0x30)
        );
    }

    /*
     * ============================================================
     * BLE UUIDS & CONFIGURATION
     * Mendukung berbagai model printer thermal 58mm/80mm (RPP02, POS-58, GOOJPRT, PT-210, dll.)
     * ============================================================
     */
    const PRINTER_SERVICES = [
        '0000ffe0-0000-1000-8000-00805f9b34fb', // Standard FFE0 (Mayoritas printer thermal 58mm/80mm)
        '49535343-fe7d-4ae5-8fa9-9fafd205e455', // ISSC / Microchip BLE UART (RPP02, dll.)
        '0000fee7-0000-1000-8000-00805f9b34fb', // Tencent / Wechat POS
        '000018f0-0000-1000-8000-00805f9b34fb', // Standard 18F0
        'e7810a71-73ae-499d-8c15-faa9aef0c3f2', // Alternate POS-58
        '0000ff00-0000-1000-8000-00805f9b34fb'
    ];

    const KNOWN_WRITE_UUIDS = [
        '0000ffe1-0000-1000-8000-00805f9b34fb',
        '49535343-8841-43f4-a8d4-ecbe34729bb3',
        '49535343-1e4d-4bd9-ba61-23c647249616',
        '0000fec7-0000-1000-8000-00805f9b34fb',
        '0000fec8-0000-1000-8000-00805f9b34fb',
        '00002af1-0000-1000-8000-00805f9b34fb',
        'bef8d6c9-9c21-4c9e-b632-bd58c1009f9f'
    ];

    const CHUNK_SIZE = 32;  // 32 byte aman untuk ATT MTU BLE 4.0/4.2
    const CHUNK_DELAY = 25; // 25ms delay mencegah buffer overrun pada printer murah

    let printerDevice = null;
    let writeCharacteristic = null;

    const receiptData = @js($receiptData);

    const statusEl = document.getElementById('printer-status');
    const printStatusEl = document.getElementById('printer-print-status');
    const connectBtn = document.getElementById('btn-connect-printer');
    const printBtn = document.getElementById('btn-print-bluetooth');
    const testBtn = document.getElementById('btn-test-print');
    const statusDot = document.getElementById('printer-dot');

    console.log('DIDISPEN receiptData:', receiptData);

    /*
     * ============================================================
     * UI STATUS MANAGEMENT
     * ============================================================
     */
    function setStatus(text) {
        if (statusEl) {
            statusEl.textContent = text;
        }
    }

    function setPrintStatus(text, type = 'info') {
        const textEl = document.getElementById('printer-print-status-text');
        const iconEl = document.getElementById('printer-print-status-icon');
        if (!printStatusEl) return;

        const configs = {
            success: {
                wrapper: 'inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1.5 rounded-lg border border-emerald-100',
                icon: 'fas fa-circle-check text-[10px]'
            },
            warning: {
                wrapper: 'inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-1.5 rounded-lg border border-amber-100',
                icon: 'fas fa-circle-exclamation text-[10px]'
            },
            error: {
                wrapper: 'inline-flex items-center gap-1.5 text-xs font-medium text-rose-700 bg-rose-50 px-2.5 py-1.5 rounded-lg border border-rose-100',
                icon: 'fas fa-circle-xmark text-[10px]'
            },
            info: {
                wrapper: 'inline-flex items-center gap-1.5 text-xs font-medium text-blue-700 bg-blue-50 px-2.5 py-1.5 rounded-lg border border-blue-100',
                icon: 'fas fa-circle-info text-[10px]'
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
                ? 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse'
                : 'w-2 h-2 rounded-full bg-slate-300';
        }

        if (!connectBtn) return;

        if (connected) {
            connectBtn.innerHTML = '<i class="fas fa-unlink"></i><span>Putuskan</span>';
            connectBtn.className = 'inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold rounded-xl transition-all active:scale-95 shrink-0';
        } else {
            connectBtn.innerHTML = '<i class="fas fa-bluetooth-b"></i><span>Hubungkan</span>';
            connectBtn.className = 'inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] bg-slate-900 hover:bg-slate-800 text-white text-xs font-medium rounded-xl transition-all shadow-sm active:scale-95 shrink-0';
        }
    }

    /*
     * ============================================================
     * BLE CONNECTION & DISCOVERY
     * ============================================================
     */
    async function connectToDevice(device) {
        printerDevice = device;

        printerDevice.removeEventListener('gattserverdisconnected', handleDisconnect);
        printerDevice.addEventListener('gattserverdisconnected', handleDisconnect);

        const devName = printerDevice.name || 'Printer Thermal';
        setStatus('Menghubungkan ke ' + devName + '...');
        setPrintStatus('Menghubungkan ke ' + devName + '...', 'info');

        const server = await printerDevice.gatt.connect();

        let foundCharacteristic = null;

        // 1. Cari service dan characteristic yang mendukung write dari daftar dikenal
        for (const serviceUuid of PRINTER_SERVICES) {
            try {
                const service = await server.getPrimaryService(serviceUuid);
                if (service) {
                    try {
                        const characteristics = await service.getCharacteristics();
                        for (const char of characteristics) {
                            if (char.properties.write || char.properties.writeWithoutResponse) {
                                foundCharacteristic = char;
                                break;
                            }
                        }
                    } catch (e) {
                        // Jika getCharacteristics dibatasi, coba UUID penulisan langsung
                        for (const writeUuid of KNOWN_WRITE_UUIDS) {
                            try {
                                const char = await service.getCharacteristic(writeUuid);
                                if (char) {
                                    foundCharacteristic = char;
                                    break;
                                }
                            } catch (err) {}
                        }
                    }
                    if (foundCharacteristic) break;
                }
            } catch (err) {
                // Service ini tidak ada di printer, lanjut cari
            }
        }

        // 2. Fallback: gunakan getPrimaryServices jika browser mengizinkan
        if (!foundCharacteristic && typeof server.getPrimaryServices === 'function') {
            try {
                const services = await server.getPrimaryServices();
                for (const service of services) {
                    try {
                        const chars = await service.getCharacteristics();
                        for (const char of chars) {
                            if (char.properties.write || char.properties.writeWithoutResponse) {
                                foundCharacteristic = char;
                                break;
                            }
                        }
                    } catch (e) {}
                    if (foundCharacteristic) break;
                }
            } catch (e) {}
        }

        if (!foundCharacteristic) {
            throw new Error('Karakteristik penulisan data printer tidak ditemukan.');
        }

        writeCharacteristic = foundCharacteristic;
        setConnectedState(true);
        setStatus('Terhubung: ' + devName);
        setPrintStatus('Printer terhubung (' + devName + '). Siap cetak.', 'success');
        console.log('BLE connected:', devName, 'Characteristic:', writeCharacteristic.uuid);
    }

    async function findAuthorizedPrinter() {
        if (!navigator.bluetooth || !navigator.bluetooth.getDevices) {
            return null;
        }

        try {
            const devices = await navigator.bluetooth.getDevices();
            if (!devices || !devices.length) {
                return null;
            }

            return devices.find(device => {
                const name = (device.name || '').toUpperCase();
                return name.includes('RPP') || name.includes('POS') || name.includes('PRINTER') || name.includes('PT-') || name.includes('MPT');
            }) || devices[0];
        } catch (error) {
            console.warn('getDevices gagal:', error);
            return null;
        }
    }

    async function requestNewPrinter() {
        setStatus('Memilih printer Bluetooth...');
        setPrintStatus('Silakan pilih printer di dialog Bluetooth...', 'info');

        const device = await navigator.bluetooth.requestDevice({
            acceptAllDevices: true,
            optionalServices: PRINTER_SERVICES
        });

        await connectToDevice(device);
    }

    async function handleConnectClick() {
        if (printerDevice && printerDevice.gatt && printerDevice.gatt.connected) {
            try {
                printerDevice.gatt.disconnect();
            } catch (error) {
                console.warn(error);
            }
            handleDisconnect();
            return;
        }

        if (!window.isSecureContext) {
            setStatus('Gagal: Web Bluetooth membutuhkan koneksi HTTPS.');
            setPrintStatus('Halaman harus diakses melalui HTTPS untuk Web Bluetooth.', 'error');
            return;
        }

        if (!('bluetooth' in navigator)) {
            setStatus('Web Bluetooth tidak didukung browser ini.');
            setPrintStatus('Gunakan Google Chrome di Android atau Desktop.', 'error');
            return;
        }

        try {
            const authorizedDevice = await findAuthorizedPrinter();
            if (authorizedDevice) {
                try {
                    await connectToDevice(authorizedDevice);
                    return;
                } catch (error) {
                    console.warn('Auto connect authorized device gagal, buka dialog baru:', error);
                    writeCharacteristic = null;
                    setConnectedState(false);
                }
            }

            await requestNewPrinter();

        } catch (error) {
            console.error('BLE connection error:', error);
            writeCharacteristic = null;
            printerDevice = null;
            setConnectedState(false);

            if (error.name === 'NotFoundError') {
                setStatus('Pemilihan printer dibatalkan.');
                setPrintStatus('Pemilihan printer dibatalkan.', 'info');
            } else if (error.name === 'SecurityError') {
                setStatus('Akses Bluetooth ditolak browser.');
                setPrintStatus('Izin Bluetooth ditolak. Aktifkan Bluetooth & Lokasi HP Anda.', 'error');
            } else {
                setStatus('Gagal terhubung: ' + error.message);
                setPrintStatus('Gagal terhubung: ' + error.message + '. Pastikan Bluetooth & Lokasi aktif.', 'error');
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
     * ESC/POS TRANSMISSION
     * ============================================================
     */
    async function sendEscPos(data) {
        if (!writeCharacteristic) {
            throw new Error('Printer belum terhubung.');
        }

        const canWriteWithoutResponse = writeCharacteristic.properties && writeCharacteristic.properties.writeWithoutResponse;

        for (let i = 0; i < data.length; i += CHUNK_SIZE) {
            const chunk = data.slice(i, i + CHUNK_SIZE);

            if (canWriteWithoutResponse && typeof writeCharacteristic.writeValueWithoutResponse === 'function') {
                await writeCharacteristic.writeValueWithoutResponse(chunk);
            } else if (typeof writeCharacteristic.writeValueWithResponse === 'function') {
                await writeCharacteristic.writeValueWithResponse(chunk);
            } else {
                await writeCharacteristic.writeValue(chunk);
            }

            if (CHUNK_DELAY > 0) {
                await new Promise(resolve => setTimeout(resolve, CHUNK_DELAY));
            }
        }
    }

    /*
     * ============================================================
     * IMAGE -> ESC/POS RASTER BITMAP
     * ============================================================
     */
    async function loadLogoBitmap(url) {
        const response = await fetch(url, { cache: 'force-cache' });
        if (!response.ok) {
            throw new Error('Logo tidak dapat dimuat.');
        }
        const blob = await response.blob();
        return await createImageBitmap(blob);
    }

    function imageToRaster(image, maxWidth = 384) {
        let width = image.width;
        let height = image.height;

        if (width > maxWidth) {
            const ratio = maxWidth / width;
            width = maxWidth;
            height = Math.round(height * ratio);
        }

        width = Math.max(8, Math.floor(width / 8) * 8);

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, width, height);
        ctx.drawImage(image, 0, 0, width, height);

        const imageData = ctx.getImageData(0, 0, width, height);
        const pixels = imageData.data;
        const bytesPerRow = width / 8;
        const bitmap = new Uint8Array(bytesPerRow * height);

        for (let y = 0; y < height; y++) {
            for (let x = 0; x < width; x++) {
                const index = (y * width + x) * 4;
                const r = pixels[index];
                const g = pixels[index + 1];
                const b = pixels[index + 2];
                const a = pixels[index + 3];

                if (a < 128) continue;

                const gray = (0.299 * r + 0.587 * g + 0.114 * b);
                if (gray < 160) {
                    const byteIndex = y * bytesPerRow + Math.floor(x / 8);
                    bitmap[byteIndex] |= (0x80 >> (x % 8));
                }
            }
        }

        const xL = bytesPerRow & 0xFF;
        const xH = (bytesPerRow >> 8) & 0xFF;
        const yL = height & 0xFF;
        const yH = (height >> 8) & 0xFF;

        return concatBytes(
            bytes(GS, 0x76, 0x30, 0x00, xL, xH, yL, yH),
            bitmap
        );
    }

    async function buildLogo() {
        if (!receiptData.logo_url) {
            return new Uint8Array();
        }

        let image = null;
        try {
            image = await loadLogoBitmap(receiptData.logo_url);
            const logo = imageToRaster(image, 384);
            return concatBytes(
                alignCenter(),
                logo,
                textBytes('\n')
            );
        } catch (error) {
            console.warn('Logo dilewati:', error.message);
            return new Uint8Array();
        } finally {
            if (image && typeof image.close === 'function') {
                image.close();
            }
        }
    }

    /*
     * ============================================================
     * TEXT HELPERS
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
        const prefix = label.padEnd(9, ' ') + ': ';
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

    /*
     * ============================================================
     * RECEIPT COMPOSITION
     * ============================================================
     */
    async function buildReceipt() {
        const parts = [];

        // Reset printer
        parts.push(bytes(ESC, 0x40));

        // 1. Logo
        const logo = await buildLogo();
        if (logo && logo.length > 0) {
            parts.push(logo);
        }

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
            printField('No. Surat', receiptData.nomor_surat),
            printField('NIS', receiptData.nis),
            printField('Nama', receiptData.nama),
            printField('Kelas', receiptData.kelas),
            printField('Tujuan', receiptData.tujuan),
            printField('Lokasi', receiptData.lokasi),
            printField('Jam', receiptData.jam_keluar + ' - ' + receiptData.jam_kembali),
            textBytes('--------------------------------\n')
        );

        // 3. QR Code Validasi Real-Time (untuk Scan Pos Satpam)
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

        // 4. Tanda Tangan Guru Piket
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

        // 5. Catatan Kaki & Pemotong Kertas
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
            cut()
        );

        return concatBytes(...parts);
    }

    /*
     * ============================================================
     * PRINT RECEIPT ACTION
     * ============================================================
     */
    async function printReceipt() {
        if (!isPrintAllowed()) {
            setStatus('Pencetakan hanya tersedia untuk dispensasi yang disetujui.');
            setPrintStatus('Pencetakan dinonaktifkan karena status belum disetujui.', 'warning');
            return;
        }

        if (!writeCharacteristic) {
            setStatus('Hubungkan printer terlebih dahulu.');
            setPrintStatus('Printer belum terhubung. Klik tombol Hubungkan.', 'warning');
            return;
        }

        try {
            printBtn.disabled = true;
            testBtn.disabled = true;

            setStatus('Menyiapkan struk...');
            setPrintStatus('Menyiapkan data struk...', 'info');

            const data = await buildReceipt();
            console.log('ESC/POS bytes:', data.length);

            const devName = printerDevice?.name || 'Printer';
            setStatus('Mengirim ke ' + devName + '...');
            setPrintStatus('Mengirim struk ke printer...', 'info');

            await sendEscPos(data);

            setStatus('Struk berhasil dicetak.');
            setPrintStatus('Struk berhasil dikirim ke printer.', 'success');

        } catch (error) {
            console.error('BLE print error:', error);
            setStatus('Gagal mencetak: ' + error.message);
            setPrintStatus('Gagal mencetak: ' + error.message, 'error');
        } finally {
            const connected = !!(printerDevice && printerDevice.gatt && printerDevice.gatt.connected && writeCharacteristic);
            setConnectedState(connected);
        }
    }

    /*
     * ============================================================
     * TEST PRINT ACTION
     * ============================================================
     */
    async function testPrint() {
        if (!isPrintAllowed()) {
            setStatus('Test print dinonaktifkan karena dispensasi belum disetujui.');
            setPrintStatus('Test print hanya aktif untuk dispensasi yang disetujui.', 'warning');
            return;
        }

        if (!writeCharacteristic) {
            setStatus('Hubungkan printer terlebih dahulu.');
            setPrintStatus('Printer belum terhubung.', 'warning');
            return;
        }

        try {
            printBtn.disabled = true;
            testBtn.disabled = true;

            setStatus('Menyiapkan test print...');
            setPrintStatus('Menyiapkan test print...', 'info');

            const logo = await buildLogo();
            const devName = printerDevice?.name || 'Thermal BLE';

            const data = concatBytes(
                bytes(ESC, 0x40),
                logo,
                alignCenter(),
                bold(true),
                textBytes('SMKN 1 BANGSRI\n'),
                textBytes('DIDISPEN TEST PRINT\n'),
                bold(false),
                textBytes('--------------------------------\n'),
                textBytes('ESC/POS BLE TEST BERHASIL\n'),
                textBytes('Printer : ' + devName + '\n'),
                textBytes('Waktu   : ' + receiptData.dicetak + ' WIB\n'),
                textBytes('--------------------------------\n'),
                textBytes('Printer siap digunakan untuk\ncetak bukti dispensasi.\n'),
                feed(4),
                cut()
            );

            console.log('Test print bytes:', data.length);
            setStatus('Mengirim test print...');
            setPrintStatus('Mengirim test print ke printer...', 'info');

            await sendEscPos(data);

            setStatus('Test print berhasil.');
            setPrintStatus('Test print berhasil dikirim ke printer.', 'success');

        } catch (error) {
            console.error('Test print error:', error);
            setStatus('Gagal test print: ' + error.message);
            setPrintStatus('Gagal test print: ' + error.message, 'error');
        } finally {
            const connected = !!(printerDevice && printerDevice.gatt && printerDevice.gatt.connected && writeCharacteristic);
            setConnectedState(connected);
        }
    }

    /*
     * ============================================================
     * EVENT LISTENERS
     * ============================================================
     */
    if (connectBtn) connectBtn.addEventListener('click', handleConnectClick);
    if (printBtn) printBtn.addEventListener('click', printReceipt);
    if (testBtn) testBtn.addEventListener('click', testPrint);

    /*
     * ============================================================
     * DIAGNOSTIK
     * ============================================================
     */
    const httpsEl = document.getElementById('ble-https');
    const apiEl = document.getElementById('ble-api');
    const browserEl = document.getElementById('ble-browser');

    if (httpsEl) httpsEl.textContent = window.isSecureContext ? 'YES (Aman)' : 'NO (Perlu HTTPS)';
    if (apiEl) apiEl.textContent = ('bluetooth' in navigator) ? 'AVAILABLE (Didukung)' : 'NOT AVAILABLE';
    if (browserEl) browserEl.textContent = navigator.userAgent;

    /*
     * ============================================================
     * AUTO RECONNECT
     * ============================================================
     */
    async function autoReconnect() {
        if (!window.isSecureContext || !('bluetooth' in navigator)) return;

        try {
            const device = await findAuthorizedPrinter();
            if (!device) return;

            console.log('Mencoba auto-reconnect:', device.name);
            await connectToDevice(device);
        } catch (error) {
            console.log('Auto reconnect dilewati:', error.message);
            setConnectedState(false);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoReconnect, { once: true });
    } else {
        autoReconnect();
    }

})();
</script>