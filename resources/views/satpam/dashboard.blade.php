@extends('satpam.layouts.app')

@section('title', 'Dashboard Satpam')
@section('page-title', 'Dashboard Satpam')

@section('content')
@include('components.alert')

{{-- ✅ CSS UNTUK SMOOTH TRANSITIONS --}}
<style>
    .filter-btn { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .filter-btn:hover { transform: translateY(-2px); }
    .filter-btn.active { transform: scale(1.05); }
    #content-area { transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out; }
    .fade-out { opacity: 0; transform: translateY(10px); }
    .fade-in { opacity: 1; transform: translateY(0); }
</style>

@php
    $currentFilter = $filter ?? 'semua';
    $terlambatCount = 0;
    foreach($siswaKeluar as $d) {
        if ($d->batas_waktu_kembali && now()->greaterThan($d->batas_waktu_kembali)) {
            $terlambatCount++;
        }
    }
@endphp

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

{{-- ============ FILTER TABS (DYNAMIC & SMOOTH) ============ --}}
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-4">
    <div class="p-2 flex flex-wrap gap-2">
        @php
        $filters = [
            'semua' => ['label' => 'Semua', 'icon' => 'fa-layer-group', 'count' => $stats['total'] ?? 0, 'color' => 'red'],
            'menunggu' => ['label' => 'Menunggu', 'icon' => 'fa-clock', 'count' => $menungguKeluar->count(), 'color' => 'amber'],
            'keluar' => ['label' => 'Keluar', 'icon' => 'fa-walking', 'count' => $siswaKeluar->count(), 'color' => 'sky'],
            'terlambat' => ['label' => 'Terlambat', 'icon' => 'fa-exclamation-triangle', 'count' => $terlambatCount, 'color' => 'red'],
            'selesai' => ['label' => 'Selesai', 'icon' => 'fa-check-double', 'count' => $stats['selesai'] ?? 0, 'color' => 'emerald'],
            'dihubungi' => ['label' => 'Dihubungi', 'icon' => 'fa-phone-alt', 'count' => $dihubungi->count(), 'color' => 'purple'],
        ];
        @endphp

        @foreach($filters as $key => $f)
        <button type="button"
                onclick="switchFilter('{{ $key }}', '{{ $f['color'] }}', event)"
                data-filter="{{ $key }}"
                class="filter-btn flex-1 min-w-[100px] px-3 py-2.5 rounded-xl text-xs font-bold text-center border transition-all
                {{ $currentFilter === $key
                    ? 'active bg-' . $f['color'] . '-600 text-white shadow-lg shadow-' . $f['color'] . '-500/30 border-transparent'
                    : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border-gray-200' }}">
            <i class="fas {{ $f['icon'] }} mr-1"></i> {{ $f['label'] }}
            @if($f['count'] > 0)
                <span class="inline-block ml-1 px-1.5 py-0.5 rounded-full text-[9px] font-black {{ $currentFilter === $key ? 'bg-white/20' : 'bg-' . $f['color'] . '-100 text-' . $f['color'] . '-700' }}">
                    {{ $f['count'] }}
                </span>
            @endif
        </button>
        @endforeach
    </div>
</div>

{{-- ============ CONTENT SECTIONS ============ --}}
<div id="content-area" class="fade-in">

    {{-- SECTION: MENUNGGU --}}
    <div id="section-menunggu" class="space-y-3 {{ $currentFilter !== 'menunggu' ? 'hidden' : '' }}">
        <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-clock text-amber-500 mr-1.5"></i>Menunggu Konfirmasi Keluar</h3>
        @foreach($menungguKeluar as $dispensasi)
            @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'menunggu', 'isOverdue' => false])
        @endforeach
    </div>

    {{-- SECTION: KELUAR --}}
    <div id="section-keluar" class="space-y-3 {{ $currentFilter !== 'keluar' ? 'hidden' : '' }}">
        <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-person-walking text-sky-500 mr-1.5"></i>Sedang Keluar</h3>
        @foreach($siswaKeluar as $dispensasi)
            @php $isOverdue = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali); @endphp
            @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'keluar', 'isOverdue' => $isOverdue])
        @endforeach
    </div>

    {{-- SECTION: TERLAMBAT --}}
    <div id="section-terlambat" class="space-y-3 {{ $currentFilter !== 'terlambat' ? 'hidden' : '' }}">
        <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-exclamation-triangle text-red-500 mr-1.5"></i>Siswa Terlambat</h3>
        @php $renderedTerlambat = 0; @endphp
        @foreach($siswaKeluar as $dispensasi)
            @php
                $isOverdue = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);
                if (!$isOverdue) continue;
                $renderedTerlambat++;
                $lateMinutes = now()->diffInMinutes($dispensasi->batas_waktu_kembali);
                $lateText = floor($lateMinutes / 60) > 0 ? floor($lateMinutes / 60).'j '.($lateMinutes % 60).'m' : $lateMinutes.'m';
            @endphp
            <div class="bg-white rounded-2xl border-2 border-red-200 shadow-sm overflow-hidden" data-dispensasi="{{ $dispensasi->id }}" data-status="terlambat" data-overdue="true" data-deadline="{{ $dispensasi->batas_waktu_kembali->format('Y-m-d H:i:s') }}">
                <div class="px-4 py-3.5 bg-red-50 border-b border-red-100">
                    <div class="flex justify-between items-start gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <p class="font-mono font-bold text-gray-800 text-xs">{{ $dispensasi->nomor_surat }}</p>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700 uppercase animate-pulse"><i class="fas fa-exclamation-triangle mr-1"></i>TERLAMBAT {{ $lateText }}</span>
                                @if($dispensasi->is_warned)<span class="px-2 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-700 uppercase"><i class="fas fa-phone-alt mr-1"></i>DIHUBUNGI</span>@endif
                            </div>
                            <p class="font-bold text-gray-900 text-sm truncate">{{ $dispensasi->siswa->nama_lengkap }}</p>
                            <p class="text-[11px] text-gray-500 truncate">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }} • {{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                        </div>
                        <a href="{{ route('satpam.dispensasi.detail', $dispensasi) }}" class="inline-flex items-center justify-center w-9 h-9 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors flex-shrink-0"><i class="fas fa-eye text-sm"></i></a>
                    </div>
                </div>
                <div class="p-4 space-y-3 bg-gray-50/50">
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="bg-white p-2.5 rounded-lg border border-gray-200"><p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Jam Keluar</p><p class="font-bold text-gray-800">{{ $dispensasi->jam_keluar }}</p></div>
                        <div class="bg-white p-2.5 rounded-lg border border-gray-200"><p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Jam Kembali</p><p class="font-bold text-red-700">{{ $dispensasi->jam_kembali }}</p></div>
                    </div>
                    @if(!empty($dispensasi->siswa->no_telepon))
                        @php
                            $hp = preg_replace('/[^0-9]/', '', $dispensasi->siswa->no_telepon);
                            if (str_starts_with($hp, '0')) $hp = '62' . substr($hp, 1);
                            $waLink = "https://wa.me/{$hp}?text=" . urlencode("⚠️ *PERINGATAN KETERLAMBATAN* ⚠️\n\nYth. *{$dispensasi->siswa->nama_lengkap}*,\nBatas waktu kembali dispensasi Anda telah **LEWAT**.\n\n📍 Tujuan: {$dispensasi->tujuan}\n⚠️ **SEGERA KEMBALI** ke sekolah.\n\n*Petugas Satpam SMKN 1 Bangsri*");
                        @endphp
                        <div id="wa-section-{{ $dispensasi->id }}" class="bg-green-50 border-2 border-green-200 rounded-xl p-3">
                            <div class="flex items-center justify-between mb-2">
                                <div><p class="text-[10px] font-bold text-green-700 uppercase">Kontak Darurat</p><p class="text-sm font-bold text-gray-800 font-mono">{{ $dispensasi->siswa->no_telepon }}</p></div>
                                <button onclick="handleWaContacted({{ $dispensasi->id }}, '{{ $waLink }}')" class="inline-flex items-center justify-center w-12 h-12 bg-green-500 hover:bg-green-600 text-white rounded-xl transition-all active:scale-95 shadow-md shadow-green-500/30"><i class="fab fa-whatsapp text-xl"></i></button>
                            </div>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('satpam.konfirmasi.kembali', $dispensasi) }}">@csrf<button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all"><i class="fas fa-door-closed mr-1.5"></i>Konfirmasi Kembali</button></form>
                </div>
            </div>
        @endforeach
        @if($renderedTerlambat === 0)
            <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center"><i class="fas fa-check-circle text-4xl text-emerald-300 mb-3"></i><p class="text-gray-500 text-sm">Tidak ada siswa yang terlambat hari ini</p></div>
        @endif
    </div>

    {{-- SECTION: SELESAI --}}
    <div id="section-selesai" class="space-y-3 {{ $currentFilter !== 'selesai' ? 'hidden' : '' }}">
        <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-check-circle text-emerald-500 mr-1.5"></i>Sudah Kembali</h3>
        @if(isset($selesai) && $selesai->count() > 0)
            @foreach($selesai as $dispensasi)
                @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'selesai', 'isOverdue' => false])
            @endforeach
        @else
            <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center"><div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-50 text-emerald-300 flex items-center justify-center text-3xl mb-3"><i class="fas fa-check-circle"></i></div><p class="text-gray-500 text-sm font-semibold">Belum ada siswa yang kembali hari ini</p></div>
        @endif
    </div>

    {{-- SECTION: DIHUBUNGI --}}
    <div id="section-dihubungi" class="space-y-3 {{ $currentFilter !== 'dihubungi' ? 'hidden' : '' }}">
        <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-phone-alt text-purple-500 mr-1.5"></i>Riwayat Siswa yang Sudah Dihubungi <span class="text-xs font-normal text-gray-500 ml-2">({{ $dihubungi->count() }} siswa)</span></h3>
        @if($dihubungi->count() > 0)
            @foreach($dihubungi as $dispensasi)
                @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'dihubungi', 'isOverdue' => false])
            @endforeach
        @else
            <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center"><div class="w-16 h-16 mx-auto rounded-2xl bg-purple-50 text-purple-300 flex items-center justify-center text-3xl mb-3"><i class="fas fa-phone-slash"></i></div><p class="text-gray-500 text-sm font-semibold">Belum ada siswa yang dihubungi hari ini</p></div>
        @endif
    </div>

    {{-- SECTION: SEMUA --}}
    <div id="section-semua" class="space-y-3 {{ $currentFilter !== 'semua' ? 'hidden' : '' }}">
        <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-layer-group text-gray-500 mr-1.5"></i>Semua Dispensasi Hari Ini</h3>
        @foreach($menungguKeluar as $dispensasi) @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'menunggu', 'isOverdue' => false]) @endforeach
        @foreach($siswaKeluar as $dispensasi) @php $isOverdue = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali); @endphp @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'keluar', 'isOverdue' => $isOverdue]) @endforeach
        @foreach($selesai as $dispensasi) @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'selesai', 'isOverdue' => false]) @endforeach
    </div>

</div> {{-- Tutup #content-area --}}

{{-- Loading Overlay --}}
<div id="loading-overlay" class="hidden fixed inset-0 bg-black/10 backdrop-blur-[2px] z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-4 shadow-xl flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-2 border-red-600 border-t-transparent"></div>
        <span class="text-xs font-bold text-gray-700">Memuat data...</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentFilter = '{{ $currentFilter }}';

// ✅ 1. FUNGSI SWITCH FILTER (AJAX SMOOTH)
function switchFilter(filterKey, color, event) {
    if (event) event.preventDefault();
    if (filterKey === currentFilter) return;

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-red-600', 'bg-amber-600', 'bg-sky-600', 'bg-emerald-600', 'bg-purple-600', 'text-white', 'shadow-lg', 'border-transparent');
        btn.classList.add('bg-gray-50', 'text-gray-600', 'border-gray-200');
    });

    const activeBtn = document.querySelector(`button[data-filter="${filterKey}"]`);
    if (activeBtn) {
        activeBtn.classList.remove('bg-gray-50', 'text-gray-600', 'border-gray-200');
        activeBtn.classList.add('active', `bg-${color}-600`, 'text-white', `shadow-lg`, `shadow-${color}-500/30`, 'border-transparent');
    }

    const contentArea = document.getElementById('content-area');
    const loading = document.getElementById('loading-overlay');

    contentArea.classList.remove('fade-in');
    contentArea.classList.add('fade-out');
    loading.classList.remove('hidden');

    fetch(`{{ url()->current() }}?filter=${filterKey}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.getElementById('content-area');

        if (newContent) {
            setTimeout(() => {
                contentArea.innerHTML = newContent.innerHTML;
                contentArea.classList.remove('fade-out');
                contentArea.classList.add('fade-in');
                loading.classList.add('hidden');
                currentFilter = filterKey;

                const newUrl = new URL(window.location);
                newUrl.searchParams.set('filter', filterKey);
                window.history.pushState({ filter: filterKey }, '', newUrl);
            }, 300);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        loading.classList.add('hidden');
        window.location.href = `{{ url()->current() }}?filter=${filterKey}`;
    });
}

// ✅ 2. HANDLE TOMBOL BACK/FORWARD BROWSER
window.addEventListener('popstate', function(event) {
    const urlParams = new URLSearchParams(window.location.search);
    const filter = urlParams.get('filter') || 'semua';
    if (filter !== currentFilter) {
        const btn = document.querySelector(`button[data-filter="${filter}"]`);
        if (btn) {
            let color = 'red';
            if(filter === 'menunggu') color = 'amber';
            if(filter === 'keluar') color = 'sky';
            if(filter === 'terlambat') color = 'red';
            if(filter === 'selesai') color = 'emerald';
            if(filter === 'dihubungi') color = 'purple';
            switchFilter(filter, color, null);
        }
    }
});

// ✅ 3. FUNGSI LENGKAP: Klik WA langsung tandai dihubungi & buka WA
function handleWaContacted(dispensasiId, waLink) {
    fetch(`/satpam/dispensasi/${dispensasiId}/wa-contacted`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        keepalive: true
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const card = document.querySelector(`[data-dispensasi="${dispensasiId}"]`);
            if (card) {
                card.dataset.warned = 'true';
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
                const badgeRow = card.querySelector('.flex.items-center.gap-2.mb-1');
                if (badgeRow && !badgeRow.querySelector('.warned-badge')) {
                    badgeRow.insertAdjacentHTML('beforeend',
                        `<span class="warned-badge px-2 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-700 uppercase ml-1">
                            <i class="fas fa-phone-alt mr-1"></i>DIHUBUNGI
                        </span>`
                    );
                }
            }
            window.open(waLink, '_blank');
        } else {
            window.open(waLink, '_blank');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.open(waLink, '_blank');
    });
}

// ✅ 4. COUNTDOWN REALTIME
function tickCountdowns() {
    document.querySelectorAll('.live-countdown[data-deadline]').forEach(el => {
        const deadline = new Date(el.dataset.deadline);
        const diffMs = deadline - new Date();

        if (diffMs <= 0) {
            const lateMin = Math.floor(-diffMs / 60000);
            const hrs = Math.floor(lateMin / 60);
            const mins = lateMin % 60;
            const lateText = hrs > 0 ? `${hrs}j ${mins}m` : `${lateMin}m`;

            el.textContent = `TERLAMBAT ${lateText}`;
            el.classList.remove('bg-amber-100', 'text-amber-700');
            el.classList.add('bg-red-100', 'text-red-700', 'animate-pulse');
        } else {
            const totalMin = Math.floor(diffMs / 60000);
            const hrs = Math.floor(totalMin / 60);
            const mins = totalMin % 60;
            const secs = Math.floor((diffMs % 60000) / 1000);
            el.textContent = hrs > 0 ? `Sisa ${hrs}j ${mins}m` : `Sisa ${mins}m ${secs}s`;
            if (totalMin <= 5) {
                el.classList.remove('bg-amber-100', 'text-amber-700');
                el.classList.add('bg-red-100', 'text-red-700');
            }
        }
    });
}

// ✅ 5. WATCHER: Deteksi kartu yang baru melewati batas waktu
const overdueNotified = new Set();
function watchOverdue() {
    document.querySelectorAll('[data-status="keluar"][data-deadline][data-overdue="false"]').forEach(card => {
        const deadline = new Date(card.dataset.deadline);
        if (new Date() > deadline) {
            card.dataset.overdue = 'true';
            const nama = card.querySelector('.font-bold.text-gray-900')?.textContent.trim() || 'Siswa';
            if (!overdueNotified.has(card.dataset.dispensasi)) {
                overdueNotified.add(card.dataset.dispensasi);
                Swal.fire({
                    icon: 'warning',
                    title: '⚠️ Siswa Terlambat!',
                    text: `${nama} telah melewati batas waktu kembali.`,
                    confirmButtonColor: '#dc2626',
                    timer: 5000,
                    timerProgressBar: true
                });
            }
        }
    });
}

// ✅ 6. INITIALIZE SAAT HALAMAN DIMUAT
document.addEventListener('DOMContentLoaded', function() {
    tickCountdowns();
    setInterval(tickCountdowns, 1000);
    setInterval(watchOverdue, 15000);
});
</script>
@endpush
