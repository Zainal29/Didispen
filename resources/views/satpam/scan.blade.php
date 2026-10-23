@extends('satpam.layouts.app')

@section('title', 'Scan QR Code')
@section('page-title', 'Scan QR Code')

@section('content')
<div class="max-w-md mx-auto space-y-4">

    {{-- Instruksi & Tip Cepat --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5 flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center text-xl flex-shrink-0 shadow-2xs">
            <i class="ti ti-qrcode"></i>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-bold text-slate-900">Scan QR Pos Gerbang</h3>
            <p class="text-xs text-slate-500 mt-0.5">Arahkan kamera ke QR Code pada layar HP siswa.</p>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- PANEL KONTROL CEPAT POS GERBANG (SATPAM EXPRESS)         --}}
    {{-- ========================================================= --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-3.5 space-y-2.5">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                <i class="ti ti-bolt text-amber-500 text-sm"></i> Kontrol Operasional Pos
            </span>
            <span id="quickModeBadge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                Mode Cepat Aktif
            </span>
        </div>

        <div class="grid grid-cols-3 gap-2 text-center">
            {{-- Toggle 1: Mode Cepat (Express Auto-Confirm) --}}
            <button type="button"
                    id="btnToggleExpress"
                    onclick="toggleExpressMode()"
                    class="p-2.5 rounded-xl border text-xs font-bold flex flex-col items-center justify-center gap-1 transition-all active:scale-95 bg-emerald-50 border-emerald-300 text-emerald-800 shadow-2xs">
                <i class="ti ti-bolt text-base"></i>
                <span class="text-[10px]">Mode Cepat</span>
                <span id="labelExpress" class="text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded-full bg-emerald-200/80 text-emerald-900">ON</span>
            </button>

            {{-- Toggle 2: Audio Beep --}}
            <button type="button"
                    id="btnToggleBeep"
                    onclick="toggleBeepSound()"
                    class="p-2.5 rounded-xl border text-xs font-bold flex flex-col items-center justify-center gap-1 transition-all active:scale-95 bg-blue-50 border-blue-300 text-blue-800 shadow-2xs">
                <i id="iconBeep" class="ti ti-volume text-base"></i>
                <span class="text-[10px]">Suara Beep</span>
                <span id="labelBeep" class="text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded-full bg-blue-200/80 text-blue-900">ON</span>
            </button>

            {{-- Toggle 3: Screen Wake Lock (Anti Layar Mati) --}}
            <button type="button"
                    id="btnToggleWakeLock"
                    onclick="toggleWakeLock()"
                    class="p-2.5 rounded-xl border text-xs font-bold flex flex-col items-center justify-center gap-1 transition-all active:scale-95 bg-amber-50 border-amber-300 text-amber-800 shadow-2xs">
                <i id="iconWakeLock" class="ti ti-sun text-base"></i>
                <span class="text-[10px]">Layar ON</span>
                <span id="labelWakeLock" class="text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded-full bg-amber-200/80 text-amber-900">ON</span>
            </button>
        </div>

        <div id="wakeLockNotice" class="text-[10px] text-slate-500 flex items-center justify-between px-1">
            <span class="flex items-center gap-1">
                <i class="ti ti-sun text-amber-600 text-xs"></i>
                <span id="wakeLockText">Anti Layar Mati aktif (layar tidak akan redup/terkunci).</span>
            </span>
            <span class="text-[9px] text-slate-400 font-mono">Baterai Friendly</span>
        </div>
    </div>

    {{-- Kamera --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="relative bg-slate-950">
            {{-- Floating Overlay Mode Satpam Express --}}
            <div id="expressOverlay"
                 class="absolute inset-x-3 top-3 z-30 hidden transition-all duration-300 pointer-events-none">
                {{-- Dinamis diisi JS --}}
            </div>

            <div id="reader" class="w-full"></div>
        </div>
        <div class="p-3.5 flex items-center justify-between border-t border-slate-200">
            <span id="scanStatus" class="inline-flex items-center text-xs font-semibold text-emerald-600">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span> Kamera aktif
            </span>
            <button onclick="restartScanner()" class="inline-flex items-center px-3.5 py-2 min-h-[38px] rounded-xl text-xs font-bold text-rose-600 bg-rose-50 border border-rose-200 hover:bg-rose-100 transition-colors">
                <i class="ti ti-reload mr-1.5 text-sm"></i> Scan Ulang
            </button>
        </div>
    </div>

    {{-- VERIFIKASI MANUAL --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5 mt-4">
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Verifikasi Manual</p>
        <form onsubmit="manualVerify(event)" class="flex gap-2">
            @csrf
            <input type="text"
                   id="manualCode"
                   required
                   placeholder="Masukkan No. Surat, NIS, atau Nama"
                   class="flex-1 h-11 px-3.5 rounded-xl border border-slate-300 bg-white text-xs font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:border-rose-600 focus:ring-2 focus:ring-rose-600/20 transition-all">
            <button type="submit"
                    class="px-4 h-11 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-xs transition-colors flex items-center">
                <i class="ti ti-search mr-1.5 text-base"></i> Cek
            </button>
        </form>
        <p class="text-[10px] text-slate-500 mt-2 flex items-center">
            <i class="ti ti-info-circle mr-1 text-xs"></i> Gunakan jika QR Code tidak bisa discan.
        </p>
    </div>

    {{-- HASIL PENCARIAN MANUAL --}}
    <div id="manualSearchResult" class="hidden mt-4 space-y-3"></div>

</div>

{{-- ======================================================== --}}
{{-- MODAL POPUP HASIL SCAN QR & KONFIRMASI --}}
{{-- ======================================================== --}}
<div id="scanModal"
     class="fixed inset-0 z-[70] flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-sm hidden overflow-y-auto"
     onclick="handleBackdropClick(event)">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-200 my-auto transition-all max-h-[90dvh] flex flex-col"
         onclick="event.stopPropagation()">

        {{-- Modal Header --}}
        <div id="modalHeader" class="p-4 bg-slate-900 text-white flex items-center justify-between transition-colors flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <div id="modalHeaderIcon" class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-base">
                    <i class="ti ti-qrcode"></i>
                </div>
                <div>
                    <h3 id="modalTitle" class="text-sm font-bold text-white leading-tight">Hasil Scan QR Code</h3>
                    <p id="modalSubtitle" class="text-[11px] text-white/80">Memeriksa data dispensasi...</p>
                </div>
            </div>
            <button type="button"
                    onclick="closeScanModal()"
                    class="text-white/70 hover:text-white p-1.5 rounded-xl hover:bg-white/10 transition-colors"
                    title="Tutup (Esc)">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div id="modalBody" class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1 min-h-0">
            {{-- Content akan di-render secara dinamis oleh JavaScript --}}
        </div>

        {{-- Modal Footer --}}
        <div id="modalFooter" class="p-4 bg-slate-50 border-t border-slate-100 flex gap-2.5 flex-shrink-0 pb-6 sm:pb-4">
            {{-- Tombol aksi akan di-render secara dinamis --}}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
let html5QrCode;
let isScanning = true;
let isProcessing = false;
let isSubmitting = false;
let currentScannedQr = null;
let autoCloseTimer = null;
let expressResetTimer = null;
let isCooldownActive = false;
let cooldownCountdownTimer = null;

// Anti-Double Scan: Cooldown Map untuk QR yang sama (5 detik)
const scannedCooldownMap = new Map();
const QR_COOLDOWN_MS = 5000;

function isQrInCooldown(code) {
    if (!code) return false;
    const now = Date.now();
    const lastTime = scannedCooldownMap.get(code);
    if (lastTime && (now - lastTime) < QR_COOLDOWN_MS) {
        return Math.ceil((QR_COOLDOWN_MS - (now - lastTime)) / 1000);
    }
    return false;
}

function registerQrCooldown(code) {
    if (!code) return;
    scannedCooldownMap.set(code, Date.now());
    const now = Date.now();
    for (const [k, v] of scannedCooldownMap.entries()) {
        if (now - v > 60000) {
            scannedCooldownMap.delete(k);
        }
    }
}

function startScannerCooldown(seconds = 3, qrCode = null) {
    if (qrCode) {
        registerQrCooldown(qrCode);
    }

    if (cooldownCountdownTimer) {
        clearInterval(cooldownCountdownTimer);
        cooldownCountdownTimer = null;
    }

    isCooldownActive = true;
    isScanning = false;
    isProcessing = false;

    let remaining = seconds;
    const updateUI = (cnt) => {
        const el = document.getElementById('scanStatus');
        if (!el) return;
        el.innerHTML = `<span class="w-2 h-2 rounded-full bg-amber-500 animate-ping mr-1.5 flex-shrink-0"></span> Cooldown: Siap scan dalam ${cnt}s...`;
        el.className = 'inline-flex items-center text-xs font-semibold text-amber-600 truncate min-w-0';
    };

    updateUI(remaining);

    cooldownCountdownTimer = setInterval(() => {
        remaining--;
        if (remaining > 0) {
            updateUI(remaining);
        } else {
            clearInterval(cooldownCountdownTimer);
            cooldownCountdownTimer = null;
            isCooldownActive = false;
            isScanning = true;
            if (html5QrCode) {
                try { html5QrCode.resume(); } catch (e) {}
            }
            setStatus('Kamera aktif — siap scan', true);
        }
    }, 1000);
}

// Konfigurasi Default dari Admin / Local Preference Perangkat
let isExpressMode = localStorage.getItem('didispen_express_mode') !== null 
    ? (localStorage.getItem('didispen_express_mode') === 'true') 
    : {{ $defaultExpressMode ? 'true' : 'false' }};
let isBeepSound = localStorage.getItem('didispen_beep_sound') !== null 
    ? (localStorage.getItem('didispen_beep_sound') === 'true') 
    : {{ $defaultBeepSound ? 'true' : 'false' }};
let isWakeLockEnabled = localStorage.getItem('didispen_wake_lock') !== null 
    ? (localStorage.getItem('didispen_wake_lock') === 'true') 
    : {{ $defaultWakeLock ? 'true' : 'false' }};
let wakeLockSentinel = null;

// =========================================================================
// 1. WEB AUDIO API SYNTHESIZER (Tanpa File Eksternal, 100% Reliabel & Instan)
// =========================================================================
const AudioHelper = {
    ctx: null,
    init() {
        if (!this.ctx && (window.AudioContext || window.webkitAudioContext)) {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            this.ctx = new AudioCtx();
        }
    },
    beepSuccess() {
        if (!isBeepSound) return;
        try {
            this.init();
            if (!this.ctx) return;
            if (this.ctx.state === 'suspended') {
                this.ctx.resume();
            }
            const now = this.ctx.currentTime;
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();
            osc.connect(gain);
            gain.connect(this.ctx.destination);

            // Nada Chime Manis (880Hz -> 1320Hz)
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, now);
            osc.frequency.exponentialRampToValueAtTime(1320, now + 0.11);

            gain.gain.setValueAtTime(0.35, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.16);

            osc.start(now);
            osc.stop(now + 0.16);
        } catch (e) {
            console.warn('Audio beep error:', e);
        }
    },
    beepWarning() {
        if (!isBeepSound) return;
        try {
            this.init();
            if (!this.ctx) return;
            if (this.ctx.state === 'suspended') {
                this.ctx.resume();
            }
            const now = this.ctx.currentTime;
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();
            osc.connect(gain);
            gain.connect(this.ctx.destination);

            // Nada Peringatan Turun Sawtooth (420Hz -> 260Hz)
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(420, now);
            osc.frequency.linearRampToValueAtTime(260, now + 0.22);

            gain.gain.setValueAtTime(0.28, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.22);

            osc.start(now);
            osc.stop(now + 0.22);
        } catch (e) {
            console.warn('Audio warning error:', e);
        }
    }
};

// =========================================================================
// 2. SCREEN WAKE LOCK API (Anti Layar Mati di Pos Gerbang)
// =========================================================================
async function requestWakeLock() {
    if ('wakeLock' in navigator && isWakeLockEnabled) {
        try {
            if (wakeLockSentinel) {
                await wakeLockSentinel.release().catch(() => {});
                wakeLockSentinel = null;
            }
            wakeLockSentinel = await navigator.wakeLock.request('screen');
            wakeLockSentinel.addEventListener('release', () => {
                updateWakeLockUI(false);
            });
            updateWakeLockUI(true);
        } catch (err) {
            console.warn('Wake Lock request error:', err);
            updateWakeLockUI(false);
        }
    } else {
        updateWakeLockUI(false);
    }
}

function releaseWakeLock() {
    if (wakeLockSentinel !== null) {
        try { wakeLockSentinel.release(); } catch (e) {}
        wakeLockSentinel = null;
    }
    updateWakeLockUI(false);
}

function updateWakeLockUI(isActive) {
    const btn = document.getElementById('btnToggleWakeLock');
    const icon = document.getElementById('iconWakeLock');
    const label = document.getElementById('labelWakeLock');
    const noticeText = document.getElementById('wakeLockText');
    if (!btn || !label) return;

    if (isWakeLockEnabled && isActive) {
        btn.className = 'p-2.5 rounded-xl border text-xs font-bold flex flex-col items-center justify-center gap-1 transition-all active:scale-95 bg-amber-50 border-amber-300 text-amber-800 shadow-2xs';
        icon.className = 'ti ti-sun text-base text-amber-600 animate-spin-slow';
        label.className = 'text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded-full bg-amber-200/80 text-amber-900';
        label.textContent = 'ON';
        if (noticeText) noticeText.textContent = 'Anti Layar Mati aktif (layar tidak akan redup/terkunci).';
    } else if (isWakeLockEnabled && !isActive) {
        btn.className = 'p-2.5 rounded-xl border text-xs font-bold flex flex-col items-center justify-center gap-1 transition-all active:scale-95 bg-amber-50/60 border-amber-200 text-amber-700';
        label.textContent = 'STANDBY';
        if (noticeText) noticeText.textContent = 'Standby (otomatis aktif saat halaman scanner terlihat).';
    } else {
        btn.className = 'p-2.5 rounded-xl border text-xs font-bold flex flex-col items-center justify-center gap-1 transition-all active:scale-95 bg-slate-50 border-slate-200 text-slate-500';
        icon.className = 'ti ti-moon text-base text-slate-400';
        label.className = 'text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded-full bg-slate-200 text-slate-600';
        label.textContent = 'OFF';
        if (noticeText) noticeText.textContent = 'Mode Hemat Baterai (layar HP bisa redup otomatis).';
    }
}

// Otomatis lepas / minta wake lock saat tab diminimize atau aktif kembali
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible' && isWakeLockEnabled) {
        requestWakeLock();
    } else {
        releaseWakeLock();
    }
});

// =========================================================================
// 3. TOGGLE KONTROL (Mode Cepat, Suara Beep, Layar Menyala)
// =========================================================================
function toggleExpressMode() {
    AudioHelper.init();
    isExpressMode = !isExpressMode;
    localStorage.setItem('didispen_express_mode', isExpressMode);
    updateExpressUI();
}

function updateExpressUI() {
    const btn = document.getElementById('btnToggleExpress');
    const label = document.getElementById('labelExpress');
    const badge = document.getElementById('quickModeBadge');
    if (!btn || !label) return;

    if (isExpressMode) {
        btn.className = 'p-2.5 rounded-xl border text-xs font-bold flex flex-col items-center justify-center gap-1 transition-all active:scale-95 bg-emerald-50 border-emerald-300 text-emerald-800 shadow-2xs';
        label.className = 'text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded-full bg-emerald-200/80 text-emerald-900';
        label.textContent = 'ON';
        if (badge) {
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200';
            badge.textContent = 'Mode Cepat Aktif';
        }
    } else {
        btn.className = 'p-2.5 rounded-xl border text-xs font-bold flex flex-col items-center justify-center gap-1 transition-all active:scale-95 bg-blue-50/70 border-blue-200 text-blue-700';
        label.className = 'text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded-full bg-blue-100 text-blue-800';
        label.textContent = 'MODAL';
        if (badge) {
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200';
            badge.textContent = 'Mode Modal (Teliti)';
        }
    }
}

function toggleBeepSound() {
    AudioHelper.init();
    isBeepSound = !isBeepSound;
    localStorage.setItem('didispen_beep_sound', isBeepSound);
    updateBeepUI();
    if (isBeepSound) {
        AudioHelper.beepSuccess();
    }
}

function updateBeepUI() {
    const btn = document.getElementById('btnToggleBeep');
    const icon = document.getElementById('iconBeep');
    const label = document.getElementById('labelBeep');
    if (!btn || !label) return;

    if (isBeepSound) {
        btn.className = 'p-2.5 rounded-xl border text-xs font-bold flex flex-col items-center justify-center gap-1 transition-all active:scale-95 bg-blue-50 border-blue-300 text-blue-800 shadow-2xs';
        icon.className = 'ti ti-volume text-base';
        label.className = 'text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded-full bg-blue-200/80 text-blue-900';
        label.textContent = 'ON';
    } else {
        btn.className = 'p-2.5 rounded-xl border text-xs font-bold flex flex-col items-center justify-center gap-1 transition-all active:scale-95 bg-slate-50 border-slate-200 text-slate-500';
        icon.className = 'ti ti-volume-off text-base text-slate-400';
        label.className = 'text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded-full bg-slate-200 text-slate-600';
        label.textContent = 'OFF';
    }
}

function toggleWakeLock() {
    AudioHelper.init();
    isWakeLockEnabled = !isWakeLockEnabled;
    localStorage.setItem('didispen_wake_lock', isWakeLockEnabled);
    if (isWakeLockEnabled) {
        requestWakeLock();
    } else {
        releaseWakeLock();
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function setStatus(text, ok = true) {
    const el = document.getElementById('scanStatus');
    if (!el) return;
    el.innerHTML = `<span class="w-2 h-2 rounded-full ${ok ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'} mr-1.5 flex-shrink-0"></span> ${escapeHtml(text)}`;
    el.className = `inline-flex items-center text-xs font-semibold ${ok ? 'text-emerald-600' : 'text-amber-600'} truncate min-w-0`;
}

// =========================================================================
// 4. SCAN SUCCESS HANDLER (Express vs Manual)
// =========================================================================
function onScanSuccess(decodedText) {
    if (!isScanning || isProcessing || isCooldownActive) return;

    // Cek cooldown 15 detik untuk QR yang sama
    const cooldownSec = isQrInCooldown(decodedText);
    if (cooldownSec) {
        setStatus(`Cooldown: QR baru saja diproses. Tunggu ${cooldownSec}s...`, false);
        return;
    }

    isProcessing = true;
    isScanning = false;
    currentScannedQr = decodedText;

    try { html5QrCode.pause(); } catch (e) {}

    // Unlock AudioContext jika belum aktif
    AudioHelper.init();

    if (isExpressMode) {
        // Mode Satpam Express: Auto-Confirm tanpa harus memencet layar
        processExpressScan(decodedText);
    } else {
        // Mode Manual: Buka Modal Verifikasi Detail
        AudioHelper.beepSuccess();
        if (navigator.vibrate) navigator.vibrate(80);
        showModalLoading();
        checkQrData(decodedText);
    }
}

// Handler Khusus Mode Satpam Express (Auto-Confirm & Fast Feedback)
function processExpressScan(code) {
    AudioHelper.beepSuccess();
    if (navigator.vibrate) navigator.vibrate([100, 60, 100]);

    showExpressOverlayLoading();

    fetch('{{ route("satpam.scan.verify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ qr_data: code, action: 'auto' })
    })
    .then(async r => {
        const data = await r.json().catch(() => null);
        if (r.status === 429) {
            return {
                success: false,
                message: 'Terlalu banyak permintaan scan. Mohon tunggu beberapa detik.'
            };
        }
        if (!r.ok) {
            return {
                success: false,
                message: (data && data.message) ? data.message : `QR Code tidak dapat diverifikasi (${r.status}).`
            };
        }
        return data;
    })
    .then(data => {
        if (data && data.success) {
            registerQrCooldown(code);
            renderExpressSuccess(data);
        } else {
            hideExpressOverlay();
            AudioHelper.beepWarning();
            if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
            renderScanError((data && data.message) ? data.message : 'QR Code tidak valid atau dispensasi tidak ditemukan.');
        }
    })
    .catch(err => {
        console.error('Express scan error:', err);
        hideExpressOverlay();
        AudioHelper.beepWarning();
        renderScanError(err.message || 'Terjadi kesalahan jaringan.');
    });
}

function showExpressOverlayLoading() {
    const overlay = document.getElementById('expressOverlay');
    if (!overlay) return;

    overlay.className = 'absolute inset-x-3 top-3 z-30 block animate-in fade-in zoom-in-95 duration-200 pointer-events-none';
    overlay.innerHTML = `
        <div class="p-3 bg-slate-900/95 backdrop-blur-md text-white rounded-2xl border border-slate-700 shadow-2xl flex items-center gap-3">
            <div class="animate-spin rounded-full h-6 w-6 border-2 border-rose-500 border-t-transparent shrink-0"></div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-white flex items-center gap-1.5">
                    <i class="ti ti-bolt text-amber-400"></i> Mode Express Auto-Confirm
                </p>
                <p class="text-[11px] text-slate-300 truncate">Memverifikasi data dispensasi siswa...</p>
            </div>
        </div>
    `;
    setStatus('Express: Memverifikasi...', true);
}

function renderExpressSuccess(data) {
    const overlay = document.getElementById('expressOverlay');
    if (!overlay) return;

    AudioHelper.beepSuccess();
    if (navigator.vibrate) navigator.vibrate([150, 70, 150]);

    const isKeluar = data.action === 'keluar';
    const disp = data.data || {};
    const namaSiswa = disp.siswa?.nama_lengkap || 'Siswa DIDISPEN';
    const kelasSiswa = disp.siswa?.kelas?.nama_kelas || '';
    const noSurat = disp.nomor_surat || '';

    overlay.className = 'absolute inset-x-3 top-3 z-30 block animate-in fade-in zoom-in-95 duration-200 pointer-events-none';
    overlay.innerHTML = `
        <div class="p-3.5 bg-emerald-950/95 backdrop-blur-md text-white rounded-2xl border-2 border-emerald-500 shadow-2xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center shrink-0 text-xl font-bold">
                    <i class="ti ${isKeluar ? 'ti-door-exit' : 'ti-door-enter'}"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="px-2 py-0.2 rounded-md bg-emerald-500 text-slate-950 text-[9px] font-extrabold uppercase tracking-wider">
                            ${isKeluar ? 'BERHASIL KELUAR' : 'BERHASIL KEMBALI'}
                        </span>
                        <span class="text-[10px] font-mono text-emerald-200">${escapeHtml(noSurat)}</span>
                    </div>
                    <h4 class="text-xs font-bold text-white truncate mt-0.5">${escapeHtml(namaSiswa)}</h4>
                    <p class="text-[10px] text-emerald-300 font-medium">${escapeHtml(kelasSiswa)}</p>
                </div>
            </div>
            <div class="mt-2 pt-2 border-t border-emerald-800/80 flex items-center justify-between text-[10px] text-emerald-300">
                <span class="flex items-center gap-1">
                    <i class="ti ti-check text-emerald-400 font-bold"></i> Tercatat di sistem
                </span>
                <span class="font-bold text-white animate-pulse">Siap scan berikutnya...</span>
            </div>
        </div>
    `;

    setStatus(isKeluar ? 'Sukses: Siswa KELUAR tercatat' : 'Sukses: Siswa KEMBALI tercatat', true);

    const finishedQr = currentScannedQr;
    registerQrCooldown(finishedQr);

    if (expressResetTimer) clearTimeout(expressResetTimer);
    expressResetTimer = setTimeout(() => {
        hideExpressOverlay();
        currentScannedQr = null;
        startScannerCooldown(1, finishedQr);
    }, 1200);
}

function hideExpressOverlay() {
    const overlay = document.getElementById('expressOverlay');
    if (overlay) {
        overlay.classList.add('hidden');
        overlay.innerHTML = '';
    }
}

function setBottomNavVisible(visible) {
    const nav = document.getElementById('mobileBottomNav');
    if (!nav) return;
    if (visible) {
        nav.classList.remove('hidden');
    } else {
        nav.classList.add('hidden');
    }
}

// 1. Tampilkan loading di modal
function showModalLoading() {
    const modal = document.getElementById('scanModal');
    const header = document.getElementById('modalHeader');
    const headerIcon = document.getElementById('modalHeaderIcon');
    const title = document.getElementById('modalTitle');
    const subtitle = document.getElementById('modalSubtitle');
    const body = document.getElementById('modalBody');
    const footer = document.getElementById('modalFooter');

    header.className = 'p-4 bg-slate-900 text-white flex items-center justify-between transition-colors';
    headerIcon.innerHTML = '<i class="ti ti-qrcode"></i>';
    title.textContent = 'Memeriksa QR Code';
    subtitle.textContent = 'Menghubungkan ke server...';

    body.innerHTML = `
        <div class="py-12 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-2 border-rose-600 border-t-transparent mx-auto mb-3"></div>
            <h4 class="font-bold text-slate-900 text-base">Memverifikasi Data Siswa...</h4>
            <p class="text-xs text-slate-500 mt-1">Mohon tunggu sebentar, sedang mencocokkan kode QR.</p>
        </div>
    `;

    footer.innerHTML = `
        <button type="button" onclick="closeScanModal()" class="w-full py-2.5 px-4 min-h-[44px] rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
            Batal
        </button>
    `;

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    setBottomNavVisible(false);
    setStatus('Memverifikasi QR Code...', false);
}

// 2. Cek data QR Code (Mode: Check/Preview)
function checkQrData(code) {
    fetch('{{ route("satpam.scan.verify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ qr_data: code, action: 'check' })
    })
    .then(async r => {
        const data = await r.json().catch(() => null);
        if (r.status === 429) {
            return {
                success: false,
                message: 'Terlalu banyak permintaan scan. Mohon tunggu beberapa detik.'
            };
        }
        if (!r.ok) {
            return {
                success: false,
                message: (data && data.message) ? data.message : `QR Code tidak ditemukan atau server error (${r.status}).`
            };
        }
        return data;
    })
    .then(data => {
        if (data && data.success && data.mode === 'preview') {
            renderScanDetail(data.data);
        } else {
            renderScanError((data && data.message) ? data.message : 'QR Code tidak valid atau dispensasi tidak ditemukan.');
        }
    })
    .catch(err => {
        console.error('Fetch error:', err);
        renderScanError(err.message || 'Terjadi kesalahan jaringan saat memverifikasi QR Code.');
    });
}

// 3. Render detail dispensasi & tombol persetujuan di Modal
function renderScanDetail(data) {
    const header = document.getElementById('modalHeader');
    const headerIcon = document.getElementById('modalHeaderIcon');
    const title = document.getElementById('modalTitle');
    const subtitle = document.getElementById('modalSubtitle');
    const body = document.getElementById('modalBody');
    const footer = document.getElementById('modalFooter');

    let isKeluar = data.action_type === 'keluar';
    let isKembali = data.action_type === 'kembali';
    let isSelesai = data.status === 'selesai';

    // Header styling
    if (isKeluar) {
        header.className = 'p-4 bg-rose-600 text-white flex items-center justify-between transition-colors';
        headerIcon.innerHTML = '<i class="ti ti-door-exit text-lg"></i>';
        title.textContent = 'Konfirmasi Siswa KELUAR';
        subtitle.textContent = 'Dispensasi telah disetujui & siap keluar';
    } else if (isKembali) {
        header.className = 'p-4 bg-emerald-600 text-white flex items-center justify-between transition-colors';
        headerIcon.innerHTML = '<i class="ti ti-door-enter text-lg"></i>';
        title.textContent = 'Konfirmasi Siswa KEMBALI';
        subtitle.textContent = 'Siswa telah kembali ke lingkungan sekolah';
    } else {
        header.className = 'p-4 bg-slate-800 text-white flex items-center justify-between transition-colors';
        headerIcon.innerHTML = '<i class="ti ti-info-circle text-lg"></i>';
        title.textContent = 'Informasi Dispensasi';
        subtitle.textContent = `Status: ${data.status.toUpperCase()}`;
    }

    // Alerts
    let alertHtml = '';

    // Deteksi Riwayat Scan Keluar (Pembedaan Guru vs Satpam)
    if (data.riwayat_scan && data.riwayat_scan.keluar && data.riwayat_scan.keluar.sudah) {
        const rk = data.riwayat_scan.keluar;
        const isGuru = rk.role === 'guru';
        alertHtml += `
            <div class="p-3.5 ${isGuru ? 'bg-blue-50 border-2 border-blue-300' : 'bg-amber-50 border-2 border-amber-300'} rounded-xl text-xs text-slate-900 flex items-start gap-3 shadow-xs">
                <div class="w-8 h-8 rounded-lg ${isGuru ? 'bg-blue-600 text-white' : 'bg-amber-500 text-white'} flex items-center justify-center shrink-0 shadow-xs mt-0.5">
                    <i class="ti ${isGuru ? 'ti-user-check' : 'ti-shield-check'} text-lg"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="font-extrabold ${isGuru ? 'text-blue-950' : 'text-amber-950'} text-[11px] uppercase tracking-wide">
                            ${isGuru ? 'ℹ️ Siswa Sudah Di-scan Keluar oleh Guru Piket' : '⚠️ Siswa Sudah Di-scan Keluar di Pos Satpam'}
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${isGuru ? 'bg-blue-100 text-blue-900' : 'bg-amber-200 text-amber-900'} font-mono">
                            ${escapeHtml(rk.waktu)}
                        </span>
                    </div>
                    <p class="text-xs ${isGuru ? 'text-blue-900' : 'text-amber-900'} mt-1 leading-snug">
                        Siswa ini telah diverifikasi <strong class="font-bold">KELUAR</strong> oleh
                        <span class="font-bold underline decoration-current">${escapeHtml(rk.role_label)} (${escapeHtml(rk.nama)})</span>
                        pada pukul <strong class="font-mono">${escapeHtml(rk.waktu)}</strong>.
                    </p>
                </div>
            </div>
        `;
    }

    // Deteksi Riwayat Scan Kembali
    if (data.riwayat_scan && data.riwayat_scan.kembali && data.riwayat_scan.kembali.sudah) {
        const rkb = data.riwayat_scan.kembali;
        alertHtml += `
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-900 flex items-start gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="ti ti-circle-check text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="font-bold text-emerald-950 text-[11px]">RIWAYAT SCAN KEMBALI</span>
                        <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-emerald-200 text-emerald-900">${escapeHtml(rkb.waktu)}</span>
                    </div>
                    <p class="text-[11px] text-emerald-800 mt-0.5 leading-snug">
                        Diverifikasi <strong>KEMBALI</strong> oleh <strong>${escapeHtml(rkb.role_label)} (${escapeHtml(rkb.nama)})</strong> pada pukul <strong>${escapeHtml(rkb.waktu)}</strong>.
                    </p>
                </div>
            </div>
        `;
    }

    if (data.is_terlambat) {
        alertHtml += `
            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 font-semibold flex items-center gap-2.5">
                <i class="ti ti-alert-triangle text-rose-600 text-lg flex-shrink-0"></i>
                <div>
                    <p class="font-bold text-rose-900">PERINGATAN: TERLAMBAT KEMBALI!</p>
                    <p class="text-[11px] text-rose-700 font-normal">Siswa telah melewati batas jam kembali (${escapeHtml(data.jam_kembali)}).</p>
                </div>
            </div>
        `;
    }
    if (data.is_sampai_pulang && isKeluar) {
        alertHtml += `
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 font-semibold flex items-center gap-2.5">
                <i class="ti ti-info-circle text-amber-600 text-lg flex-shrink-0"></i>
                <div>
                    <p class="font-bold text-amber-900">Dispensasi Sampai Pulang</p>
                    <p class="text-[11px] text-amber-700 font-normal">Siswa diizinkan hingga jam pulang, tidak wajib scan kembali.</p>
                </div>
            </div>
        `;
    }

    // Foto verifikasi atau avatar
    let photoHtml = '';
    if (data.foto_verifikasi) {
        photoHtml = `
            <div class="relative flex-shrink-0">
                <img src="${data.foto_verifikasi}"
                     alt="Foto ${escapeHtml(data.siswa.nama_lengkap)}"
                     class="w-20 h-20 object-cover rounded-xl border-2 border-rose-500 shadow-xs bg-slate-100">
                <span class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 bg-rose-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full whitespace-nowrap shadow-xs">
                    Foto Wajah
                </span>
            </div>
        `;
    } else {
        photoHtml = `
            <div class="w-20 h-20 rounded-xl bg-rose-50 text-rose-600 flex flex-col items-center justify-center flex-shrink-0 border border-rose-100 shadow-2xs">
                <i class="ti ti-school text-3xl mb-1"></i>
                <span class="text-[9px] text-slate-500 font-semibold">Siswa</span>
            </div>
        `;
    }

    body.innerHTML = `
        ${alertHtml}

        {{-- Profil Siswa --}}
        <div class="flex items-start gap-3.5 p-3.5 bg-slate-50/80 rounded-xl border border-slate-200">
            ${photoHtml}
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 flex-wrap mb-1">
                    ${(data.tipe_pengajuan === 'rombongan' || data.tipe_pengajuan === 'organisasi') ? `
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                            <i class="ti ti-users text-xs"></i>
                            <span>Rombongan</span>
                            <span class="text-[9px] bg-purple-200/70 text-purple-800 px-1 rounded-sm ml-0.5 font-mono">${data.total_rombongan || 1} siswa</span>
                        </span>
                    ` : `
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            <i class="ti ti-user text-xs"></i>
                            <span>Individu</span>
                        </span>
                    `}
                </div>
                <h4 class="font-bold text-slate-900 text-base leading-snug">${escapeHtml(data.siswa.nama_lengkap)}</h4>
                <p class="text-xs text-slate-500 font-mono mt-0.5 flex items-center"><i class="ti ti-id text-slate-400 mr-1 text-sm"></i>NIS: ${escapeHtml(data.siswa.nis)}</p>
                <p class="text-xs font-semibold text-slate-700 mt-1 flex items-center"><i class="ti ti-school text-slate-400 mr-1 text-sm"></i>${escapeHtml(data.siswa.kelas)} • ${escapeHtml(data.siswa.jurusan)}</p>
                <p class="text-[11px] text-slate-500 mt-1 flex items-center"><i class="ti ti-user-shield text-slate-400 mr-1 text-sm"></i>Piket: <span class="font-medium text-slate-700 ml-1">${escapeHtml(data.guru)}</span></p>
            </div>
        </div>

        {{-- Ringkasan Surat & Jam --}}
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">No. Surat</span>
                <span class="font-mono font-bold text-slate-800 truncate block">${escapeHtml(data.nomor_surat)}</span>
            </div>
            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Waktu Dispensasi</span>
                <span class="font-bold text-slate-800 block">${escapeHtml(data.jam_keluar)} &rarr; ${escapeHtml(data.jam_kembali)}</span>
            </div>
        </div>

        ${data.status_note ? `
            <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-xl text-xs text-indigo-900 font-medium flex items-center gap-2.5">
                <i class="ti ti-info-circle text-indigo-600 text-lg flex-shrink-0"></i>
                <p class="leading-relaxed">${escapeHtml(data.status_note)}</p>
            </div>
        ` : ''}

        ${(data.tipe_pengajuan === 'rombongan' || data.tipe_pengajuan === 'organisasi') ? `
            <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl text-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-bold text-purple-900 flex items-center gap-1.5">
                        <i class="ti ti-users text-purple-600 text-sm"></i>
                        Dispensasi Rombongan
                    </span>
                    <span class="px-2 py-0.5 bg-purple-200 text-purple-800 text-[10px] font-bold rounded-full">
                        ${data.total_rombongan} Siswa
                    </span>
                </div>
                ${data.anggota && data.anggota.length > 0 ? `
                    <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                        <p class="text-[10px] font-bold text-purple-700 uppercase tracking-wide">Daftar Anggota:</p>
                        ${data.anggota.map((ang, idx) => `
                            <div class="flex items-center justify-between py-1 px-2 bg-white/80 rounded-lg border border-purple-100 text-[11px]">
                                <span class="font-medium text-slate-800">${idx + 1}. ${escapeHtml(ang.nama_lengkap)}</span>
                                <span class="text-slate-500 font-mono text-[10px]">${escapeHtml(ang.kelas)}</span>
                            </div>
                        `).join('')}
                    </div>
                ` : '<p class="text-[11px] text-purple-600 italic">Tidak ada anggota tambahan.</p>'}
            </div>
        ` : ''}

        {{-- Keperluan & Alasan --}}
        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Keperluan / Alasan</span>
            <p class="text-slate-800 font-medium leading-relaxed">${escapeHtml(data.alasan || '-')}</p>
            ${data.lokasi ? `<p class="text-[11px] text-slate-500 mt-1 flex items-center"><i class="ti ti-map-pin text-purple-500 mr-1 text-xs"></i>Lokasi: <strong class="ml-1 text-slate-700">${escapeHtml(data.lokasi)}</strong></p>` : ''}
            ${data.tujuan ? `<p class="text-[11px] text-slate-500 mt-1 flex items-center"><i class="ti ti-map-pin text-rose-500 mr-1 text-xs"></i>Tujuan: <strong class="ml-1 text-slate-700">${escapeHtml(data.tujuan)}</strong></p>` : ''}
        </div>

        {{-- Pertanyaan Konfirmasi --}}
        <div class="text-center pt-2">
            ${isKeluar ? `
                <p class="text-sm font-bold text-slate-900">
                    Setujui siswa ini untuk <span class="text-rose-600 underline decoration-rose-300 font-extrabold">KELUAR</span> dari sekolah?
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">Cocokkan wajah siswa dengan foto sebelum menyetujui.</p>
            ` : isKembali ? `
                <p class="text-sm font-bold text-slate-900">
                    Konfirmasi siswa ini telah <span class="text-emerald-600 underline decoration-emerald-300 font-extrabold">KEMBALI</span> ke sekolah?
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">Status dispensasi akan otomatis ditandai SELESAI.</p>
            ` : isSelesai ? `
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold flex items-center justify-center">
                    <i class="ti ti-circle-check mr-1.5 text-emerald-600 text-sm"></i> Dispensasi ini sudah selesai diproses.
                </div>
            ` : `
                <p class="text-xs text-slate-600">Dispensasi dalam status <strong>${escapeHtml(data.status)}</strong>. Tidak memerlukan aksi scan.</p>
            `}
        </div>
    `;

    // Footer actions
    if (isKeluar) {
        footer.innerHTML = `
            <button type="button"
                    onclick="closeScanModal()"
                    class="py-3 px-4 min-h-[44px] rounded-xl bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-semibold text-xs transition-colors">
                <i class="ti ti-x mr-1"></i> Batal
            </button>
            <button type="button"
                    id="btnConfirmAction"
                    onclick="submitConfirmation('keluar')"
                    class="flex-1 py-3 px-4 min-h-[44px] rounded-xl bg-rose-600 hover:bg-rose-700 active:scale-[0.98] text-white font-bold text-xs shadow-xs transition-all flex items-center justify-center gap-1.5">
                <i class="ti ti-door-exit text-base"></i> Ya, Setujui KELUAR
            </button>
        `;
    } else if (isKembali) {
        footer.innerHTML = `
            <button type="button"
                    onclick="closeScanModal()"
                    class="py-3 px-4 min-h-[44px] rounded-xl bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-semibold text-xs transition-colors">
                <i class="ti ti-x mr-1"></i> Batal
            </button>
            <button type="button"
                    id="btnConfirmAction"
                    onclick="submitConfirmation('kembali')"
                    class="flex-1 py-3 px-4 min-h-[44px] rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-bold text-xs shadow-xs transition-all flex items-center justify-center gap-1.5">
                <i class="ti ti-door-enter text-base"></i> Ya, Konfirmasi KEMBALI
            </button>
        `;
    } else {
        footer.innerHTML = `
            <button type="button"
                    onclick="closeScanModal()"
                    class="w-full py-3 px-4 min-h-[44px] rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition-colors flex items-center justify-center">
                <i class="ti ti-check mr-1.5 text-base"></i> Tutup & Scan Ulang
            </button>
        `;
    }

    setStatus('Menunggu konfirmasi...', true);
}

// 4. Render Error Modal
function renderScanError(message) {
    const header = document.getElementById('modalHeader');
    const headerIcon = document.getElementById('modalHeaderIcon');
    const title = document.getElementById('modalTitle');
    const subtitle = document.getElementById('modalSubtitle');
    const body = document.getElementById('modalBody');
    const footer = document.getElementById('modalFooter');

    header.className = 'p-4 bg-rose-600 text-white flex items-center justify-between transition-colors';
    headerIcon.innerHTML = '<i class="ti ti-alert-triangle text-lg"></i>';
    title.textContent = 'QR Code Tidak Valid';
    subtitle.textContent = 'Gagal memverifikasi dispensasi';

    body.innerHTML = `
        <div class="py-8 text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-3xl mb-3">
                <i class="ti ti-x"></i>
            </div>
            <h4 class="font-bold text-slate-900 text-base">Tidak Dapat Memproses</h4>
            <p class="text-xs text-slate-600 mt-1 max-w-sm mx-auto">${escapeHtml(message)}</p>
        </div>
    `;

    footer.innerHTML = `
        <button type="button"
                onclick="closeScanModal()"
                class="w-full py-3 px-4 min-h-[44px] rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition-colors flex items-center justify-center">
            <i class="ti ti-reload mr-1.5 text-base"></i> Scan Ulang
        </button>
    `;

    setStatus('Scan gagal — kode tidak valid', false);
}

// 5. Submit Konfirmasi (Keluar / Kembali)
function submitConfirmation(action) {
    const btn = document.getElementById('btnConfirmAction');
    if (!btn || !currentScannedQr || isSubmitting) return;

    isSubmitting = true;
    btn.disabled = true;
    btn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent mr-1.5 inline-block"></div> Memproses...';
    btn.classList.add('opacity-75', 'cursor-not-allowed', 'pointer-events-none');

    const codeToConfirm = currentScannedQr;

    fetch('{{ route("satpam.scan.verify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ qr_data: codeToConfirm, action: action })
    })
    .then(async r => {
        const data = await r.json().catch(() => null);
        if (!r.ok) {
            throw new Error((data && data.message) ? data.message : `Terjadi kesalahan pada server (${r.status})`);
        }
        return data;
    })
    .then(data => {
        isSubmitting = false;
        if (data && data.success) {
            registerQrCooldown(codeToConfirm);
            if (navigator.vibrate) navigator.vibrate([120, 60, 120]);
            renderSuccessState(data);
        } else {
            Swal.fire('Gagal', (data && data.message) ? data.message : 'Terjadi kesalahan saat konfirmasi.', 'error');
            btn.disabled = false;
            btn.innerHTML = action === 'keluar' ? '<i class="ti ti-door-exit mr-1.5 text-base"></i> Ya, Setujui KELUAR' : '<i class="ti ti-door-enter mr-1.5 text-base"></i> Ya, Konfirmasi KEMBALI';
            btn.classList.remove('opacity-75', 'cursor-not-allowed', 'pointer-events-none');
        }
    })
    .catch(err => {
        isSubmitting = false;
        console.error('Submit error:', err);
        Swal.fire('Gagal', err.message || 'Terjadi kesalahan jaringan.', 'error');
        btn.disabled = false;
        btn.innerHTML = action === 'keluar' ? '<i class="ti ti-door-exit mr-1.5 text-base"></i> Ya, Setujui KELUAR' : '<i class="ti ti-door-enter mr-1.5 text-base"></i> Ya, Konfirmasi KEMBALI';
        btn.classList.remove('opacity-75', 'cursor-not-allowed', 'pointer-events-none');
    });
}

// 6. Render State Sukses di Modal
function renderSuccessState(data) {
    const header = document.getElementById('modalHeader');
    const headerIcon = document.getElementById('modalHeaderIcon');
    const title = document.getElementById('modalTitle');
    const subtitle = document.getElementById('modalSubtitle');
    const body = document.getElementById('modalBody');
    const footer = document.getElementById('modalFooter');

    header.className = 'p-4 bg-emerald-600 text-white flex items-center justify-between transition-colors';
    headerIcon.innerHTML = '<i class="ti ti-check text-lg"></i>';
    title.textContent = 'Berhasil Dikonfirmasi!';
    subtitle.textContent = 'Data telah dicatat ke sistem';

    body.innerHTML = `
        <div class="py-8 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mb-3 shadow-inner">
                <i class="ti ti-check"></i>
            </div>
            <h4 class="font-bold text-slate-900 text-lg leading-snug">${escapeHtml(data.message)}</h4>
            <p class="text-xs text-slate-500 mt-2">Modal akan tertutup otomatis dalam 3 detik untuk scan berikutnya.</p>
        </div>
    `;

    footer.innerHTML = `
        <button type="button"
                onclick="closeScanModal(true)"
                class="w-full py-3 px-4 min-h-[44px] rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors shadow-xs flex items-center justify-center">
            <i class="ti ti-reload mr-1.5 text-base"></i> Scan Siswa Berikutnya
        </button>
    `;

    setStatus('Berhasil — siap scan berikutnya', true);

    if (autoCloseTimer) clearTimeout(autoCloseTimer);
    autoCloseTimer = setTimeout(() => {
        closeScanModal(true);
    }, 3000);
}

// 7. Tutup Modal & Nyalakan Kembali Scanner dengan Cooldown
function closeScanModal(startCooldown = true) {
    if (autoCloseTimer) {
        clearTimeout(autoCloseTimer);
        autoCloseTimer = null;
    }

    const modal = document.getElementById('scanModal');
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    setBottomNavVisible(true);

    const closedQr = currentScannedQr;
    currentScannedQr = null;
    isSubmitting = false;

    if (startCooldown) {
        startScannerCooldown(3, closedQr);
    } else {
        isProcessing = false;
        isScanning = true;
        if (html5QrCode) {
            try { html5QrCode.resume(); } catch (e) {}
        }
        setStatus('Kamera aktif — siap scan');
    }
}

function handleBackdropClick(e) {
    if (e.target.id === 'scanModal') {
        closeScanModal();
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('scanModal');
        if (modal && !modal.classList.contains('hidden')) {
            closeScanModal();
        }
    }
});

function restartScanner() {
    closeScanModal();
    document.getElementById('manualSearchResult').classList.add('hidden');
}

// VERIFIKASI MANUAL - AJAX
function manualVerify(e) {
    e.preventDefault();

    const code = document.getElementById('manualCode').value.trim();
    if (!code) {
        Swal.fire('Error', 'Masukkan nomor surat, NIS, atau nama siswa', 'error');
        return;
    }

    const resultDiv = document.getElementById('manualSearchResult');
    resultDiv.classList.remove('hidden');
    resultDiv.innerHTML = '<div class="text-center p-4"><div class="animate-spin rounded-full h-6 w-6 border-2 border-rose-600 border-t-transparent mx-auto mb-2"></div><p class="text-xs text-slate-500">Mencari data...</p></div>';

    fetch('{{ route("satpam.search-dispensasi") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ query: code })
    })
    .then(r => {
        const contentType = r.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server mengembalikan HTML, bukan JSON.');
        }
        return r.json();
    })
    .then(data => {
        if (data.success && data.data && data.data.length > 0) {
            let html = '';
            data.data.forEach(d => {
                let statusBadge = '';
                if (d.status === 'disetujui') {
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>DISETUJUI</span>`;
                } else if (d.status === 'keluar') {
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200"><span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>SEDANG KELUAR</span>`;
                } else if (d.status === 'selesai') {
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>SELESAI</span>`;
                } else {
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">${escapeHtml(d.status.toUpperCase())}</span>`;
                }

                let tagBadge = '';
                if (d.is_organisasi || d.tipe_pengajuan === 'rombongan') {
                    tagBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                        <i class="ti ti-users text-xs"></i>
                        <span>Rombongan</span>
                        <span class="text-[9px] bg-purple-200/70 text-purple-800 px-1 rounded-sm ml-0.5 font-mono">${d.total_rombongan} siswa</span>
                    </span>`;
                } else {
                    tagBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        <i class="ti ti-user text-xs"></i>
                        <span>Individu</span>
                    </span>`;
                }

                let pembinaHtml = '';

                let actionSection = '';
                if (d.status === 'disetujui') {
                    actionSection = `
                        <div class="flex flex-col sm:flex-row items-stretch gap-2 pt-1">
                            <button onclick="quickAction('keluar', ${d.id})" class="flex-1 px-4 py-2.5 min-h-[42px] bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition-colors shadow-2xs flex items-center justify-center">
                                <i class="ti ti-door-exit mr-1.5 text-base"></i> Konfirmasi KELUAR
                            </button>
                            <a href="${d.detail_url || '#'}" class="inline-flex items-center justify-center gap-1 px-3.5 py-2.5 min-h-[42px] bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors border border-slate-200 shadow-2xs">
                                <i class="ti ti-eye text-sm"></i>
                                <span>Detail</span>
                            </a>
                        </div>
                    `;
                } else if (d.status === 'keluar') {
                    actionSection = `
                        <div class="flex flex-col sm:flex-row items-stretch gap-2 pt-1">
                            <button onclick="quickAction('kembali', ${d.id})" class="flex-1 px-4 py-2.5 min-h-[42px] bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors shadow-2xs flex items-center justify-center">
                                <i class="ti ti-door-enter mr-1.5 text-base"></i> Konfirmasi KEMBALI
                            </button>
                            <a href="${d.detail_url || '#'}" class="inline-flex items-center justify-center gap-1 px-3.5 py-2.5 min-h-[42px] bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors border border-slate-200 shadow-2xs">
                                <i class="ti ti-eye text-sm"></i>
                                <span>Detail</span>
                            </a>
                        </div>
                    `;
                } else {
                    actionSection = `
                        <div class="pt-1">
                            <a href="${d.detail_url || '#'}" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 min-h-[42px] bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors border border-slate-200 shadow-2xs">
                                <i class="ti ti-file-text text-sm"></i>
                                <span>Lihat Detail Dispensasi</span>
                            </a>
                        </div>
                    `;
                }

                html += `
                    <div class="border border-slate-200/90 rounded-2xl p-4 bg-white shadow-xs space-y-3">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1.5 sm:gap-2 pb-2 border-b border-slate-100">
                            <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                                <span class="font-mono font-bold text-xs sm:text-sm text-slate-800 break-all">${escapeHtml(d.nomor_surat)}</span>
                                ${tagBadge}
                            </div>
                            <div class="self-start sm:self-auto flex-shrink-0">
                                ${statusBadge}
                            </div>
                        </div>

                        <div>
                            <p class="font-bold text-slate-900 text-sm leading-snug">${escapeHtml(d.siswa_nama)}</p>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">${escapeHtml(d.siswa_nis)} • ${escapeHtml(d.siswa_kelas)}</p>
                        </div>

                        ${pembinaHtml}

                        <div class="grid grid-cols-2 gap-2.5 text-xs">
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider mb-0.5">Jam Keluar:</span>
                                <strong class="text-slate-900 font-bold">${escapeHtml(d.jam_keluar)} WIB</strong>
                                ${d.waktu_keluar_aktual ? `<p class="text-[10px] text-sky-700 font-semibold mt-1">Aktual: ${escapeHtml(d.waktu_keluar_aktual)} WIB</p>` : ''}
                            </div>
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider mb-0.5">Batas Kembali:</span>
                                <strong class="text-slate-900 font-bold">${escapeHtml(d.jam_kembali)}</strong>
                                ${d.waktu_kembali_aktual ? `<p class="text-[10px] text-emerald-700 font-semibold mt-1">Aktual: ${escapeHtml(d.waktu_kembali_aktual)} WIB</p>` : ''}
                            </div>
                        </div>

                        ${actionSection}
                    </div>
                `;
            });
            resultDiv.innerHTML = html;
        } else {
            resultDiv.innerHTML = '<div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-center"><p class="text-rose-700 font-bold text-sm">Dispensasi tidak ditemukan</p><p class="text-xs text-rose-600 mt-1">Periksa kembali kata kunci pencarian</p></div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = `<div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-center"><p class="text-rose-700 font-bold text-sm">Terjadi kesalahan</p><p class="text-xs text-rose-600 mt-1">${escapeHtml(error.message)}</p></div>`;
    });
}

// QUICK ACTION - KONFIRMASI CEPAT MANUAL
function quickAction(action, dispensasiId) {
    const confirmMsg = action === 'keluar' ? 'Konfirmasi siswa KELUAR dari sekolah?' : 'Konfirmasi siswa KEMBALI ke sekolah?';

    Swal.fire({
        title: 'Konfirmasi',
        text: confirmMsg,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: action === 'keluar' ? '#e11d48' : '#059669',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Konfirmasi',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            const url = action === 'keluar'
                ? '{{ route("satpam.konfirmasi.keluar", ":id") }}'.replace(':id', dispensasiId)
                : '{{ route("satpam.konfirmasi.kembali", ":id") }}'.replace(':id', dispensasiId);

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(async r => {
                const data = await r.json().catch(() => null);
                if (!r.ok) {
                    throw new Error((data && data.message) ? data.message : `HTTP Error ${r.status}`);
                }
                return data;
            })
            .then(data => {
                if (data && data.success) {
                    Swal.fire('Berhasil!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', (data && data.message) ? data.message : 'Gagal memproses konfirmasi.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', error.message || 'Terjadi kesalahan koneksi', 'error');
            });
        }
    });
}

function initScanner() {
    html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 220, height: 220 } };

    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
    .then(() => {
        isScanning = true;
        setStatus('Kamera aktif — siap scan');
    })
    .catch(err => {
        html5QrCode.start({ facingMode: "user" }, config, onScanSuccess)
        .then(() => {
            isScanning = true;
            setStatus('Kamera depan aktif');
        })
        .catch(err2 => {
            setStatus('Kamera tidak tersedia — gunakan verifikasi manual', false);
        });
    });
}

document.addEventListener("DOMContentLoaded", function() {
    updateExpressUI();
    updateBeepUI();
    updateWakeLockUI(false);

    if (isWakeLockEnabled) {
        requestWakeLock();
    }

    // Inisialisasi audio helper pada interaksi pertama
    document.addEventListener('click', function unlockAudio() {
        AudioHelper.init();
    }, { once: true });

    initScanner();
});
</script>
@endpush
