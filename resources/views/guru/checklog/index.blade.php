@extends('guru.layouts.app')

@section('title', 'Check In/Out')
@section('page-title', 'Catatan Keluar & Kembali')

@section('content')
@include('components.alert')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- ================================================== --}}
    {{-- KOLOM KIRI: FORM / STOPWATCH                       --}}
    {{-- ================================================== --}}
    <div class="lg:col-span-1 space-y-4">

        @if(!$sedangKeluar)
            {{-- ============ FORM CATAT KELUAR ============ --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 py-4 sm:px-5 sm:py-5 border-b border-gray-100 flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-sky-500 text-white flex items-center justify-center shadow-md shadow-blue-500/30 flex-shrink-0">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-blue-700">Catat Keluar</h3>
                        <p class="text-[11px] text-gray-500">Isi data sebelum meninggalkan sekolah.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('guru.checklog.store') }}" class="p-4 sm:p-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Alasan Keluar <span class="text-red-500">*</span></label>
                        <textarea name="alasan" required rows="3" placeholder="Contoh: Urusan keluarga mendadak"
                                  class="w-full px-3.5 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-sm font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Tujuan <span class="text-red-500">*</span></label>
                        <input type="text" name="tujuan" required placeholder="Contoh: Bank BRI Cabang Pusat"
                               class="w-full h-11 px-3.5 rounded-xl border-2 border-gray-200 bg-white text-sm font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Lokasi <span class="text-gray-400 font-normal">(Opsional)</span></label>
                        <input type="text" name="lokasi" placeholder="Contoh: Jl. Sudirman No. 10"
                               class="w-full h-11 px-3.5 rounded-xl border-2 border-gray-200 bg-white text-sm font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all">
                    </div>
                    <button type="submit"
                            class="w-full inline-flex justify-center items-center px-4 py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 shadow-lg shadow-red-500/30 active:scale-[0.98] transition-all">
                        <i class="fas fa-sign-out-alt mr-2"></i>Catat Saya Keluar
                    </button>
                </form>
            </div>
        @else
            {{-- ============ STOPWATCH SEDANG KELUAR ============ --}}
            <div class="bg-white rounded-2xl border-2 border-red-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-gradient-to-r from-red-50 to-amber-50 border-b border-red-100 flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-red-500 text-white flex items-center justify-center flex-shrink-0 animate-pulse">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-red-700">Anda Sedang Keluar</h3>
                        <p class="text-[11px] text-red-600">Durasi berjalan real-time.</p>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    {{-- Timer Besar --}}
                    <div class="text-center mb-4">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-red-600 mb-2">
                            <i class="fas fa-stopwatch mr-1"></i>Durasi Keluar
                        </p>
                        <div class="bg-red-50 border border-red-100 rounded-2xl p-4 shadow-inner">
                            <p class="text-4xl sm:text-5xl font-black text-red-700 font-mono tracking-tight" id="durationTimer">00.00.00</p>
                        </div>
                        <p class="text-[11px] text-red-600 mt-2">
                            Mulai: {{ str_replace(':', '.', $sedangKeluar->jam_keluar->timezone('Asia/Jakarta')->format('H:i:s')) }}
                        </p>
                    </div>

                    {{-- Detail Keluar --}}
                    <div class="space-y-2 text-xs mb-4">
                        <div class="flex justify-between gap-2">
                            <span class="text-red-600"><i class="fas fa-map-marker-alt mr-1"></i>Tujuan</span>
                            <span class="font-bold text-gray-800 text-right">{{ $sedangKeluar->tujuan }}</span>
                        </div>
                        @if($sedangKeluar->lokasi)
                            <div class="flex justify-between gap-2">
                                <span class="text-red-600"><i class="fas fa-location-arrow mr-1"></i>Lokasi</span>
                                <span class="font-bold text-gray-800 text-right">{{ $sedangKeluar->lokasi }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between gap-2">
                            <span class="text-red-600"><i class="fas fa-info-circle mr-1"></i>Alasan</span>
                            <span class="font-bold text-gray-800 text-right max-w-[60%]">{{ Str::limit($sedangKeluar->alasan, 30) }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('guru.checklog.checkin', $sedangKeluar) }}">
                        @csrf
                        <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 shadow-lg shadow-emerald-500/30 active:scale-[0.98] transition-all">
                            <i class="fas fa-sign-in-alt mr-2"></i>Catat Saya Kembali
                        </button>
                    </form>
                </div>
            </div>

            {{-- ============ JAM SEKARANG ============ --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-500 mb-1">Waktu Sekarang</p>
                        <p class="text-2xl font-black text-blue-700 font-mono" id="currentTime">
                            {{ str_replace(':', '.', now()->timezone('Asia/Jakarta')->format('H:i:s')) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-[11px] font-semibold text-gray-600" id="currentDate">
                            {{ now()->timezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ================================================== --}}
    {{-- KOLOM KANAN: RIWAYAT                               --}}
    {{-- ================================================== --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            <div class="px-4 py-3 sm:p-5 border-b border-gray-100 flex justify-between items-center gap-2">
                <h3 class="text-sm font-bold text-gray-900">
                    <i class="fas fa-history mr-1.5 text-blue-600"></i>Riwayat 10 Terakhir
                </h3>
                <div class="text-[11px] text-gray-500 font-semibold">
                    <i class="fas fa-sync-alt mr-1 text-blue-500"></i>Update: <span id="lastUpdate" class="font-mono">{{ str_replace(':', '.', now()->timezone('Asia/Jakarta')->format('H:i:s')) }}</span>
                </div>
            </div>

            {{-- MOBILE: Kartu --}}
            <div class="md:hidden divide-y divide-gray-100">
                @forelse($riwayat as $log)
                    <div class="p-4 {{ $log->status === 'keluar' ? 'bg-red-50/50' : '' }}">
                        <div class="flex justify-between items-start gap-2 mb-1.5">
                            <div class="min-w-0">
                                <p class="font-bold text-gray-900 text-sm truncate">{{ $log->tujuan }}</p>
                                <p class="text-[11px] text-gray-500">
                                    {{ $log->jam_keluar->timezone('Asia/Jakarta')->format('d/m/Y') }} • {{ $log->jam_keluar->timezone('Asia/Jakarta')->isoFormat('dddd') }}
                                </p>
                            </div>
                            @if($log->status === 'keluar')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-700 border border-red-200 animate-pulse flex-shrink-0">
                                    ● Sedang Keluar
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 flex-shrink-0">
                                    <i class="fas fa-check-circle mr-1"></i>Selesai
                                </span>
                            @endif
                        </div>

                        @if($log->lokasi)
                            <p class="text-[11px] text-gray-500 mb-1.5"><i class="fas fa-map-marker-alt mr-1"></i>{{ $log->lokasi }}</p>
                        @endif

                        <div class="grid grid-cols-3 gap-2 text-[11px]">
                            <div class="px-2 py-1.5 rounded-lg bg-blue-50 border border-blue-100">
                                <p class="text-blue-500 text-[9px] font-bold uppercase"><i class="fas fa-door-open mr-0.5"></i>Keluar</p>
                                <p class="font-mono font-bold text-blue-800">{{ str_replace(':', '.', $log->jam_keluar->timezone('Asia/Jakarta')->format('H:i:s')) }}</p>
                            </div>
                            <div class="px-2 py-1.5 rounded-lg bg-emerald-50 border border-emerald-100">
                                <p class="text-emerald-500 text-[9px] font-bold uppercase"><i class="fas fa-door-closed mr-0.5"></i>Kembali</p>
                                @if($log->jam_kembali)
                                    <p class="font-mono font-bold text-emerald-800">{{ str_replace(':', '.', $log->jam_kembali->timezone('Asia/Jakarta')->format('H:i:s')) }}</p>
                                @else
                                    <p class="font-mono font-bold text-gray-400">–</p>
                                @endif
                            </div>
                            <div class="px-2 py-1.5 rounded-lg bg-gray-50 border border-gray-100">
                                <p class="text-gray-400 text-[9px] font-bold uppercase">Durasi</p>
                                @if($log->jam_kembali)
                                    @php
                                        $diff = $log->jam_keluar->diff($log->jam_kembali);
                                        $totalSeconds = ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
                                        $h = floor($totalSeconds / 3600);
                                        $m = floor(($totalSeconds % 3600) / 60);
                                        $s = $totalSeconds % 60;
                                    @endphp
                                    <p class="font-mono font-bold text-emerald-700">{{ sprintf('%02d.%02d.%02d', $h, $m, $s) }}</p>
                                @else
                                    <p class="font-mono font-bold text-red-600" data-checkout="{{ $log->jam_keluar->toIso8601String() }}">00.00.00</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-300 flex items-center justify-center text-2xl mb-3"><i class="fas fa-history"></i></div>
                        <p class="text-gray-500 font-semibold text-sm">Belum ada riwayat keluar/masuk</p>
                    </div>
                @endforelse
            </div>

            {{-- DESKTOP: Tabel --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="p-4 text-left">Tanggal</th>
                            <th class="p-4 text-left">Tujuan</th>
                            <th class="p-4 text-left"><i class="fas fa-door-open mr-1"></i>Jam Keluar</th>
                            <th class="p-4 text-left"><i class="fas fa-door-closed mr-1"></i>Jam Kembali</th>
                            <th class="p-4 text-left">Durasi</th>
                            <th class="p-4 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($riwayat as $log)
                            <tr class="hover:bg-blue-50/40 transition-colors {{ $log->status === 'keluar' ? 'bg-red-50/50' : '' }}">
                                <td class="p-4">
                                    <div class="font-semibold text-gray-800">{{ $log->jam_keluar->timezone('Asia/Jakarta')->format('d/m/Y') }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $log->jam_keluar->timezone('Asia/Jakarta')->isoFormat('dddd') }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="font-semibold text-gray-800">{{ $log->tujuan }}</div>
                                    @if($log->lokasi)
                                        <div class="text-[11px] text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i>{{ $log->lokasi }}</div>
                                    @endif
                                </td>
                                <td class="p-4 font-mono font-semibold text-blue-700 text-xs">
                                    {{ str_replace(':', '.', $log->jam_keluar->timezone('Asia/Jakarta')->format('H:i:s')) }}
                                </td>
                                <td class="p-4 font-mono text-xs">
                                    @if($log->jam_kembali)
                                        <span class="font-semibold text-emerald-700">{{ str_replace(':', '.', $log->jam_kembali->timezone('Asia/Jakarta')->format('H:i:s')) }}</span>
                                    @else
                                        <span class="text-gray-400 font-semibold">–</span>
                                        <span class="text-[11px] text-red-500 block">Masih keluar</span>
                                    @endif
                                </td>
                                <td class="p-4 font-mono text-xs">
                                    @if($log->jam_kembali)
                                        @php
                                            $diff = $log->jam_keluar->diff($log->jam_kembali);
                                            $totalSeconds = ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
                                            $h = floor($totalSeconds / 3600);
                                            $m = floor(($totalSeconds % 3600) / 60);
                                            $s = $totalSeconds % 60;
                                        @endphp
                                        <span class="font-semibold text-emerald-700">{{ sprintf('%02d.%02d.%02d', $h, $m, $s) }}</span>
                                    @else
                                        <span class="text-red-600 font-semibold" data-checkout="{{ $log->jam_keluar->toIso8601String() }}">00.00.00</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($log->status === 'keluar')
                                        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-red-100 text-red-700 border border-red-200 animate-pulse inline-flex items-center">
                                            <span class="w-2 h-2 bg-red-600 rounded-full mr-2 animate-ping"></span>Sedang Keluar
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 inline-flex items-center">
                                            <i class="fas fa-check-circle mr-1.5"></i>Selesai
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-12 text-center">
                                    <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-300 flex items-center justify-center text-2xl mb-3"><i class="fas fa-history"></i></div>
                                    <p class="text-gray-500 font-semibold text-sm">Belum ada riwayat keluar/masuk</p>
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
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });

    const currentTimeEl = document.getElementById('currentTime');
    if (currentTimeEl) currentTimeEl.textContent = timeString;

    const currentDateEl = document.getElementById('currentDate');
    if (currentDateEl) currentDateEl.textContent = dateString;

    const lastUpdateEl = document.getElementById('lastUpdate');
    if (lastUpdateEl) lastUpdateEl.textContent = timeString;

    // Durasi stopwatch untuk sesi keluar yang sedang aktif
    @if($sedangKeluar)
    const checkoutTime = new Date('{{ $sedangKeluar->jam_keluar->toIso8601String() }}');
    const diffMs = now - checkoutTime;
    const totalSeconds = Math.max(0, Math.floor(diffMs / 1000));

    const durationEl = document.getElementById('durationTimer');
    if (durationEl) durationEl.textContent = formatDuration(totalSeconds);
    @endif

    // Durasi berjalan untuk semua entri "Masih keluar" di riwayat
    document.querySelectorAll('[data-checkout]').forEach(el => {
        const checkoutTime = new Date(el.getAttribute('data-checkout'));
        const diffMs = now - checkoutTime;
        const totalSeconds = Math.max(0, Math.floor(diffMs / 1000));
        el.textContent = formatDuration(totalSeconds);
        el.classList.add('text-red-600', 'font-semibold');
    });
}

setInterval(updateRealTime, 1000);
updateRealTime();
</script>
@endpush
@endsection