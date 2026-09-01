@extends('guru.layouts.app')

@section('title', 'Dashboard Guru Piket')
@section('page-title', 'Dashboard')

@section('content')
@include('components.alert')
{{-- ✅ TAMBAHKAN CSS INI UNTUK EFEK SMOOTH --}}
<style>
    .filter-btn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .filter-btn:hover {
        transform: translateY(-2px);
    }
    .filter-btn.active {
        transform: scale(1.05);
    }
    #content-area {
        transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
    }
    .fade-out {
        opacity: 0;
        transform: translateY(10px);
    }
    .fade-in {
        opacity: 1;
        transform: translateY(0);
    }
</style>


{{-- HERO SECTION --}}
<div class="bg-gradient-to-br from-blue-600 to-sky-500 rounded-2xl shadow-lg shadow-blue-500/30 p-4 sm:p-6 mb-4 text-white">
    <div class="flex items-center justify-between">
        <div class="min-w-0">
            <h2 class="text-lg sm:text-2xl font-black tracking-tight mt-0.5 truncate">
                Halo, {{ auth()->user()->name }} 👋
            </h2>
            <p class="text-blue-100 text-[11px] mt-1 hidden sm:block">
                Pantau dispensasi siswa hari ini dengan mudah.
            </p>
        </div>
        <a href="{{ route('guru.pengajuan.create') }}" class="hidden sm:inline-flex items-center px-5 py-3 rounded-xl bg-white text-blue-700 text-sm font-bold shadow-xl hover:-translate-y-0.5 transition-all flex-shrink-0">
            <i class="fas fa-plus mr-2"></i> Buat Dispensasi
        </a>
    </div>
</div>

{{-- STATISTIK (4 kartu) --}}
<div class="grid grid-cols-2 gap-2 sm:gap-4 mb-4">
    @php
    $cards = [
        ['Menunggu', $stats['menunggu'] ?? 0, 'fa-clock', 'bg-amber-100 text-amber-600', 'border-amber-300'],
        ['Disetujui', $stats['disetujui'] ?? 0, 'fa-check-circle', 'bg-emerald-100 text-emerald-600', 'border-emerald-300'],
        ['Sedang Keluar', $stats['keluar'] ?? 0, 'fa-walking', 'bg-sky-100 text-sky-600', 'border-sky-300'],
        ['Selesai', $stats['selesai'] ?? 0, 'fa-check-double', 'bg-gray-100 text-gray-600', 'border-gray-300'],
    ];
    @endphp
    @foreach($cards as $card)
    <div class="bg-white rounded-2xl border {{ $card[4] }} shadow-sm p-3 sm:p-4">
        <div class="flex items-center justify-between mb-2">
            <div class="w-9 h-9 rounded-xl {{ $card[3] }} flex items-center justify-center">
                <i class="fas {{ $card[2] }} text-sm"></i>
            </div>
            <span class="text-2xl sm:text-3xl font-black text-gray-900">{{ $card[1] }}</span>
        </div>
        <p class="text-[10px] sm:text-xs font-bold text-gray-600 uppercase tracking-wider">{{ $card[0] }}</p>
    </div>
    @endforeach
</div>

{{-- ========================================== --}}
{{-- FILTER TABS (DIPERBAIKI: MENGGUNAKAN BUTTON + JS) --}}
{{-- ========================================== --}}
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-4">
    <div class="p-2 flex flex-wrap gap-2">
        @php
        $filters = [
            'semua' => ['label' => 'Semua', 'icon' => 'fa-layer-group', 'count' => $stats['total'] ?? 0, 'color' => 'blue'],
            'menunggu' => ['label' => 'Menunggu', 'icon' => 'fa-clock', 'count' => $stats['menunggu'] ?? 0, 'color' => 'amber'],
            'disetujui' => ['label' => 'Disetujui', 'icon' => 'fa-check-circle', 'count' => $stats['disetujui'] ?? 0, 'color' => 'emerald'],
            'keluar' => ['label' => 'Keluar', 'icon' => 'fa-walking', 'count' => $stats['keluar'] ?? 0, 'color' => 'sky'],
            'selesai' => ['label' => 'Selesai', 'icon' => 'fa-check-double', 'count' => $stats['selesai'] ?? 0, 'color' => 'gray'],
            'terlambat' => ['label' => 'Terlambat', 'icon' => 'fa-exclamation-triangle', 'count' => count($terlambat ?? []), 'color' => 'red'],
        ];
        @endphp

        @foreach($filters as $key => $f)
        <button type="button"
                onclick="switchFilter('{{ $key }}', '{{ $f['color'] }}', event)"
                data-filter="{{ $key }}"
                class="filter-btn flex-1 min-w-[100px] px-3 py-2.5 rounded-xl text-xs font-bold text-center border transition-all
                {{ $filter === $key
                    ? 'active bg-' . $f['color'] . '-600 text-white shadow-lg shadow-' . $f['color'] . '-500/30 border-transparent'
                    : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border-gray-200' }}">
            <i class="fas {{ $f['icon'] }} mr-1"></i> {{ $f['label'] }}
            @if($f['count'] > 0)
                <span class="inline-block ml-1 px-1.5 py-0.5 rounded-full text-[9px] font-black {{ $filter === $key ? 'bg-white/20' : 'bg-' . $f['color'] . '-100 text-' . $f['color'] . '-700' }}">
                    {{ $f['count'] }}
                </span>
            @endif
        </button>
        @endforeach
    </div>
</div>

{{-- ========================================== --}}
{{-- DAFTAR DISPENSASI (DIBUNGKUS ID CONTENT-AREA) --}}
{{-- ========================================== --}}
<div id="content-area" class="fade-in bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
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
        <span class="text-xs font-bold text-gray-500">{{ count($displayData) }} data</span>
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($displayData as $item)
        <div class="p-4 hover:bg-gray-50 transition-colors">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">

                {{-- Info Siswa & Dispensasi --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span class="font-mono text-xs font-bold text-gray-600">{{ $item->nomor_surat }}</span>

                        {{-- Badge Status --}}
                        @php
                            $badgeClass = match($item->status) {
                                'menunggu' => 'bg-amber-100 text-amber-700',
                                'disetujui' => 'bg-emerald-100 text-emerald-700',
                                'keluar' => 'bg-sky-100 text-sky-700',
                                'selesai' => 'bg-gray-100 text-gray-700',
                                default => 'bg-gray-100 text-gray-700'
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $badgeClass }}">
                            {{ $item->status }}
                        </span>
                    </div>

                    <p class="font-bold text-gray-900 text-sm truncate">{{ $item->siswa->nama_lengkap }}</p>
                    <p class="text-xs text-gray-500 mb-1">
                        {{ $item->siswa->kelas?->nama_kelas ?? '-' }} • {{ $item->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}
                    </p>
                    <p class="text-xs text-gray-600 line-clamp-2">
                        <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $item->kategori)) }}:</span>
                        {{ Str::limit($item->alasan, 60) }}
                    </p>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex gap-2 flex-shrink-0">
                    {{-- ✅ TOMBOL DETAIL (MATA) - SELALU MUNCUL --}}
                    <a href="{{ route('guru.pengajuan.show', $item) }}"
                       class="inline-flex items-center justify-center px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-lg transition-colors border border-blue-200"
                       title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </a>

                    @if($item->status === 'menunggu')
                        {{-- Tombol Setujui --}}
                        <form method="POST" action="{{ route('guru.pengajuan.approve', $item) }}" class="inline">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Setujui dispensasi {{ $item->siswa->nama_lengkap }}?')"
                                    class="inline-flex items-center justify-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors"
                                    title="Setujui">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>

                        {{-- Tombol Tolak --}}
                        <button type="button"
                                onclick="rejectDispensasi({{ $item->id }}, '{{ $item->siswa->nama_lengkap }}')"
                                class="inline-flex items-center justify-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-lg transition-colors"
                                title="Tolak">
                            <i class="fas fa-times"></i>
                        </button>
                    @else
                        {{-- Tombol Cetak (untuk status selain menunggu) --}}
                        @if(in_array($item->status, ['disetujui', 'keluar', 'selesai']))
                            <a href="{{ route('guru.cetak-pdf', [$item, 'format' => 'thermal']) }}"
                               target="_blank"
                               class="inline-flex items-center justify-center px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-lg transition-colors border border-emerald-200"
                               title="Cetak Struk">
                                <i class="fas fa-print"></i>
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="p-10 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-gray-50 text-gray-300 flex items-center justify-center text-2xl mb-3">
                <i class="fas fa-inbox"></i>
            </div>
            <p class="text-gray-500 font-semibold text-sm">Tidak ada data dispensasi untuk filter ini</p>
            <p class="text-gray-400 text-xs mt-1">Data akan muncul ketika siswa mengajukan dispensasi.</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Loading Overlay (Muncul saat proses smooth switching) --}}
<div id="loading-overlay" class="hidden fixed inset-0 bg-black/10 backdrop-blur-[2px] z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-4 shadow-xl flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-2 border-blue-600 border-t-transparent"></div>
        <span class="text-xs font-bold text-gray-700">Memuat data...</span>
    </div>
</div>

@push('scripts')
<script>
let currentFilter = '{{ $filter }}';

function switchFilter(filterKey, color, event) {
    if (event) event.preventDefault();
    if (filterKey === currentFilter) return; // Jangan reload jika tab sama

    // 1. Update UI Tombol Active secara instan
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-blue-600', 'bg-amber-600', 'bg-emerald-600', 'bg-sky-600', 'bg-gray-600', 'bg-red-600', 'text-white', 'shadow-lg', 'border-transparent');
        btn.classList.add('bg-gray-50', 'text-gray-600', 'border-gray-200');
    });

    const activeBtn = document.querySelector(`button[data-filter="${filterKey}"]`);
    if (activeBtn) {
        activeBtn.classList.remove('bg-gray-50', 'text-gray-600', 'border-gray-200');
        activeBtn.classList.add('active', `bg-${color}-600`, 'text-white', `shadow-lg`, `shadow-${color}-500/30`, 'border-transparent');
    }

    // 2. Efek Fade Out pada konten lama
    const contentArea = document.getElementById('content-area');
    const loading = document.getElementById('loading-overlay');

    contentArea.classList.remove('fade-in');
    contentArea.classList.add('fade-out');
    loading.classList.remove('hidden');

    // 3. Fetch data baru via AJAX (Tanpa Reload Halaman)
    fetch(`{{ url()->current() }}?filter=${filterKey}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Parse HTML yang diterima untuk mengambil hanya bagian #content-area
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.getElementById('content-area');

        if (newContent) {
            // Tunda sedikit agar efek fade-out selesai
            setTimeout(() => {
                contentArea.innerHTML = newContent.innerHTML;

                // Efek Fade In pada konten baru
                contentArea.classList.remove('fade-out');
                contentArea.classList.add('fade-in');
                loading.classList.add('hidden');

                currentFilter = filterKey;

                // Update URL browser tanpa reload (agar tombol Back berfungsi)
                const newUrl = new URL(window.location);
                newUrl.searchParams.set('filter', filterKey);
                window.history.pushState({ filter: filterKey }, '', newUrl);
            }, 300); // Sesuai durasi transition CSS (0.3s)
        }
    })
    .catch(error => {
        console.error('Error:', error);
        loading.classList.add('hidden');
        // Fallback: Jika AJAX gagal, lakukan reload biasa
        window.location.href = `{{ url()->current() }}?filter=${filterKey}`;
    });
}

// Menangani tombol "Back" / "Forward" pada browser
window.addEventListener('popstate', function(event) {
    const urlParams = new URLSearchParams(window.location.search);
    const filter = urlParams.get('filter') || 'semua';
    if (filter !== currentFilter) {
        // Temukan warna filter yang sesuai untuk dipicu
        const btn = document.querySelector(`button[data-filter="${filter}"]`);
        if (btn) {
            // Ambil warna dari class yang ada (simplified logic)
            let color = 'blue';
            if(filter === 'menunggu') color = 'amber';
            if(filter === 'disetujui') color = 'emerald';
            if(filter === 'keluar') color = 'sky';
            if(filter === 'selesai') color = 'gray';
            if(filter === 'terlambat') color = 'red';

            switchFilter(filter, color, null);
        }
    }
});


// Fungsi untuk menolak dispensasi (Bisa dipanggil dari dalam loop AJAX)
function rejectDispensasi(id, namaSiswa) {
    Swal.fire({
        title: 'Tolak Dispensasi',
        text: `Masukkan alasan penolakan untuk ${namaSiswa}:`,
        input: 'textarea',
        inputPlaceholder: 'Contoh: Alasan tidak jelas, siswa masih bisa mengikuti pelajaran...',
        inputAttributes: { rows: 3 },
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: 'Ya, Tolak',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value || value.trim() === '') return 'Alasan penolakan wajib diisi!';
        }
    }).then(result => {
        if (result.isConfirmed) {
            // Buat form dinamis untuk submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/guru/pengajuan/${id}/reject`; // Sesuaikan dengan route Anda

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
</script>
@endpush
@endsection
