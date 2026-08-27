@extends('satpam.layouts.app')

@section('title', 'Dashboard Satpam')
@section('page-title', 'Dashboard Satpam')

@section('content')
@include('components.alert')

@php
    // ✅ HITUNG JUMLAH TERLAMBAT DI PHP (Lebih akurat daripada JS, mencegah double count)
    $terlambatCount = 0;
    foreach($siswaKeluar as $d) {
        if ($d->batas_waktu_kembali && now()->greaterThan($d->batas_waktu_kembali)) {
            $terlambatCount++;
        }
    }
@endphp

{{-- ============ HERO SECTION ============ --}}
<div class="relative overflow-hidden rounded-2xl mb-4">
    <div class="absolute inset-0 bg-gradient-to-r from-red-700 via-red-600 to-rose-500"></div>
    <div class="absolute -top-16 -right-16 w-56 h-56 bg-white/10 rounded-full"></div>
    <div class="absolute -bottom-20 left-20 w-60 h-60 bg-white/10 rounded-full"></div>

    <div class="relative z-10 p-4 sm:p-6 flex items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="text-red-100 text-[10px] sm:text-[11px] font-bold uppercase tracking-widest">
                {{ now()->isoFormat('dddd, D MMMM Y') }} • Pos Gerbang
            </p>
            <h2 class="text-lg sm:text-2xl font-black text-white tracking-tight mt-0.5 truncate">
                Halo, {{ auth()->user()->name }} 👋
            </h2>
            <p class="text-red-100 text-[11px] mt-1 hidden sm:block">Pantau keluar-masuk siswa dispensasi hari ini dengan mudah.</p>
        </div>
        <a href="{{ route('satpam.scan') }}" class="hidden sm:inline-flex items-center px-5 py-3 rounded-xl bg-white text-red-700 text-sm font-bold shadow-xl hover:-translate-y-0.5 transition-all flex-shrink-0">
            <i class="fas fa-qrcode mr-2"></i> Scan QR
        </a>
    </div>
</div>

{{-- ============ FILTER TABS ============ --}}
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-4">
    <div class="p-2 flex flex-wrap gap-2">
        <button onclick="switchTab('all')" id="tab-all" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition-all bg-red-600 text-white shadow-md">
            <i class="fas fa-layer-group mr-1.5"></i>Semua
        </button>
        <button onclick="switchTab('menunggu')" id="tab-menunggu" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200">
            <i class="fas fa-clock mr-1.5"></i>Menunggu <span id="badge-menunggu" class="px-2 py-0.5 bg-amber-500 text-white text-[10px] rounded-full ml-1">{{ $menungguKeluar->count() }}</span>
        </button>
        <button onclick="switchTab('keluar')" id="tab-keluar" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200">
            <i class="fas fa-person-walking mr-1.5"></i>Keluar <span id="badge-keluar" class="px-2 py-0.5 bg-sky-500 text-white text-[10px] rounded-full ml-1">{{ $siswaKeluar->count() }}</span>
        </button>
        {{-- ✅ BADGE TERLAMBAT DIHITUNG DARI PHP --}}
        <button onclick="switchTab('terlambat')" id="tab-terlambat" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200">
            <i class="fas fa-exclamation-triangle mr-1.5"></i>Terlambat <span id="badge-terlambat" class="px-2 py-0.5 bg-red-500 text-white text-[10px] rounded-full ml-1">{{ $terlambatCount }}</span>
        </button>
        <button onclick="switchTab('selesai')" id="tab-selesai" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200">
            <i class="fas fa-check-circle mr-1.5"></i>Selesai <span id="badge-selesai" class="px-2 py-0.5 bg-emerald-500 text-white text-[10px] rounded-full ml-1">{{ $stats['selesai'] ?? 0 }}</span>
        </button>
        <button onclick="switchTab('dihubungi')" id="tab-dihubungi" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold transition-all bg-gray-100 text-gray-600 hover:bg-gray-200">
            <i class="fas fa-phone-alt mr-1.5"></i>Dihubungi <span id="badge-dihubungi" class="px-2 py-0.5 bg-purple-500 text-white text-[10px] rounded-full ml-1">{{ $dihubungi->count() }}</span>
        </button>
    </div>
</div>

{{-- ============ CONTENT SECTIONS ============ --}}

{{-- SECTION: MENUNGGU KELUAR --}}
<div id="section-menunggu" class="space-y-3 hidden">
    <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-clock text-amber-500 mr-1.5"></i>Menunggu Konfirmasi Keluar</h3>
    @foreach($menungguKeluar as $dispensasi)
        @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'menunggu', 'isOverdue' => false])
    @endforeach
</div>

{{-- SECTION: SEDANG KELUAR --}}
<div id="section-keluar" class="space-y-3 hidden">
    <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-person-walking text-sky-500 mr-1.5"></i>Sedang Keluar</h3>
    @foreach($siswaKeluar as $dispensasi)
        @php $isOverdue = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali); @endphp
        @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'keluar', 'isOverdue' => $isOverdue])
    @endforeach
</div>

{{-- ✅ SECTION: TERLAMBAT (Dedicated & Clean) --}}
<div id="section-terlambat" class="space-y-3 hidden">
    <h3 class="text-sm font-bold text-gray-700 mb-3">
        <i class="fas fa-exclamation-triangle text-red-500 mr-1.5"></i>Siswa Terlambat
    </h3>
    
    @php $renderedTerlambat = 0; @endphp
    @foreach($siswaKeluar as $dispensasi)
        @php
            $isOverdue = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);
            if (!$isOverdue) continue; // Skip jika tidak terlambat
            
            $renderedTerlambat++;
            $lateMinutes = now()->diffInMinutes($dispensasi->batas_waktu_kembali);
            $lateHours = floor($lateMinutes / 60);
            $lateRemainingMins = $lateMinutes % 60;
            $lateText = $lateHours > 0 ? "{$lateHours}j {$lateRemainingMins}m" : "{$lateMinutes}m";
            
            // TimeHelper untuk tampilan jam aktual
            $waktuKeluarAktual = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar);
            $waktuKembaliAktual = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali);
        @endphp
        
        <div class="bg-white rounded-2xl border-2 border-red-200 shadow-sm overflow-hidden"
             data-dispensasi="{{ $dispensasi->id }}"
             data-status="terlambat"
             data-overdue="true"
             data-deadline="{{ $dispensasi->batas_waktu_kembali->format('Y-m-d H:i:s') }}">
            
            {{-- Header --}}
            <div class="px-4 py-3.5 bg-red-50 border-b border-red-100">
                <div class="flex justify-between items-start gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <p class="font-mono font-bold text-gray-800 text-xs">{{ $dispensasi->nomor_surat }}</p>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700 uppercase animate-pulse">
                                <i class="fas fa-exclamation-triangle mr-1"></i>TERLAMBAT {{ $lateText }}
                            </span>
                            @if($dispensasi->is_warned)
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-700 uppercase">
                                    <i class="fas fa-phone-alt mr-1"></i>DIHUBUNGI
                                </span>
                            @endif
                        </div>
                        <p class="font-bold text-gray-900 text-sm truncate">{{ $dispensasi->siswa->nama_lengkap }}</p>
                        <p class="text-[11px] text-gray-500 truncate">
                            {{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }} • {{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}
                        </p>
                    </div>
                    {{-- ✅ TOMBOL MATA (DETAIL) --}}
                    <a href="{{ route('satpam.dispensasi.detail', $dispensasi) }}"
                       class="inline-flex items-center justify-center w-9 h-9 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors flex-shrink-0" 
                       title="Lihat Detail Dispensasi">
                        <i class="fas fa-eye text-sm"></i>
                    </a>
                </div>
            </div>

            {{-- Detail & Aksi --}}
            <div class="p-4 space-y-3 bg-gray-50/50">
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="bg-white p-2.5 rounded-lg border border-gray-200">
                        <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Jam Keluar</p>
                        <p class="font-bold text-gray-800">{{ $dispensasi->jam_keluar }}</p>
                        @if($waktuKeluarAktual !== '-')
                            <p class="text-[10px] text-blue-600 mt-0.5 font-mono flex items-center"><i class="far fa-clock mr-1"></i> {{ $waktuKeluarAktual }}</p>
                        @endif
                    </div>
                    <div class="bg-white p-2.5 rounded-lg border border-gray-200">
                        <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Jam Kembali</p>
                        <p class="font-bold text-red-700">{{ $dispensasi->jam_kembali }}</p>
                        @if($waktuKembaliAktual !== '-')
                            <p class="text-[10px] text-red-600 mt-0.5 font-mono flex items-center"><i class="far fa-clock mr-1"></i> {{ $waktuKembaliAktual }}</p>
                        @endif
                    </div>
                </div>

                {{-- ✅ KONTAK DARURAT & WHATSAPP (Hanya untuk status keluar/terlambat) --}}
                @if(!empty($dispensasi->siswa->no_telepon))
                    @php
                        $hp = preg_replace('/[^0-9]/', '', $dispensasi->siswa->no_telepon);
                        if (str_starts_with($hp, '0')) $hp = '62' . substr($hp, 1);
                        $pesan = "⚠️ *PERINGATAN KETERLAMBATAN* ⚠️\n\nYth. *{$dispensasi->siswa->nama_lengkap}*,\nBatas waktu kembali dispensasi Anda telah **LEWAT** sejak {$lateText} yang lalu.\n\n📍 Tujuan: {$dispensasi->tujuan}\n⚠️ **SEGERA KEMBALI** ke sekolah atau lapor ke Pos Satpam.\n\nTerima kasih,\n*Petugas Satpam SMKN 1 Bangsri*";
                        $waLink = "https://wa.me/{$hp}?text=" . urlencode($pesan);
                    @endphp
                    
                    <div id="wa-section-{{ $dispensasi->id }}" class="bg-green-50 border-2 border-green-200 rounded-xl p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="text-[10px] font-bold text-green-700 uppercase">Kontak Darurat</p>
                                <p class="text-sm font-bold text-gray-800 font-mono">{{ $dispensasi->siswa->no_telepon }}</p>
                            </div>
                            <button onclick="handleWaContacted({{ $dispensasi->id }}, '{{ $waLink }}')" 
                                    class="inline-flex items-center justify-center w-12 h-12 bg-green-500 hover:bg-green-600 text-white rounded-xl transition-all active:scale-95 shadow-md shadow-green-500/30" 
                                    title="Hubungi via WhatsApp">
                                <i class="fab fa-whatsapp text-xl"></i>
                            </button>
                        </div>
                        <p class="text-[10px] text-green-600"><i class="fas fa-info-circle mr-1"></i>Klik untuk hubungi & tandai sudah dihubungi</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('satpam.konfirmasi.kembali', $dispensasi) }}">
                    @csrf
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all">
                        <i class="fas fa-door-closed mr-1.5"></i>Konfirmasi Kembali (Manual)
                    </button>
                </form>
            </div>
        </div>
    @endforeach
    
    @if($renderedTerlambat === 0)
        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
            <i class="fas fa-check-circle text-4xl text-emerald-300 mb-3"></i>
            <p class="text-gray-500 text-sm">Tidak ada siswa yang terlambat hari ini</p>
        </div>
    @endif
</div>

{{-- SECTION: SELESAI --}}
<div id="section-selesai" class="space-y-3 hidden">
    <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-check-circle text-emerald-500 mr-1.5"></i>Sudah Kembali</h3>
    @if(isset($selesai) && $selesai->count() > 0)
        @foreach($selesai as $dispensasi)
            @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'selesai', 'isOverdue' => false])
        @endforeach
    @else
        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-50 text-emerald-300 flex items-center justify-center text-3xl mb-3"><i class="fas fa-check-circle"></i></div>
            <p class="text-gray-500 text-sm font-semibold">Belum ada siswa yang kembali hari ini</p>
        </div>
    @endif
</div>

{{-- ✅ SECTION: SUDAH DIHUBUNGI (HANYA HARI INI) --}}
<div id="section-dihubungi" class="space-y-3 hidden">
    <h3 class="text-sm font-bold text-gray-700 mb-3">
        <i class="fas fa-phone-alt text-purple-500 mr-1.5"></i>
        Riwayat Siswa yang Sudah Dihubungi
        <span class="text-xs font-normal text-gray-500 ml-2">({{ $dihubungi->count() }} siswa hari ini)</span>
    </h3>

    @if($dihubungi->count() > 0)
        @foreach($dihubungi as $dispensasi)
            @php
                $isOverdue = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);
            @endphp

            <div class="bg-white rounded-2xl border-2 border-purple-200 shadow-sm overflow-hidden" data-status="dihubungi" data-warned="true">
                <div class="px-4 py-3.5 bg-purple-50 border-b border-purple-100">
                    <div class="flex justify-between items-start gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="font-mono font-bold text-gray-800 text-xs">{{ $dispensasi->nomor_surat }}</p>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-700 uppercase">
                                    <i class="fas fa-phone-alt mr-1"></i>DIHUBUNGI
                                </span>
                            </div>
                            <p class="font-bold text-gray-900 text-sm truncate">{{ $dispensasi->siswa->nama_lengkap }}</p>
                            <p class="text-[11px] text-gray-500 truncate">
                                {{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }} • {{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}
                            </p>
                        </div>
                        <a href="{{ route('satpam.dispensasi.detail', $dispensasi) }}" class="inline-flex items-center justify-center w-9 h-9 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors" title="Lihat Detail">
                            <i class="fas fa-eye text-sm"></i>
                        </a>
                    </div>
                </div>

                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="bg-white p-2.5 rounded-lg border border-gray-200">
                            <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Jam Keluar</p>
                            <p class="font-bold text-blue-700">{{ $dispensasi->jam_keluar }}</p>
                        </div>
                        <div class="bg-white p-2.5 rounded-lg border border-gray-200">
                            <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Jam Kembali</p>
                            <p class="font-bold {{ $isOverdue ? 'text-red-700' : 'text-amber-700' }}">{{ $dispensasi->jam_kembali }}</p>
                        </div>
                    </div>

                    @if($dispensasi->warned_at)
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                        <p class="text-purple-700 text-xs font-bold">
                            <i class="fas fa-clock mr-1"></i> Dihubungi pada: {{ $dispensasi->warned_at->isoFormat('D MMMM Y, HH:mm') }} WIB
                        </p>
                    </div>
                    @endif

                    @if($dispensasi->status === 'keluar')
                        <form method="POST" action="{{ route('satpam.konfirmasi.kembali', $dispensasi) }}">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all">
                                <i class="fas fa-door-closed mr-1.5"></i>Konfirmasi Kembali
                            </button>
                        </form>
                    @elseif($dispensasi->status === 'selesai')
                        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-center">
                            <p class="text-emerald-700 text-xs font-bold"><i class="fas fa-check-circle mr-1"></i>Siswa Sudah Kembali</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-purple-50 text-purple-300 flex items-center justify-center text-3xl mb-3"><i class="fas fa-phone-slash"></i></div>
            <p class="text-gray-500 text-sm font-semibold">Belum ada siswa yang dihubungi hari ini</p>
        </div>
    @endif
</div>

{{-- SECTION: ALL (SEMUA) --}}
<div id="section-all" class="space-y-3">
    <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-layer-group text-gray-500 mr-1.5"></i>Semua Dispensasi Hari Ini</h3>
    @foreach($menungguKeluar as $dispensasi)
        @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'menunggu', 'isOverdue' => false])
    @endforeach
    @foreach($siswaKeluar as $dispensasi)
        @php $isOverdue = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali); @endphp
        @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'keluar', 'isOverdue' => $isOverdue])
    @endforeach
    @foreach($selesai as $dispensasi)
        @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'selesai', 'isOverdue' => false])
    @endforeach
</div>

@endsection

@push('scripts')
<script>
// ✅ FUNGSI TAB SWITCHING
function switchTab(tabName) {
    document.querySelectorAll('[id^="section-"]').forEach(section => section.classList.add('hidden'));
    document.querySelectorAll('[id^="tab-"]').forEach(tab => {
        tab.classList.remove('bg-red-600', 'text-white', 'shadow-md');
        tab.classList.add('bg-gray-100', 'text-gray-600');
    });

    document.getElementById('section-' + tabName).classList.remove('hidden');
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('bg-gray-100', 'text-gray-600');
    activeTab.classList.add('bg-red-600', 'text-white', 'shadow-md');
}

// ✅ FUNGSI BARU: Klik WA langsung tandai dihubungi & buka WA
function handleWaContacted(dispensasiId, waLink) {
    fetch(`/satpam/dispensasi/${dispensasiId}/wa-contacted`, {
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
            // Update UI menjadi "Sudah Dihubungi"
            const card = document.querySelector(`[data-dispensasi="${dispensasiId}"]`);
            if (card) {
                card.dataset.warned = 'true';
                const waSection = document.getElementById(`wa-section-${dispensasiId}`);
                if (waSection) {
                    waSection.innerHTML = `
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="text-[10px] font-bold text-green-700 uppercase">Kontak Darurat</p>
                                <p class="text-sm font-bold text-gray-800 font-mono">${waSection.querySelector('.font-mono').textContent}</p>
                            </div>
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-300 text-gray-500 rounded-xl cursor-not-allowed" title="Sudah dihubungi">
                                <i class="fas fa-check text-xl"></i>
                            </div>
                        </div>
                        <p class="text-[10px] text-green-600">
                            <i class="fas fa-check-circle mr-1"></i>Sudah dihubungi via WhatsApp
                        </p>
                    `;
                }
                
                // Tambah badge DIHUBUNGI di header kartu
                const badgeRow = card.querySelector('.flex.items-center.gap-2.mb-1');
                if (badgeRow && !badgeRow.querySelector('.warned-badge')) {
                    badgeRow.insertAdjacentHTML('beforeend', 
                        `<span class="warned-badge px-2 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-700 uppercase ml-1">
                            <i class="fas fa-phone-alt mr-1"></i>DIHUBUNGI
                        </span>`
                    );
                }
            }
            window.open(waLink, '_blank');
        } else {
            window.open(waLink, '_blank');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.open(waLink, '_blank');
    });
}

// ✅ COUNTDOWN REALTIME
function tickCountdowns() {
    document.querySelectorAll('.live-countdown[data-deadline]').forEach(el => {
        const deadline = new Date(el.dataset.deadline);
        const diffMs = deadline - new Date();

        if (diffMs <= 0) {
            const lateMin = Math.floor(-diffMs / 60000);
            const hrs = Math.floor(lateMin / 60);
            const mins = lateMin % 60;
            const lateText = hrs > 0 ? `${hrs}j ${mins}m` : `${lateMin}m`;
            
            el.textContent = `TERLAMBAT ${lateText}`;
            el.classList.remove('bg-amber-100', 'text-amber-700');
            el.classList.add('bg-red-100', 'text-red-700', 'animate-pulse');
        } else {
            const totalMin = Math.floor(diffMs / 60000);
            const hrs = Math.floor(totalMin / 60);
            const mins = totalMin % 60;
            const secs = Math.floor((diffMs % 60000) / 1000);
            el.textContent = hrs > 0 ? `Sisa ${hrs}j ${mins}m` : `Sisa ${mins}m ${secs}s`;
            if (totalMin <= 5) {
                el.classList.remove('bg-amber-100', 'text-amber-700');
                el.classList.add('bg-red-100', 'text-red-700');
            }
        }
    });
}

// ✅ WATCHER: Deteksi kartu yang baru melewati batas waktu
const overdueNotified = new Set();
function watchOverdue() {
    document.querySelectorAll('[data-status="keluar"][data-deadline][data-overdue="false"]').forEach(card => {
        const deadline = new Date(card.dataset.deadline);
        if (new Date() > deadline) {
            card.dataset.overdue = 'true';
            
            const nama = card.querySelector('.font-bold.text-gray-900')?.textContent.trim() || 'Siswa';
            if (!overdueNotified.has(card.dataset.dispensasi)) {
                overdueNotified.add(card.dataset.dispensasi);
                Swal.fire({
                    icon: 'warning',
                    title: '⚠️ Siswa Terlambat!',
                    text: `${nama} telah melewati batas waktu kembali.`,
                    confirmButtonColor: '#dc2626',
                    timer: 5000,
                    timerProgressBar: true
                });
            }
        }
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    tickCountdowns();
    setInterval(tickCountdowns, 1000);
    setInterval(watchOverdue, 15000);
});
</script>
@endpush