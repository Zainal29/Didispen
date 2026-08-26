@extends('guru.layouts.app')

@section('title', 'Verifikasi Dispensasi')
@section('page-title', 'Verifikasi Pengajuan Dispensasi')

@section('content')
@include('components.alert')

    {{-- ============ HERO PIKET ============ --}}
    <div class="relative overflow-hidden rounded-2xl mb-4">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-700 via-blue-600 to-sky-500"></div>
        <div class="absolute -top-16 -right-16 w-48 h-48 bg-white/10 rounded-full"></div>
        <div class="relative z-10 p-4 sm:p-5 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-blue-100 text-[10px] sm:text-[11px] font-bold uppercase tracking-widest">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
                <h2 class="text-base sm:text-lg font-black text-white tracking-tight mt-0.5">Verifikasi Pengajuan Dispensasi</h2>
                <p class="text-blue-100 text-[11px] mt-1 truncate">Guru Bertugas: {{ $piketHariIni->guru?->nama_lengkap ?? auth()->user()->guru?->nama_lengkap }}</p>
            </div>
        </div>
    </div>

    {{-- ============ FILTER ============ --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Filter Status</label>
                <select name="status" onchange="this.form.submit()"
                        class="w-full h-11 px-3.5 rounded-xl border-2 border-gray-200 bg-white text-sm font-medium text-gray-700 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all">
                    <option value="">Semua Status</option>
                    <option value="menunggu"  {{ request('status') == 'menunggu'  ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak"   {{ request('status') == 'ditolak'   ? 'selected' : '' }}>Ditolak</option>
                    <option value="selesai"   {{ request('status') == 'selesai'   ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Cari Siswa</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIS..."
                           class="w-full h-11 pl-10 pr-3.5 rounded-xl border-2 border-gray-200 bg-white text-sm font-medium text-gray-700 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- ============ DAFTAR PENGAJUAN ============ --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- MOBILE: Kartu --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse($dispensasi as $d)
                @php
                    $displayStatus = $d->status === 'keluar' ? 'disetujui' : $d->status;
                    $badges = [
                        'menunggu'  => 'bg-amber-100 text-amber-700 border-amber-200',
                        'disetujui' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'ditolak'   => 'bg-red-100 text-red-700 border-red-200',
                        'selesai'   => 'bg-gray-100 text-gray-600 border-gray-200',
                    ];
                @endphp
                <div class="p-4">
                    <div class="flex justify-between items-start gap-2 mb-1.5">
                        <div class="min-w-0">
                            <p class="font-mono font-bold text-gray-800 text-xs">{{ $d->nomor_surat }}</p>
                            <p class="font-bold text-gray-900 text-sm truncate">{{ $d->siswa->nama_lengkap }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border flex-shrink-0 {{ $badges[$displayStatus] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                            {{ ucfirst($displayStatus) }}
                        </span>
                    </div>
                    <p class="text-[11px] text-gray-500 truncate">
                        {{ $d->siswa->kelas?->nama_kelas ?? '-' }} • {{ $d->siswa->user->nis_nip ?? '-' }}
                    </p>

                    <div class="grid grid-cols-2 gap-2 mt-2.5 text-[11px]">
                        <div class="px-2.5 py-2 rounded-xl bg-gray-50 border border-gray-100">
                            <p class="text-gray-400 text-[9px] font-bold uppercase mb-0.5">Kategori</p>
                            <p class="font-bold text-gray-800 capitalize truncate">{{ str_replace('_', ' ', $d->kategori) }}</p>
                        </div>
                        <div class="px-2.5 py-2 rounded-xl bg-blue-50/70 border border-blue-100">
                            <p class="text-blue-500 text-[9px] font-bold uppercase mb-0.5">Waktu</p>
                            <p class="font-bold text-blue-800 truncate">{{ $d->jam_keluar }} - {{ $d->jam_kembali }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 mt-2 text-[11px] text-blue-600 font-semibold">
                        <i class="far fa-clock"></i>
                        {{ \App\Helpers\TimeHelper::getWaktuAktual($d->jam_keluar) }}
                    </div>

                    {{-- Aksi Mobile --}}
                    <div class="flex gap-2 mt-3 pt-3 border-t border-gray-100">
                        <a href="{{ route('guru.pengajuan.show', $d) }}"
                           class="flex-1 inline-flex justify-center items-center px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all">
                            <i class="fas fa-eye mr-1.5"></i>Detail
                        </a>
                        @if($d->status === 'menunggu')
                            <form method="POST" action="{{ route('guru.pengajuan.approve', $d) }}" class="flex-shrink-0">
                                @csrf
                                <button type="submit" data-confirm="Setujui dispensasi {{ $d->siswa->nama_lengkap }}?"
                                        class="w-9 h-9 rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 inline-flex items-center justify-center active:scale-95 transition-all" title="Setujui">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <button onclick="rejectDispensasi({{ $d->id }}, '{{ $d->siswa->nama_lengkap }}')"
                                    class="w-9 h-9 rounded-xl text-white bg-red-600 hover:bg-red-700 inline-flex items-center justify-center active:scale-95 transition-all" title="Tolak">
                                <i class="fas fa-times"></i>
                            </button>
                        @elseif(in_array($d->status, ['disetujui', 'keluar', 'selesai']))
                            <a href="{{ route('guru.cetak-pdf', [$d, 'format' => 'thermal']) }}" target="_blank"
                               class="w-9 h-9 rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 inline-flex items-center justify-center active:scale-95 transition-all" title="Cetak PDF Thermal (58mm)">
                                <i class="fas fa-print"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-10 text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-300 flex items-center justify-center text-2xl mb-3">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <p class="text-gray-500 font-semibold text-sm">Tidak ada pengajuan dispensasi saat ini.</p>
                </div>
            @endforelse
        </div>

        {{-- DESKTOP: Tabel --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="p-4 text-left">No. Surat</th>
                        <th class="p-4 text-left">Siswa</th>
                        <th class="p-4 text-left">Kelas</th>
                        <th class="p-4 text-left">Kategori</th>
                        <th class="p-4 text-left">Waktu</th>
                        <th class="p-4 text-left">Status</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($dispensasi as $d)
                        @php
                            $displayStatus = $d->status === 'keluar' ? 'disetujui' : $d->status;
                            $badges = [
                                'menunggu'  => 'bg-amber-100 text-amber-700 border-amber-200',
                                'disetujui' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'ditolak'   => 'bg-red-100 text-red-700 border-red-200',
                                'selesai'   => 'bg-gray-100 text-gray-600 border-gray-200',
                            ];
                        @endphp
                        <tr class="hover:bg-blue-50/40 transition-colors">
                            <td class="p-4 font-mono font-bold text-gray-800 text-xs">{{ $d->nomor_surat }}</td>
                            <td class="p-4">
                                <div class="font-bold text-gray-900">{{ $d->siswa->nama_lengkap }}</div>
                                <div class="text-[11px] text-gray-500 font-mono">{{ $d->siswa->user->nis_nip ?? '-' }}</div>
                            </td>
                            <td class="p-4 text-xs font-medium text-gray-600">{{ $d->siswa->kelas?->nama_kelas ?? '-' }}</td>
                            <td class="p-4 text-xs capitalize font-medium text-gray-600">{{ str_replace('_', ' ', $d->kategori) }}</td>
                            <td class="p-4">
                                <div class="font-bold text-gray-800 text-xs">{{ $d->jam_keluar }} s.d {{ $d->jam_kembali }}</div>
                                <div class="text-[11px] text-blue-600 font-semibold mt-0.5 flex items-center">
                                    <i class="far fa-clock mr-1"></i>{{ \App\Helpers\TimeHelper::getWaktuAktual($d->jam_keluar) }}
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $badges[$displayStatus] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                    {{ ucfirst($displayStatus) }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    <a href="{{ route('guru.pengajuan.show', $d) }}" class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-all shadow-sm" title="Detail">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    @if($d->status === 'menunggu')
                                        <form method="POST" action="{{ route('guru.pengajuan.approve', $d) }}" class="inline">
                                            @csrf
                                            <button type="submit" data-confirm="Setujui dispensasi {{ $d->siswa->nama_lengkap }}?"
                                                    class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white flex items-center justify-center transition-all shadow-sm" title="Setujui">
                                                <i class="fas fa-check text-xs"></i>
                                            </button>
                                        </form>
                                        <button onclick="rejectDispensasi({{ $d->id }}, '{{ $d->siswa->nama_lengkap }}')"
                                                class="w-9 h-9 rounded-xl bg-red-50 text-red-600 hover:bg-red-600 hover:text-white flex items-center justify-center transition-all shadow-sm" title="Tolak">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    @elseif(in_array($d->status, ['disetujui', 'keluar', 'selesai']))
                                        <a href="{{ route('guru.cetak-pdf', [$d, 'format' => 'thermal']) }}" target="_blank"
                                           class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white flex items-center justify-center transition-all shadow-sm" title="Cetak PDF Thermal (58mm)">
                                            <i class="fas fa-print text-xs"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center">
                                <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-300 flex items-center justify-center text-2xl mb-3"><i class="fas fa-inbox"></i></div>
                                <p class="text-gray-500 font-semibold text-sm">Tidak ada pengajuan dispensasi untuk shift Anda hari ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dispensasi->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">{{ $dispensasi->links() }}</div>
        @endif
    </div>

@push('scripts')
<script>
function rejectDispensasi(id, nama) {
    Swal.fire({
        title: 'Tolak Dispensasi',
        text: `Masukkan alasan penolakan untuk ${nama}:`,
        input: 'textarea',
        inputPlaceholder: 'Contoh: Alasan tidak jelas, siswa masih bisa mengikuti pelajaran...',
        inputAttributes: { rows: 4 },
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
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/guru/pengajuan/${id}/reject`;
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
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

// Konfirmasi approve
document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const form = this.closest('form');
        Swal.fire({
            title: 'Konfirmasi Persetujuan',
            text: this.dataset.confirm,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(result => { if (result.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
@endsection