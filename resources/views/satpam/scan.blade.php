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
                <span class="w-2 h-2 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span> Kamera aktif
            </span>
            <button onclick="restartScanner()" class="inline-flex items-center px-3 py-2 rounded-xl text-[11px] font-bold text-red-600 bg-red-50 border border-red-100 active:bg-red-100 transition-colors">
                <i class="fas fa-redo mr-1.5"></i> Scan Ulang
            </button>
        </div>
    </div>

    {{-- Hasil Scan QR --}}
    <div id="scanResult" class="hidden rounded-2xl border-2 p-4"></div>

    {{-- VERIFIKASI MANUAL --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mt-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Verifikasi Manual</p>
        <form onsubmit="manualVerify(event)" class="flex gap-2">
            <input type="text"
                   id="manualCode"
                   required
                   placeholder="Masukkan No. Surat / NIS / Nama..."
                   class="flex-1 h-11 px-3.5 rounded-xl border-2 border-gray-200 bg-white text-xs font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:border-red-600 focus:ring-4 focus:ring-red-100 transition-all">
            <button type="submit"
                    class="px-4 h-11 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-red-600 to-rose-500 shadow-md shadow-red-500/20 active:scale-[0.98] transition-all">
                <i class="fas fa-search mr-1"></i> Cek
            </button>
        </form>
        <p class="text-[10px] text-gray-400 mt-2">
            <i class="fas fa-info-circle mr-1"></i> Gunakan jika QR Code tidak bisa discan.
        </p>
    </div>

    {{-- HASIL PENCARIAN MANUAL --}}
    <div id="manualSearchResult" class="hidden mt-4 space-y-3"></div>

</div>
@endsection

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
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ qr_data: code })
    })
    .then(r => r.json())
    .then(data => {
        const result = document.getElementById('scanResult');
        result.classList.remove('hidden');

        if (data.success) {
            if (navigator.vibrate) navigator.vibrate([120, 60, 120]);
            const themeColor = data.action === 'keluar' ? 'emerald' : (data.is_terlambat ? 'red' : 'emerald');
            const icon = data.action === 'keluar' ? 'fa-door-open' : 'fa-door-closed';

            result.className = `rounded-2xl border-2 p-4 bg-${themeColor}-50 border-${themeColor}-300`;

            let extraInfo = '';
            if (data.action === 'keluar' && data.is_sampai_pulang) {
                extraInfo = `<div class="mt-2 p-2 bg-amber-100 border border-amber-200 rounded-lg text-[11px] text-amber-800 font-semibold"><i class="fas fa-info-circle mr-1"></i> Dispensasi sampai pulang. Tidak wajib scan kembali.</div>`;
            } else if (data.is_terlambat) {
                extraInfo = `<div class="mt-2 p-2 bg-red-100 border border-red-200 rounded-lg text-[11px] text-red-800 font-semibold"><i class="fas fa-exclamation-triangle mr-1"></i> Siswa terlambat kembali!</div>`;
            }

            result.innerHTML = `
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-11 h-11 rounded-full bg-${themeColor}-500 text-white flex items-center justify-center text-lg shadow-lg"><i class="fas ${icon}"></i></div>
                    <div>
                        <p class="font-black text-${themeColor}-800 text-base leading-tight">${escapeHtml(data.message)}</p>
                        <p class="text-[11px] text-${themeColor}-600 font-semibold">QR Code valid & terverifikasi</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-${themeColor}-200 p-3 text-xs space-y-1">
                    <p><span class="text-gray-500">Nama:</span> <strong class="text-gray-800">${escapeHtml(data.data.siswa.nama_lengkap)}</strong></p>
                    <p><span class="text-gray-500">Kelas:</span> <strong class="text-gray-800">${escapeHtml(data.data.siswa.kelas?.nama_kelas ?? '-')}</strong></p>
                    <p><span class="text-gray-500">Jam Kembali:</span> <strong class="text-gray-800">${escapeHtml(data.data.jam_kembali)}</strong></p>
                </div>
                ${extraInfo}
                <div class="mt-3 text-right">
                    <button onclick="restartScanner()" class="px-3 py-1.5 bg-${themeColor}-600 text-white rounded-lg text-xs font-bold shadow hover:bg-${themeColor}-700">
                        <i class="fas fa-redo mr-1"></i> Scan Berikutnya
                    </button>
                </div>`;
            setStatus('Selesai — data ditampilkan', true);
        } else {
            if (navigator.vibrate) navigator.vibrate(300);
            result.className = 'rounded-2xl border-2 p-4 bg-red-50 border-red-300';
            result.innerHTML = `
                <div class="flex items-center space-x-3">
                    <div class="w-11 h-11 rounded-full bg-red-500 text-white flex items-center justify-center text-lg shadow-lg shadow-red-500/40"><i class="fas fa-xmark"></i></div>
                    <div><p class="font-black text-red-800 text-base leading-tight">GAGAL</p><p class="text-xs text-red-700 font-semibold">${escapeHtml(data.message)}</p></div>
                </div>
                <div class="mt-3 text-right">
                    <button onclick="restartScanner()" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-bold shadow hover:bg-red-700"><i class="fas fa-redo mr-1"></i> Coba Lagi</button>
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
}

function onScanSuccess(decodedText) {
    if (!isScanning) return;
    isScanning = false;
    try { html5QrCode.pause(); } catch (e) {}
    verify(decodedText);
}

function restartScanner() {
    document.getElementById('scanResult').classList.add('hidden');
    document.getElementById('manualSearchResult').classList.add('hidden');
    isScanning = true;
    if (html5QrCode) {
        try { html5QrCode.resume(); } catch (e) {}
        setStatus('Kamera aktif — menunggu scan');
    }
}

// ✅ VERIFIKASI MANUAL - PERBAIKAN LENGKAP
function manualVerify(e) {
    e.preventDefault();
    const code = document.getElementById('manualCode').value.trim();
    if (!code) return;

    const resultDiv = document.getElementById('manualSearchResult');
    if (!resultDiv) {
        alert('Element tidak ditemukan. Silakan refresh halaman.');
        return;
    }

    resultDiv.classList.remove('hidden');
    resultDiv.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin text-red-500 text-2xl"></i><p class="text-xs text-gray-500 mt-2">Mencari data...</p></div>';

    // ✅ PERBAIKAN: Gunakan route() helper, bukan URL hardcoded
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
        // ✅ PERBAIKAN: Cek apakah response adalah JSON
        const contentType = r.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server mengembalikan HTML, bukan JSON. Kemungkinan ada error di server.');
        }
        return r.json();
    })
    .then(data => {
        if (data.success && data.data && data.data.length > 0) {
            let html = '';
            data.data.forEach(d => {
                let actionBtn = '';
                let statusClass = d.status === 'disetujui' ? 'bg-emerald-100 text-emerald-700' :
                                 d.status === 'keluar' ? 'bg-sky-100 text-sky-700' :
                                 'bg-gray-100 text-gray-700';

                if (d.status === 'disetujui') {
                    actionBtn = `<button onclick="quickAction('keluar', ${d.id})" class="w-full mt-2 px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-lg"><i class="fas fa-door-open mr-1"></i> Konfirmasi KELUAR</button>`;
                } else if (d.status === 'keluar') {
                    actionBtn = `<button onclick="quickAction('kembali', ${d.id})" class="w-full mt-2 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg"><i class="fas fa-door-closed mr-1"></i> Konfirmasi KEMBALI</button>`;
                } else {
                    actionBtn = `<p class="text-xs text-gray-500 text-center mt-2 italic">Status: ${d.status}</p>`;
                }

                html += `
                    <div class="border border-gray-200 rounded-xl p-3 bg-white shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-mono font-bold text-sm text-gray-800">${d.nomor_surat}</p>
                                <p class="font-bold text-gray-900">${d.siswa_nama}</p>
                                <p class="text-xs text-gray-500">${d.siswa_nis} • ${d.siswa_kelas}</p>
                            </div>
                            <span class="px-2 py-1 rounded text-[10px] font-bold ${statusClass}">${d.status.toUpperCase()}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                            <div><span class="text-gray-500">Keluar:</span> <strong>${d.jam_keluar}</strong></div>
                            <div><span class="text-gray-500">Kembali:</span> <strong>${d.jam_kembali}</strong></div>
                        </div>
                        ${actionBtn}
                    </div>
                `;
            });
            resultDiv.innerHTML = html;
        } else {
            resultDiv.innerHTML = '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center"><p class="text-red-700 font-bold text-sm">Dispensasi tidak ditemukan</p><p class="text-xs text-red-600 mt-1">Periksa kembali kata kunci pencarian</p></div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = `<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center"><p class="text-red-700 font-bold text-sm">Terjadi kesalahan</p><p class="text-xs text-red-600 mt-1">${error.message}</p></div>`;
    });

    document.getElementById('manualCode').value = '';
}

// ✅ QUICK ACTION - PERBAIKAN LENGKAP
function quickAction(action, dispensasiId) {
    const confirmMsg = action === 'keluar' ? 'Konfirmasi siswa KELUAR dari sekolah?' : 'Konfirmasi siswa KEMBALI ke sekolah?';
    if (!confirm(confirmMsg)) return;

    // ✅ PERBAIKAN: Gunakan route() helper
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
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + (data.message || 'Gagal memproses'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Terjadi kesalahan koneksi');
    });
}

function initScanner() {
    html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 220, height: 220 } };

    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
    .then(() => {
        isScanning = true;
        setStatus('Kamera aktif — menunggu scan');
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
    initScanner();
});
</script>
@endpush
