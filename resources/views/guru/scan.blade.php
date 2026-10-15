@extends('guru.layouts.app')
@section('title', 'Scan QR Dispensasi')
@section('page-title', 'Scan QR Code')
@section('content')

<div class="max-w-xl mx-auto space-y-4 w-full min-w-0" x-data="{ activeTab: 'camera' }">
    {{-- Tab Switcher --}}
    <div class="bg-gray-100/80 p-1 rounded-xl flex items-center gap-1 border border-gray-200">
        <button type="button"
                @click="activeTab = 'camera'"
                :class="activeTab === 'camera' ? 'bg-white text-blue-600 shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
                class="flex-1 py-2.5 px-3 rounded-lg text-xs transition-all flex items-center justify-center gap-1.5">
            <i class="ti ti-qrcode text-base"></i>
            <span>Scan Kamera</span>
        </button>
        <button type="button"
                @click="activeTab = 'manual'"
                :class="activeTab === 'manual' ? 'bg-white text-blue-600 shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
                class="flex-1 py-2.5 px-3 rounded-lg text-xs transition-all flex items-center justify-center gap-1.5">
            <i class="ti ti-search text-base"></i>
            <span>Cari Manual</span>
        </button>
    </div>

    {{-- Card Scanner --}}
    <div x-show="activeTab === 'camera'" class="bg-white border border-gray-200/90 rounded-2xl p-4 sm:p-6 text-center shadow-sm transition-all">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3 border border-blue-100">
            <i class="ti ti-qrcode text-2xl"></i>
        </div>
        <h2 class="text-base font-bold text-gray-900 mb-1">Scanner QR Code Dispensasi</h2>
        <p class="text-xs text-gray-500 mb-4">Arahkan kamera ke QR Code pada layar HP siswa.</p>

        <div class="relative bg-gray-900 rounded-2xl overflow-hidden shadow-inner w-full max-w-sm mx-auto border-2 border-gray-800">
            <div id="reader" class="w-full"></div>
        </div>

        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3.5 gap-2">
            <span id="scanStatus" class="inline-flex items-center text-xs font-semibold text-emerald-600 truncate min-w-0">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse flex-shrink-0"></span> Kamera aktif
            </span>
            <button onclick="window.location.reload()" class="inline-flex items-center px-3.5 py-2 min-h-[38px] rounded-xl text-xs font-semibold text-blue-600 bg-blue-50 border border-blue-200 hover:bg-blue-100 transition-colors flex-shrink-0">
                <i class="ti ti-refresh mr-1.5"></i>Reset Scanner
            </button>
        </div>
    </div>

    {{-- VERIFIKASI MANUAL GURU --}}
    <div x-show="activeTab === 'manual'" x-cloak class="bg-white rounded-2xl border border-gray-200/90 shadow-sm p-5 space-y-3 transition-all">
        <div class="flex items-center gap-3 pb-2 border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <i class="ti ti-search text-lg"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-900">Verifikasi Manual</h3>
                <p class="text-[11px] text-gray-500">Cari berdasarkan No. Surat, NIS, Nama Siswa, Organisasi, atau Pembina</p>
            </div>
        </div>
        <form onsubmit="manualVerify(event)" class="flex gap-2 pt-1">
            @csrf
            <input type="text"
                   id="manualCode"
                   required
                   placeholder="Ketik No. Surat, NIS, Nama, Organisasi, Pembina..."
                   class="flex-1 h-11 px-3.5 rounded-xl border border-gray-300 bg-white text-xs font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
            <button type="submit"
                    class="px-5 h-11 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors shrink-0 shadow-sm flex items-center justify-center gap-1.5">
                <i class="ti ti-search text-sm"></i> Cek
            </button>
        </form>
        <p class="text-[11px] text-gray-500 flex items-center gap-1">
            <i class="ti ti-info-circle text-blue-500"></i> Gunakan opsi ini jika layar HP siswa retak atau kamera gagal memindai QR Code.
        </p>
    </div>

    {{-- HASIL PENCARIAN MANUAL --}}
    <div id="manualSearchResult" class="hidden space-y-3"></div>
</div>

{{-- ======================================================== --}}
{{-- MODAL POPUP HASIL SCAN QR & KONFIRMASI GURU --}}
{{-- ======================================================== --}}
<div id="scanModal"
     class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-gray-900/75 backdrop-blur-sm hidden overflow-y-auto"
     onclick="handleBackdropClick(event)">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 my-auto transition-all max-h-[90dvh] flex flex-col"
         onclick="event.stopPropagation()">

        {{-- Modal Header --}}
        <div id="modalHeader" class="p-4 bg-gray-900 text-white flex items-center justify-between transition-colors flex-shrink-0">
            <div class="flex items-center gap-2.5 min-w-0">
                <div id="modalHeaderIcon" class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="ti ti-qrcode text-lg"></i>
                </div>
                <div class="min-w-0">
                    <h3 id="modalTitle" class="text-sm font-bold text-white leading-tight truncate">Hasil Scan QR Code</h3>
                    <p id="modalSubtitle" class="text-[11px] text-white/80 truncate">Memeriksa data dispensasi...</p>
                </div>
            </div>
            <button type="button"
                    onclick="closeScanModal()"
                    class="text-white/70 hover:text-white w-11 h-11 flex items-center justify-center rounded-lg hover:bg-white/10 transition-colors flex-shrink-0"
                    title="Tutup (Esc)">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div id="modalBody" class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1 min-h-0">
            {{-- Konten dinamis --}}
        </div>

        {{-- Modal Footer --}}
        <div id="modalFooter" class="p-4 bg-gray-50 border-t border-gray-100 flex gap-2.5 flex-shrink-0">
            {{-- Tombol dinamis --}}
        </div>
    </div>
</div>

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
    el.className = `inline-flex items-center text-xs font-semibold ${ok ? 'text-emerald-600' : 'text-amber-600'}`;
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

// 1. Loading di Modal
function showModalLoading() {
    const modal = document.getElementById('scanModal');
    const header = document.getElementById('modalHeader');
    const headerIcon = document.getElementById('modalHeaderIcon');
    const title = document.getElementById('modalTitle');
    const subtitle = document.getElementById('modalSubtitle');
    const body = document.getElementById('modalBody');
    const footer = document.getElementById('modalFooter');

    header.className = 'p-4 bg-gray-900 text-white flex items-center justify-between transition-colors';
    headerIcon.innerHTML = '<i class="ti ti-qrcode text-lg"></i>';
    title.textContent = 'Memeriksa QR Code';
    subtitle.textContent = 'Menghubungkan ke server...';

    body.innerHTML = `
        <div class="py-12 text-center">
            <i class="ti ti-loader-2 animate-spin text-blue-600 text-4xl mb-3 inline-block"></i>
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

// 2. Cek Data QR Code
function checkQrData(code) {
    fetch('{{ route("guru.scan.verify") }}', {
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

// 3. Render Detail di Modal
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

    if (isKeluar) {
        header.className = 'p-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex items-center justify-between transition-colors';
        headerIcon.innerHTML = '<i class="ti ti-door-exit text-lg"></i>';
        title.textContent = 'Konfirmasi Siswa KELUAR';
        subtitle.textContent = 'Dispensasi telah disetujui & siap keluar';
    } else if (isKembali) {
        header.className = 'p-4 bg-gradient-to-r from-emerald-600 to-teal-600 text-white flex items-center justify-between transition-colors';
        headerIcon.innerHTML = '<i class="ti ti-door-enter text-lg"></i>';
        title.textContent = 'Konfirmasi Siswa KEMBALI';
        subtitle.textContent = 'Siswa telah kembali ke sekolah';
    } else {
        header.className = 'p-4 bg-gray-800 text-white flex items-center justify-between transition-colors';
        headerIcon.innerHTML = '<i class="ti ti-info-circle text-lg"></i>';
        title.textContent = 'Informasi Dispensasi';
        subtitle.textContent = `Status: ${data.status.toUpperCase()}`;
    }

    let alertHtml = '';
    if (data.is_terlambat) {
        alertHtml += `
            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-800 font-semibold flex items-center gap-2.5">
                <i class="ti ti-alert-triangle text-red-600 text-base flex-shrink-0"></i>
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
                <i class="ti ti-info-circle text-amber-600 text-base flex-shrink-0"></i>
                <div>
                    <p class="font-bold text-amber-900">Dispensasi Sampai Pulang</p>
                    <p class="text-[11px] text-amber-700 font-normal">Siswa diizinkan hingga jam pulang, tidak wajib scan kembali.</p>
                </div>
            </div>
        `;
    }

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
                <i class="ti ti-school text-2xl mb-1"></i>
                <span class="text-[9px] text-gray-500 font-medium">Siswa</span>
            </div>
        `;
    }

    body.innerHTML = `
        ${alertHtml}

        <div class="flex items-start gap-3.5 p-3.5 bg-slate-50/80 rounded-xl border border-slate-200">
            ${photoHtml}
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 flex-wrap mb-1">
                    ${data.tipe_pengajuan === 'organisasi' ? `
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                            <i class="ti ti-users text-xs"></i>
                            <span>${escapeHtml(data.nama_organisasi || 'Organisasi')}</span>
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
                <p class="text-[11px] text-slate-500 mt-1 flex items-center"><i class="ti ti-user-check text-slate-400 mr-1 text-sm"></i>Piket: <span class="font-medium text-slate-700">${escapeHtml(data.guru)}</span></p>
            </div>
        </div>

        ${data.pembina_text ? `
            <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl px-3.5 py-2.5 flex items-start gap-2.5 text-xs">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="ti ti-tie text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 mb-0.5">Guru Pembina</p>
                    <p class="text-xs font-bold text-indigo-950 leading-snug">${escapeHtml(data.pembina_text)}</p>
                </div>
            </div>
        ` : ''}

        ${data.tipe_pengajuan === 'organisasi' && data.anggota && data.anggota.length > 0 ? `
            <div class="p-3 bg-purple-50/70 border border-purple-200 rounded-xl text-xs space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-purple-950 flex items-center gap-1.5">
                        <i class="ti ti-users text-purple-600"></i>
                        Anggota Rombongan (${data.total_rombongan || (data.anggota.length + 1)} Siswa)
                    </span>
                </div>
                <div class="space-y-1 max-h-28 overflow-y-auto pr-1">
                    <div class="flex items-center justify-between py-0.5 text-[11px]">
                        <span class="font-semibold text-slate-900 truncate">1. ${escapeHtml(data.siswa.nama_lengkap)}</span>
                        <span class="text-[9px] font-bold text-purple-700 bg-purple-100 px-1.5 py-0.2 rounded">Ketua</span>
                    </div>
                    ${data.anggota.map((ang, idx) => `
                        <div class="flex items-center justify-between py-0.5 text-[11px] border-t border-purple-100 text-slate-700">
                            <span class="truncate">${idx + 2}. ${escapeHtml(ang.nama_lengkap)}</span>
                            <span class="text-[10px] text-slate-500 font-mono">${escapeHtml(ang.kelas)}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        ` : ''}

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

        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Keperluan / Alasan</span>
            <p class="text-slate-800 font-medium leading-relaxed">${escapeHtml(data.alasan || '-')}</p>
            ${data.tujuan ? `<p class="text-[11px] text-slate-500 mt-1.5 flex items-center gap-1"><i class="ti ti-map-pin text-rose-500"></i>Tujuan: <strong class="text-slate-700">${escapeHtml(data.tujuan)}</strong></p>` : ''}
        </div>

        <div class="text-center pt-2">
            ${isKeluar ? `
                <p class="text-sm font-bold text-gray-900">
                    Setujui siswa ini untuk <span class="text-blue-600 underline decoration-blue-300 font-extrabold">KELUAR</span> dari sekolah?
                </p>
                <p class="text-[11px] text-gray-500 mt-0.5">Cocokkan identitas siswa sebelum menyetujui.</p>
            ` : isKembali ? `
                <p class="text-sm font-bold text-gray-900">
                    Konfirmasi siswa ini telah <span class="text-emerald-600 underline decoration-emerald-300 font-extrabold">KEMBALI</span> ke sekolah?
                </p>
                <p class="text-[11px] text-gray-500 mt-0.5">Status dispensasi akan otomatis ditandai SELESAI.</p>
            ` : isSelesai ? `
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold">
                    <i class="ti ti-circle-check mr-1 text-emerald-600"></i> Dispensasi ini sudah selesai diproses.
                </div>
            ` : `
                <p class="text-xs text-gray-600">Dispensasi dalam status <strong>${escapeHtml(data.status)}</strong>. Tidak memerlukan aksi scan.</p>
            `}
        </div>
    `;

    if (isKeluar) {
        footer.innerHTML = `
            <button type="button"
                    onclick="closeScanModal()"
                    class="py-3 px-4 rounded-xl bg-white hover:bg-gray-100 border border-gray-300 text-gray-700 font-semibold text-xs transition-colors">
                <i class="ti ti-x mr-1"></i> Batal
            </button>
            <button type="button"
                    id="btnConfirmAction"
                    onclick="submitConfirmation('keluar')"
                    class="flex-1 py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-bold text-xs shadow-md shadow-blue-600/20 transition-all flex items-center justify-center gap-1.5">
                <i class="ti ti-door-exit text-base"></i> Ya, Setujui KELUAR
            </button>
        `;
    } else if (isKembali) {
        footer.innerHTML = `
            <button type="button"
                    onclick="closeScanModal()"
                    class="py-3 px-4 rounded-xl bg-white hover:bg-gray-100 border border-gray-300 text-gray-700 font-semibold text-xs transition-colors">
                <i class="ti ti-x mr-1"></i> Batal
            </button>
            <button type="button"
                    id="btnConfirmAction"
                    onclick="submitConfirmation('kembali')"
                    class="flex-1 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-1.5">
                <i class="ti ti-door-enter text-base"></i> Ya, Konfirmasi KEMBALI
            </button>
        `;
    } else {
        footer.innerHTML = `
            <button type="button"
                    onclick="closeScanModal()"
                    class="w-full py-3 px-4 rounded-xl bg-gray-800 hover:bg-gray-900 text-white font-semibold text-xs transition-colors">
                <i class="ti ti-check mr-1.5"></i> Tutup & Scan Ulang
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
    headerIcon.innerHTML = '<i class="ti ti-alert-triangle text-lg"></i>';
    title.textContent = 'QR Code Tidak Valid';
    subtitle.textContent = 'Gagal memverifikasi dispensasi';

    body.innerHTML = `
        <div class="py-8 text-center">
            <div class="w-14 h-14 mx-auto rounded-full bg-red-100 text-red-600 flex items-center justify-center text-2xl mb-3">
                <i class="ti ti-x text-2xl"></i>
            </div>
            <h4 class="font-bold text-gray-800 text-base">Tidak Dapat Memproses</h4>
            <p class="text-xs text-gray-600 mt-1 max-w-sm mx-auto">${escapeHtml(message)}</p>
        </div>
    `;

    footer.innerHTML = `
        <button type="button"
                onclick="closeScanModal()"
                class="w-full py-3 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold text-xs transition-colors">
            <i class="ti ti-refresh mr-1.5"></i> Scan Ulang
        </button>
    `;

    setStatus('Scan gagal — kode tidak valid', false);
}

// 5. Submit Konfirmasi
function submitConfirmation(action) {
    const btn = document.getElementById('btnConfirmAction');
    if (!btn || !currentScannedQr) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader-2 animate-spin mr-1.5 inline-block"></i> Memproses...';
    btn.classList.add('opacity-75', 'cursor-not-allowed');

    fetch('{{ route("guru.scan.verify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ qr_data: currentScannedQr, action: action })
    })
    .then(async r => {
        const data = await r.json().catch(() => null);
        if (!r.ok) {
            throw new Error((data && data.message) ? data.message : `Terjadi kesalahan pada server (${r.status})`);
        }
        return data;
    })
    .then(data => {
        if (data && data.success) {
            if (navigator.vibrate) navigator.vibrate([120, 60, 120]);
            renderSuccessState(data);
        } else {
            Swal.fire('Gagal', (data && data.message) ? data.message : 'Terjadi kesalahan saat konfirmasi.', 'error');
            btn.disabled = false;
            btn.innerHTML = action === 'keluar' ? '<i class="ti ti-door-exit text-base"></i> Ya, Setujui KELUAR' : '<i class="ti ti-door-enter text-base"></i> Ya, Konfirmasi KEMBALI';
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    })
    .catch(err => {
        console.error('Submit error:', err);
        Swal.fire('Gagal', err.message || 'Terjadi kesalahan jaringan.', 'error');
        btn.disabled = false;
        btn.innerHTML = action === 'keluar' ? '<i class="ti ti-door-exit text-base"></i> Ya, Setujui KELUAR' : '<i class="ti ti-door-enter text-base"></i> Ya, Konfirmasi KEMBALI';
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
    headerIcon.innerHTML = '<i class="ti ti-check text-lg"></i>';
    title.textContent = 'Berhasil Dikonfirmasi!';
    subtitle.textContent = 'Data telah dicatat ke sistem';

    body.innerHTML = `
        <div class="py-8 text-center">
            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mb-3 shadow-inner">
                <i class="ti ti-check text-3xl"></i>
            </div>
            <h4 class="font-bold text-gray-900 text-lg leading-snug">${escapeHtml(data.message)}</h4>
            <p class="text-xs text-gray-500 mt-2">Modal akan tertutup otomatis dalam 3 detik untuk scan berikutnya.</p>
        </div>
    `;

    footer.innerHTML = `
        <button type="button"
                onclick="closeScanModal()"
                class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors shadow-md shadow-emerald-600/20">
            <i class="ti ti-refresh mr-1.5"></i> Scan Siswa Berikutnya
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
    const resultDiv = document.getElementById('manualSearchResult');
    if (resultDiv) resultDiv.classList.add('hidden');
}

function initScanner() {
    html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

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

// VERIFIKASI MANUAL - AJAX GURU
function manualVerify(e) {
    e.preventDefault();

    const code = document.getElementById('manualCode').value.trim();
    if (!code) {
        Swal.fire('Error', 'Masukkan nomor surat, NIS, atau nama siswa', 'error');
        return;
    }

    const resultDiv = document.getElementById('manualSearchResult');
    resultDiv.classList.remove('hidden');
    resultDiv.innerHTML = '<div class="text-center p-4 bg-white rounded-xl border border-gray-200 shadow-sm"><i class="ti ti-loader-2 animate-spin text-blue-500 text-2xl inline-block"></i><p class="text-xs text-gray-500 mt-2">Mencari data...</p></div>';

    fetch('{{ route("guru.search-dispensasi") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ query: code })
    })
    .then(async r => {
        const data = await r.json().catch(() => null);
        if (!r.ok) {
            throw new Error((data && data.message) ? data.message : `HTTP Error ${r.status}`);
        }
        return data;
    })
    .then(data => {
        if (data.success && data.data && data.data.length > 0) {
            let html = '';
            data.data.forEach(d => {
                let statusBadge = '';
                if (d.status === 'disetujui') {
                    statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Disetujui</span>';
                } else if (d.status === 'keluar') {
                    statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200"><span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span> Sedang Keluar</span>';
                } else if (d.status === 'selesai') {
                    statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Selesai</span>';
                } else {
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">${escapeHtml(d.status.toUpperCase())}</span>`;
                }

                let actionBtn = '';
                if (d.status === 'disetujui') {
                    actionBtn = `<button onclick="quickAction('keluar', ${d.id})" class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 min-h-[44px] bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-colors shadow-xs"><i class="ti ti-door-exit text-base"></i> Konfirmasi KELUAR</button>`;
                } else if (d.status === 'keluar') {
                    actionBtn = `<button onclick="quickAction('kembali', ${d.id})" class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 min-h-[44px] bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors shadow-xs"><i class="ti ti-door-enter text-base"></i> Konfirmasi KEMBALI</button>`;
                } else if (d.status === 'selesai') {
                    actionBtn = `<div class="flex-1 flex items-center justify-center sm:justify-start gap-1.5 px-3 py-2.5 min-h-[44px] bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-semibold"><i class="ti ti-circle-check text-emerald-600 text-base shrink-0"></i><span>Dispensasi Selesai</span></div>`;
                } else {
                    actionBtn = `<div class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2.5 min-h-[44px] bg-slate-50 text-slate-600 border border-slate-200 rounded-xl text-xs font-semibold"><span>Status: ${escapeHtml(d.status)}</span></div>`;
                }

                let tagBadge = '';
                if (d.is_organisasi) {
                    tagBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                        <i class="ti ti-users text-xs"></i>
                        <span>${escapeHtml(d.nama_organisasi || 'Organisasi')}</span>
                        <span class="text-[10px] bg-purple-200/70 text-purple-900 px-1.5 py-0.2 rounded font-mono">${d.total_rombongan} Siswa</span>
                    </span>`;
                } else {
                    tagBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                        <i class="ti ti-user text-xs"></i>
                        <span>Individu</span>
                    </span>`;
                }

                let pembinaHtml = '';
                if (d.pembina_text) {
                    pembinaHtml = `
                        <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl px-3.5 py-2.5 flex items-start gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5">
                                <i class="ti ti-tie text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 mb-0.5">Guru Pembina</p>
                                <p class="text-xs font-bold text-indigo-950 leading-snug">${escapeHtml(d.pembina_text)}</p>
                            </div>
                        </div>
                    `;
                }

                let anggotaHtml = '';
                if (d.anggota_list && d.anggota_list.length > 0) {
                    anggotaHtml = `
                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 text-xs space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1">
                                    <i class="ti ti-users text-xs"></i> Anggota Rombongan (${d.total_rombongan} Siswa)
                                </span>
                            </div>
                            <div class="space-y-1 max-h-24 overflow-y-auto pr-1">
                                <div class="flex items-center justify-between py-0.5 text-[11px]">
                                    <span class="font-bold text-slate-900 truncate">1. ${escapeHtml(d.siswa_nama)}</span>
                                    <span class="text-[9px] font-bold text-purple-700 bg-purple-100 px-1.5 py-0.2 rounded">Ketua</span>
                                </div>
                                ${d.anggota_list.map((a, i) => `
                                    <div class="flex items-center justify-between py-0.5 text-[11px] border-t border-slate-100 text-slate-700">
                                        <span class="truncate">${i + 2}. ${escapeHtml(a.nama)}</span>
                                        <span class="text-[10px] text-slate-500 font-mono">${escapeHtml(a.kelas)}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }

                html += `
                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm hover:border-slate-300 transition-all p-4 sm:p-5 space-y-3.5">
                        {{-- Top Header Row --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-2 border-b border-slate-100">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono font-bold text-xs sm:text-sm text-slate-800">${escapeHtml(d.nomor_surat)}</span>
                                ${tagBadge}
                            </div>
                            <div class="flex items-center gap-2">
                                ${statusBadge}
                            </div>
                        </div>

                        {{-- Siswa Identity --}}
                        <div class="pt-0.5">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="font-bold text-slate-900 text-base leading-snug">${escapeHtml(d.siswa_nama)}</h4>
                                ${d.is_organisasi ? `<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">Ketua (Pemohon)</span>` : ''}
                            </div>
                            <p class="text-xs text-slate-500 font-medium mt-1 flex items-center gap-1.5 flex-wrap">
                                <span class="font-mono text-slate-700 font-semibold flex items-center"><i class="ti ti-id text-slate-400 mr-1 text-sm"></i>${escapeHtml(d.siswa_nis)}</span>
                                <span class="text-slate-300">•</span>
                                <span class="text-slate-700 font-semibold flex items-center"><i class="ti ti-school text-slate-400 mr-1 text-sm"></i>${escapeHtml(d.siswa_kelas)}</span>
                                <span class="text-slate-300">•</span>
                                <span class="text-slate-500">${escapeHtml(d.siswa_jurusan)}</span>
                            </p>
                        </div>

                        {{-- Guru Pembina (Jika Ada) --}}
                        ${pembinaHtml}

                        {{-- Anggota Rombongan (Jika Ada) --}}
                        ${anggotaHtml}

                        {{-- Jam Keluar & Kembali Grid --}}
                        <div class="grid grid-cols-2 gap-2.5 text-xs">
                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 space-y-1">
                                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    <span>Jam Keluar</span>
                                    <i class="ti ti-door-exit text-rose-500"></i>
                                </div>
                                <p class="font-bold text-slate-900 text-sm">${escapeHtml(d.jam_keluar)} <span class="text-[10px] font-normal text-slate-500">WIB</span></p>
                                ${d.waktu_keluar_aktual ? `<p class="text-[10px] text-sky-700 font-semibold flex items-center gap-1"><i class="ti ti-circle-check text-xs"></i>Aktual: ${escapeHtml(d.waktu_keluar_aktual)} WIB</p>` : ''}
                            </div>
                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 space-y-1">
                                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    <span>Batas Kembali</span>
                                    <i class="ti ti-door-enter text-emerald-500"></i>
                                </div>
                                <p class="font-bold text-slate-900 text-sm ${d.is_sampai_pulang ? 'text-emerald-700' : ''}">${escapeHtml(d.jam_kembali)}</p>
                                ${d.waktu_kembali_aktual ? `<p class="text-[10px] text-emerald-700 font-semibold flex items-center gap-1"><i class="ti ti-circle-check text-xs"></i>Aktual: ${escapeHtml(d.waktu_kembali_aktual)} WIB</p>` : ''}
                            </div>
                        </div>

                        {{-- Keperluan & Alasan --}}
                        <div class="bg-slate-50/70 border border-slate-200/70 rounded-xl p-3 text-xs text-slate-600 space-y-1">
                            <p><span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Keperluan:</span> <strong class="text-slate-800">${escapeHtml(d.alasan || '-')}</strong></p>
                            ${(d.tujuan || d.lokasi) ? `<p><span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tujuan:</span> <span class="text-slate-700">${escapeHtml(d.tujuan || d.lokasi)}</span></p>` : ''}
                            ${d.guru_piket ? `<p class="text-[10px] text-slate-500 pt-1.5 border-t border-slate-200/60 flex items-center gap-1"><i class="ti ti-user-shield text-slate-400"></i> Disetujui Guru Piket: <strong class="text-slate-700 ml-1">${escapeHtml(d.guru_piket)}</strong></p>` : ''}
                        </div>

                        {{-- Action Buttons & Detail Link --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 pt-1">
                            ${actionBtn}
                            <a href="${d.detail_url}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 min-h-[44px] bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors border border-slate-200 shadow-2xs">
                                <i class="ti ti-file-text text-sm"></i>
                                <span>Lihat Detail Pengajuan</span>
                            </a>
                        </div>
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

// QUICK ACTION - KONFIRMASI CEPAT MANUAL GURU
function quickAction(action, dispensasiId) {
    const confirmMsg = action === 'keluar' ? 'Konfirmasi siswa KELUAR dari sekolah?' : 'Konfirmasi siswa KEMBALI ke sekolah?';

    Swal.fire({
        title: 'Konfirmasi',
        text: confirmMsg,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: action === 'keluar' ? '#2563eb' : '#059669',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Konfirmasi',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            const url = action === 'keluar'
                ? '{{ route("guru.konfirmasi.keluar", ":id") }}'.replace(':id', dispensasiId)
                : '{{ route("guru.konfirmasi.kembali", ":id") }}'.replace(':id', dispensasiId);

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

document.addEventListener("DOMContentLoaded", function() {
    initScanner();
});
</script>
@endpush
@endsection
