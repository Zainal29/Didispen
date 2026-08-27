@extends('satpam.layouts.app')

@section('title', 'Scan QR Code')
@section('page-title', 'Scan QR Code')

@section('content')

<div class="max-w-md mx-auto space-y-4">

    {{-- Instruksi --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center space-x-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-600 to-rose-500 text-white flex items-center justify-center shadow-md shadow-red-500/30 flex-shrink-0">
            <i class="fas fa-qrcode"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-900">Scan QR Dispensasi</h3>
            <p class="text-[11px] text-gray-500">Arahkan kamera ke QR Code pada layar HP siswa.</p>
        </div>
    </div>

    {{-- Kamera --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="relative bg-gray-900">
            <div id="reader" class="w-full"></div>
        </div>
        <div class="p-3.5 flex items-center justify-between border-t border-gray-100">
            <span id="scanStatus" class="inline-flex items-center text-[11px] font-bold text-emerald-600">
                <span class="w-2 h-2 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span> Kamera aktif — menunggu scan
            </span>
            <button onclick="restartScanner()" class="inline-flex items-center px-3 py-2 rounded-xl text-[11px] font-bold text-red-600 bg-red-50 border border-red-100 active:bg-red-100 transition-colors">
                <i class="fas fa-redo mr-1.5"></i>Scan Ulang
            </button>
        </div>
    </div>

    {{-- Hasil Scan --}}
    <div id="scanResult" class="hidden rounded-2xl border-2 p-4"></div>

    {{-- Verifikasi Manual (cadangan jika kamera bermasalah) --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Verifikasi Manual</p>
        <form onsubmit="manualVerify(event)" class="flex gap-2">
            <input type="text" id="manualCode" required placeholder="Tempel / ketik kode QR siswa..."
                   class="flex-1 h-11 px-3.5 rounded-xl border-2 border-gray-200 bg-white text-xs font-mono text-gray-800 placeholder-gray-400 focus:outline-none focus:border-red-600 focus:ring-4 focus:ring-red-100 transition-all">
            <button type="submit" class="px-4 h-11 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-red-600 to-rose-500 shadow-md shadow-red-500/20 active:scale-[0.98] transition-all">
                <i class="fas fa-search mr-1"></i>Cek
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
let html5QrCode;
let isScanning = false;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function setStatus(text, ok = true) {
    const el = document.getElementById('scanStatus');
    if (!el) return;
    el.innerHTML = `<span class="w-2 h-2 rounded-full ${ok ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'} mr-1.5"></span> ${escapeHtml(text)}`;
    el.className = `inline-flex items-center text-[11px] font-bold ${ok ? 'text-emerald-600' : 'text-amber-600'}`;
}

function verify(code) {
    setStatus('Memverifikasi data...', false);

    fetch('{{ route("satpam.scan.verify") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ qr_data: code })
    })
    .then(r => r.json())
    .then(data => {
        const result = document.getElementById('scanResult');
        result.classList.remove('hidden');

        if (data.success) {
            if (navigator.vibrate) navigator.vibrate([120, 60, 120]);
            result.className = 'rounded-2xl border-2 p-4 bg-emerald-50 border-emerald-300';
            result.innerHTML = `
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-11 h-11 rounded-full bg-emerald-500 text-white flex items-center justify-center text-lg shadow-lg shadow-emerald-500/40"><i class="fas fa-check"></i></div>
                    <div>
                        <p class="font-black text-emerald-800 text-base leading-tight">${escapeHtml(data.message)}</p>
                        <p class="text-[11px] text-emerald-600 font-semibold">QR Code valid & terverifikasi</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-emerald-200 p-3 text-xs space-y-1">
                    <p><span class="text-gray-500">Nama:</span> <strong class="text-gray-800">${escapeHtml(data.data.siswa.nama_lengkap)}</strong></p>
                    <p><span class="text-gray-500">Kelas:</span> <strong class="text-gray-800">${escapeHtml(data.data.siswa.kelas?.nama_kelas ?? '-')}</strong></p>
                    <p><span class="text-gray-500">No. Surat:</span> <strong class="font-mono text-gray-800">${escapeHtml(data.data.nomor_surat)}</strong></p>
                    <p><span class="text-gray-500">Jam Kembali:</span> <strong class="text-gray-800">${escapeHtml(data.data.jam_kembali)}</strong></p>
                </div>
                <div class="mt-3 text-right">
                    <button onclick="restartScanner()" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-bold shadow hover:bg-emerald-700">
                        <i class="fas fa-redo mr-1"></i> Scan / Verifikasi Berikutnya
                    </button>
                </div>`;
            setStatus('Selesai — data ditampilkan', true);
        } else {
            if (navigator.vibrate) navigator.vibrate(300);
            result.className = 'rounded-2xl border-2 p-4 bg-red-50 border-red-300';
            result.innerHTML = `
                <div class="flex items-center space-x-3">
                    <div class="w-11 h-11 rounded-full bg-red-500 text-white flex items-center justify-center text-lg shadow-lg shadow-red-500/40"><i class="fas fa-xmark"></i></div>
                    <div>
                        <p class="font-black text-red-800 text-base leading-tight">GAGAL</p>
                        <p class="text-xs text-red-700 font-semibold">${escapeHtml(data.message)}</p>
                    </div>
                </div>
                <div class="mt-3 text-right">
                    <button onclick="restartScanner()" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-bold shadow hover:bg-red-700">
                        <i class="fas fa-redo mr-1"></i> Coba Lagi
                    </button>
                </div>`;
            setStatus('Gagal memverifikasi', false);
        }
    })
    .catch(() => {
        const result = document.getElementById('scanResult');
        result.classList.remove('hidden');
        result.className = 'rounded-2xl border-2 p-4 bg-red-50 border-red-300';
        result.innerHTML = '<p class="text-red-800 font-bold text-sm"><i class="fas fa-triangle-exclamation mr-1"></i>Terjadi kesalahan koneksi.</p>';
        setStatus('Kesalahan koneksi', false);
    });
    // PERUBAHAN: Blok setTimeout otomatis DIBAPUS/DIHAPUS, sehingga hasil akan terus tampil sampai satpam menekan tombol "Scan / Verifikasi Berikutnya" secara manual.
}

function onScanSuccess(decodedText) {
    if (!isScanning) return;
    isScanning = false;
    try {
        html5QrCode.pause();
    } catch (e) {}
    verify(decodedText);
}

function restartScanner() {
    document.getElementById('scanResult').classList.add('hidden');
    isScanning = true;
    if (html5QrCode) {
        try {
            html5QrCode.resume();
        } catch (e) {
            // Jika gagal resume, inisialisasi ulang
        }
        setStatus('Kamera aktif — menunggu scan');
    }
}

function manualVerify(e) {
    e.preventDefault();
    const code = document.getElementById('manualCode').value.trim();
    if (!code) return;
    isScanning = false;
    try {
        html5QrCode.pause();
    } catch (e) {}
    verify(code);
    document.getElementById('manualCode').value = '';
}

function initScanner() {
    html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 220, height: 220 } };
    
    html5QrCode.start(
        { facingMode: "environment" }, 
        config, 
        onScanSuccess
    )
    .then(() => { 
        isScanning = true; 
        setStatus('Kamera aktif — menunggu scan');
    })
    .catch(err => {
        html5QrCode.start(
            { facingMode: "user" }, 
            config, 
            onScanSuccess
        ).then(() => {
            isScanning = true;
            setStatus('Kamera depan aktif — menunggu scan', true);
        }).catch(err2 => {
            setStatus('Kamera tidak tersedia — gunakan verifikasi manual', false);
        });
    });
}

document.addEventListener("DOMContentLoaded", function() {
    initScanner();
});
</script>
@endpush
@endsection