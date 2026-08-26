@extends('siswa.layouts.app')

@section('title', 'Riwayat Pengajuan')
@section('page-title', 'Riwayat Pengajuan Dispensasi')

@section('content')

@include('components.alert')

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    {{-- Header + Filter --}}
    <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-gray-900"><i class="fas fa-clock-rotate-left mr-1.5 text-blue-600"></i>Riwayat Pengajuan</h3>
            <p class="text-[11px] text-gray-500 mt-0.5">Pantau seluruh pengajuan dispensasi Anda.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" class="flex-1 sm:flex-none">
                <select name="status" onchange="this.form.submit()"
                        class="w-full sm:w-auto h-10 px-3 rounded-xl border-2 border-gray-200 bg-white text-xs font-bold text-gray-600 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all">
                    <option value="">Semua Status</option>
                    <option value="menunggu"  {{ request('status') == 'menunggu'  ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak"   {{ request('status') == 'ditolak'   ? 'selected' : '' }}>Ditolak</option>
                    <option value="selesai"   {{ request('status') == 'selesai'   ? 'selected' : '' }}>Selesai</option>
                </select>
            </form>
            <a href="{{ route('siswa.pengajuan.create') }}"
               class="inline-flex items-center px-3.5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 shadow-lg shadow-blue-500/30 flex-shrink-0">
                <i class="fas fa-plus mr-1.5"></i>Buat
            </a>
        </div>
    </div>

    {{-- ===== MOBILE: Kartu Riwayat ===== --}}
    <div class="md:hidden divide-y divide-gray-100">
        @forelse($pengajuan as $p)
            @php
                $badges = [
                    'menunggu'  => 'bg-amber-100 text-amber-700 border-amber-200',
                    'disetujui' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'ditolak'   => 'bg-red-100 text-red-700 border-red-200',
                    'keluar'    => 'bg-sky-100 text-sky-700 border-sky-200',
                    'selesai'   => 'bg-gray-100 text-gray-600 border-gray-200',
                ];
            @endphp
            <div class="p-4">
                <div class="flex justify-between items-start gap-2 mb-1.5">
                    <p class="font-mono font-bold text-gray-800 text-xs">{{ $p->nomor_surat }}</p>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border flex-shrink-0 {{ $badges[$p->status] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                        {{ ucfirst($p->status) }}
                    </span>
                </div>
                <p class="text-[11px] text-gray-500">{{ $p->created_at->format('d/m/Y') }} • <span class="capitalize">{{ str_replace('_', ' ', $p->kategori) }}</span></p>
                <p class="text-[11px] text-gray-500 mt-0.5 truncate"><i class="far fa-clock mr-1"></i>{{ $p->jam_keluar }} – {{ $p->jam_kembali }} • {{ $p->tujuan }}</p>
                <div class="flex items-center justify-between mt-3">
                    <a href="{{ route('siswa.pengajuan.show', $p) }}"
                       class="inline-flex items-center px-3.5 py-2 rounded-xl text-[11px] font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 shadow-md shadow-blue-500/20 active:scale-95 transition-transform">
                        <i class="fas fa-eye mr-1.5"></i>Lihat Detail
                    </a>
                    @if($p->qr_code && $p->status === 'disetujui')
                        <button onclick="showQRCode({{ $p->id }})" class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 active:bg-blue-600 active:text-white transition-colors" title="Lihat QR Code">
                            <i class="fas fa-qrcode"></i>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-10 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-300 flex items-center justify-center text-2xl mb-3"><i class="fas fa-inbox"></i></div>
                <p class="text-gray-500 font-semibold text-sm">Belum ada pengajuan dispensasi</p>
            </div>
        @endforelse
    </div>

    {{-- ===== DESKTOP: Tabel ===== --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="p-4 text-left">No. Surat</th>
                    <th class="p-4 text-left">Tanggal</th>
                    <th class="p-4 text-left">Kategori</th>
                    <th class="p-4 text-left">Tujuan</th>
                    <th class="p-4 text-left">Waktu</th>
                    <th class="p-4 text-left">Status</th>
                    <th class="p-4 text-center">QR Code</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pengajuan as $p)
                    @php
                        $badges = [
                            'menunggu'  => 'bg-amber-100 text-amber-700 border-amber-200',
                            'disetujui' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'ditolak'   => 'bg-red-100 text-red-700 border-red-200',
                            'keluar'    => 'bg-sky-100 text-sky-700 border-sky-200',
                            'selesai'   => 'bg-gray-100 text-gray-600 border-gray-200',
                        ];
                    @endphp
                    <tr class="hover:bg-blue-50/40 transition-colors">
                        <td class="p-4 font-mono font-bold text-gray-800 text-xs">{{ $p->nomor_surat }}</td>
                        <td class="p-4 text-gray-500">{{ $p->created_at->format('d/m/Y') }}</td>
                        <td class="p-4 capitalize text-gray-600">{{ str_replace('_', ' ', $p->kategori) }}</td>
                        <td class="p-4 text-gray-600">{{ $p->tujuan }}</td>
                        <td class="p-4 text-gray-500 text-xs">{{ $p->jam_keluar }} – {{ $p->jam_kembali }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $badges[$p->status] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            @if($p->qr_code && $p->status === 'disetujui')
                                <button onclick="showQRCode({{ $p->id }})" class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all inline-flex items-center justify-center" title="Lihat QR Code">
                                    <i class="fas fa-qrcode"></i>
                                </button>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-300 flex items-center justify-center text-2xl mb-3"><i class="fas fa-inbox"></i></div>
                            <p class="text-gray-500 font-semibold text-sm">Belum ada pengajuan dispensasi</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pengajuan->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">{{ $pengajuan->links() }}</div>
    @endif
</div>

{{-- ============ MODAL QR CODE ============ --}}
<div id="qrModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-5 max-w-sm w-full text-center">
        <div class="w-12 h-12 mx-auto rounded-xl bg-gradient-to-br from-blue-600 to-sky-500 text-white flex items-center justify-center text-xl shadow-lg shadow-blue-500/30 mb-3">
            <i class="fas fa-qrcode"></i>
        </div>
        <h3 class="text-base font-bold text-gray-900">QR Code Dispensasi</h3>
        <p class="text-xs text-gray-500 mt-1 mb-4">Tunjukkan layar ini ke Petugas Satpam</p>

        <div id="qrContent" class="flex justify-center items-center bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl p-4 mb-4 min-h-[200px]">
            <p class="text-gray-400 text-sm"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat...</p>
        </div>

        <button onclick="closeQRModal()" class="w-full px-4 py-2.5 rounded-xl text-sm font-bold text-gray-600 border border-gray-200 hover:bg-gray-50 transition-colors">
            Tutup
        </button>
    </div>
</div>

@push('scripts')
<script>
function showQRCode(dispensasiId) {
    const modal = document.getElementById('qrModal');
    const content = document.getElementById('qrContent');

    modal.classList.remove('hidden');
    content.innerHTML = '<p class="text-gray-400 text-sm"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat QR Code...</p>';

    fetch(`/siswa/qr-code/${dispensasiId}`)
        .then(response => {
            if (!response.ok) throw new Error('Gagal memuat data');
            return response.json();
        })
        .then(data => {
            if (data.qr_code) {
                const image = document.createElement('img');
                image.src = '/storage/' + data.qr_code.split('/').map(encodeURIComponent).join('/');
                image.alt = 'QR Code';
                image.className = 'w-52 h-52 object-contain rounded-lg';
                content.replaceChildren(image);
            } else {
                content.innerHTML = '<p class="text-red-500 text-sm">QR Code belum tersedia.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<p class="text-red-500 text-sm">Gagal memuat QR Code.</p>';
        });
}

function closeQRModal() {
    document.getElementById('qrModal').classList.add('hidden');
}

document.getElementById('qrModal').addEventListener('click', function(e) {
    if (e.target === this) closeQRModal();
});
</script>
@endpush
@endsection