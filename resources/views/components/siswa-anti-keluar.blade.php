@php
    $siswa = auth()->user()?->siswa;
    $activeDispensasi = $activeDispensasi ?? ($siswa ? \App\Models\Dispensasi::where(function ($q) use ($siswa) {
        $q->where('siswa_id', $siswa->id)
          ->orWhereHas('anggotaSiswa', function ($a) use ($siswa) {
              $a->where('siswa.id', $siswa->id);
          });
    })
    ->whereIn('status', ['menunggu', 'disetujui', 'keluar'])
    ->latest()
    ->first() : null);
@endphp

@if($activeDispensasi)
<div id="didispenAntiKeluarContainer" x-data="antiKeluarHandler()" x-init="initAntiKeluar()" class="relative z-50">

    {{-- ========================================================================= --}}
    {{-- 1. FLOATING PERMISSION BANNER (Jika Izin Notifikasi HP Belum Diberikan)   --}}
    {{-- ========================================================================= --}}
    <div x-show="showPermissionBanner"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="-translate-y-4 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="-translate-y-4 opacity-0"
         class="fixed top-3 inset-x-3 sm:left-auto sm:right-4 sm:max-w-md z-[70] bg-slate-900/95 backdrop-blur-md text-white rounded-2xl p-3.5 shadow-2xl border border-slate-700/80"
         style="display: none;">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center shrink-0 border border-blue-500/30 text-lg">
                <i class="ti ti-bell-ringing animate-bounce"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-white">Aktifkan Pengingat Notifikasi HP</p>
                <p class="text-[11px] text-slate-300 mt-0.5 leading-relaxed">
                    Terima peringatan di bilah atas HP saat Anda me-minimize web agar dispensasi tidak kadaluarsa.
                </p>
                <div class="mt-2.5 flex items-center gap-2">
                    <button type="button"
                            @click="requestNotificationPermission()"
                            class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-[11px] font-bold shadow-xs active:scale-95 transition-all">
                        Izinkan Notifikasi
                    </button>
                    <button type="button"
                            @click="showPermissionBanner = false"
                            class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-medium transition-colors">
                        Nanti
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. MODAL TEGURAN SAAT BUKA HP KEMBALI (VISIBILITY WAKE-UP MODAL)          --}}
    {{-- ========================================================================= --}}
    <div x-show="showWakeupModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         style="display: none;">

        <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden text-slate-900 animate-in zoom-in-95 duration-200">
            {{-- Header Berwarna Sesuai Status --}}
            <div class="p-5 pb-4 text-center {{ $activeDispensasi->status === 'menunggu' ? 'bg-amber-500 text-white' : ($activeDispensasi->status === 'disetujui' ? 'bg-emerald-600 text-white' : 'bg-blue-600 text-white') }}">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-3xl mb-2.5 shadow-xs border border-white/30">
                    @if($activeDispensasi->status === 'menunggu')
                        <i class="ti ti-hourglass-low animate-pulse"></i>
                    @elseif($activeDispensasi->status === 'disetujui')
                        <i class="ti ti-qrcode"></i>
                    @else
                        <i class="ti ti-walk"></i>
                    @endif
                </div>
                <span class="inline-block px-2.5 py-0.5 rounded-full bg-white/20 text-[10px] font-extrabold uppercase tracking-wider mb-1">
                    Peringatan Dispensasi Berjalan
                </span>
                <h3 class="text-base sm:text-lg font-bold leading-snug">
                    @if($activeDispensasi->status === 'menunggu')
                        Segera Temui Guru Piket!
                    @elseif($activeDispensasi->status === 'disetujui')
                        Segera Scan Keluar di Pos Satpam!
                    @else
                        Anda Sedang di Luar Sekolah
                    @endif
                </h3>
            </div>

            {{-- Body Modal --}}
            <div class="p-5 space-y-3.5">
                {{-- Detail Status Dispensasi --}}
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs text-slate-700 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">No. Surat</span>
                        <span class="font-mono font-bold text-slate-900">{{ $activeDispensasi->nomor_surat }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                        <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Batas Waktu</span>
                        <span class="font-bold text-slate-900">
                            @if($activeDispensasi->status === 'menunggu')
                                Kadaluarsa pukul {{ $activeDispensasi->getBatasWaktuExpiredText() }}
                            @elseif($activeDispensasi->status === 'disetujui')
                                Batas scan gerbang: 20 Menit ({{ $activeDispensasi->getBatasWaktuExpiredText() }})
                            @else
                                Batas kembali: {{ $activeDispensasi->isSampaiPulang() ? 'Sampai Pulang' : \App\Helpers\TimeHelper::getWaktuAktual($activeDispensasi->jam_kembali, $activeDispensasi->created_at?->dayOfWeek) }} WIB
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Instruksi Spesifik --}}
                <div class="text-xs text-slate-600 leading-relaxed space-y-2">
                    @if($activeDispensasi->status === 'menunggu')
                        <p class="font-semibold text-slate-800">
                            Pengajuan Anda belum diverifikasi. Datang langsung ke Ruang Piket sekarang agar tidak otomatis kadaluarsa!
                        </p>
                    @elseif($activeDispensasi->status === 'disetujui')
                        <p class="font-semibold text-slate-800">
                            Tunjukkan QR Code Anda kepada Satpam di pos gerbang dalam waktu maksimal 20 menit dari persetujuan.
                        </p>
                        <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2 text-[11px] font-semibold">
                            <i class="ti ti-shield-check text-base text-emerald-600 shrink-0"></i>
                            <span>Wajib memakai helm SNI dan tertib berlalu lintas!</span>
                        </div>
                    @else
                        <p class="font-semibold text-slate-800">
                            Pastikan Anda menjaga keselamatan diri, wajib memakai helm, dan segera lakukan scan kembali di pos Satpam begitu tiba di sekolah.
                        </p>
                    @endif
                </div>

                {{-- Kotak Pesan WhatsApp --}}
                <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200/90 text-emerald-950 flex items-start gap-2.5">
                    <i class="ti ti-brand-whatsapp text-emerald-600 text-lg mt-0.5 shrink-0"></i>
                    <div class="text-[11px] leading-relaxed">
                        <span class="font-bold block text-emerald-900">Perhatikan WhatsApp Anda:</span>
                        Sistem DIDISPEN telah mengirimkan pesan otomatis ke nomor WhatsApp Anda. Buka dan baca petunjuk resmi di chat tersebut!
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="pt-2 flex flex-col sm:flex-row gap-2">
                    <a href="{{ route('siswa.pengajuan.show', $activeDispensasi) }}"
                       class="flex-1 py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold text-center shadow-xs active:scale-95 transition-all">
                        <i class="ti ti-qrcode mr-1"></i>Lihat Detail / QR Code
                    </a>
                    <button type="button"
                            @click="showWakeupModal = false"
                            class="py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold text-center transition-colors">
                        Saya Paham, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.DIDISPEN_ACTIVE_DISPENSASI = {
    id: {{ $activeDispensasi->id }},
    nomor_surat: @json($activeDispensasi->nomor_surat),
    status: @json($activeDispensasi->status),
    batas_expired_text: @json($activeDispensasi->getBatasWaktuExpiredText()),
    jam_keluar_text: @json(\App\Helpers\TimeHelper::getWaktuAktual($activeDispensasi->jam_keluar, $activeDispensasi->created_at?->dayOfWeek)),
    jam_kembali_text: @json($activeDispensasi->isSampaiPulang() ? 'Sampai Pulang' : \App\Helpers\TimeHelper::getWaktuAktual($activeDispensasi->jam_kembali, $activeDispensasi->created_at?->dayOfWeek)),
    sampai_pulang: {{ $activeDispensasi->isSampaiPulang() ? 'true' : 'false' }},
    show_url: @json(route('siswa.pengajuan.show', $activeDispensasi)),
};

function antiKeluarHandler() {
    return {
        showPermissionBanner: false,
        showWakeupModal: false,
        lastHiddenTime: 0,

        initAntiKeluar() {
            const self = this;
            const data = window.DIDISPEN_ACTIVE_DISPENSASI;
            if (!data) return;

            // 1. Cek Permission Notifikasi
            if ('Notification' in window) {
                if (Notification.permission === 'default') {
                    // Munculkan tawaran izin setelah 2 detik
                    setTimeout(() => {
                        self.showPermissionBanner = true;
                    }, 2000);
                }
            }

            // 2. Dialog Penahan Tutup Tab / Back (beforeunload)
            window.addEventListener('beforeunload', function(e) {
                if (window.__didispenSubmitting) return;

                const message = 'Perhatian! Dispensasi Anda sedang berjalan. Jangan tinggalkan halaman sebelum menyelesaikan kewajiban izin!';
                e.preventDefault();
                e.returnValue = message;
                return message;
            });

            // Hindari dialog penahan saat submit form yang sah
            document.addEventListener('submit', function() {
                window.__didispenSubmitting = true;
            });

            // Hindari dialog penahan saat klik tautan internal DIDISPEN
            document.addEventListener('click', function(e) {
                const targetLink = e.target.closest('a');
                if (targetLink && targetLink.href) {
                    const isInternal = targetLink.href.startsWith(window.location.origin) || targetLink.getAttribute('href').startsWith('/');
                    const targetAttr = targetLink.getAttribute('target');
                    if (isInternal && (!targetAttr || targetAttr === '_self')) {
                        window.__didispenSubmitting = true;
                        setTimeout(() => { window.__didispenSubmitting = false; }, 3000);
                    }
                }
            });

            // 3. Deteksi Minimize & Balik ke Layar (visibilitychange)
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'hidden') {
                    self.lastHiddenTime = Date.now();
                    self.handleAppMinimized(data);
                } else if (document.visibilityState === 'visible') {
                    const elapsed = Date.now() - (self.lastHiddenTime || 0);
                    // Jika diminimize lebih dari 1.5 detik, tampilkan modal wake-up
                    if (self.lastHiddenTime > 0 && elapsed > 1500) {
                        self.showWakeupModal = true;
                        if ('vibrate' in navigator) {
                            try { navigator.vibrate([120, 80, 120]); } catch (e) {}
                        }
                    }
                }
            });

            // 4. Deteksi tombol Back di HP (popstate history)
            window.history.pushState({ didispenGuard: true }, '');
            window.addEventListener('popstate', function(event) {
                if (window.DIDISPEN_ACTIVE_DISPENSASI && !window.__didispenSubmitting) {
                    self.showWakeupModal = true;
                    window.history.pushState({ didispenGuard: true }, '');
                }
            });
        },

        requestNotificationPermission() {
            const self = this;
            if ('Notification' in window) {
                Notification.requestPermission().then(permission => {
                    self.showPermissionBanner = false;
                    if (permission === 'granted') {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Pengingat Aktif!',
                                text: 'Sistem akan mengirimkan notifikasi penting saat Anda me-minimize web.',
                                timer: 2500,
                                showConfirmButton: false,
                            });
                        }
                    }
                });
            } else {
                self.showPermissionBanner = false;
            }
        },

        handleAppMinimized(data) {
            // Getarkan HP saat siswa minimize/keluar
            if ('vibrate' in navigator) {
                try {
                    navigator.vibrate([250, 100, 250]);
                } catch (e) {}
            }

            // Siapkan judul & teks notifikasi persis sesuai instruksi Paket 3
            let title = '⚠️ Peringatan DIDISPEN!';
            let body = 'Dispensasi Anda sedang aktif. Catatan: Pesan resmi dikirim via WhatsApp DIDISPEN.';

            if (data.status === 'menunggu') {
                title = '🔔 Jangan Tutup Web!';
                body = 'Jangan tutup web! Segera temui Guru Piket (Sisa 20 Menit). Catatan: Pesan resmi dikirim via WhatsApp DIDISPEN.';
            } else if (data.status === 'disetujui') {
                title = '🚨 Segera ke Pos Satpam!';
                body = 'Segera ke Pos Satpam (Sisa 20 Menit). Wajib pakai helm! Catatan: Pesan resmi dikirim via WhatsApp DIDISPEN.';
            } else if (data.status === 'keluar') {
                title = '🛵 Hati-hati di Jalan!';
                const jamKembali = data.sampai_pulang ? 'KBM Selesai' : data.jam_kembali_text;
                body = `Hati-hati di jalan, wajib helm. Batas kembali pukul ${jamKembali} WIB. Catatan: Pesan resmi dikirim via WhatsApp DIDISPEN.`;
            }

            // Tampilkan Notifikasi Sistem di Status Bar HP (Web Notification API)
            if ('Notification' in window && Notification.permission === 'granted') {
                try {
                    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                        navigator.serviceWorker.ready.then(reg => {
                            reg.showNotification(title, {
                                body: body,
                                icon: '/icons/icon-192.png',
                                badge: '/icons/icon-192.png',
                                vibrate: [250, 100, 250],
                                tag: 'didispen-active-reminder',
                                renotify: true,
                                data: { url: data.show_url }
                            });
                        }).catch(() => {
                            new Notification(title, { body: body, icon: '/icons/icon-192.png' });
                        });
                    } else {
                        new Notification(title, { body: body, icon: '/icons/icon-192.png' });
                    }
                } catch (err) {
                    console.warn('Gagal memicu notification:', err);
                }
            }
        }
    };
}
</script>
@endif
