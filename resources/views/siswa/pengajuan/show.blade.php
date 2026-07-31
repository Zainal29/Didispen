@extends('siswa.layouts.app')

@section('title', 'Detail Pengajuan')
@section('page-title', 'Detail Pengajuan Dispensasi')

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- ✅ REAL-TIME CLOCK CARD (sama seperti tampilan guru) --}}
    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-lg shadow-lg p-6 mb-6 text-white">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-indigo-100 text-sm mb-1">Waktu Saat Ini</p>
                <h2 class="text-4xl font-bold font-mono" id="realTimeClock">00:00:00</h2>
                <p class="text-indigo-100 mt-2" id="realTimeDate">Loading...</p>
            </div>
            <div class="text-6xl opacity-30">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800">{{ $dispensasi->nomor_surat }}</h3>
                <p class="text-sm text-gray-500">
                    <i class="far fa-calendar-plus mr-1"></i>
                    Diajukan: {{ $dispensasi->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i:s') }} WIB
                </p>
            </div>
            @php
                $statusColors = [
                    'menunggu' => 'bg-yellow-100 text-yellow-800',
                    'disetujui' => 'bg-green-100 text-green-800',
                    'ditolak' => 'bg-red-100 text-red-800',
                    'keluar' => 'bg-blue-100 text-blue-800',
                    'selesai' => 'bg-gray-100 text-gray-800',
                ];
            @endphp
            <span class="px-3 py-1 rounded-full text-sm font-bold {{ $statusColors[$dispensasi->status] ?? 'bg-gray-100' }}">
                {{ ucfirst($dispensasi->status) }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm mb-6">
            <div>
                <span class="text-gray-500">Kategori:</span>
                <p class="font-semibold capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
            </div>
            <div>
                <span class="text-gray-500">Lokasi:</span>
                <p class="font-semibold">{{ $dispensasi->lokasi ?? '-' }}</p>
            </div>
            <div class="col-span-2">
                <span class="text-gray-500">Alasan:</span>
                <p class="font-semibold">{{ $dispensasi->alasan }}</p>
            </div>
            <div class="col-span-2">
                <span class="text-gray-500">Tujuan:</span>
                <p class="font-semibold">{{ $dispensasi->tujuan }}</p>
            </div>

            {{-- ✅ Jam Keluar & Kembali + Waktu Aktual (pakai TimeHelper, konsisten dgn tampilan guru) --}}
            <div>
                <span class="text-gray-500">Jam Keluar:</span>
                <p class="font-semibold text-indigo-700">
                    {{ $dispensasi->jam_keluar }}
                    <span class="text-xs text-gray-500 block mt-1 font-normal">
                        <i class="far fa-clock mr-1"></i>
                        {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar) }}
                    </span>
                </p>
            </div>
            <div>
                <span class="text-gray-500">Jam Kembali:</span>
                <p class="font-semibold text-indigo-700">
                    {{ $dispensasi->jam_kembali }}
                    <span class="text-xs text-gray-500 block mt-1 font-normal">
                        <i class="far fa-clock mr-1"></i>
                        {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali) }}
                    </span>
                </p>
            </div>

            @if($dispensasi->catatan_admin)
            <div class="col-span-2 bg-yellow-50 p-3 rounded border-l-4 border-yellow-400">
                <span class="text-gray-500 text-xs">Catatan Guru:</span>
                <p class="font-semibold">{{ $dispensasi->catatan_admin }}</p>
            </div>
            @endif
        </div>

        <div class="border-t pt-4 flex space-x-3">
            @if(in_array($dispensasi->status, ['disetujui', 'selesai']))
            <a href="{{ route('siswa.cetak', $dispensasi) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                <i class="fas fa-print mr-2"></i>Cetak Surat
            </a>
            @endif
            <a href="{{ route('siswa.pengajuan.index') }}" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ✅ REAL-TIME CLOCK FUNCTION (sama seperti tampilan guru)
function updateRealTimeClock() {
    const now = new Date();

    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const timeString = `${hours}:${minutes}:${seconds}`;

    const options = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    const dateString = now.toLocaleDateString('id-ID', options);

    document.getElementById('realTimeClock').textContent = timeString + ' WIB';
    document.getElementById('realTimeDate').textContent = dateString;
}

setInterval(updateRealTimeClock, 1000);
updateRealTimeClock();
</script>
@endpush
@endsection