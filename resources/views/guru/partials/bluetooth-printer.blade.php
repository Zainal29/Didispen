
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

            @if($dispensasi->status === 'disetujui')

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

        'logo_url' => asset('images/logo-didispen.png'),
        'print_enabled' => $dispensasi->status === 'disetujui',
    ];
@endphp

<script>
(function () {
    'use strict';

    const SERVICE_UUID =
        '49535343-fe7d-4ae5-8fa9-9fafd205e455';

    const WRITE_UUID =
        '49535343-8841-43f4-a8d4-ecbe34729bb3';

    const CHUNK_SIZE = 100;
    const CHUNK_DELAY = 30;

    let printerDevice = null;
    let writeCharacteristic = null;

    const receiptData = @js($receiptData);

    const statusEl =
        document.getElementById('printer-status');

    const printStatusEl =
        document.getElementById('printer-print-status');

    const connectBtn =
        document.getElementById('btn-connect-printer');

    const printBtn =
        document.getElementById('btn-print-bluetooth');

    const testBtn =
        document.getElementById('btn-test-print');

    console.log(
        'DIDISPEN receiptData:',
        receiptData
    );

    /*
     * ============================================================
     * STATUS
     * ============================================================
     */

    function setStatus(text) {
        if (statusEl) {
            statusEl.textContent = text;
        }
    }

   function setPrintStatus(text, type = 'info') {
    const textEl = document.getElementById(
        'printer-print-status-text'
    );

    const iconEl = document.getElementById(
        'printer-print-status-icon'
    );

    if (!printStatusEl) {
        return;
    }

    const configs = {
        success: {
            wrapper:
                'inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100',
            icon:
                'fas fa-circle-check text-[10px]'
        },

        warning: {
            wrapper:
                'inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-100',
            icon:
                'fas fa-circle-exclamation text-[10px]'
        },

        error: {
            wrapper:
                'inline-flex items-center gap-1.5 text-xs font-medium text-rose-700 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-100',
            icon:
                'fas fa-circle-xmark text-[10px]'
        },

        info: {
            wrapper:
                'inline-flex items-center gap-1.5 text-xs font-medium text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100',
            icon:
                'fas fa-circle-info text-[10px]'
        }
    };

    const config =
        configs[type] || configs.info;

    printStatusEl.className =
        config.wrapper;

    if (textEl) {
        textEl.textContent = text;
    }

    if (iconEl) {
        iconEl.className =
            config.icon;
    }
}

    /*
     * ============================================================
     * PRINT PERMISSION
     *
     * Hanya status disetujui yang boleh mencetak.
     * ============================================================
     */

    function isPrintAllowed() {
        return receiptData.print_enabled === true;
    }

   const statusDot = document.getElementById('printer-dot');

function setConnectedState(connected) {
    const canPrint =
        connected &&
        isPrintAllowed();

    if (printBtn) {
        printBtn.disabled = !canPrint;
    }

    if (testBtn) {
        testBtn.disabled = !canPrint;
    }

    if (statusDot) {
        statusDot.className = connected
            ? 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse'
            : 'w-2 h-2 rounded-full bg-slate-300';
    }

    if (!connectBtn) {
        return;
    }

    if (connected) {
        connectBtn.innerHTML =
            '<i class="fas fa-unlink"></i><span>Putuskan</span>';

        connectBtn.className =
            'inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold rounded-xl transition-all active:scale-95 shrink-0';

    } else {
        connectBtn.innerHTML =
            '<i class="fas fa-bluetooth-b"></i><span>Hubungkan</span>';

        connectBtn.className =
            'inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] bg-slate-900 hover:bg-slate-800 text-white text-xs font-medium rounded-xl transition-all shadow-sm active:scale-95 shrink-0';
    }
}

    /*
     * ============================================================
     * BLE CONNECTION
     * ============================================================
     */

    async function connectToDevice(device) {
        printerDevice = device;

        printerDevice.removeEventListener(
            'gattserverdisconnected',
            handleDisconnect
        );

        printerDevice.addEventListener(
            'gattserverdisconnected',
            handleDisconnect
        );

        setStatus(
            'Menghubungkan ke ' +
            (printerDevice.name || 'RPP02') +
            '...'
        );

        const server =
            await printerDevice.gatt.connect();

        const service =
            await server.getPrimaryService(
                SERVICE_UUID
            );

        writeCharacteristic =
            await service.getCharacteristic(
                WRITE_UUID
            );

        console.log(
            'BLE connected:',
            printerDevice.name
        );

        console.log(
            'Service:',
            service.uuid
        );

        console.log(
            'Characteristic:',
            writeCharacteristic.uuid
        );

        console.log(
            'Properties:',
            writeCharacteristic.properties
        );

        setConnectedState(true);

        setStatus(
            'Terhubung: ' +
            (printerDevice.name || 'RPP02')
        );
    }

    async function findAuthorizedPrinter() {
        if (!navigator.bluetooth.getDevices) {
            return null;
        }

        try {
            const devices =
                await navigator.bluetooth.getDevices();

            console.log(
                'Authorized BLE devices:',
                devices
            );

            if (!devices.length) {
                return null;
            }

            return devices.find(device =>
                (device.name || '')
                    .toUpperCase()
                    .includes('RPP02')
            ) || devices[0];

        } catch (error) {
            console.warn(
                'getDevices gagal:',
                error
            );

            return null;
        }
    }

    async function requestNewPrinter() {
        setStatus(
            'Memilih printer Bluetooth...'
        );

        const device =
            await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,

                optionalServices: [
                    SERVICE_UUID
                ]
            });

        await connectToDevice(device);
    }

    async function handleConnectClick() {
        if (
            printerDevice &&
            printerDevice.gatt &&
            printerDevice.gatt.connected
        ) {
            try {
                printerDevice.gatt.disconnect();

            } catch (error) {
                console.warn(error);
            }

            handleDisconnect();

            return;
        }

        if (!window.isSecureContext) {
            setStatus(
                'Gagal: halaman bukan HTTPS.'
            );

            return;
        }

        if (!('bluetooth' in navigator)) {
            setStatus(
                'Web Bluetooth tidak tersedia.'
            );

            return;
        }

        try {
            const authorizedDevice =
                await findAuthorizedPrinter();

            if (authorizedDevice) {
                try {
                    await connectToDevice(
                        authorizedDevice
                    );

                    return;

                } catch (error) {
                    console.warn(
                        'Auto reconnect gagal:',
                        error
                    );

                    writeCharacteristic = null;

                    setConnectedState(false);
                }
            }

            await requestNewPrinter();

        } catch (error) {
            console.error(
                'BLE connection error:',
                error
            );

            writeCharacteristic = null;

            setConnectedState(false);

            if (error.name === 'NotFoundError') {
                setStatus(
                    'Pemilihan printer dibatalkan.'
                );
            } else {
                setStatus(
                    'Gagal terhubung: ' +
                    error.message
                );
            }
        }
    }

    function handleDisconnect() {
        writeCharacteristic = null;

        setConnectedState(false);

        setStatus(
            'Printer terputus.'
        );
    }

    /*
     * ============================================================
     * ESC/POS TRANSMISSION
     * ============================================================
     */

   async function sendEscPos(data) {
    if (!writeCharacteristic) {
        throw new Error(
            'Printer belum terhubung.'
        );
    }

    for (
        let i = 0;
        i < data.length;
        i += CHUNK_SIZE
    ) {
        const chunk =
            data.slice(
                i,
                i + CHUNK_SIZE
            );

        await writeCharacteristic
            .writeValueWithoutResponse(
                chunk
            );

        await new Promise(resolve =>
            setTimeout(
                resolve,
                CHUNK_DELAY
            )
        );
    }
}

    /*
     * ============================================================
     * IMAGE -> ESC/POS RASTER BITMAP
     *
     * Logo PNG diubah menjadi bitmap hitam/putih.
     * ============================================================
     */

    async function loadLogoBitmap(url) {
        const response =
            await fetch(
                url,
                {
                    cache: 'force-cache'
                }
            );

        if (!response.ok) {
            throw new Error(
                'Logo tidak dapat dimuat.'
            );
        }

        const blob =
            await response.blob();

        return await createImageBitmap(
            blob
        );
    }

    function imageToRaster(
        image,
        maxWidth = 384
    ) {
        let width =
            image.width;

        let height =
            image.height;

        /*
         * Resize logo agar aman untuk
         * printer thermal 58mm.
         */
        if (width > maxWidth) {
            const ratio =
                maxWidth / width;

            width =
                maxWidth;

            height =
                Math.round(
                    height * ratio
                );
        }

        /*
         * Lebar harus kelipatan 8
         * karena bitmap ESC/POS menggunakan bit.
         */
        width =
            Math.max(
                8,
                Math.floor(
                    width / 8
                ) * 8
            );

        const canvas =
            document.createElement(
                'canvas'
            );

        canvas.width =
            width;

        canvas.height =
            height;

        const ctx =
            canvas.getContext(
                '2d',
                {
                    willReadFrequently: true
                }
            );

        ctx.fillStyle =
            '#FFFFFF';

        ctx.fillRect(
            0,
            0,
            width,
            height
        );

        ctx.drawImage(
            image,
            0,
            0,
            width,
            height
        );

        const imageData =
            ctx.getImageData(
                0,
                0,
                width,
                height
            );

        const pixels =
            imageData.data;

        const bytesPerRow =
            width / 8;

        const bitmap =
            new Uint8Array(
                bytesPerRow * height
            );

        for (
            let y = 0;
            y < height;
            y++
        ) {
            for (
                let x = 0;
                x < width;
                x++
            ) {
                const index =
                    (
                        y * width +
                        x
                    ) * 4;

                const r =
                    pixels[index];

                const g =
                    pixels[index + 1];

                const b =
                    pixels[index + 2];

                const a =
                    pixels[index + 3];

                /*
                 * Transparansi dianggap putih.
                 */
                if (a < 128) {
                    continue;
                }

                /*
                 * Grayscale.
                 */
                const gray =
                    (
                        0.299 * r +
                        0.587 * g +
                        0.114 * b
                    );

                /*
                 * Threshold.
                 *
                 * Semakin kecil nilai ini,
                 * semakin banyak area hitam.
                 */
                if (gray < 160) {
                    const byteIndex =
                        y * bytesPerRow +
                        Math.floor(x / 8);

                    bitmap[byteIndex] |=
                        (
                            0x80 >>
                            (x % 8)
                        );
                }
            }
        }

        /*
         * GS v 0
         *
         * 1D 76 30 00
         * xL xH yL yH
         * bitmap
         */
        const xL =
            bytesPerRow & 0xFF;

        const xH =
            (bytesPerRow >> 8) & 0xFF;

        const yL =
            height & 0xFF;

        const yH =
            (height >> 8) & 0xFF;

        return concatBytes(
            bytes(
                GS,
                0x76,
                0x30,
                0x00,
                xL,
                xH,
                yL,
                yH
            ),
            bitmap
        );
    }

    async function buildLogo() {
    if (!receiptData.logo_url) {
        return new Uint8Array();
    }

    let image = null;

    try {
        image = await loadLogoBitmap(
            receiptData.logo_url
        );

        const logo = imageToRaster(
            image,
            384
        );

        return concatBytes(
            alignCenter(),
            logo,
            textBytes('\n')
        );

    } catch (error) {
        console.warn(
            'Logo tidak dapat dicetak:',
            error
        );

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

    function wrapText(
        value,
        width = 32
    ) {
        const text =
            String(value ?? '-')
                .trim();

        if (!text) {
            return ['-'];
        }

        const words =
            text.split(/\s+/);

        const lines = [];

        let current = '';

        for (const word of words) {
            if (word.length > width) {
                if (current) {
                    lines.push(current);

                    current = '';
                }

                for (
                    let i = 0;
                    i < word.length;
                    i += width
                ) {
                    lines.push(
                        word.substring(
                            i,
                            i + width
                        )
                    );
                }

                continue;
            }

            const candidate =
                current
                    ? current + ' ' + word
                    : word;

            if (
                candidate.length <= width
            ) {
                current =
                    candidate;

            } else {
                if (current) {
                    lines.push(
                        current
                    );
                }

                current =
                    word;
            }
        }

        if (current) {
            lines.push(current);
        }

        return lines.length
            ? lines
            : ['-'];
    }

    function printField(
        label,
        value
    ) {
        const prefix =
            label.padEnd(
                10,
                ' '
            ) + ': ';

        const available =
            32 - prefix.length;

        const lines =
            wrapText(
                value,
                Math.max(
                    10,
                    available
                )
            );

        let result =
            textBytes(
                prefix +
                lines[0] +
                '\n'
            );

        for (
            let i = 1;
            i < lines.length;
            i++
        ) {
            result =
                concatBytes(
                    result,

                    textBytes(
                        ' '.repeat(
                            prefix.length
                        ) +
                        lines[i] +
                        '\n'
                    )
                );
        }

        return result;
    }

    /*
     * ============================================================
     * RECEIPT
     * ============================================================
     */

    async function buildReceipt() {
        const parts = [];

        /*
         * Logo.
         */
        const logo =
            await buildLogo();

        parts.push(
            bytes(
                ESC,
                0x40
            ),

            logo,

            alignCenter(),

            bold(true),

            textBytes(
                'SMKN 1 BANGSRI\n'
            ),

            bold(false),

            textBytes(
                'Sistem Informasi Dispensasi\n'
            ),

            textBytes(
                '--------------------------------\n'
            ),

            bold(true),

            textBytes(
                'BUKTI DISPENSASI\n'
            ),

            bold(false),

            textBytes(
                '--------------------------------\n'
            ),

            alignLeft(),

            printField(
                'No. Surat',
                receiptData.nomor_surat
            ),

            printField(
                'NIS',
                receiptData.nis
            ),

            printField(
                'Nama',
                receiptData.nama
            ),

            printField(
                'Kelas',
                receiptData.kelas
            ),

            printField(
                'Tujuan',
                receiptData.tujuan
            ),

            printField(
                'Lokasi',
                receiptData.lokasi
            ),

            printField(
                'Jam',
                receiptData.jam_keluar +
                ' - ' +
                receiptData.jam_kembali
            ),

            textBytes(
                '--------------------------------\n'
            ),

            alignCenter(),

            textBytes(
                'Bangsri, ' +
                receiptData.tanggal +
                '\n'
            ),

            textBytes(
                'Guru Piket,\n\n\n\n'
            ),

            bold(true),

            textBytes(
                receiptData.nama_guru +
                '\n'
            ),

            bold(false)
        );

        if (receiptData.nip_guru) {
            parts.push(
                textBytes(
                    'NIP. ' +
                    receiptData.nip_guru +
                    '\n'
                )
            );
        }

        parts.push(
            textBytes(
                '--------------------------------\n'
            ),

            textBytes(
                'Dicetak: ' +
                receiptData.dicetak +
                ' WIB\n'
            ),

            textBytes(
                'Struk ini sah jika ditandatangani\n'
            ),

            textBytes(
                'oleh Guru Piket.\n'
            ),

            bold(true),

            textBytes(
                '- TERIMA KASIH -\n'
            ),

            bold(false),

            feed(4),

            cut()
        );

        return concatBytes(
            ...parts
        );
    }

    /*
     * ============================================================
     * PRINT RECEIPT
     * ============================================================
     */

   async function printReceipt() {
    if (!isPrintAllowed()) {
        setStatus(
            'Pencetakan hanya tersedia untuk dispensasi yang disetujui.'
        );

        setPrintStatus(
            'Pencetakan dinonaktifkan karena status belum disetujui.',
            'warning'
        );

        return;
    }

    if (!writeCharacteristic) {
        setStatus(
            'Hubungkan printer terlebih dahulu.'
        );

        setPrintStatus(
            'Printer belum terhubung.',
            'warning'
        );

        return;
    }

    try {
        printBtn.disabled = true;
        testBtn.disabled = true;

        setStatus(
            'Menyiapkan logo dan struk...'
        );

        setPrintStatus(
            'Menyiapkan data struk...',
            'info'
        );

        const data =
            await buildReceipt();

        console.log(
            'ESC/POS bytes:',
            data.length
        );

        setStatus(
            'Mengirim struk ke RPP02...'
        );

        setPrintStatus(
            'Mengirim struk ke printer...',
            'info'
        );

        await sendEscPos(data);

        setStatus(
            'Struk berhasil dicetak.'
        );

        setPrintStatus(
            'Struk berhasil dikirim ke printer.',
            'success'
        );

    } catch (error) {
        console.error(
            'BLE print error:',
            error
        );

        setStatus(
            'Gagal mencetak: ' +
            error.message
        );

        setPrintStatus(
            'Gagal mencetak: ' +
            error.message,
            'error'
        );

    } finally {
        const connected =
            !!(
                printerDevice &&
                printerDevice.gatt &&
                printerDevice.gatt.connected &&
                writeCharacteristic
            );

        setConnectedState(
            connected
        );

        /*
         * Jangan menimpa pesan sukses/error
         * yang sudah ditampilkan di atas.
         */
    }
}

    /*
     * ============================================================
     * TEST PRINT
     * ============================================================
     */

    async function testPrint() {
    if (!isPrintAllowed()) {
        setStatus(
            'Test print dinonaktifkan karena dispensasi belum disetujui.'
        );

        setPrintStatus(
            'Test print hanya aktif untuk dispensasi yang disetujui.',
            'warning'
        );

        return;
    }

    if (!writeCharacteristic) {
        setStatus(
            'Hubungkan printer terlebih dahulu.'
        );

        setPrintStatus(
            'Printer belum terhubung.',
            'warning'
        );

        return;
    }

    try {
        printBtn.disabled = true;
        testBtn.disabled = true;

        setStatus(
            'Menyiapkan test print...'
        );

        setPrintStatus(
            'Menyiapkan test print...',
            'info'
        );

        const logo =
            await buildLogo();

        const data =
            concatBytes(
                bytes(
                    ESC,
                    0x40
                ),

                logo,

                alignCenter(),

                bold(true),

                textBytes(
                    'SMKN 1 BANGSRI\n'
                ),

                bold(false),

                textBytes(
                    'DIDISPEN\n'
                ),

                textBytes(
                    '--------------------------------\n'
                ),

                textBytes(
                    'ESC/POS BLE TEST\n'
                ),

                textBytes(
                    'RPP02\n'
                ),

                textBytes(
                    '--------------------------------\n'
                ),

                textBytes(
                    'Printer berhasil menerima data.\n'
                ),

                feed(4),

                cut()
            );

        console.log(
            'Test print bytes:',
            data.length
        );

        setStatus(
            'Mengirim test print...'
        );

        setPrintStatus(
            'Mengirim test print ke RPP02...',
            'info'
        );

        await sendEscPos(data);

        setStatus(
            'Test print berhasil.'
        );

        setPrintStatus(
            'Test print berhasil dikirim ke printer.',
            'success'
        );

    } catch (error) {
        console.error(
            'Test print error:',
            error
        );

        setStatus(
            'Gagal test print: ' +
            error.message
        );

        setPrintStatus(
            'Gagal test print: ' +
            error.message,
            'error'
        );

    } finally {
        const connected =
            !!(
                printerDevice &&
                printerDevice.gatt &&
                printerDevice.gatt.connected &&
                writeCharacteristic
            );

        setConnectedState(
            connected
        );
    }
}

    /*
     * ============================================================
     * EVENTS
     * ============================================================
     */

    if (connectBtn) {
        connectBtn.addEventListener(
            'click',
            handleConnectClick
        );
    }

    if (printBtn) {
        printBtn.addEventListener(
            'click',
            printReceipt
        );
    }

    if (testBtn) {
        testBtn.addEventListener(
            'click',
            testPrint
        );
    }

    /*
     * ============================================================
     * BLE INFORMATION
     * ============================================================
     */

    const httpsEl =
        document.getElementById(
            'ble-https'
        );

    const apiEl =
        document.getElementById(
            'ble-api'
        );

    const browserEl =
        document.getElementById(
            'ble-browser'
        );

    if (httpsEl) {
        httpsEl.textContent =
            window.isSecureContext
                ? 'YES'
                : 'NO';
    }

    if (apiEl) {
        apiEl.textContent =
            ('bluetooth' in navigator)
                ? 'AVAILABLE'
                : 'NOT AVAILABLE';
    }

    if (browserEl) {
        browserEl.textContent =
            navigator.userAgent;
    }

    /*
     * ============================================================
     * AUTO RECONNECT
     * ============================================================
     */

    async function autoReconnect() {
        if (
            !window.isSecureContext ||
            !('bluetooth' in navigator)
        ) {
            return;
        }

        try {
            const device =
                await findAuthorizedPrinter();

            if (!device) {
                return;
            }

            console.log(
                'Mencoba reconnect:',
                device.name
            );

            await connectToDevice(
                device
            );

        } catch (error) {
            console.log(
                'Auto reconnect dilewati:',
                error.message
            );

            setConnectedState(false);
        }
    }

    if (
        document.readyState ===
        'loading'
    ) {
        document.addEventListener(
            'DOMContentLoaded',
            autoReconnect,
            {
                once: true
            }
        );

    } else {
        autoReconnect();
    }

})();
</script>