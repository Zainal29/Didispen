@extends('guru.layouts.app')

@section('title', 'Check In/Out')
@section('page-title', 'Catatan Keluar & Kembali')

@section('content')
@include('components.alert')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- FORM INPUT --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4 text-gray-800">
                @if($sedangKeluar) 
                    <span class="text-red-600"><i class="fas fa-door-open mr-2"></i>Anda Sedang Keluar</span>
                @else 
                    <span class="text-blue-600"><i class="fas fa-plus-circle mr-2"></i>Catat Keluar</span>
                @endif
            </h3>

            @if(!$sedangKeluar)
                <form method="POST" action="{{ route('guru.checklog.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Keluar <span class="text-red-500">*</span></label>
                            <textarea name="alasan" required rows="3" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Contoh: Urusan keluarga mendadak"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan <span class="text-red-500">*</span></label>
                            <input type="text" name="tujuan" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Contoh: Bank BRI Cabang Pusat">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi (Opsional)</label>
                            <input type="text" name="lokasi" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Contoh: Jl. Sudirman No. 10">
                        </div>
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded transition shadow-lg">
                            <i class="fas fa-sign-out-alt mr-2"></i> Catat Saya Keluar
                        </button>
                    </div>
                </form>
            @else
                {{-- DISPLAY STOPWATCH --}}
                <div class="bg-gradient-to-br from-red-50 to-red-100 border-l-4 border-red-500 p-6 rounded-lg mb-6 shadow-inner">
                    <div class="text-center mb-6">
                        <p class="text-sm text-red-700 font-semibold mb-2">
                            <i class="fas fa-stopwatch mr-1"></i> Durasi Keluar
                        </p>
                        <div class="bg-white rounded-xl p-4 shadow-inner">
                            <p class="text-5xl font-bold text-red-800 font-mono" id="durationTimer">
                                00.00.00
                            </p>
                        </div>
                        <p class="text-xs text-red-600 mt-2">Mulai: {{ str_replace(':', '.', $sedangKeluar->jam_keluar->timezone('Asia/Jakarta')->format('H:i:s')) }}</p>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-red-200 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-red-700"><i class="fas fa-map-marker-alt mr-1"></i> Tujuan:</span>
                            <span class="font-semibold text-red-900">{{ $sedangKeluar->tujuan }}</span>
                        </div>
                        @if($sedangKeluar->lokasi)
                        <div class="flex justify-between text-sm">
                            <span class="text-red-700"><i class="fas fa-location-arrow mr-1"></i> Lokasi:</span>
                            <span class="font-semibold text-red-900">{{ $sedangKeluar->lokasi }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-sm">
                            <span class="text-red-700"><i class="fas fa-info-circle mr-1"></i> Alasan:</span>
                            <span class="font-semibold text-red-900 text-right max-w-xs">{{ Str::limit($sedangKeluar->alasan, 30) }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('guru.checklog.checkin', $sedangKeluar) }}" class="mt-6">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition shadow-lg transform hover:scale-105">
                            <i class="fas fa-sign-in-alt mr-2"></i> Catat Saya Kembali
                        </button>
                    </form>
                </div>

                {{-- CURRENT TIME DISPLAY --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-blue-600 font-semibold mb-1">WAKTU SEKARANG</p>
                            <p class="text-2xl font-bold text-blue-800 font-mono" id="currentTime">
                                {{ str_replace(':', '.', now()->timezone('Asia/Jakarta')->format('H:i:s')) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-blue-700" id="currentDate">
                                {{ now()->timezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- RIWAYAT --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-5 border-b flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Riwayat 10 Terakhir</h3>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-history mr-1"></i> Update: <span id="lastUpdate">{{ str_replace(':', '.', now()->timezone('Asia/Jakarta')->format('H:i:s')) }}</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="p-3 text-left">Tanggal</th>
                            <th class="p-3 text-left">Tujuan</th>
                            <th class="p-3 text-left">
                                <i class="fas fa-door-open mr-1"></i>Jam Keluar
                            </th>
                            <th class="p-3 text-left">
                                <i class="fas fa-door-closed mr-1"></i>Jam Kembali
                            </th>
                            <th class="p-3 text-left">Durasi</th>
                            <th class="p-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($riwayat as $log)
                        <tr class="hover:bg-gray-50 {{ $log->status === 'keluar' ? 'bg-red-50' : '' }}">
                            <td class="p-3 text-sm">
                                <div class="font-semibold">{{ $log->jam_keluar->timezone('Asia/Jakarta')->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $log->jam_keluar->timezone('Asia/Jakarta')->isoFormat('dddd') }}</div>
                            </td>
                            <td class="p-3 text-sm">
                                <div class="font-semibold">{{ $log->tujuan }}</div>
                                @if($log->lokasi)
                                <div class="text-xs text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i>{{ $log->lokasi }}</div>
                                @endif
                            </td>
                            {{-- FIX: Jam Keluar adalah fakta yang sudah terjadi, jadi HARUS selalu
                                 menampilkan waktu asli ($log->jam_keluar) dalam timezone WIB, bukan now()
                                 yang berubah tiap detik dan bukan waktu UTC mentah dari DB --}}
                            <td class="p-3 text-sm font-mono">
                                <span class="text-blue-700 font-semibold">{{ str_replace(':', '.', $log->jam_keluar->timezone('Asia/Jakarta')->format('H:i:s')) }}</span>
                            </td>
                            {{-- FIX: Jam Kembali belum ada nilainya selama masih di luar,
                                 jadi tampilkan strip (-) statis, bukan jam hidup yang menyesatkan --}}
                            <td class="p-3 text-sm font-mono">
                                @if($log->jam_kembali)
                                    <span class="text-green-700 font-semibold">{{ str_replace(':', '.', $log->jam_kembali->timezone('Asia/Jakarta')->format('H:i:s')) }}</span>
                                @else
                                    <span class="text-gray-400 font-semibold">–</span>
                                    <span class="text-xs text-red-500 block">Masih keluar</span>
                                @endif
                            </td>
                            <td class="p-3 text-sm font-mono">
                                @if($log->jam_kembali)
                                    @php
                                        $diff = $log->jam_keluar->diff($log->jam_kembali);
                                        $totalSeconds = ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
                                        $h = floor($totalSeconds / 3600);
                                        $m = floor(($totalSeconds % 3600) / 60);
                                        $s = $totalSeconds % 60;
                                    @endphp
                                    <span class="font-semibold text-green-700">
                                        {{ sprintf('%02d.%02d.%02d', $h, $m, $s) }}
                                    </span>
                                @else
                                    <span class="text-red-600 font-semibold" data-checkout="{{ $log->jam_keluar->toIso8601String() }}">
                                        00.00.00
                                    </span>
                                @endif
                            </td>
                            <td class="p-3">
                                @if($log->status === 'keluar')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 animate-pulse inline-flex items-center">
                                        <span class="w-2 h-2 bg-red-600 rounded-full mr-2 animate-ping"></span>
                                        Sedang Keluar
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 inline-flex items-center">
                                        <i class="fas fa-check-circle mr-1.5"></i> Selesai
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">
                                <i class="fas fa-history text-4xl mb-2 text-gray-300"></i>
                                <p>Belum ada riwayat keluar/masuk</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ------------------------------------------------------------
// SINKRONISASI JAM: hitung selisih (offset) antara jam server
// (dikirim lewat PHP now()) dan jam client saat halaman dimuat.
// Semua "waktu sekarang" di JS pakai getSyncedNow() supaya tetap
// konsisten dengan jam server, bukan jam device masing-masing guru.
// ------------------------------------------------------------
const SERVER_TIME_AT_LOAD = new Date('{{ now()->toIso8601String() }}');
const CLIENT_TIME_AT_LOAD = new Date();
const SERVER_OFFSET_MS = SERVER_TIME_AT_LOAD.getTime() - CLIENT_TIME_AT_LOAD.getTime();

function getSyncedNow() {
    return new Date(Date.now() + SERVER_OFFSET_MS);
}

function formatDuration(totalSeconds) {
    const h = Math.floor(totalSeconds / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    const s = totalSeconds % 60;
    return String(h).padStart(2, '0') + '.' +
           String(m).padStart(2, '0') + '.' +
           String(s).padStart(2, '0');
}

// Real-time stopwatch and clock (disinkronkan ke jam server)
function updateRealTime() {
    const now = getSyncedNow();

    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const timeString = `${hours}.${minutes}.${seconds}`;

    const dateString = now.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    const currentTimeEl = document.getElementById('currentTime');
    if (currentTimeEl) {
        currentTimeEl.textContent = timeString;
    }

    const currentDateEl = document.getElementById('currentDate');
    if (currentDateEl) {
        currentDateEl.textContent = dateString;
    }

    const lastUpdateEl = document.getElementById('lastUpdate');
    if (lastUpdateEl) {
        lastUpdateEl.textContent = timeString;
    }

    // Durasi stopwatch untuk sesi keluar yang sedang aktif (kartu kiri)
    // Pakai ISO 8601 (toIso8601String) supaya JS parsing waktu absolut yang
    // benar, tidak ambigu terhadap timezone browser/server (root cause bug
    // "durasi loncat jam" sebelumnya)
    @if($sedangKeluar)
    const checkoutTime = new Date('{{ $sedangKeluar->jam_keluar->toIso8601String() }}');
    const diffMs = now - checkoutTime;
    const totalSeconds = Math.max(0, Math.floor(diffMs / 1000));

    const durationEl = document.getElementById('durationTimer');
    if (durationEl) {
        durationEl.textContent = formatDuration(totalSeconds);
    }
    @endif

    // Durasi berjalan untuk semua entri "Masih keluar" di tabel riwayat
    document.querySelectorAll('[data-checkout]').forEach(el => {
        const checkoutTime = new Date(el.getAttribute('data-checkout'));
        const diffMs = now - checkoutTime;
        const totalSeconds = Math.max(0, Math.floor(diffMs / 1000));

        el.textContent = formatDuration(totalSeconds);
        el.classList.add('text-red-600', 'font-semibold');
    });
}

// Update setiap detik
setInterval(updateRealTime, 1000);

// Panggilan awal
updateRealTime();
</script>
@endpush
@endsection