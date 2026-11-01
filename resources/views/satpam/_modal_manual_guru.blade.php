@php
    $manualItems = $manualGuruMenunggu ?? collect();
    $hasManual = $manualItems->count() > 0;
@endphp

{{-- ================================================================= --}}
{{-- 1. BANNER PERINGATAN DISPENSASI MANUAL GURU PIKET (POS GERBANG)   --}}
{{-- ================================================================= --}}
<div id="bannerManualGuru"
     class="{{ $hasManual ? 'flex' : 'hidden' }} bg-gradient-to-r from-blue-700 via-indigo-700 to-blue-800 text-white rounded-2xl p-4 shadow-md mb-4 items-center justify-between gap-3.5 border border-blue-400/80 transition-all">
    <div class="flex items-center gap-3 min-w-0">
        <div class="w-10 h-10 rounded-xl bg-white/20 border border-white/30 flex items-center justify-center text-xl shrink-0 shadow-xs">
            <i class="ti ti-bell-ringing animate-bounce text-amber-300"></i>
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap mb-0.5">
                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 tracking-wider">
                    Siswa Tanpa HP
                </span>
                <span class="text-xs text-blue-100 font-semibold">Dibuatkan Manual di Ruang Piket</span>
            </div>
            <p class="text-sm font-extrabold text-white leading-snug truncate">
                Ada <span id="manualGuruCountText">{{ $manualItems->count() }}</span> Siswa Dibuatkan Izin oleh Guru Piket
            </p>
            <p class="text-[11px] text-blue-100/90 leading-tight mt-0.5 hidden sm:block">
                Siswa tidak membawa ponsel / tidak perlu scan barcode. Verifikasi KELUAR langsung dari tombol di samping.
            </p>
        </div>
    </div>
    <button type="button"
            onclick="bukaModalManualGuru()"
            class="px-3.5 py-2.5 bg-white hover:bg-blue-50 text-blue-700 font-bold text-xs rounded-xl shadow-xs shrink-0 flex items-center gap-1.5 transition-all active:scale-95">
        <i class="ti ti-shield-check text-base text-blue-600"></i>
        <span>Lihat & Verifikasi</span>
    </button>
</div>

{{-- ================================================================= --}}
{{-- 2. MODAL POPUP DETAIL & VERIFIKASI KELUAR DISPENSASI MANUAL       --}}
{{-- ================================================================= --}}
<div id="modalManualGuru"
     class="fixed inset-0 z-[80] flex items-center justify-center p-3 sm:p-4 bg-slate-950/80 backdrop-blur-sm hidden overflow-y-auto"
     onclick="handleModalManualBackdrop(event)">
    <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-200 my-auto flex flex-col max-h-[92dvh] transition-all"
         onclick="event.stopPropagation()">

        {{-- Modal Header --}}
        <div class="p-4 bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white flex items-center justify-between shrink-0 border-b border-blue-950">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-blue-600/40 border border-blue-400/50 flex items-center justify-center text-xl shrink-0 text-blue-200">
                    <i class="ti ti-user-shield"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm sm:text-base font-bold text-white flex items-center gap-2">
                        <span>Dispensasi Manual Guru Piket</span>
                        <span id="modalManualBadgeCount" class="text-[10px] font-extrabold px-2 py-0.2 rounded-full bg-amber-400 text-slate-950 font-mono">
                            {{ $manualItems->count() }} Menunggu
                        </span>
                    </h3>
                    <p class="text-[11px] text-blue-200/90 truncate">Siswa tanpa HP • Verifikasi keluar gerbang tanpa perlu scan QR</p>
                </div>
            </div>
            <button type="button"
                    onclick="tutupModalManualGuru()"
                    class="text-white/70 hover:text-white p-1.5 rounded-xl hover:bg-white/10 transition-colors"
                    title="Tutup (Esc)">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div id="modalManualGuruBody" class="p-4 sm:p-5 space-y-3.5 overflow-y-auto flex-1 min-h-0 bg-slate-50/50">
            {{-- Instruksi untuk Satpam --}}
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-950 flex items-start gap-2.5">
                <i class="ti ti-info-circle text-blue-600 text-base shrink-0 mt-0.5"></i>
                <p class="leading-relaxed">
                    Daftar di bawah adalah siswa yang permohonan izinnya <strong>dibuatkan langsung oleh Guru Piket di ruang piket</strong> (biasanya siswa tidak membawa HP/HP mati). Jika siswa sudah tiba di pos gerbang, tekan tombol <strong>"Verifikasi KELUAR"</strong> untuk mengizinkan siswa keluar.
                </p>
            </div>

            {{-- Container List Siswa Manual --}}
            <div id="manualGuruListContainer" class="space-y-3">
                @forelse($manualItems as $d)
                    <div id="manualCard_{{ $d->id }}"
                         class="bg-white rounded-2xl border border-blue-200 p-4 space-y-3 shadow-2xs hover:border-blue-300 transition-all">
                        {{-- Identitas Siswa --}}
                        <div class="flex items-start gap-3">
                            @if($d->foto_verifikasi && \Illuminate\Support\Facades\Storage::disk('public')->exists($d->foto_verifikasi))
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($d->foto_verifikasi) }}"
                                     alt="Foto {{ $d->siswa?->nama_lengkap }}"
                                     class="w-14 h-14 object-cover rounded-xl border border-blue-300 shadow-2xs shrink-0">
                            @else
                                <div class="w-14 h-14 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 flex flex-col items-center justify-center shrink-0">
                                    <i class="ti ti-school text-2xl"></i>
                                    <span class="text-[8px] font-semibold text-slate-500">Siswa</span>
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded-md text-[10px] font-bold bg-blue-100 text-blue-900 border border-blue-300">
                                        <i class="ti ti-user-shield text-xs text-blue-600"></i>
                                        <span>Dibuat: {{ $d->guru?->nama_lengkap ?? 'Guru Piket' }}</span>
                                    </span>
                                    @if($d->isRombongan())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded-md text-[10px] font-bold bg-purple-100 text-purple-900 border border-purple-200">
                                            <i class="ti ti-users text-xs text-purple-600"></i>
                                            <span>Rombongan ({{ $d->anggotaSiswa->count() + 1 }} siswa)</span>
                                        </span>
                                    @endif
                                </div>
                                <h4 class="font-bold text-slate-900 text-sm sm:text-base leading-snug">
                                    {{ $d->siswa?->nama_lengkap ?? 'Nama Siswa Tidak Diketahui' }}
                                </h4>
                                <p class="text-xs text-slate-500 font-mono mt-0.5">
                                    NIS: {{ $d->siswa?->user?->nis_nip ?? '-' }} • {{ $d->siswa?->kelas?->nama_kelas ?? '-' }}
                                </p>
                            </div>
                        </div>

                        {{-- Rincian Waktu & Alasan --}}
                        <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Waktu Izin</span>
                                <span class="font-bold text-slate-800">
                                    {{ $d->jam_keluar }} &rarr; {{ $d->sampai_pulang ? 'Pulang' : $d->jam_kembali }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">No. Surat</span>
                                <span class="font-mono font-bold text-slate-700 truncate block">
                                    {{ $d->nomor_surat }}
                                </span>
                            </div>
                            <div class="col-span-2 pt-1 border-t border-slate-200/60">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Keperluan</span>
                                <p class="text-slate-800 font-medium text-xs leading-relaxed">
                                    {{ $d->alasan }}
                                </p>
                                @if($d->lokasi || $d->tujuan)
                                    <p class="text-[11px] text-slate-500 mt-1 flex items-center">
                                        <i class="ti ti-map-pin text-rose-500 mr-1 text-xs"></i>
                                        Tujuan: <strong class="ml-1 text-slate-700">{{ $d->lokasi ?? $d->tujuan }}</strong>
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex items-center gap-2 pt-1">
                            <a href="{{ route('satpam.dispensasi.detail', $d->id) }}"
                               target="_blank"
                               class="px-3 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors flex items-center justify-center gap-1 shadow-2xs">
                                <i class="ti ti-file-text text-sm"></i>
                                <span>Detail</span>
                            </a>
                            <button type="button"
                                    data-id="{{ $d->id }}"
                                    data-siswa="{{ $d->siswa?->nama_lengkap ?? 'Siswa' }}"
                                    data-guru="{{ $d->guru?->nama_lengkap ?? 'Guru Piket' }}"
                                    onclick="verifikasiKeluarManual(this)"
                                    class="flex-1 py-2.5 px-4 min-h-[42px] bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-[0.98] text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5">
                                <i class="ti ti-shield-check text-base"></i>
                                <span>Verifikasi KELUAR</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div id="manualEmptyPlaceholder" class="p-8 text-center bg-white rounded-2xl border border-slate-200">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2 text-2xl">
                            <i class="ti ti-circle-check"></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">Tidak Ada Dispensasi Manual Menunggu</h4>
                        <p class="text-xs text-slate-500 mt-1">Semua siswa yang dibuatkan dispensasi manual oleh Guru Piket telah diverifikasi keluar.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="p-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-2 shrink-0">
            <span class="text-[11px] text-slate-500 flex items-center gap-1">
                <i class="ti ti-clock text-slate-400"></i>
                <span>Data diperbarui realtime</span>
            </span>
            <button type="button"
                    onclick="tutupModalManualGuru()"
                    class="py-2 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
if (typeof window.escapeHtml !== 'function') {
    window.escapeHtml = function(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    };
}

let manualGuruCount = {{ $manualItems->count() }};
let isModalManualOpen = false;

function bukaModalManualGuru() {
    const modal = document.getElementById('modalManualGuru');
    if (!modal) return;
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    isModalManualOpen = true;
}

function tutupModalManualGuru() {
    const modal = document.getElementById('modalManualGuru');
    if (!modal) return;
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    isModalManualOpen = false;
}

function handleModalManualBackdrop(e) {
    if (e.target.id === 'modalManualGuru') {
        tutupModalManualGuru();
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && isModalManualOpen) {
        tutupModalManualGuru();
    }
});

function verifikasiKeluarManual(btnOrId, maybeNamaSiswa, maybeNamaGuru) {
    let dispensasiId, namaSiswa, namaGuru;
    if (typeof btnOrId === 'object' && btnOrId !== null && btnOrId.dataset) {
        dispensasiId = btnOrId.dataset.id;
        namaSiswa = btnOrId.dataset.siswa || 'Siswa';
        namaGuru = btnOrId.dataset.guru || 'Guru Piket';
    } else {
        dispensasiId = btnOrId;
        namaSiswa = maybeNamaSiswa || 'Siswa';
        namaGuru = maybeNamaGuru || 'Guru Piket';
    }

    Swal.fire({
        title: 'Verifikasi Siswa KELUAR?',
        html: `
            <div class="text-left text-xs sm:text-sm text-slate-700 space-y-2 p-1">
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl">
                    <p class="font-extrabold text-blue-950 text-sm leading-tight">${escapeHtml(namaSiswa)}</p>
                    <p class="text-xs text-blue-800 font-medium mt-1 flex items-center gap-1">
                        <i class="ti ti-user-shield text-sm text-blue-600"></i> Dibuatkan oleh: <b>${escapeHtml(namaGuru)}</b>
                    </p>
                </div>
                <p class="text-slate-600 text-xs">
                    Pastikan siswa ini sudah berada di pos gerbang dan diperiksa sebelum diizinkan keluar sekolah.
                </p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="ti ti-shield-check mr-1.5"></i> Ya, Verifikasi KELUAR',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl shadow-xl border border-slate-200'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Mencatat siswa keluar di pos gerbang...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const url = '{{ route("satpam.konfirmasi.keluar", ":id") }}'.replace(':id', dispensasiId);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil KELUAR!',
                        text: `Siswa ${namaSiswa} telah dicatat resmi KELUAR dari sekolah.`,
                        confirmButtonColor: '#2563eb',
                        timer: 2000,
                        timerProgressBar: true
                    });

                    // Hapus kartu siswa dari modal
                    hapusKartuManual(dispensasiId);
                } else {
                    Swal.fire('Gagal', (data && data.message) || 'Gagal memverifikasi keluar.', 'error');
                }
            })
            .catch(err => {
                console.error('Verifikasi manual error:', err);
                Swal.fire('Error', err.message || 'Terjadi kesalahan jaringan.', 'error');
            });
        }
    });
}

function hapusKartuManual(dispensasiId) {
    const card = document.getElementById(`manualCard_${dispensasiId}`);
    if (card) {
        card.style.transition = 'all 0.3s ease';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.95)';
        setTimeout(() => {
            card.remove();
            manualGuruCount = Math.max(0, manualGuruCount - 1);
            perbaruiBadgeManual(manualGuruCount);

            const container = document.getElementById('manualGuruListContainer');
            if (container && container.children.length === 0) {
                container.innerHTML = `
                    <div id="manualEmptyPlaceholder" class="p-8 text-center bg-white rounded-2xl border border-slate-200">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2 text-2xl">
                            <i class="ti ti-circle-check"></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">Semua Siswa Telah Keluar</h4>
                        <p class="text-xs text-slate-500 mt-1">Seluruh siswa yang dibuatkan manual telah berhasil diverifikasi keluar.</p>
                    </div>
                `;
                // Sembunyikan banner di halaman
                const banner = document.getElementById('bannerManualGuru');
                if (banner) banner.classList.add('hidden');
            }
        }, 300);
    }
}

function perbaruiBadgeManual(cnt) {
    const txt = document.getElementById('manualGuruCountText');
    if (txt) txt.textContent = cnt;

    const modalBadge = document.getElementById('modalManualBadgeCount');
    if (modalBadge) modalBadge.textContent = `${cnt} Menunggu`;

    const banner = document.getElementById('bannerManualGuru');
    if (banner) {
        if (cnt > 0) {
            banner.classList.remove('hidden');
            banner.classList.add('flex');
        } else {
            banner.classList.add('hidden');
            banner.classList.remove('flex');
        }
    }
}

// Auto-open modal saat halaman dibuka jika ada siswa manual yang menunggu
document.addEventListener('DOMContentLoaded', function() {
    if (manualGuruCount > 0) {
        // Otomatis buka modal untuk satpam agar langsung terlihat
        setTimeout(() => {
            bukaModalManualGuru();
        }, 500);
    }

    // Polling setiap 25 detik untuk memeriksa pengajuan manual baru dari guru piket
    setInterval(cekDispensasiManualBaru, 25000);
});

function cekDispensasiManualBaru() {
    fetch('{{ route("satpam.manual-guru-pending") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        if (res && res.success) {
            const newCount = res.count || 0;
            if (newCount !== manualGuruCount) {
                manualGuruCount = newCount;
                perbaruiBadgeManual(newCount);
                renderUlangDaftarManual(res.data || []);
            }
        }
    })
    .catch(() => {});
}

function renderUlangDaftarManual(items) {
    const container = document.getElementById('manualGuruListContainer');
    if (!container) return;

    if (!items || items.length === 0) {
        container.innerHTML = `
            <div id="manualEmptyPlaceholder" class="p-8 text-center bg-white rounded-2xl border border-slate-200">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2 text-2xl">
                    <i class="ti ti-circle-check"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-900">Tidak Ada Dispensasi Manual Menunggu</h4>
                <p class="text-xs text-slate-500 mt-1">Semua siswa yang dibuatkan dispensasi manual oleh Guru Piket telah diverifikasi keluar.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = items.map(d => `
        <div id="manualCard_${d.id}"
             class="bg-white rounded-2xl border border-blue-200 p-4 space-y-3 shadow-2xs hover:border-blue-300 transition-all">
            <div class="flex items-start gap-3">
                ${d.foto_verifikasi ? `
                    <img src="${d.foto_verifikasi}"
                         alt="Foto ${escapeHtml(d.siswa_nama)}"
                         class="w-14 h-14 object-cover rounded-xl border border-blue-300 shadow-2xs shrink-0">
                ` : `
                    <div class="w-14 h-14 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 flex flex-col items-center justify-center shrink-0">
                        <i class="ti ti-school text-2xl"></i>
                        <span class="text-[8px] font-semibold text-slate-500">Siswa</span>
                    </div>
                `}
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 flex-wrap mb-1">
                        <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded-md text-[10px] font-bold bg-blue-100 text-blue-900 border border-blue-300">
                            <i class="ti ti-user-shield text-xs text-blue-600"></i>
                            <span>Dibuat: ${escapeHtml(d.guru_nama)}</span>
                        </span>
                        ${d.tipe_pengajuan === 'rombongan' ? `
                            <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded-md text-[10px] font-bold bg-purple-100 text-purple-900 border border-purple-200">
                                <i class="ti ti-users text-xs text-purple-600"></i>
                                <span>Rombongan (${d.total_rombongan} siswa)</span>
                            </span>
                        ` : ''}
                    </div>
                    <h4 class="font-bold text-slate-900 text-sm sm:text-base leading-snug">
                        ${escapeHtml(d.siswa_nama)}
                    </h4>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">
                        NIS: ${escapeHtml(d.siswa_nis)} • ${escapeHtml(d.siswa_kelas)}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Waktu Izin</span>
                    <span class="font-bold text-slate-800">
                        ${escapeHtml(d.jam_keluar)} &rarr; ${d.is_sampai_pulang ? 'Pulang' : escapeHtml(d.jam_kembali)}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">No. Surat</span>
                    <span class="font-mono font-bold text-slate-700 truncate block">
                        ${escapeHtml(d.nomor_surat)}
                    </span>
                </div>
                <div class="col-span-2 pt-1 border-t border-slate-200/60">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Keperluan</span>
                    <p class="text-slate-800 font-medium text-xs leading-relaxed">
                        ${escapeHtml(d.alasan || '-')}
                    </p>
                    ${d.lokasi || d.tujuan ? `
                        <p class="text-[11px] text-slate-500 mt-1 flex items-center">
                            <i class="ti ti-map-pin text-rose-500 mr-1 text-xs"></i>
                            Tujuan: <strong class="ml-1 text-slate-700">${escapeHtml(d.lokasi || d.tujuan)}</strong>
                        </p>
                    ` : ''}
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <a href="${d.detail_url}"
                   target="_blank"
                   class="px-3 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors flex items-center justify-center gap-1 shadow-2xs">
                    <i class="ti ti-file-text text-sm"></i>
                    <span>Detail</span>
                </a>
                <button type="button"
                        data-id="${d.id}"
                        data-siswa="${escapeHtml(d.siswa_nama)}"
                        data-guru="${escapeHtml(d.guru_nama)}"
                        onclick="verifikasiKeluarManual(this)"
                        class="flex-1 py-2.5 px-4 min-h-[42px] bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-[0.98] text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5">
                    <i class="ti ti-shield-check text-base"></i>
                    <span>Verifikasi KELUAR</span>
                </button>
            </div>
        </div>
    `).join('');
}
</script>
