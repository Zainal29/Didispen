@extends('guru.layouts.app')

@section('title', 'Verifikasi Dispensasi')
@section('page-title', 'Verifikasi Pengajuan Dispensasi')

@section('content')
@include('components.alert')

@if(!isset($piketHariIni) || !$piketHariIni)
    <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded mb-6">
        <p class="text-red-800 font-semibold">
            <i class="fas fa-exclamation-circle mr-2"></i>
            Anda tidak memiliki jadwal piket hari ini. Silakan kembali ke Dashboard.
        </p>
        <a href="{{ route('guru.dashboard') }}" class="inline-block mt-3 text-sm text-red-700 hover:underline">
            &larr; Kembali ke Dashboard
        </a>
    </div>
@else

    {{-- Filter --}}
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    <option value="keluar" {{ request('status') == 'keluar' ? 'selected' : '' }}>Sedang Keluar</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                    <tr>
                        <th class="p-3 text-left">No. Surat</th>
                        <th class="p-3 text-left">Siswa</th>
                        <th class="p-3 text-left">Kelas</th>
                        <th class="p-3 text-left">Kategori</th>
                        <th class="p-3 text-left">Waktu</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($dispensasi as $d)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-3 font-mono text-sm">{{ $d->nomor_surat }}</td>
                        <td class="p-3 font-semibold">{{ $d->siswa->nama_lengkap }}</td>
                        <td class="p-3 text-sm">{{ $d->siswa->kelas->nama_kelas }}</td>
                        <td class="p-3 text-sm capitalize">{{ str_replace('_', ' ', $d->kategori) }}</td>
                        
                        {{-- Kolom Waktu --}}
                        <td class="p-3 text-sm">
                            <div class="font-medium text-gray-800">
                                {{ $d->jam_keluar }} s.d {{ $d->jam_kembali }}
                            </div>
                            <div class="text-xs text-indigo-600 font-semibold mt-1 flex items-center">
                                <i class="far fa-clock mr-1.5"></i>
                                {{ \App\Helpers\TimeHelper::getWaktuAktual($d->jam_keluar) }}
                            </div>
                        </td>
                        
                        <td class="p-3">
                            @php
                                $colors = [
                                    'menunggu' => 'bg-yellow-100 text-yellow-800',
                                    'disetujui' => 'bg-green-100 text-green-800',
                                    'ditolak' => 'bg-red-100 text-red-800',
                                    'keluar' => 'bg-blue-100 text-blue-800',
                                    'selesai' => 'bg-gray-100 text-gray-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded text-xs font-bold {{ $colors[$d->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($d->status) }}
                            </span>
                        </td>
                        
                        {{-- ✅ PERBAIKAN: Hapus tombol konfirmasi guru, ganti dengan info status --}}
                        <td class="p-3 text-center space-x-2">
                            <a href="{{ route('guru.pengajuan.show', $d) }}" class="text-blue-600 hover:text-blue-800" title="Lihat Detail & QR Code">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            @if($d->status === 'menunggu')
                                <form method="POST" action="{{ route('guru.pengajuan.approve', $d) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800" title="Setujui" onclick="return confirm('Setujui dispensasi ini?')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <button onclick="rejectDispensasi({{ $d->id }})" class="text-red-600 hover:text-red-800" title="Tolak">
                                    <i class="fas fa-times"></i>
                                </button>
                            @elseif(in_array($d->status, ['disetujui', 'keluar', 'selesai']))
                                <span class="text-xs text-gray-500 italic" title="Konfirmasi keluar/kembali dilakukan oleh Satpam via Scan QR">
                                    <i class="fas fa-info-circle mr-1"></i>Menunggu Satpam
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                            <p>Tidak ada pengajuan dispensasi untuk shift Anda hari ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($dispensasi->hasPages())
        <div class="p-4 border-t bg-gray-50">
            {{ $dispensasi->links() }}
        </div>
        @endif
    </div>
@endif

@push('scripts')
<script>
function rejectDispensasi(id) {
    let alasan = prompt("Masukkan alasan penolakan:");
    if (alasan && alasan.trim() !== "") {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/guru/pengajuan/${id}/reject`;
        form.innerHTML = `@csrf <input type="hidden" name="catatan_admin" value="${alasan}">`;
        document.body.appendChild(form);
        form.submit();
    } else if (alasan !== null) {
        alert("Alasan penolakan wajib diisi!");
    }
}
</script>
@endpush
@endsection