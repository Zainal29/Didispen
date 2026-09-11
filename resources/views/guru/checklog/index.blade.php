@extends('guru.layouts.app')
@section('title', 'Check In/Out')
@section('page-title', 'Catatan Keluar & Kembali')
@section('content')
@include('components.alert')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- KOLOM KIRI: FORM / STOPWATCH --}}
    <div class="lg:col-span-1 space-y-4">
        @if(!$sedangKeluar)
            {{-- FORM CATAT KELUAR --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-4 border-b border-gray-200 bg-gray-50/50 flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Catat Keluar</h3>
                        <p class="text-[11px] text-gray-500">Isi data sebelum meninggalkan sekolah.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('guru.checklog.store') }}" class="p-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Alasan Keluar <span class="text-red-500">*</span></label>
                        <textarea name="alasan" required rows="3" placeholder="Contoh: Urusan keluarga mendadak"
                                  class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Tujuan <span class="text-red-500">*</span></label>
                        <input type="text" name="tujuan" required placeholder="Contoh: Bank BRI Cabang Pusat"
                               class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Lokasi <span class="text-gray-400 font-normal">(Opsional)</span></label>
                        <input type="text" name="lokasi" placeholder="Contoh: Jl. Sudirman No. 10"
                               class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
                    </div>
                    <button type="submit"
                            class="w-full inline-flex justify-center items-center px-4 py-3 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i>Catat Saya Keluar
                    </button>
                </form>
            </div>
        @else
            {{-- STOPWATCH SEDANG KELUAR --}}
            <div class="bg-white border border-red-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-red-50 border-b border-red-100 flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Anda Sedang Keluar</h3>
                        <p class="text-[11px] text-red-600">Durasi berjalan real-time.</p>
                    </div>
                </div>
                <div class="p-4">
                    {{-- Timer Besar --}}
                    <div class="text-center mb-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-red-600 mb-2">
                            <i class="fas fa-stopwatch mr-1"></i>Durasi Keluar
                        </p>
                        <div class="bg-red-50 border border-red-100 rounded-lg p-4">
                            <p class="text-3xl sm:text-4xl font-bold text-red-700 font-mono" id="durationTimer">00:00:00</p>
                        </div>
                        <p class="text-xs text-red-600 mt-2">
                            Mulai: {{ $sedangKeluar->jam_keluar->timezone('Asia/Jakarta')->format('H:i') }}
                        </p>
                    </div>
                    {{-- Detail Keluar --}}
                    <div class="space-y-2 text-xs mb-4">
                        <div class="flex justify-between gap-2">
                            <span class="text-red-600"><i class="fas fa-map-marker-alt mr-1"></i>Tujuan</span>
                            <span class="font-semibold text-gray-900 text-right">{{ $sedangKeluar->tujuan }}</span>
                        </div>
                        @if($sedangKeluar->lokasi)
                            <div class="flex justify-between gap-2">
                                <span class="text-red-600"><i class="fas fa-location-arrow mr-1"></i>Lokasi</span>
                                <span class="font-semibold text-gray-900 text-right">{{ $sedangKeluar->lokasi }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between gap-2">
                            <span class="text-red-600"><i class="fas fa-info-circle mr-1"></i>Alasan</span>
                            <span class="font-semibold text-gray-900 text-right max-w-[60%]">{{ Str::limit($sedangKeluar->alasan, 30) }}</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('guru.checklog.checkin', $sedangKeluar) }}">
                        @csrf
                        <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-3 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>Catat Saya Kembali
                        </button>
                    </form>
                </div>
            </div>

            {{-- JAM SEKARANG --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-600 mb-1">Waktu Sekarang</p>
                        <p class="text-xl font-bold text-blue-700 font-mono" id="currentTime">
                            {{ now()->timezone('Asia/Jakarta')->format('H:i:s') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold text-gray-700" id="currentDate">
                            {{ now()->timezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- KOLOM KANAN: RIWAYAT --}}
    <div class="lg:col-span-2">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50/50 flex justify-between items-center gap-2">
                <h3 class="text-sm font-bold text-gray-900">
                    <i class="fas fa-history text-gray-400 mr-1.5"></i>Riwayat 10 Terakhir
                </h3>
                <div class="text-xs text-gray-500 font-semibold">
                    <i class="fas fa-sync-alt mr-1 text-blue-500"></i>Update: <span id="lastUpdate" class="font-mono">{{ now()->timezone('Asia/Jakarta')->format('H:i:s') }}</span>
                </div>
            </div>

            {{-- MOBILE: Kartu --}}
            <div class="md:hidden divide-y divide-gray-100">
                @forelse($riwayat as $log)
                    <div class="p-4 {{ $log->status === 'keluar' ? 'bg-red-50' : '' }}">
                        <div class="flex justify-between items-start gap-2 mb-1.5">
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $log->tujuan }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $log->jam_keluar->timezone('Asia/Jakarta')->format('d/m/Y') }} • {{ $log->jam_keluar->timezone('Asia/Jakarta')->isoFormat('dddd') }}
                                </p>
                            </div>
                            @if($log->status === 'keluar')
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-red-100 text-red-700 flex-shrink-0">
                                    <i class="fas fa-circle text-[6px] mr-1"></i>Sedang Keluar
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-emerald-100 text-emerald-700 flex-shrink-0">
                                    <i class="fas fa-check-circle mr-1"></i>Selesai
                                </span>
                            @endif
                        </div>
                        @if($log->lokasi)
                            <p class="text-xs text-gray-500 mb-1.5"><i class="fas fa-map-marker-alt mr-1"></i>{{ $log->lokasi }}</p>
                        @endif
                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div class="px-2 py-1.5 rounded-lg bg-blue-50 border border-blue-100">
                                <p class="text-blue-600 text-[9px] font-bold uppercase"><i class="fas fa-door-open mr-0.5"></i>Keluar</p>
                                <p class="font-mono font-semibold text-blue-800">{{ $log->jam_keluar->timezone('Asia/Jakarta')->format('H:i') }}</p>
                            </div>
                            <div class="px-2 py-1.5 rounded-lg bg-emerald-50 border border-emerald-100">
                                <p class="text-emerald-600 text-[9px] font-bold uppercase"><i class="fas fa-door-closed mr-0.5"></i>Kembali</p>
                                @if($log->jam_kembali)
                                    <p class="font-mono font-semibold text-emerald-800">{{ $log->jam_kembali->timezone('Asia/Jakarta')->format('H:i') }}</p>
                                @else
                                    <p class="font-mono font-semibold text-gray-400">–</p>
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
                                    <p class="font-mono font-semibold text-emerald-700">{{ sprintf('%02d:%02d:%02d', $h, $m, $s) }}</p>
                                @else
                                    <p class="font-mono font-semibold text-red-600" data-checkout="{{ $log->jam_keluar->toIso8601String() }}">00:00:00</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center">
                        <div class="w-16 h-16 mx-auto rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center text-2xl mb-3"><i class="fas fa-history"></i></div>
                        <p class="text-gray-700 font-semibold text-sm">Belum ada riwayat keluar/masuk</p>
                    </div>
                @endforelse
            </div>

            {{-- DESKTOP: Tabel --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="p-4 text-left font-semibold">Tanggal</th>
                            <th class="p-4 text-left font-semibold">Tujuan</th>
                            <th class="p-4 text-left font-semibold"><i class="fas fa-door-open mr-1"></i>Jam Keluar</th>
                            <th class="p-4 text-left font-semibold"><i class="fas fa-door-closed mr-1"></i>Jam Kembali</th>
                            <th class="p-4 text-left font-semibold">Durasi</th>
                            <th class="p-4 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($riwayat as $log)
                            <tr class="hover:bg-gray-50 transition-colors {{ $log->status === 'keluar' ? 'bg-red-50' : '' }}">
                                <td class="p-4">
                                    <div class="font-semibold text-gray-900">{{ $log->jam_keluar->timezone('Asia/Jakarta')->format('d/m/Y') }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $log->jam_keluar->timezone('Asia/Jakarta')->isoFormat('dddd') }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="font-semibold text-gray-900">{{ $log->tujuan }}</div>
                                    @if($log->lokasi)
                                        <div class="text-xs text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i>{{ $log->lokasi }}</div>
                                    @endif
                                </td>
                                <td class="p-4 font-mono font-semibold text-blue-700 text-xs">
                                    {{ $log->jam_keluar->timezone('Asia/Jakarta')->format('H:i') }}
                                </td>
                                <td class="p-4 font-mono text-xs">
                                    @if($log->jam_kembali)
                                        <span class="font-semibold text-emerald-700">{{ $log->jam_kembali->timezone('Asia/Jakarta')->format('H:i') }}</span>
                                    @else
                                        <span class="text-gray-400 font-semibold">–</span>
                                        <span class="text-xs text-red-500 block">Masih keluar</span>
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
                                        <span class="font-semibold text-emerald-700">{{ sprintf('%02d:%02d:%02d', $h, $m, $s) }}</span>
                                    @else
                                        <span class="text-red-600 font-semibold" data-checkout="{{ $log->jam_keluar->toIso8601String() }}">00:00:00</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($log->status === 'keluar')
                                        <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-red-100 text-red-700 inline-flex items-center">
                                            <span class="w-2 h-2 bg-red-600 rounded-full mr-2"></span>Sedang Keluar
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-700 inline-flex items-center">
                                            <i class="fas fa-check-circle mr-1.5"></i>Selesai
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-12 text-center">
                                    <div class="w-16 h-16 mx-auto rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center text-2xl mb-3"><i class="fas fa-history"></i></div>
                                    <p class="text-gray-700 font-semibold text-sm">Belum ada riwayat keluar/masuk</p>
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
    return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
}

function updateRealTime() {
    const now = getSyncedNow();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const timeString = `${hours}:${minutes}:${seconds}`;
    const dateString = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

    const currentTimeEl = document.getElementById('currentTime');
    if (currentTimeEl) currentTimeEl.textContent = timeString;

    const currentDateEl = document.getElementById('currentDate');
    if (currentDateEl) currentDateEl.textContent = dateString;

    const lastUpdateEl = document.getElementById('lastUpdate');
    if (lastUpdateEl) lastUpdateEl.textContent = timeString;

    @if($sedangKeluar)
    const checkoutTime = new Date('{{ $sedangKeluar->jam_keluar->toIso8601String() }}');
    const diffMs = now - checkoutTime;
    const totalSeconds = Math.max(0, Math.floor(diffMs / 1000));
    const durationEl = document.getElementById('durationTimer');
    if (durationEl) durationEl.textContent = formatDuration(totalSeconds);
    @endif

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
