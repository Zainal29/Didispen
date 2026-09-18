@extends('satpam.layouts.app')

@section('title', 'Scan QR Code')
@section('page-title', 'Scan QR Code')

@section('content')
<div class="max-w-md mx-auto space-y-4">

    {{-- Instruksi --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center space-x-3">
        <div class="w-10 h-10 rounded-lg bg-red-600 text-white flex items-center justify-center flex-shrink-0">
            <i class="fas fa-qrcode"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-900">Scan QR Dispensasi</h3>
            <p class="text-[11px] text-gray-500">Arahkan kamera ke QR Code pada layar HP siswa.</p>
        </div>
    </div>

    {{-- Kamera --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="relative bg-gray-900">
            <div id="reader" class="w-full"></div>
        </div>
        <div class="p-3.5 flex items-center justify-between border-t border-gray-200">
            <span id="scanStatus" class="inline-flex items-center text-[11px] font-semibold text-emerald-600">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Kamera aktif
            </span>
            <button onclick="restartScanner()" class="inline-flex items-center px-3 py-2 rounded-lg text-[11px] font-semibold text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 transition-colors">
                <i class="fas fa-redo mr-1.5"></i> Scan Ulang
            </button>
        </div>
    </div>

    {{-- VERIFIKASI MANUAL --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mt-4">
        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-2">Verifikasi Manual</p>
        <form onsubmit="manualVerify(event)" class="flex gap-2">
            @csrf
            <input type="text"
                   id="manualCode"
                   required
                   placeholder="Masukkan No. Surat, NIS, atau Nama"
                   class="flex-1 h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-xs font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:border-red-600 focus:ring-2 focus:ring-red-600/20 transition-all">
            <button type="submit"
                    class="px-4 h-11 rounded-lg text-xs font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors">
                <i class="fas fa-search mr-1"></i> Cek
            </button>
        </form>
        <p class="text-[10px] text-gray-500 mt-2">
            <i class="fas fa-info-circle mr-1"></i> Gunakan jika QR Code tidak bisa discan.
        </p>
    </div>

    {{-- HASIL PENCARIAN MANUAL --}}
    <div id="manualSearchResult" class="hidden mt-4 space-y-3"></div>

</div>

{{-- ======================================================== --}}
{{-- MODAL POPUP HASIL SCAN QR & KONFIRMASI --}}
{{-- ======================================================== --}}
<div id="scanModal"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/75 backdrop-blur-sm hidden overflow-y-auto"
     onclick="handleBackdropClick(event)">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 my-8 transition-all"
         onclick="event.stopPropagation()">

        {{-- Modal Header --}}
        <div id="modalHeader" class="p-4 bg-gray-900 text-white flex items-center justify-between transition-colors">
            <div class="flex items-center gap-2.5">
                <div id="modalHeaderIcon" class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-sm">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div>
                    <h3 id="modalTitle" class="text-sm font-bold text-white leading-tight">Hasil Scan QR Code</h3>
                    <p id="modalSubtitle" class="text-[11px] text-white/80">Memeriksa data dispensasi...</p>
                </div>
            </div>
            <button type="button"
                    onclick="closeScanModal()"
                    class="text-white/70 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition-colors"
                    title="Tutup (Esc)">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div id="modalBody" class="p-5 space-y-4 max-h-[75vh] overflow-y-auto">
            {{-- Content akan di-render secara dinamis oleh JavaScript --}}
        </div>

        {{-- Modal Footer --}}
        <div id="modalFooter" class="p-4 bg-gray-50 border-t border-gray-100 flex gap-2.5">
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
let currentScannedQr = null;
let autoCloseTimer = null;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function setStatus(text, ok = true) {
    const el = document.getElementById('scanStatus');
    if (!el) return;
    el.innerHTML = `<span class="w-2 h-2 rounded-full ${ok ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'} mr-1.5"></span> ${escapeHtml(text)}`;
    el.className = `inline-flex items-center text-[11px] font-semibold ${ok ? 'text-emerald-600' : 'text-amber-600'}`;
}

function onScanSuccess(decodedText) {
    if (!isScanning || isProcessing) return;

    isProcessing = true;
    isScanning = false;
    currentScannedQr = decodedText;

    try { html5QrCode.pause(); } catch (e) {}
    if (navigator.vibrate) navigator.vibrate(80);

    showModalLoading();
    checkQrData(decodedText);
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

    header.className = 'p-4 bg-gray-900 text-white flex items-center justify-between transition-colors';
    headerIcon.innerHTML = '<i class="fas fa-qrcode"></i>';
    title.textContent = 'Memeriksa QR Code';
    subtitle.textContent = 'Menghubungkan ke server...';

    body.innerHTML = `
        <div class="py-12 text-center">
            <i class="fas fa-circle-notch fa-spin text-red-600 text-4xl mb-3"></i>
            <h4 class="font-bold text-gray-800 text-base">Memverifikasi Data Siswa...</h4>
            <p class="text-xs text-gray-500 mt-1">Mohon tunggu sebentar, sedang mencocokkan kode QR.</p>
        </div>
    `;

    footer.innerHTML = `
        <button type="button" onclick="closeScanModal()" class="w-full py-2.5 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition-colors">
            Batal
        </button>
    `;

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
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
    .then(r => {
        if (r.status === 429) {
            return {
                success: false,
                message: 'Terlalu banyak permintaan scan. Mohon tunggu beberapa detik.'
            };
        }
        return r.json();
    })
    .then(data => {
        if (data.success && data.mode === 'preview') {
            renderScanDetail(data.data);
        } else {
            renderScanError(data.message || 'QR Code tidak valid atau dispensasi tidak ditemukan.');
        }
    })
    .catch(err => {
        console.error('Fetch error:', err);
        renderScanError('Terjadi kesalahan jaringan saat memverifikasi QR Code.');
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
        header.className = 'p-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex items-center justify-between transition-colors';
        headerIcon.innerHTML = '<i class="fas fa-door-open"></i>';
        title.textContent = 'Konfirmasi Siswa KELUAR';
        subtitle.textContent = 'Dispensasi telah disetujui & siap keluar';
    } else if (isKembali) {
        header.className = 'p-4 bg-gradient-to-r from-emerald-600 to-teal-600 text-white flex items-center justify-between transition-colors';
        headerIcon.innerHTML = '<i class="fas fa-door-closed"></i>';
        title.textContent = 'Konfirmasi Siswa KEMBALI';
        subtitle.textContent = 'Siswa telah kembali ke lingkungan sekolah';
    } else {
        header.className = 'p-4 bg-gray-800 text-white flex items-center justify-between transition-colors';
        headerIcon.innerHTML = '<i class="fas fa-info-circle"></i>';
        title.textContent = 'Informasi Dispensasi';
        subtitle.textContent = `Status: ${data.status.toUpperCase()}`;
    }

    // Alerts
    let alertHtml = '';
    if (data.is_terlambat) {
        alertHtml += `
            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-800 font-semibold flex items-center gap-2.5">
                <i class="fas fa-exclamation-triangle text-red-600 text-base flex-shrink-0"></i>
                <div>
                    <p class="font-bold text-red-900">PERINGATAN: TERLAMBAT KEMBALI!</p>
                    <p class="text-[11px] text-red-700 font-normal">Siswa telah melewati batas jam kembali (${escapeHtml(data.jam_kembali)}).</p>
                </div>
            </div>
        `;
    }
    if (data.is_sampai_pulang && isKeluar) {
        alertHtml += `
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 font-semibold flex items-center gap-2.5">
                <i class="fas fa-info-circle text-amber-600 text-base flex-shrink-0"></i>
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
                     class="w-20 h-20 object-cover rounded-xl border-2 border-blue-500 shadow-sm bg-gray-100">
                <span class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 bg-blue-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full whitespace-nowrap shadow">
                    Foto Wajah
                </span>
            </div>
        `;
    } else {
        photoHtml = `
            <div class="w-20 h-20 rounded-xl bg-blue-50 text-blue-600 flex flex-col items-center justify-center flex-shrink-0 border border-blue-100 shadow-sm">
                <i class="fas fa-user-graduate text-2xl mb-1"></i>
                <span class="text-[9px] text-gray-500 font-medium">Siswa</span>
            </div>
        `;
    }

    body.innerHTML = `
        ${alertHtml}

        {{-- Profil Siswa --}}
        <div class="flex items-start gap-3.5 p-3.5 bg-gray-50/80 rounded-xl border border-gray-200">
            ${photoHtml}
            <div class="min-w-0 flex-1">
                <h4 class="font-bold text-gray-900 text-base leading-snug">${escapeHtml(data.siswa.nama_lengkap)}</h4>
                <p class="text-xs text-gray-500 font-mono mt-0.5"><i class="fas fa-id-card text-gray-400 mr-1"></i>NIS: ${escapeHtml(data.siswa.nis)}</p>
                <p class="text-xs font-semibold text-gray-700 mt-1"><i class="fas fa-graduation-cap text-gray-400 mr-1"></i>${escapeHtml(data.siswa.kelas)} • ${escapeHtml(data.siswa.jurusan)}</p>
                <p class="text-[11px] text-gray-500 mt-1"><i class="fas fa-user-check text-gray-400 mr-1"></i>Piket: <span class="font-medium text-gray-700">${escapeHtml(data.guru)}</span></p>
            </div>
        </div>

        {{-- Ringkasan Surat & Jam --}}
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-200">
                <span class="text-[10px] uppercase font-bold text-gray-400 block mb-0.5">No. Surat</span>
                <span class="font-mono font-bold text-gray-800 truncate block">${escapeHtml(data.nomor_surat)}</span>
            </div>
            <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-200">
                <span class="text-[10px] uppercase font-bold text-gray-400 block mb-0.5">Waktu Dispensasi</span>
                <span class="font-bold text-gray-800 block">${escapeHtml(data.jam_keluar)} &rarr; ${escapeHtml(data.jam_kembali)}</span>
            </div>
        </div>

        {{-- Keperluan & Alasan --}}
        <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 text-xs">
            <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1">Keperluan / Alasan</span>
            <p class="text-gray-800 font-medium leading-relaxed">${escapeHtml(data.alasan || '-')}</p>
            ${data.tujuan ? `<p class="text-[11px] text-gray-500 mt-1.5"><i class="fas fa-map-marker-alt text-red-500 mr-1"></i>Tujuan: <strong>${escapeHtml(data.tujuan)}</strong></p>` : ''}
        </div>

        {{-- Pertanyaan Konfirmasi --}}
        <div class="text-center pt-2">
            ${isKeluar ? `
                <p class="text-sm font-bold text-gray-900">
                    Setujui siswa ini untuk <span class="text-blue-600 underline decoration-blue-300 font-extrabold">KELUAR</span> dari sekolah?
                </p>
                <p class="text-[11px] text-gray-500 mt-0.5">Cocokkan wajah siswa dengan foto sebelum menyetujui.</p>
            ` : isKembali ? `
                <p class="text-sm font-bold text-gray-900">
                    Konfirmasi siswa ini telah <span class="text-emerald-600 underline decoration-emerald-300 font-extrabold">KEMBALI</span> ke sekolah?
                </p>
                <p class="text-[11px] text-gray-500 mt-0.5">Status dispensasi akan otomatis ditandai SELESAI.</p>
            ` : isSelesai ? `
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold">
                    <i class="fas fa-check-circle mr-1 text-emerald-600"></i> Dispensasi ini sudah selesai diproses.
                </div>
            ` : `
                <p class="text-xs text-gray-600">Dispensasi dalam status <strong>${escapeHtml(data.status)}</strong>. Tidak memerlukan aksi scan.</p>
            `}
        </div>
    `;

    // Footer actions
    if (isKeluar) {
        footer.innerHTML = `
            <button type="button"
                    onclick="closeScanModal()"
                    class="py-3 px-4 rounded-xl bg-white hover:bg-gray-100 border border-gray-300 text-gray-700 font-semibold text-xs transition-colors">
                <i class="fas fa-times mr-1"></i> Batal
            </button>
            <button type="button"
                    id="btnConfirmAction"
                    onclick="submitConfirmation('keluar')"
                    class="flex-1 py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-bold text-xs shadow-md shadow-blue-600/20 transition-all flex items-center justify-center gap-1.5">
                <i class="fas fa-door-open"></i> Ya, Setujui KELUAR
            </button>
        `;
    } else if (isKembali) {
        footer.innerHTML = `
            <button type="button"
                    onclick="closeScanModal()"
                    class="py-3 px-4 rounded-xl bg-white hover:bg-gray-100 border border-gray-300 text-gray-700 font-semibold text-xs transition-colors">
                <i class="fas fa-times mr-1"></i> Batal
            </button>
            <button type="button"
                    id="btnConfirmAction"
                    onclick="submitConfirmation('kembali')"
                    class="flex-1 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-1.5">
                <i class="fas fa-door-closed"></i> Ya, Konfirmasi KEMBALI
            </button>
        `;
    } else {
        footer.innerHTML = `
            <button type="button"
                    onclick="closeScanModal()"
                    class="w-full py-3 px-4 rounded-xl bg-gray-800 hover:bg-gray-900 text-white font-semibold text-xs transition-colors">
                <i class="fas fa-check mr-1.5"></i> Tutup & Scan Ulang
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

    header.className = 'p-4 bg-red-600 text-white flex items-center justify-between transition-colors';
    headerIcon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
    title.textContent = 'QR Code Tidak Valid';
    subtitle.textContent = 'Gagal memverifikasi dispensasi';

    body.innerHTML = `
        <div class="py-8 text-center">
            <div class="w-14 h-14 mx-auto rounded-full bg-red-100 text-red-600 flex items-center justify-center text-2xl mb-3">
                <i class="fas fa-times"></i>
            </div>
            <h4 class="font-bold text-gray-800 text-base">Tidak Dapat Memproses</h4>
            <p class="text-xs text-gray-600 mt-1 max-w-sm mx-auto">${escapeHtml(message)}</p>
        </div>
    `;

    footer.innerHTML = `
        <button type="button"
                onclick="closeScanModal()"
                class="w-full py-3 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold text-xs transition-colors">
            <i class="fas fa-redo mr-1.5"></i> Scan Ulang
        </button>
    `;

    setStatus('Scan gagal — kode tidak valid', false);
}

// 5. Submit Konfirmasi (Keluar / Kembali)
function submitConfirmation(action) {
    const btn = document.getElementById('btnConfirmAction');
    if (!btn || !currentScannedQr) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-1.5"></i> Memproses...';
    btn.classList.add('opacity-75', 'cursor-not-allowed');

    fetch('{{ route("satpam.scan.verify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ qr_data: currentScannedQr, action: action })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (navigator.vibrate) navigator.vibrate([120, 60, 120]);
            renderSuccessState(data);
        } else {
            Swal.fire('Gagal', data.message || 'Terjadi kesalahan saat konfirmasi.', 'error');
            btn.disabled = false;
            btn.innerHTML = action === 'keluar' ? '<i class="fas fa-door-open"></i> Ya, Setujui KELUAR' : '<i class="fas fa-door-closed"></i> Ya, Konfirmasi KEMBALI';
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    })
    .catch(err => {
        console.error('Submit error:', err);
        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
        btn.disabled = false;
        btn.innerHTML = action === 'keluar' ? '<i class="fas fa-door-open"></i> Ya, Setujui KELUAR' : '<i class="fas fa-door-closed"></i> Ya, Konfirmasi KEMBALI';
        btn.classList.remove('opacity-75', 'cursor-not-allowed');
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
    headerIcon.innerHTML = '<i class="fas fa-check"></i>';
    title.textContent = 'Berhasil Dikonfirmasi!';
    subtitle.textContent = 'Data telah dicatat ke sistem';

    body.innerHTML = `
        <div class="py-8 text-center">
            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mb-3 shadow-inner">
                <i class="fas fa-check"></i>
            </div>
            <h4 class="font-bold text-gray-900 text-lg leading-snug">${escapeHtml(data.message)}</h4>
            <p class="text-xs text-gray-500 mt-2">Modal akan tertutup otomatis dalam 3 detik untuk scan berikutnya.</p>
        </div>
    `;

    footer.innerHTML = `
        <button type="button"
                onclick="closeScanModal()"
                class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors shadow-md shadow-emerald-600/20">
            <i class="fas fa-redo mr-1.5"></i> Scan Siswa Berikutnya
        </button>
    `;

    setStatus('Berhasil — siap scan berikutnya', true);

    if (autoCloseTimer) clearTimeout(autoCloseTimer);
    autoCloseTimer = setTimeout(() => {
        closeScanModal();
    }, 3000);
}

// 7. Tutup Modal & Nyalakan Kembali Scanner
function closeScanModal() {
    if (autoCloseTimer) {
        clearTimeout(autoCloseTimer);
        autoCloseTimer = null;
    }

    const modal = document.getElementById('scanModal');
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');

    currentScannedQr = null;
    isProcessing = false;
    isScanning = true;

    if (html5QrCode) {
        try { html5QrCode.resume(); } catch (e) {}
    }

    setStatus('Kamera aktif — siap scan');
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
    resultDiv.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin text-red-500 text-2xl"></i><p class="text-xs text-gray-500 mt-2">Mencari data...</p></div>';

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
                let actionBtn = '';
                let statusClass = d.status === 'disetujui' ? 'bg-emerald-100 text-emerald-700' :
                                 d.status === 'keluar' ? 'bg-sky-100 text-sky-700' :
                                 'bg-gray-100 text-gray-700';

                if (d.status === 'disetujui') {
                    actionBtn = `<button onclick="quickAction('keluar', ${d.id})" class="w-full mt-2 px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-lg transition-colors"><i class="fas fa-door-open mr-1"></i> Konfirmasi KELUAR</button>`;
                } else if (d.status === 'keluar') {
                    actionBtn = `<button onclick="quickAction('kembali', ${d.id})" class="w-full mt-2 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors"><i class="fas fa-door-closed mr-1"></i> Konfirmasi KEMBALI</button>`;
                } else {
                    actionBtn = `<p class="text-xs text-gray-500 text-center mt-2 italic">Status: ${d.status}</p>`;
                }

                html += `
                    <div class="border border-gray-200 rounded-lg p-3 bg-white shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-mono font-semibold text-sm text-gray-800">${escapeHtml(d.nomor_surat)}</p>
                                <p class="font-bold text-gray-900">${escapeHtml(d.siswa_nama)}</p>
                                <p class="text-xs text-gray-500">${escapeHtml(d.siswa_nis)} • ${escapeHtml(d.siswa_kelas)}</p>
                            </div>
                            <span class="px-2 py-1 rounded-md text-[10px] font-bold ${statusClass}">${escapeHtml(d.status.toUpperCase())}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                            <div><span class="text-gray-500">Keluar:</span> <strong>${escapeHtml(d.jam_keluar)}</strong></div>
                            <div><span class="text-gray-500">Kembali:</span> <strong>${escapeHtml(d.jam_kembali)}</strong></div>
                        </div>
                        ${actionBtn}
                    </div>
                `;
            });
            resultDiv.innerHTML = html;
        } else {
            resultDiv.innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center"><p class="text-red-700 font-semibold text-sm">Dispensasi tidak ditemukan</p><p class="text-xs text-red-600 mt-1">Periksa kembali kata kunci pencarian</p></div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = `<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center"><p class="text-red-700 font-semibold text-sm">Terjadi kesalahan</p><p class="text-xs text-red-600 mt-1">${escapeHtml(error.message)}</p></div>`;
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
        confirmButtonColor: action === 'keluar' ? '#0284c7' : '#059669',
        cancelButtonColor: '#6b7280',
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
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Berhasil!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
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
    initScanner();
});
</script>
@endpush
