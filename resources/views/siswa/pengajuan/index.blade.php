@extends('siswa.layouts.app')

@section('title', 'Riwayat Pengajuan')
@section('page-title', 'Riwayat Pengajuan Dispensasi')

@section('content')
@include('components.alert')

<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800">Riwayat Pengajuan</h3>
        <a href="{{ route('siswa.pengajuan.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm transition">
            <i class="fas fa-plus mr-1"></i> Buat Pengajuan Baru
        </a>
    </div>

    {{-- Filter --}}
    <div class="p-4 bg-gray-50 border-b">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    {{-- ✅ DIHAPUS: Opsi "Sedang Keluar" --}}
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">No. Surat</th>
                    <th class="p-3 text-left">Tanggal</th>
                    <th class="p-3 text-left">Kategori</th>
                    <th class="p-3 text-left">Tujuan</th>
                    <th class="p-3 text-left">Waktu</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-center">QR Code</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($pengajuan as $p)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-3 font-mono text-sm">{{ $p->nomor_surat }}</td>
                    <td class="p-3 text-sm">{{ $p->created_at->format('d/m/Y') }}</td>
                    <td class="p-3 text-sm capitalize">{{ str_replace('_', ' ', $p->kategori) }}</td>
                    <td class="p-3 text-sm">{{ $p->tujuan }}</td>
                    <td class="p-3 text-sm">{{ $p->jam_keluar }} - {{ $p->jam_kembali }}</td>
                    <td class="p-3">
                        @php
                            // ✅ PERBAIKAN: Status 'keluar' ditampilkan sebagai 'Disetujui' agar lebih sederhana
                            $displayStatus = $p->status === 'keluar' ? 'disetujui' : $p->status;
                            
                            $colors = [
                                'menunggu' => 'bg-yellow-100 text-yellow-800',
                                'disetujui' => 'bg-green-100 text-green-800',
                                'ditolak' => 'bg-red-100 text-red-800',
                                'selesai' => 'bg-gray-100 text-gray-800',
                            ];
                        @endphp
                        <span class="px-2 py-1 rounded text-xs font-bold {{ $colors[$displayStatus] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($displayStatus) }}
                        </span>
                    </td>
                    <td class="p-3 text-center">
                        {{-- QR Code tetap bisa dilihat jika statusnya disetujui, keluar, atau selesai --}}
                        @if($p->qr_code && in_array($p->status, ['disetujui', 'keluar', 'selesai']))
                            <button onclick="showQRCode({{ $p->id }})" class="text-indigo-600 hover:text-indigo-800 transition" title="Lihat QR Code">
                                <i class="fas fa-qrcode text-2xl"></i>
                            </button>
                        @else
                            <span class="text-gray-300">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                        <p>Belum ada pengajuan dispensasi</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pengajuan->hasPages())
    <div class="p-4 border-t bg-gray-50">
        {{ $pengajuan->links() }}
    </div>
    @endif
</div>

{{-- Modal QR Code --}}
<div id="qrModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl p-6 max-w-sm w-full mx-4 text-center">
        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-800">QR Code Dispensasi</h3>
            <p class="text-sm text-gray-600 mt-1">Tunjukkan layar ini ke Petugas Satpam</p>
        </div>
        
        {{-- ✅ DIPERBAIKI: Menghapus karakter '@' yang tidak sengaja tertulis --}}
        <div id="qrContent" class="flex justify-center items-center bg-gray-50 p-4 rounded-lg border border-dashed border-gray-300 mb-4 min-h-[200px]">
            <p class="text-gray-400 text-sm"><i class="fas fa-spinner fa-spin mr-2"></i> Memuat...</p>
        </div>
        
        <button onclick="closeQRModal()" class="w-full px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition font-medium">
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
    content.innerHTML = '<p class="text-gray-400 text-sm"><i class="fas fa-spinner fa-spin mr-2"></i> Memuat QR Code...</p>';

    fetch(`/siswa/qr-code/${dispensasiId}`)
        .then(response => {
            if (!response.ok) throw new Error('Gagal memuat data');
            return response.json();
        })
        .then(data => {
            if (data.qr_code) {
                content.innerHTML = `<img src="/storage/${data.qr_code}" alt="QR Code" class="w-56 h-56 object-contain">`;
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