@php
    $waLink = '';
    if (!empty($dispensasi->siswa->no_telepon)) {
        $hp = preg_replace('/[^0-9]/', '', $dispensasi->siswa->no_telepon);
        if (str_starts_with($hp, '0')) $hp = '62' . substr($hp, 1);
        
        $namaKelas = $dispensasi->siswa->kelas?->nama_kelas ?? 'Siswa';
        $pesan = "Halo *{$dispensasi->siswa->nama_lengkap}* ({$namaKelas}),\n\n";
        
        if ($status === 'menunggu') {
            $pesan .= "Anda tercatat menunggu konfirmasi keluar dispensasi.\n";
        } else {
            $pesan .= "Anda tercatat sedang dispensasi keluar sekolah.\n";
        }
        
        $pesan .= "📄 No. Surat: {$dispensasi->nomor_surat}\n";
        $pesan .= "📍 Tujuan: {$dispensasi->tujuan}\n";
        $pesan .= "⏰ Batas Kembali: {$dispensasi->jam_kembali}\n\n";
        $pesan .= "Mohon segera kembali ke sekolah atau lapor ke Pos Satpam. Terima kasih.";
        
        $waLink = "https://wa.me/{$hp}?text=" . urlencode($pesan);
    }
    
    $isWarned = $dispensasi->is_warned ?? false;
    $waktuKeluarAktual = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar);
    $waktuKembaliAktual = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali);
@endphp

<div class="card-container bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow" 
     data-dispensasi="{{ $dispensasi->id }}"
     data-status="{{ $status }}"
     data-overdue="{{ $isOverdue ? 'true' : 'false' }}"
     data-warned="{{ $isWarned ? 'true' : 'false' }}">
    
    {{-- Header Card --}}
    <div class="px-4 py-3.5 border-b border-gray-100 {{ $isOverdue ? 'bg-red-50' : ($status === 'menunggu' ? 'bg-amber-50' : 'bg-gray-50') }}">
        <div class="flex justify-between items-start gap-2">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-1">
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
                <p class="font-bold text-gray-900 text-sm truncate">{{ $dispensasi->siswa->nama_lengkap }}</p>
                <p class="text-[11px] text-gray-500 truncate">
                    {{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }} • {{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}
                </p>
            </div>
            
            <a href="{{ route('satpam.dispensasi.detail', $dispensasi) }}" 
               class="inline-flex items-center justify-center w-9 h-9 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors shadow-sm"
               title="Lihat Detail Dispensasi">
                <i class="fas fa-eye text-sm"></i>
            </a>
        </div>
    </div>

    {{-- Detail Section (Expandable saat diklik) --}}
    <div class="detail-section hidden bg-gray-50 p-4 space-y-3">
        <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="bg-white p-2.5 rounded-lg border border-gray-200">
                <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Tujuan</p>
                <p class="font-bold text-gray-800">{{ $dispensasi->tujuan }}</p>
            </div>
            <div class="bg-white p-2.5 rounded-lg border border-gray-200">
                <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Lokasi</p>
                <p class="font-bold text-gray-800">{{ $dispensasi->lokasi ?? '-' }}</p>
            </div>
            
            <div class="bg-white p-2.5 rounded-lg border border-gray-200">
                <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Jam Keluar</p>
                <p class="font-bold text-blue-700">{{ $dispensasi->jam_keluar }}</p>
                @if($waktuKeluarAktual !== '-')
                    <p class="text-[10px] text-blue-600 mt-0.5 font-mono flex items-center">
                        <i class="far fa-clock mr-1"></i> {{ $waktuKeluarAktual }}
                    </p>
                @endif
            </div>
            
            <div class="bg-white p-2.5 rounded-lg border border-gray-200">
                <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">Jam Kembali</p>
                <p class="font-bold {{ $isOverdue ? 'text-red-700' : 'text-amber-700' }}">
                    {{ $dispensasi->jam_kembali }}
                </p>
                @if($waktuKembaliAktual !== '-')
                    <p class="text-[10px] {{ $isOverdue ? 'text-red-600' : 'text-amber-600' }} mt-0.5 font-mono flex items-center">
                        <i class="far fa-clock mr-1"></i> {{ $waktuKembaliAktual }}
                    </p>
                @endif
            </div>
        </div>

        {{-- ✅ KONTAK DARURAT & WHATSAPP (LOGIKA DIPERBAIKI) --}}
        @if(!empty($dispensasi->siswa->no_telepon))
        <div class="bg-green-50 border-2 border-green-200 rounded-xl p-3" id="wa-section-{{ $dispensasi->id }}">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <p class="text-[10px] font-bold text-green-700 uppercase">Kontak Darurat</p>
                    <p class="text-sm font-bold text-gray-800 font-mono">{{ $dispensasi->siswa->no_telepon }}</p>
                </div>
                
                @if(!$isWarned)
                    {{-- Tombol WhatsApp (Belum Dihubungi) --}}
                    <button onclick="handleWaContacted({{ $dispensasi->id }}, '{{ $waLink }}')" 
                            class="inline-flex items-center justify-center w-12 h-12 bg-green-500 hover:bg-green-600 text-white rounded-xl transition-all shadow-lg shadow-green-500/30 hover:scale-105"
                            title="Hubungi via WhatsApp">
                        <i class="fab fa-whatsapp text-2xl"></i>
                    </button>
                @else
                    {{-- Badge Sudah Dihubungi --}}
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-300 text-gray-500 rounded-xl cursor-not-allowed" title="Sudah dihubungi">
                        <i class="fas fa-check text-xl"></i>
                    </div>
                @endif
            </div>
            <p class="text-[10px] text-green-600" id="wa-hint-{{ $dispensasi->id }}">
                <i class="fas fa-info-circle mr-1"></i>Klik ikon WhatsApp untuk menghubungi
            </p>
        </div>
        @endif

        {{-- Tombol Aksi --}}
        <div class="flex gap-2 pt-2">
            @if($status === 'menunggu')
                <a href="{{ route('satpam.scan') }}" 
                   class="flex-1 inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-red-600 to-rose-500 shadow-md shadow-red-500/20 active:scale-[0.98] transition-all">
                    <i class="fas fa-qrcode mr-1.5"></i>Scan QR Code
                </a>
            @elseif($status === 'keluar' || $status === 'terlambat')
                <form method="POST" action="{{ route('satpam.konfirmasi.kembali', $dispensasi) }}" class="flex-1">
                    @csrf
                    <button type="submit" 
                            data-confirm="Konfirmasi {{ $dispensasi->siswa->nama_lengkap }} KEMBALI ke sekolah?"
                            class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-500/20 active:scale-[0.98] transition-all">
                        <i class="fas fa-door-closed mr-1.5"></i>Konfirmasi Kembali
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Quick Info (Selalu Terlihat di Bawah Kartu) --}}
    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-[11px]">
        <div class="flex items-center gap-3">
            <span class="flex items-center text-gray-600" title="Aktual: {{ $waktuKeluarAktual }}">
                <i class="far fa-clock mr-1 text-gray-400"></i>
                {{ $dispensasi->jam_keluar }}
            </span>
            <span class="text-gray-300">|</span>
            <span class="flex items-center {{ $isOverdue ? 'text-red-600 font-bold' : 'text-amber-600' }}" title="Aktual: {{ $waktuKembaliAktual }}">
                <i class="fas fa-hourglass-half mr-1"></i>
                {{ $dispensasi->jam_kembali }}
            </span>
        </div>
        @if($status === 'menunggu')
            <span class="px-2 py-1 rounded-full text-[9px] font-bold bg-amber-100 text-amber-700">
                Menunggu Scan
            </span>
        @elseif($status === 'selesai')
            <span class="px-2 py-1 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-700">
                Selesai
            </span>
        @endif
    </div>
</div>

