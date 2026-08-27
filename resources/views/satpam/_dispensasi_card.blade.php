@php
    $statusColor = match($status) {
        'menunggu' => 'border-amber-200 bg-amber-50/30',
        'keluar' => $isOverdue ? 'border-red-300 bg-red-50/30' : 'border-sky-200 bg-sky-50/30',
        'terlambat' => 'border-red-300 bg-red-50/30',
        'selesai' => 'border-emerald-200 bg-emerald-50/30',
        'dihubungi' => 'border-purple-200 bg-purple-50/30',
        default => 'border-gray-200 bg-gray-50/30'
    };
    
    $deadlineStr = $dispensasi->batas_waktu_kembali ? $dispensasi->batas_waktu_kembali->format('Y-m-d H:i:s') : '';
    $waktuKeluarAktual = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar);
    $waktuKembaliAktual = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali);
    $isWarned = $dispensasi->is_warned ?? false;
@endphp

<div 
    class="bg-white rounded-2xl border-2 {{ $statusColor }} shadow-sm overflow-hidden transition-all hover:shadow-md"
    data-dispensasi="{{ $dispensasi->id }}"
    data-status="{{ $status }}"
    data-overdue="{{ $isOverdue ? 'true' : 'false' }}"
    data-warned="{{ $isWarned ? 'true' : 'false' }}"
    @if($deadlineStr && in_array($status, ['keluar', 'terlambat']))
        data-deadline="{{ $deadlineStr }}"
    @endif
>
    {{-- Header Kartu --}}
    <div class="px-4 py-3.5 border-b border-gray-100 {{ $isOverdue ? 'bg-red-100/50' : 'bg-white' }}">
        <div class="flex justify-between items-start gap-2">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <p class="font-mono font-bold text-gray-800 text-xs">{{ $dispensasi->nomor_surat }}</p>
                    @if($isOverdue)
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700 uppercase animate-pulse">
                            <i class="fas fa-exclamation-triangle mr-1"></i>TERLAMBAT
                        </span>
                    @endif
                    @if($isWarned)
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-700 uppercase">
                            <i class="fas fa-phone-alt mr-1"></i>DIHUBUNGI
                        </span>
                    @endif
                </div>
                <p class="font-bold text-gray-900 text-sm truncate">{{ $dispensasi->siswa->nama_lengkap ?? 'Siswa Tidak Ditemukan' }}</p>
                <p class="text-[11px] text-gray-500 truncate">
                    {{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }} • {{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}
                </p>
            </div>
            
            <div class="flex flex-col items-end gap-1">
                {{-- Tombol Mata (Eye Icon) --}}
                <a href="{{ route('satpam.dispensasi.detail', $dispensasi) }}" 
                   class="inline-flex items-center justify-center w-8 h-8 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors" 
                   title="Lihat Detail Dispensasi">
                    <i class="fas fa-eye text-sm"></i>
                </a>
                
                {{-- Badge Status --}}
                @if($status === 'menunggu')
                    <span class="px-2 py-1 rounded-full text-[9px] font-bold bg-amber-100 text-amber-700">Menunggu</span>
                @elseif($status === 'keluar')
                    <span class="px-2 py-1 rounded-full text-[9px] font-bold bg-sky-100 text-sky-700">Keluar</span>
                @elseif($status === 'terlambat')
                    <span class="px-2 py-1 rounded-full text-[9px] font-bold bg-red-100 text-red-700">Terlambat</span>
                @elseif($status === 'selesai')
                    <span class="px-2 py-1 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-700">Selesai</span>
                @elseif($status === 'dihubungi')
                    <span class="px-2 py-1 rounded-full text-[9px] font-bold bg-purple-100 text-purple-700">Dihubungi</span>
                @endif
                
                {{-- Live Countdown --}}
                @if($deadlineStr && in_array($status, ['keluar', 'terlambat']))
                    <span class="live-countdown px-2 py-0.5 rounded text-[9px] font-bold {{ $isOverdue ? 'bg-red-100 text-red-700 animate-pulse' : 'bg-amber-100 text-amber-700' }}" 
                          data-deadline="{{ $deadlineStr }}">
                        Menghitung...
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Detail Section (Expandable dengan klik kartu) --}}
    <div class="detail-section hidden p-4 space-y-3 bg-gray-50/50 cursor-pointer" onclick="event.stopPropagation(); window.location.href='{{ route('satpam.dispensasi.detail', $dispensasi) }}'">
        <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="bg-white p-2.5 rounded-lg border border-gray-200">
                <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Jam Keluar</p>
                <p class="font-bold text-gray-800">{{ $dispensasi->jam_keluar }}</p>
                @if($waktuKeluarAktual !== '-')
                    <p class="text-[10px] text-blue-600 mt-0.5 font-mono flex items-center">
                        <i class="far fa-clock mr-1"></i> {{ $waktuKeluarAktual }}
                    </p>
                @endif
            </div>
            <div class="bg-white p-2.5 rounded-lg border border-gray-200">
                <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Jam Kembali</p>
                <p class="font-bold {{ $isOverdue ? 'text-red-700' : 'text-amber-700' }}">{{ $dispensasi->jam_kembali }}</p>
                @if($waktuKembaliAktual !== '-')
                    <p class="text-[10px] {{ $isOverdue ? 'text-red-600' : 'text-amber-600' }} mt-0.5 font-mono flex items-center">
                        <i class="far fa-clock mr-1"></i> {{ $waktuKembaliAktual }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Tombol Aksi: Hanya muncul jika status 'keluar' atau 'terlambat' --}}
        @if(in_array($status, ['keluar', 'terlambat']))
            @if(!empty($dispensasi->siswa->no_telepon))
                @php
                    $hp = preg_replace('/[^0-9]/', '', $dispensasi->siswa->no_telepon);
                    $hp = str_starts_with($hp, '0') ? '62' . substr($hp, 1) : $hp;
                    $lateText = $isOverdue ? ' telah melewati batas waktu kembali' : '';
                    $pesan = "Halo {$dispensasi->siswa->nama_lengkap}, ini dari Pos Satpam. Mohon segera kembali ke sekolah sesuai batas waktu dispensasi ({$dispensasi->jam_kembali}).{$lateText} Terima kasih.";
                    $waLink = "https://wa.me/{$hp}?text=" . urlencode($pesan);
                @endphp
                <div id="wa-section-{{ $dispensasi->id }}" class="flex items-center gap-2">
                    <div class="flex-1 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                        <p class="text-[10px] font-bold text-green-700 uppercase">Kontak Darurat</p>
                        <p class="text-xs font-bold text-gray-800 font-mono">{{ $dispensasi->siswa->no_telepon }}</p>
                    </div>
                    <button onclick="handleWaContacted({{ $dispensasi->id }}, '{{ $waLink }}'); event.stopPropagation();" 
                            class="inline-flex items-center justify-center w-12 h-12 bg-green-500 hover:bg-green-600 text-white rounded-xl transition-all active:scale-95 shadow-md shadow-green-500/30 flex-shrink-0" 
                            title="Hubungi via WhatsApp & Tandai">
                        <i class="fab fa-whatsapp text-xl"></i>
                    </button>
                </div>
            @endif

            <form method="POST" action="{{ route('satpam.konfirmasi.kembali', $dispensasi) }}" class="mt-2" onsubmit="event.stopPropagation();">
                @csrf
                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all shadow-md shadow-emerald-600/20">
                    <i class="fas fa-door-closed mr-1.5"></i>Konfirmasi Kembali (Manual)
                </button>
            </form>
        @endif
        
        <div class="text-center text-[10px] text-gray-400 mt-2">
            <i class="fas fa-info-circle mr-1"></i>Klik area ini untuk lihat detail lengkap
        </div>
    </div>
</div>