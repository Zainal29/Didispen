@extends('satpam.layouts.app')

@section('title', 'Dashboard Satpam')
@section('page-title', 'Dashboard Satpam')

@section('content')
@include('components.alert')

{{-- ============ HERO SECTION ============ --}}
<div class="relative overflow-hidden rounded-2xl mb-4">
    <div class="absolute inset-0 bg-gradient-to-r from-red-700 via-red-600 to-rose-500"></div>
    <div class="absolute -top-16 -right-16 w-56 h-56 bg-white/10 rounded-full"></div>
    <div class="absolute -bottom-20 left-20 w-60 h-60 bg-white/10 rounded-full"></div>

    <div class="relative z-10 p-4 sm:p-6 flex items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="text-red-100 text-[10px] sm:text-[11px] font-bold uppercase tracking-widest">
                {{ now()->isoFormat('dddd, D MMMM Y') }} • Pos Gerbang
            </p>
            <h2 class="text-lg sm:text-2xl font-black text-white tracking-tight mt-0.5 truncate">
                Halo, {{ auth()->user()->name }} 👋
            </h2>
            <p class="text-red-100 text-[11px] mt-1 hidden sm:block">Pantau keluar-masuk siswa dispensasi hari ini dengan mudah.</p>
        </div>
        <a href="{{ route('satpam.scan') }}" class="hidden sm:inline-flex items-center px-5 py-3 rounded-xl bg-white text-red-700 text-sm font-bold shadow-xl hover:-translate-y-0.5 transition-all flex-shrink-0">
            <i class="fas fa-qrcode mr-2"></i> Scan QR
        </a>
    </div>
</div>

{{-- ============ FILTER TABS ============ --}}
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-4">
    <div class="p-2 flex flex-wrap gap-2">
        <button onclick="switchTab('all')" id="tab-all" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition-all bg-red-600 text-white shadow-md">
            <i class="fas fa-layer-group mr-1.5"></i>Semua
        </button>
        <button onclick="switchTab('menunggu')" id="tab-menunggu" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200">
            <i class="fas fa-clock mr-1.5"></i>Menunggu <span id="badge-menunggu" class="px-2 py-0.5 bg-amber-500 text-white text-[10px] rounded-full ml-1">{{ $menungguKeluar->count() }}</span>
        </button>
        <button onclick="switchTab('keluar')" id="tab-keluar" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200">
            <i class="fas fa-person-walking mr-1.5"></i>Keluar <span id="badge-keluar" class="px-2 py-0.5 bg-sky-500 text-white text-[10px] rounded-full ml-1">{{ $siswaKeluar->count() }}</span>
        </button>
        <button onclick="switchTab('terlambat')" id="tab-terlambat" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200">
            <i class="fas fa-exclamation-triangle mr-1.5"></i>Terlambat <span id="badge-terlambat" class="px-2 py-0.5 bg-red-500 text-white text-[10px] rounded-full ml-1">0</span>
        </button>
        <button onclick="switchTab('selesai')" id="tab-selesai" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200">
            <i class="fas fa-check-circle mr-1.5"></i>Selesai <span id="badge-selesai" class="px-2 py-0.5 bg-emerald-500 text-white text-[10px] rounded-full ml-1">{{ $stats['selesai'] ?? 0 }}</span>
        </button>
        <button onclick="switchTab('dihubungi')" id="tab-dihubungi" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200">
            <i class="fas fa-phone-alt mr-1.5"></i>Sudah Dihubungi <span id="badge-dihubungi" class="px-2 py-0.5 bg-purple-500 text-white text-[10px] rounded-full ml-1">0</span>
        </button>
    </div>
</div>

{{-- ============ CONTENT SECTIONS ============ --}}

{{-- SECTION: MENUNGGU KELUAR (hidden saat load — tab aktif awal adalah "Semua") --}}
<div id="section-menunggu" class="space-y-3 hidden">
    <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-clock text-amber-500 mr-1.5"></i>Menunggu Konfirmasi Keluar</h3>
    @foreach($menungguKeluar as $dispensasi)
        @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'menunggu', 'isOverdue' => false])
    @endforeach
</div>

{{-- SECTION: SEDANG KELUAR --}}
<div id="section-keluar" class="space-y-3 hidden">
    <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-person-walking text-sky-500 mr-1.5"></i>Sedang Keluar</h3>
    @foreach($siswaKeluar as $dispensasi)
        @php $isOverdue = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali); @endphp
        @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'keluar', 'isOverdue' => $isOverdue])
    @endforeach
</div>

{{-- SECTION: TERLAMBAT --}}
<div id="section-terlambat" class="space-y-3 hidden">
    <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-exclamation-triangle text-red-500 mr-1.5"></i>Siswa Terlambat</h3>
    @php $terlambatCount = 0; @endphp
    @foreach($siswaKeluar as $dispensasi)
        @php
            $isOverdue = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);
            if($isOverdue) $terlambatCount++;
        @endphp
        @if($isOverdue)
            @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'terlambat', 'isOverdue' => true])
        @endif
    @endforeach
    @if($terlambatCount === 0)
        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
            <i class="fas fa-check-circle text-4xl text-emerald-300 mb-3"></i>
            <p class="text-gray-500 text-sm">Tidak ada siswa yang terlambat hari ini</p>
        </div>
    @endif
</div>

{{-- SECTION: SELESAI (SUDAH KEMBALI) --}}
<div id="section-selesai" class="space-y-3 hidden">
    <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-check-circle text-emerald-500 mr-1.5"></i>Sudah Kembali</h3>
    @if(isset($selesai) && $selesai->count() > 0)
        @foreach($selesai as $dispensasi)
            @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'selesai', 'isOverdue' => false])
        @endforeach
    @else
        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-50 text-emerald-300 flex items-center justify-center text-3xl mb-3"><i class="fas fa-check-circle"></i></div>
            <p class="text-gray-500 text-sm font-semibold">Belum ada siswa yang kembali hari ini</p>
        </div>
    @endif
</div>

{{-- ✅ SECTION: SUDAH DIHUBUNGI (VERSI BERSIH & PESAN PERINGATAN) --}}
<div id="section-dihubungi" class="space-y-3 hidden">
    <h3 class="text-sm font-bold text-gray-700 mb-3">
        <i class="fas fa-phone-alt text-purple-500 mr-1.5"></i>
        Riwayat Siswa yang Sudah Dihubungi
        <span class="text-xs font-normal text-gray-500 ml-2">({{ $dihubungi->count() }} siswa)</span>
    </h3>
    
    @if($dihubungi->count() > 0)
        @foreach($dihubungi as $dispensasi)
            @php
                $isOverdue = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);
                $waLink = '';
                
                if (!empty($dispensasi->siswa->no_telepon)) {
                    $hp = preg_replace('/[^0-9]/', '', $dispensasi->siswa->no_telepon);
                    if (str_starts_with($hp, '0')) {
                        $hp = '62' . substr($hp, 1);
                    }
                    
                    // ✅ PESAN PERINGATAN KETERLAMBATAN
                    $pesan = "⚠️ *PERINGATAN KETERLAMBATAN DISPENSASI* ⚠️\n\n" .
                             "Yth. *{$dispensasi->siswa->nama_lengkap}*,\n\n" .
                             "Berdasarkan data sistem, batas waktu kembali dispensasi Anda (No. *{$dispensasi->nomor_surat}*) telah **LEWAT** pada pukul *{$dispensasi->jam_kembali}*.\n\n" .
                             "📍 Tujuan Dispensasi: {$dispensasi->tujuan}\n\n" .
                             "⚠️ **SEGERA KEMBALI** ke lingkungan sekolah atau lapor ke Pos Satpam untuk menghindari sanksi administratif.\n\n" .
                             "Terima kasih,\n*Petugas Satpam SMKN 1 Bangsri*";
                             
                    $waLink = "https://wa.me/{$hp}?text=" . urlencode($pesan);
                }
            @endphp
            
            <div class="bg-white rounded-2xl border-2 border-purple-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3.5 bg-purple-50 border-b border-purple-100">
                    <div class="flex justify-between items-start gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="font-mono font-bold text-gray-800 text-xs">{{ $dispensasi->nomor_surat }}</p>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-700 uppercase">
                                    <i class="fas fa-phone-alt mr-1"></i>DIHUBUNGI
                                </span>
                                @if($isOverdue)
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700 uppercase">TERLAMBAT</span>
                                @endif
                            </div>
                            <p class="font-bold text-gray-900 text-sm truncate">{{ $dispensasi->siswa->nama_lengkap }}</p>
                            <p class="text-[11px] text-gray-500 truncate">
                                {{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }} • {{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}
                            </p>
                        </div>
                        <a href="{{ route('satpam.dispensasi.detail', $dispensasi) }}" 
                           class="inline-flex items-center justify-center w-9 h-9 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors" title="Lihat Detail">
                            <i class="fas fa-eye text-sm"></i>
                        </a>
                    </div>
                </div>
                
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="bg-white p-2.5 rounded-lg border border-gray-200">
                            <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Jam Keluar</p>
                            <p class="font-bold text-blue-700">{{ $dispensasi->jam_keluar }}</p>
                        </div>
                        <div class="bg-white p-2.5 rounded-lg border border-gray-200">
                            <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Jam Kembali</p>
                            <p class="font-bold {{ $isOverdue ? 'text-red-700' : 'text-amber-700' }}">{{ $dispensasi->jam_kembali }}</p>
                        </div>
                    </div>
                    
                    @if($dispensasi->warned_at)
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                        <p class="text-purple-700 text-xs font-bold">
                            <i class="fas fa-clock mr-1"></i>
                            Dihubungi pada: {{ $dispensasi->warned_at->isoFormat('D MMMM Y, HH:mm') }} WIB
                        </p>
                    </div>
                    @endif
                    
                    @if(!empty($dispensasi->siswa->no_telepon))
                    <div class="flex items-center gap-2">
                        <div class="flex-1 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                            <p class="text-[10px] font-bold text-green-700 uppercase">Kontak</p>
                            <p class="text-xs font-bold text-gray-800 font-mono">{{ $dispensasi->siswa->no_telepon }}</p>
                        </div>
                      
                    </div>
                    @endif
                    
                    @if($dispensasi->status === 'keluar')
                        <form method="POST" action="{{ route('satpam.konfirmasi.kembali', $dispensasi) }}">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all">
                                <i class="fas fa-door-closed mr-1.5"></i>Konfirmasi Kembali
                            </button>
                        </form>
                    @elseif($dispensasi->status === 'selesai')
                        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-center">
                            <p class="text-emerald-700 text-xs font-bold"><i class="fas fa-check-circle mr-1"></i>Siswa Sudah Kembali</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-purple-50 text-purple-300 flex items-center justify-center text-3xl mb-3"><i class="fas fa-phone-slash"></i></div>
            <p class="text-gray-500 text-sm font-semibold">Belum ada siswa yang dihubungi</p>
            <p class="text-gray-400 text-xs mt-1">Gunakan tombol "Tandai Sudah Dihubungi" pada kartu siswa</p>
        </div>
    @endif
</div>

{{-- SECTION: ALL (SEMUA) --}}
<div id="section-all" class="space-y-3">
    <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-layer-group text-gray-500 mr-1.5"></i>Semua Dispensasi Hari Ini</h3>
    @foreach($menungguKeluar as $dispensasi)
        @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'menunggu', 'isOverdue' => false])
    @endforeach
    @foreach($siswaKeluar as $dispensasi)
        @php $isOverdue = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali); @endphp
        @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'keluar', 'isOverdue' => $isOverdue])
    @endforeach
    @foreach($selesai as $dispensasi)
        @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'selesai', 'isOverdue' => false])
    @endforeach
</div>

@endsection

@push('scripts')
<script>
function updateBadges() {
    // ✅ FIX: hitung HANYA kartu di section masing-masing (bukan seluruh dokumen),
    // karena section "Semua" merender ulang kartu menunggu/keluar (dulu badge jadi 2x).
    document.getElementById('badge-menunggu').textContent = document.querySelectorAll('#section-menunggu [data-status="menunggu"]').length;
    document.getElementById('badge-keluar').textContent = document.querySelectorAll('#section-keluar [data-status="keluar"]').length;
    document.getElementById('badge-terlambat').textContent = document.querySelectorAll('[data-status="keluar"][data-overdue="true"]').length;
    document.getElementById('badge-dihubungi').textContent = {{ $dihubungi->count() }};
}

function switchTab(tabName) {
    document.querySelectorAll('[id^="section-"]').forEach(section => section.classList.add('hidden'));
    document.querySelectorAll('[id^="tab-"]').forEach(tab => {
        tab.classList.remove('bg-red-600', 'text-white', 'shadow-md');
        tab.classList.add('bg-gray-100', 'text-gray-600');
    });
    
    document.getElementById('section-' + tabName).classList.remove('hidden');
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('bg-gray-100', 'text-gray-600');
    activeTab.classList.add('bg-red-600', 'text-white', 'shadow-md');
}

// ✅ BARU: Buka/tutup detail kartu (tombol WA & Konfirmasi Kembali ada di dalamnya)
function toggleCardDetail(event, el) {
    if (event.target.closest('a, button, form')) return;
    const detail = el.querySelector('.detail-section');
    if (detail) detail.classList.toggle('hidden');
}

// ✅ FUNGSI BARU: Klik WA langsung tandai dihubungi & buka WA
function handleWaContacted(dispensasiId, waLink) {
    fetch(`/satpam/dispensasi/${dispensasiId}/wa-contacted`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        keepalive: true // Agar request tetap jalan meski tab baru terbuka
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 1. Update UI menjadi "Sudah Dihubungi"
            const waSection = document.getElementById(`wa-section-${dispensasiId}`);
            if (waSection) {
                waSection.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <p class="text-[10px] font-bold text-green-700 uppercase">Kontak Darurat</p>
                            <p class="text-sm font-bold text-gray-800 font-mono">${waSection.querySelector('.font-mono').textContent}</p>
                        </div>
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-300 text-gray-500 rounded-xl cursor-not-allowed" title="Sudah dihubungi">
                            <i class="fas fa-check text-xl"></i>
                        </div>
                    </div>
                    <p class="text-[10px] text-green-600">
                        <i class="fas fa-check-circle mr-1"></i>Sudah dihubungi via WhatsApp
                    </p>
                `;
            }
            
            // 2. Update badge jumlah di tab & buka WhatsApp di tab baru
            if (typeof updateBadges === 'function') updateBadges();
            window.open(waLink, '_blank');
        } else {
            // Status bukan "keluar" — tetap buka WhatsApp tanpa menandai
            window.open(waLink, '_blank');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Fallback: Tetap buka WhatsApp meski AJAX gagal
        window.open(waLink, '_blank');
    });
}

// ✅ BARU: Countdown realtime untuk semua kartu yang punya batas waktu
function tickCountdowns() {
    document.querySelectorAll('.live-countdown[data-deadline]').forEach(el => {
        const deadline = new Date(el.dataset.deadline);
        const diffMs = deadline - new Date();

        if (diffMs <= 0) {
            const lateMin = Math.floor(-diffMs / 60000);
            el.textContent = `TERLAMBAT ${lateMin}m`;
            el.classList.remove('bg-amber-100', 'text-amber-700');
            el.classList.add('bg-red-100', 'text-red-700', 'animate-pulse');
        } else {
            const totalMin = Math.floor(diffMs / 60000);
            const hrs = Math.floor(totalMin / 60);
            const mins = totalMin % 60;
            el.textContent = hrs > 0 ? `Sisa ${hrs}j ${mins}m` : `Sisa ${mins}m`;
            if (totalMin <= 5) {
                el.classList.remove('bg-amber-100', 'text-amber-700');
                el.classList.add('bg-red-100', 'text-red-700');
            }
        }
    });
}

// ✅ BARU: Watcher — deteksi kartu yang baru melewati batas waktu & beri notifikasi
const overdueNotified = new Set();
function watchOverdue() {
    document.querySelectorAll('[data-status="keluar"][data-deadline][data-overdue="false"]').forEach(card => {
        const deadline = new Date(card.dataset.deadline);
        if (new Date() > deadline) {
            card.dataset.overdue = 'true';

            // Tambah badge TERLAMBAT di header kartu
            const badgeRow = card.querySelector('.flex.items-center.gap-2.mb-1');
            if (badgeRow && !badgeRow.querySelector('.overdue-flag')) {
                badgeRow.insertAdjacentHTML('afterbegin',
                    `<span class="overdue-flag px-2 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700 uppercase animate-pulse">
                        <i class="fas fa-exclamation-triangle mr-1"></i>TERLAMBAT
                    </span>`);
            }

            const nama = card.querySelector('.font-bold.text-gray-900')?.textContent.trim() || 'Siswa';
            if (!overdueNotified.has(card.dataset.dispensasi)) {
                overdueNotified.add(card.dataset.dispensasi);
                Swal.fire({
                    icon: 'warning',
                    title: '⚠️ Siswa Terlambat!',
                    text: `${nama} telah melewati batas waktu kembali. Segera hubungi via WhatsApp.`,
                    confirmButtonColor: '#dc2626',
                    timer: 8000,
                    timerProgressBar: true
                });
            }
            updateBadges();
        }
    });
}

updateBadges();
tickCountdowns();
setInterval(tickCountdowns, 1000);
setInterval(watchOverdue, 15000);
</script>
@endpush
