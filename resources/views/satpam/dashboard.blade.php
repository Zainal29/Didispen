@extends('satpam.layouts.app')

@section('title', 'Dashboard Satpam')
@section('page-title', 'Dashboard Satpam')

@section('content')
@include('components.alert')

<style>
    .filter-btn { transition: all 0.2s ease-in-out; }
    .filter-btn:hover { transform: translateY(-1px); }
    .filter-btn.active { transform: scale(1.02); }
    #content-area { transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out; }
    .fade-out { opacity: 0; transform: translateY(8px); }
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
<div class="bg-red-600 rounded-xl p-4 sm:p-6 mb-4 text-white">
    <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="text-red-100 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider">
                {{ now()->isoFormat('dddd, D MMMM Y') }} • Pos Gerbang
            </p>
            <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight mt-0.5 truncate">
                Halo, {{ auth()->user()->name }} <i class="fas fa-hand-sparkles text-yellow-300 ml-1"></i>
            </h2>
            <p class="text-red-100 text-[11px] mt-1 hidden sm:block">Pantau keluar-masuk siswa dispensasi hari ini dengan mudah.</p>
        </div>
        <a href="{{ route('satpam.scan') }}" class="hidden sm:inline-flex items-center px-4 py-2.5 rounded-lg bg-white text-red-700 text-sm font-semibold hover:bg-red-50 transition-colors flex-shrink-0">
            <i class="fas fa-qrcode mr-2"></i> Scan QR
        </a>
    </div>
</div>

{{-- ============ FILTER TABS ============ --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-4">
    <div class="p-2 flex flex-wrap gap-2">
        @php
        $filters = [
            'semua' => ['label' => 'Semua', 'icon' => 'fa-layer-group', 'count' => $stats['total'] ?? 0, 'color' => 'gray'],
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
                class="filter-btn flex-1 min-w-[100px] px-3 py-2 rounded-lg text-xs font-semibold text-center border transition-all
                {{ $currentFilter === $key
                    ? 'active bg-' . $f['color'] . '-600 text-white border-transparent'
                    : 'bg-gray-50 text-gray-600 hover:bg-black-100 border-gray-200' }}">
            <i class="fas {{ $f['icon'] }} mr-1"></i> {{ $f['label'] }}
            @if($f['count'] > 0)
                <span class="inline-block ml-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold {{ $currentFilter === $key ? 'bg-white/20' : 'bg-' . $f['color'] . '-100 text-' . $f['color'] . '-700' }}">
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
        @if($menungguKeluar->count() > 0)
            @foreach($menungguKeluar as $dispensasi)
                @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'menunggu', 'isOverdue' => false])
            @endforeach
        @else
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <div class="w-14 h-14 mx-auto rounded-lg bg-amber-50 text-amber-400 flex items-center justify-center text-2xl mb-3"><i class="fas fa-clock"></i></div>
                <p class="text-gray-700 text-sm font-semibold">Belum ada siswa yang menunggu konfirmasi keluar hari ini</p>
                <p class="text-gray-500 text-xs mt-1">Siswa yang sudah disetujui guru akan muncul di sini</p>
            </div>
        @endif
    </div>

    {{-- SECTION: KELUAR --}}
    <div id="section-keluar" class="space-y-3 {{ $currentFilter !== 'keluar' ? 'hidden' : '' }}">
        <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-person-walking text-sky-500 mr-1.5"></i>Sedang Keluar</h3>
        @if($siswaKeluar->count() > 0)
            @foreach($siswaKeluar as $dispensasi)
                @php $isOverdue = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali); @endphp
                @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'keluar', 'isOverdue' => $isOverdue])
            @endforeach
        @else
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <div class="w-14 h-14 mx-auto rounded-lg bg-sky-50 text-sky-400 flex items-center justify-center text-2xl mb-3"><i class="fas fa-person-walking"></i></div>
                <p class="text-gray-700 text-sm font-semibold">Belum ada siswa yang sedang keluar hari ini</p>
                <p class="text-gray-500 text-xs mt-1">Siswa yang sudah di-scan keluar oleh satpam akan muncul di sini</p>
            </div>
        @endif
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
                $lateMinutes = \App\Helpers\DispensasiTimeHelper::hitungMenitTerlambat($dispensasi->batas_waktu_kembali);
                $lateText = \App\Helpers\DispensasiTimeHelper::formatDurasiTerlambat($lateMinutes, short: true);
            @endphp
            <div class="bg-white rounded-xl border border-red-200 shadow-sm overflow-hidden" data-dispensasi="{{ $dispensasi->id }}" data-status="terlambat" data-overdue="true" data-deadline="{{ $dispensasi->batas_waktu_kembali->format('Y-m-d H:i:s') }}">
                <div class="px-4 py-3.5 bg-red-50 border-b border-red-100">
                    <div class="flex justify-between items-start gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <p class="font-mono font-semibold text-gray-700 text-xs">{{ $dispensasi->nomor_surat }}</p>
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-red-100 text-red-700 uppercase">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>TERLAMBAT {{ $lateText }}
                                </span>
                                @if($dispensasi->is_warned)
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-purple-100 text-purple-700 uppercase">
                                        <i class="fas fa-phone-alt mr-1"></i>DIHUBUNGI
                                    </span>
                                @endif
                            </div>
                            <p class="font-bold text-gray-900 text-sm truncate">{{ $dispensasi->siswa->nama_lengkap }}</p>
                            <p class="text-[10px] text-gray-500 truncate">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }} • {{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                        </div>
                        <a href="{{ route('satpam.dispensasi.detail', $dispensasi) }}" class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex-shrink-0">
                            <i class="fas fa-eye text-xs"></i>
                        </a>
                    </div>
                </div>
                <div class="p-4 space-y-3 bg-gray-50/50">
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="bg-white p-2.5 rounded-lg border border-gray-200">
                            <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Jam Keluar</p>
                            <p class="font-semibold text-gray-800">{{ $dispensasi->jam_keluar }}</p>
                        </div>
                        <div class="bg-white p-2.5 rounded-lg border border-gray-200">
                            <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Jam Kembali</p>
                            <p class="font-semibold text-red-700">{{ $dispensasi->jam_kembali }}</p>
                        </div>
                    </div>
                    @if(!empty($dispensasi->siswa->no_telepon))
                        @php
                            $hp = preg_replace('/[^0-9]/', '', $dispensasi->siswa->no_telepon);
                            if (str_starts_with($hp, '0')) $hp = '62' . substr($hp, 1);
                            $waLink = "https://wa.me/{$hp}?text=" . urlencode("*PERINGATAN KETERLAMBATAN*\n\nYth. *{$dispensasi->siswa->nama_lengkap}*,\nBatas waktu kembali dispensasi Anda telah LEWAT.\n\nLokasi Tujuan: {$dispensasi->tujuan}\nSEGERA KEMBALI ke sekolah.\n\nPetugas Satpam SMKN 1 Bangsri");
                        @endphp
                        <div id="wa-section-{{ $dispensasi->id }}" class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <p class="text-[10px] font-bold text-green-700 uppercase">Kontak Darurat</p>
                                    <p class="text-sm font-semibold text-gray-800 font-mono">{{ $dispensasi->siswa->no_telepon }}</p>
                                </div>
                                <button onclick="handleWaContacted({{ $dispensasi->id }}, '{{ $waLink }}')"
                                        class="inline-flex items-center justify-center w-10 h-10 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors {{ $dispensasi->is_warned ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ $dispensasi->is_warned ? 'disabled' : '' }}>
                                    <i class="fab fa-whatsapp text-lg"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                    <a href="{{ route('satpam.scan') }}"
                       class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                        <i class="fas fa-camera mr-1.5"></i>Scan QR Code untuk Kembali
                    </a>
                </div>
            </div>
        @endforeach

        @if($renderedTerlambat === 0)
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <div class="w-14 h-14 mx-auto rounded-lg bg-emerald-50 text-emerald-400 flex items-center justify-center text-2xl mb-3"><i class="fas fa-check-circle"></i></div>
                <p class="text-gray-700 text-sm font-semibold">Tidak ada siswa yang terlambat hari ini</p>
                <p class="text-gray-500 text-xs mt-1">Semua siswa yang keluar telah kembali tepat waktu</p>
            </div>
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
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <div class="w-14 h-14 mx-auto rounded-lg bg-emerald-50 text-emerald-400 flex items-center justify-center text-2xl mb-3"><i class="fas fa-check-circle"></i></div>
                <p class="text-gray-700 text-sm font-semibold">Belum ada siswa yang kembali hari ini</p>
                <p class="text-gray-500 text-xs mt-1">Siswa yang sudah di-scan kembali akan muncul di sini</p>
            </div>
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
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <div class="w-14 h-14 mx-auto rounded-lg bg-purple-50 text-purple-400 flex items-center justify-center text-2xl mb-3"><i class="fas fa-phone-slash"></i></div>
                <p class="text-gray-700 text-sm font-semibold">Belum ada siswa yang dihubungi hari ini</p>
                <p class="text-gray-500 text-xs mt-1">Gunakan tombol "Hubungi" pada kartu siswa yang terlambat</p>
            </div>
        @endif
    </div>

    {{-- SECTION: SEMUA --}}
    <div id="section-semua" class="space-y-3 {{ $currentFilter !== 'semua' ? 'hidden' : '' }}">
        <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-layer-group text-gray-500 mr-1.5"></i>Semua Dispensasi Hari Ini</h3>
        @if($menungguKeluar->count() > 0 || $siswaKeluar->count() > 0 || (isset($selesai) && $selesai->count() > 0))
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
        @else
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <div class="w-14 h-14 mx-auto rounded-lg bg-gray-50 text-gray-400 flex items-center justify-center text-2xl mb-3"><i class="fas fa-layer-group"></i></div>
                <p class="text-gray-700 text-sm font-semibold">Belum ada data dispensasi hari ini</p>
                <p class="text-gray-500 text-xs mt-1">Semua aktivitas dispensasi akan muncul di sini</p>
            </div>
        @endif
    </div>

</div>

{{-- Loading Overlay --}}
<div id="loading-overlay" class="hidden fixed inset-0 bg-black/10 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-4 shadow-lg flex items-center space-x-3 border border-gray-200">
        <div class="animate-spin rounded-full h-5 w-5 border-2 border-red-600 border-t-transparent"></div>
        <span class="text-xs font-semibold text-gray-700">Memuat data...</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentFilter = '{{ $currentFilter }}';

function switchFilter(filterKey, color, event) {
    if (event) event.preventDefault();
    if (filterKey === currentFilter) return;

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-red-600', 'bg-amber-600', 'bg-sky-600', 'bg-emerald-600', 'bg-purple-600', 'bg-gray-600', 'text-white', 'border-transparent');
        btn.classList.add('bg-gray-50', 'text-gray-600', 'border-gray-200');
    });

    const activeBtn = document.querySelector(`button[data-filter="${filterKey}"]`);
    if (activeBtn) {
        activeBtn.classList.remove('bg-gray-50', 'text-gray-600', 'border-gray-200');
        activeBtn.classList.add('active', `bg-${color}-600`, 'text-white', 'border-transparent');
    }

    const contentArea = document.getElementById('content-area');
    const loading = document.getElementById('loading-overlay');

    contentArea.classList.remove('fade-in');
    contentArea.classList.add('fade-out');
    loading.classList.remove('hidden');

    fetch(`{{ url()->current() }}?filter=${filterKey}`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
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
            }, 200);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        loading.classList.add('hidden');
        window.location.href = `{{ url()->current() }}?filter=${filterKey}`;
    });
}

window.addEventListener('popstate', function(event) {
    const urlParams = new URLSearchParams(window.location.search);
    const filter = urlParams.get('filter') || 'semua';
    if (filter !== currentFilter) {
        const btn = document.querySelector(`button[data-filter="${filter}"]`);
        if (btn) {
            let color = 'gray';
            if(filter === 'menunggu') color = 'amber';
            if(filter === 'keluar') color = 'sky';
            if(filter === 'terlambat') color = 'red';
            if(filter === 'selesai') color = 'emerald';
            if(filter === 'dihubungi') color = 'purple';
            switchFilter(filter, color, null);
        }
    }
});

function handleWaContacted(dispensasiId, waLink) {
    fetch(`/satpam/dispensasi/${dispensasiId}/wa-contacted`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
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
                                <p class="text-sm font-semibold text-gray-800 font-mono">${waSection.querySelector('.font-mono').textContent}</p>
                            </div>
                            <div class="inline-flex items-center justify-center w-10 h-10 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed" title="Sudah dihubungi">
                                <i class="fas fa-check text-base"></i>
                            </div>
                        </div>
                        <p class="text-[10px] text-green-600"><i class="fas fa-check-circle mr-1"></i>Sudah dihubungi via WhatsApp</p>
                    `;
                }
                const badgeRow = card.querySelector('.flex.items-center.gap-2.mb-1');
                if (badgeRow && !badgeRow.querySelector('.warned-badge')) {
                    badgeRow.insertAdjacentHTML('beforeend', `<span class="warned-badge px-2 py-0.5 rounded-md text-[9px] font-bold bg-purple-100 text-purple-700 uppercase ml-1"><i class="fas fa-phone-alt mr-1"></i>DIHUBUNGI</span>`);
                }
            }
            window.open(waLink, '_blank');
        } else {
            window.open(waLink, '_blank');
        }
    })
    .catch(error => { console.error('Error:', error); window.open(waLink, '_blank'); });
}

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
            el.classList.add('bg-red-100', 'text-red-700');
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
                    html: '<i class="fas fa-exclamation-triangle text-amber-500 text-4xl mb-3"></i><h3 style="color: #1f2937; font-size: 1.125rem; font-weight: 700; margin-bottom: 0.5rem;">Siswa Terlambat!</h3>',
                    text: `${nama} telah melewati batas waktu kembali.`,
                    confirmButtonColor: '#dc2626',
                    timer: 5000,
                    timerProgressBar: true
                });
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    tickCountdowns();
    setInterval(tickCountdowns, 1000);
    setInterval(watchOverdue, 15000);
});
</script>
@endpush
