@extends('guru.layouts.app')
@section('title', 'Dashboard Guru Piket')
@section('page-title', 'Dashboard')
@section('content')
@include('components.alert')

<style>
.stat-card-btn { transition: all 0.2s ease; }
.stat-card-btn:hover { transform: translateY(-1px); }
.stat-card-btn.active { transform: scale(1.01); }
#content-area { transition: opacity 0.2s ease; }
.fade-out { opacity: 0; }
.fade-in { opacity: 1; }
</style>

{{-- HERO SECTION - Clean, solid color --}}
<div class="bg-blue-600 rounded-xl p-4 sm:p-6 mb-4 text-white">
    <div class="flex items-center justify-between">
        <div class="min-w-0">
            <p class="text-blue-100 text-[10px] sm:text-xs font-semibold uppercase tracking-wider">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            <h2 class="text-lg sm:text-xl font-bold text-white mt-0.5">Halo, {{ auth()->user()->name }}!</h2>
            <p class="text-blue-100 text-xs sm:text-sm mt-1">Pantau dan kelola dispensasi siswa hari ini.</p>
        </div>
        <a href="{{ route('guru.pengajuan.create') }}" class="hidden sm:inline-flex items-center px-4 py-2 rounded-lg bg-white text-blue-700 text-xs font-semibold hover:bg-blue-50 transition-colors flex-shrink-0">
            <i class="fas fa-plus mr-1.5"></i>Buat Dispensasi
        </a>
    </div>
</div>

{{-- KOLOM PENCARIAN SISWA --}}
<div class="bg-white border border-gray-200 rounded-xl p-4 mb-4">
    <form method="GET" action="{{ route('guru.dashboard') }}" class="flex gap-2">
        <input type="hidden" name="filter" value="{{ $filter ?? 'semua' }}">
        <div class="flex-1 relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <i class="fas fa-search text-sm"></i>
            </span>
            <input type="text" name="search" value="{{ $search ?? '' }}"
                   placeholder="Cari nama siswa, NIS, atau nomor surat..."
                   class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all"
                   autofocus>
            @if($search)
                <a href="{{ route('guru.dashboard', ['filter' => $filter]) }}"
                   class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-500 transition-colors" title="Hapus pencarian">
                    <i class="fas fa-times-circle text-lg"></i>
                </a>
            @endif
        </div>
        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
            <i class="fas fa-search mr-1.5 hidden sm:inline"></i>Cari
        </button>
    </form>
    @if($search)
        <div class="mt-2 text-xs text-gray-500 flex items-center">
            <i class="fas fa-info-circle mr-1"></i>
            Menampilkan hasil pencarian untuk: <strong class="text-gray-800 mx-1">"{{ $search }}"</strong>
            <span class="mx-1">•</span>
            <span>{{ $displayData->count() }} data ditemukan</span>
        </div>
    @endif
</div>

{{-- STATISTIK SEBAGAI FILTER UTAMA --}}
@php
$cards = [
    'menunggu' => ['Menunggu', $stats['menunggu'] ?? 0, 'fa-clock', 'amber'],
    'disetujui' => ['Disetujui', $stats['disetujui'] ?? 0, 'fa-check-circle', 'emerald'],
    'keluar'    => ['Sedang Keluar', $stats['keluar'] ?? 0, 'fa-walking', 'sky'],
    'selesai'   => ['Selesai', $stats['selesai'] ?? 0, 'fa-check-double', 'gray'],
];
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 mb-3">
    @foreach($cards as $key => $card)
        @php
            $isActive = $filter === $key;
            $color = $card[3];
        @endphp
        <button type="button"
                onclick="switchFilter('{{ $key }}', '{{ $color }}', event)"
                data-filter="{{ $key }}"
                class="stat-card-btn text-left rounded-xl border p-3 sm:p-4 transition-all w-full
                {{ $isActive
                    ? 'active bg-white border-' . $color . '-500 ring-2 ring-' . $color . '-500/20 shadow-sm'
                    : 'bg-white border-gray-200 hover:border-gray-300' }}">
            <div class="flex items-center justify-between mb-2">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors
                    {{ $isActive ? 'bg-' . $color . '-500 text-white' : 'bg-' . $color . '-100 text-' . $color . '-600' }}">
                    <i class="fas {{ $card[2] }} text-sm"></i>
                </div>
                <span class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $card[1] }}</span>
            </div>
            <p class="text-[11px] sm:text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ $card[0] }}</p>
        </button>
    @endforeach
</div>

{{-- SECONDARY FILTER BUTTONS --}}
<div class="grid grid-cols-2 gap-2 mb-4">
    <button type="button"
            onclick="switchFilter('semua', 'blue', event)"
            data-filter="semua"
            class="filter-btn px-3 py-2 rounded-lg text-xs font-semibold border transition-all text-center
            {{ $filter === 'semua' ? 'active bg-blue-600 text-white shadow-sm border-transparent' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-200' }}">
        <i class="fas fa-layer-group mr-1.5"></i>Tampilkan Semua ({{ $stats['total'] ?? 0 }})
    </button>
    <button type="button"
            onclick="switchFilter('terlambat', 'red', event)"
            data-filter="terlambat"
            class="filter-btn px-3 py-2 rounded-lg text-xs font-semibold border transition-all text-center
            {{ $filter === 'terlambat' ? 'active bg-red-600 text-white shadow-sm border-transparent' : 'bg-white text-red-600 hover:bg-red-50 border-gray-200' }}">
        <i class="fas fa-exclamation-triangle mr-1.5"></i>Terlambat ({{ count($terlambat ?? []) }})
    </button>
</div>

{{-- DAFTAR DISPENSASI --}}
<div id="content-area" class="fade-in bg-white border border-gray-200 rounded-xl overflow-hidden mb-16 sm:mb-0">
    <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gray-50/50">
        <h3 class="text-sm font-bold text-gray-900">
            @php
            $titleMap = [
                'semua' => 'Semua Dispensasi Hari Ini',
                'menunggu' => 'Pengajuan Menunggu Persetujuan',
                'disetujui' => 'Disetujui (Menunggu Scan Satpam)',
                'keluar' => 'Siswa Sedang Keluar',
                'selesai' => 'Siswa Sudah Kembali',
                'terlambat' => 'Siswa Terlambat Kembali',
            ];
            @endphp
            {{ $titleMap[$filter] ?? 'Daftar Dispensasi' }}
        </h3>
        <span class="text-xs font-semibold text-gray-600 bg-gray-200 px-2.5 py-1 rounded-lg">{{ count($displayData) }} data</span>
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($displayData as $item)
            @php
                $highlightName = $search ?
                    preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark class="bg-yellow-200 text-gray-900 rounded px-0.5">$1</mark>', $item->siswa->nama_lengkap) :
                    $item->siswa->nama_lengkap;
                $isLate = $item->status === 'keluar' && $item->batas_waktu_kembali && now()->greaterThan($item->batas_waktu_kembali);
                $lateMinutes = $isLate ? \App\Helpers\DispensasiTimeHelper::hitungMenitTerlambat($item->batas_waktu_kembali) : 0;
                $lateText = $isLate ? \App\Helpers\DispensasiTimeHelper::formatDurasiTerlambat($lateMinutes, short: true) : '';
                $waLink = '';
                if ($isLate && !empty($item->siswa->no_telepon)) {
                    $hp = preg_replace('/[^0-9]/', '', $item->siswa->no_telepon);
                    $hp = str_starts_with($hp, '0') ? '62' . substr($hp, 1) : $hp;
                    $waLink = "https://wa.me/{$hp}?text=" . urlencode("*PERINGATAN DISPENSASI*\n\nYth. {$item->siswa->nama_lengkap},\nAnda telah melewati batas waktu kembali dispensasi (Terlambat {$lateText}).\n\nSegera kembali ke sekolah atau lapor ke Guru Piket.\n\nTerima kasih.");
                }
            @endphp
            <div class="p-4 transition-colors {{ $isLate ? 'bg-red-50/60 hover:bg-red-100/60 border-l-4 border-l-red-500' : 'hover:bg-gray-50/80' }}">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <span class="font-mono text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded">{{ $item->nomor_surat }}</span>
                            @php
                                $badgeClass = match($item->status) {
                                    'menunggu' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'disetujui' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'keluar' => 'bg-sky-100 text-sky-800 border-sky-200',
                                    'selesai' => 'bg-gray-200 text-gray-800 border-gray-300',
                                    default => 'bg-gray-100 text-gray-800 border-gray-200'
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded border text-[10px] font-semibold uppercase tracking-wide {{ $badgeClass }}">
                                {{ $item->status }}
                            </span>
                            @if($isLate)
                                <span class="px-2 py-0.5 rounded border text-[10px] font-semibold uppercase tracking-wide bg-red-100 text-red-800 border-red-300">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Terlambat {{ $lateText }}
                                </span>
                            @endif
                        </div>
                        <p class="font-semibold text-gray-900 text-sm truncate mt-1">
                            {!! $highlightName !!}
                        </p>
                        <p class="text-xs font-medium text-gray-600 mb-1">
                            {{ $item->siswa->kelas?->nama_kelas ?? '-' }} • {{ $item->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}
                        </p>
                        <p class="text-xs text-gray-700 line-clamp-2">
                            <span class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $item->kategori)) }}:</span>
                            {{ Str::limit($item->alasan, 70) }}
                        </p>
                        @if($isLate)
                            <p class="text-xs text-red-600 font-semibold mt-1.5 flex items-center">
                                <i class="far fa-clock mr-1.5"></i>
                                Batas kembali: {{ \Carbon\Carbon::parse($item->batas_waktu_kembali)->format('H:i') }} WIB
                            </p>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2 flex-shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-200 sm:border-gray-100">
                        @if($isLate && $waLink)
                            <button onclick="handleGuruWaContacted({{ $item->id }}, '{{ $waLink }}', this)"
                                    class="inline-flex items-center justify-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg transition-colors {{ $item->is_warned ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ $item->is_warned ? 'disabled' : '' }}>
                                <i class="fab fa-whatsapp mr-1.5"></i>
                                <span class="wa-text">{{ $item->is_warned ? 'Sudah Dihubungi' : 'Hubungi' }}</span>
                            </button>
                        @endif
                        <a href="{{ route('guru.pengajuan.show', $item) }}"
                           class="inline-flex items-center justify-center px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors border border-blue-200"
                           title="Lihat Detail">
                            <i class="fas fa-eye mr-1.5"></i>Detail
                        </a>
                        @if($item->status === 'menunggu')
                            <form method="POST" action="{{ route('guru.pengajuan.approve', $item) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Setujui dispensasi {{ $item->siswa->nama_lengkap }}?')"
                                        class="inline-flex items-center justify-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors"
                                        title="Setujui">
                                    <i class="fas fa-check mr-1.5"></i>Setuju
                                </button>
                            </form>
                            <button type="button"
                                    onclick="rejectDispensasi({{ $item->id }}, '{{ $item->siswa->nama_lengkap }}')"
                                    class="inline-flex items-center justify-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors"
                                    title="Tolak">
                                <i class="fas fa-times mr-1.5"></i>Tolak
                            </button>
                        @else
                            @if(in_array($item->status, ['disetujui']))
                                <a href="{{ route('guru.cetak-pdf', [$item, 'format' => 'thermal']) }}"
                                   target="_blank"
                                   class="inline-flex items-center justify-center px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-lg transition-colors border border-emerald-300"
                                   title="Cetak Struk">
                                    <i class="fas fa-print mr-1.5"></i>Struk
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="p-10 text-center">
                <div class="w-16 h-16 mx-auto rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center text-2xl mb-3">
                    <i class="fas fa-search"></i>
                </div>
                <p class="text-gray-800 font-semibold text-sm">
                    @if($search)
                        Tidak ada hasil pencarian untuk "{{ $search }}"
                    @else
                        Tidak ada data dispensasi untuk filter ini
                    @endif
                </p>
                <p class="text-gray-600 text-xs mt-1">
                    @if($search)
                        Coba ubah kata kunci pencarian atau hapus filter
                    @else
                        Data akan muncul ketika siswa mengajukan dispensasi
                    @endif
                </p>
            </div>
        @endforelse
    </div>
</div>

{{-- FLOATING ACTION BUTTON (MOBILE) --}}
<a href="{{ route('guru.pengajuan.create') }}"
   class="sm:hidden fixed bottom-20 right-4 bg-blue-600 text-white p-4 rounded-full shadow-lg flex items-center justify-center z-40 active:scale-95 transition-transform">
    <i class="fas fa-plus text-lg"></i>
</a>

{{-- Loading Overlay --}}
<div id="loading-overlay" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-4 shadow-lg flex items-center space-x-3 border border-gray-200">
        <div class="animate-spin rounded-full h-5 w-5 border-2 border-blue-600 border-t-transparent"></div>
        <span class="text-xs font-semibold text-gray-800">Memuat data...</span>
    </div>
</div>

@push('scripts')
<script>
let currentFilter = '{{ $filter }}';

function switchFilter(filterKey, color, event) {
    if (event) event.preventDefault();
    if (filterKey === currentFilter) return;

    document.querySelectorAll('.stat-card-btn').forEach(card => {
        card.classList.remove('active', 'ring-2', 'shadow-sm');
        card.classList.add('border-gray-200');
        card.className = card.className.replace(/border-\w+-500/g, '');
        card.className = card.className.replace(/ring-\w+-500\/20/g, '');
        const iconContainer = card.querySelector('div > div');
        if (iconContainer) iconContainer.className = iconContainer.className.replace(/bg-\w+-500 text-white/g, '');
    });

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-blue-600', 'bg-red-600', 'text-white', 'shadow-sm', 'border-transparent');
        btn.classList.add('bg-white', 'border-gray-200');
    });

    const activeStatCard = document.querySelector(`button.stat-card-btn[data-filter="${filterKey}"]`);
    if (activeStatCard) {
        activeStatCard.classList.add('active', `border-${color}-500`, `ring-2`, `ring-${color}-500/20`, 'shadow-sm');
        const iconContainer = activeStatCard.querySelector('div > div');
        if (iconContainer) iconContainer.classList.add(`bg-${color}-500`, 'text-white');
    }

    const activeBtn = document.querySelector(`button.filter-btn[data-filter="${filterKey}"]`);
    if (activeBtn) activeBtn.classList.add('active', `bg-${color}-600`, 'text-white', 'shadow-sm', 'border-transparent');

    const contentArea = document.getElementById('content-area');
    const loading = document.getElementById('loading-overlay');
    contentArea.classList.remove('fade-in');
    contentArea.classList.add('fade-out');
    loading.classList.remove('hidden');

    fetch(`{{ url()->current() }}?filter=${filterKey}`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const newContent = parser.parseFromString(html, 'text/html').getElementById('content-area');
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
        let color = 'blue';
        if(filter === 'menunggu') color = 'amber';
        if(filter === 'disetujui') color = 'emerald';
        if(filter === 'keluar') color = 'sky';
        if(filter === 'selesai') color = 'gray';
        if(filter === 'terlambat') color = 'red';
        switchFilter(filter, color, null);
    }
});

function rejectDispensasi(id, namaSiswa) {
    Swal.fire({
        title: 'Tolak Dispensasi',
        text: `Masukkan alasan penolakan untuk ${namaSiswa}:`,
        input: 'textarea',
        inputPlaceholder: 'Contoh: Alasan tidak jelas...',
        inputAttributes: { rows: 3 },
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Tolak',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value || value.trim() === '') return 'Alasan penolakan wajib diisi!';
        }
    }).then(result => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/guru/pengajuan/${id}/reject`;
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').content;
            const reason = document.createElement('input');
            reason.type = 'hidden';
            reason.name = 'catatan_admin';
            reason.value = result.value;
            form.append(csrf, reason);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function handleGuruWaContacted(dispensasiId, waLink, button) {
    if (button.disabled) {
        window.open(waLink, '_blank');
        return;
    }

    fetch(`/guru/dispensasi/${dispensasiId}/wa-contacted`, {
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
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');
            const waText = button.querySelector('.wa-text');
            if (waText) waText.textContent = 'Sudah Dihubungi';

            const card = button.closest('.p-4');
            if (card) {
                const badgeRow = card.querySelector('.flex.items-center.gap-2.mb-1');
                if (badgeRow && !badgeRow.querySelector('.warned-badge')) {
                    badgeRow.insertAdjacentHTML('beforeend',
                        `<span class="warned-badge px-2 py-0.5 rounded border text-[10px] font-semibold uppercase tracking-wide bg-purple-100 text-purple-800 border-purple-200 ml-2">
                            <i class="fas fa-phone-alt mr-1"></i>Dihubungi
                        </span>`
                    );
                }
            }
            window.open(waLink, '_blank');
        } else {
            alert('Gagal menandai status: ' + data.message);
            window.open(waLink, '_blank');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.open(waLink, '_blank');
    });
}
</script>
@endpush
@endsection
