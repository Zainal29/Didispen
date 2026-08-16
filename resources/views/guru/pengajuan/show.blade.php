    @extends('guru.layouts.app')

    @section('title', 'Detail Dispensasi')
    @section('page-title', 'Detail Pengajuan')

    @section('content')
    @include('components.alert')

    @php
        $displayStatus = $dispensasi->status === 'keluar' ? 'disetujui' : $dispensasi->status;
        $statusColors = [
            'menunggu'  => 'text-amber-600 bg-amber-50 border border-amber-200',
            'disetujui' => 'text-emerald-600 bg-emerald-50 border border-emerald-200',
            'ditolak'   => 'text-red-600 bg-red-50 border border-red-200',
            'selesai'   => 'text-gray-600 bg-gray-50 border border-gray-200',
        ];
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ============ KOLOM KIRI: Detail Dispensasi ============ --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-700 via-blue-600 to-sky-500"></div>
                <div class="absolute -top-16 -right-16 w-48 h-48 bg-white/10 rounded-full"></div>
                <div class="relative z-10 p-4 sm:p-6 flex justify-between items-start gap-3">
                    <div class="min-w-0">
                        <p class="text-blue-100 text-[10px] font-bold uppercase tracking-widest">Surat Dispensasi</p>
                        <h2 class="text-lg sm:text-2xl font-black text-white font-mono tracking-tight truncate mt-0.5">{{ $dispensasi->nomor_surat }}</h2>
                        <p class="text-blue-100 text-[11px] mt-1.5">
                            <i class="far fa-calendar-plus mr-1"></i>Diajukan: {{ $dispensasi->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                        </p>
                    </div>
                    <span class="px-3.5 py-1.5 rounded-full text-xs sm:text-sm font-bold shadow-md flex-shrink-0 {{ $statusColors[$dispensasi->status] ?? 'text-gray-600 bg-gray-50' }}">
                        {{ ucfirst($displayStatus) }}
                    </span>
                </div>
            </div>

            <div class="p-4 sm:p-6 space-y-4">

                {{-- Info Grid --}}
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3">
                        <p class="text-gray-400 text-[9px] font-bold uppercase mb-0.5">Kategori</p>
                        <p class="font-bold text-gray-800 capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3">
                        <p class="text-gray-400 text-[9px] font-bold uppercase mb-0.5">Lokasi</p>
                        <p class="font-bold text-gray-800 truncate">{{ $dispensasi->lokasi ?? '-' }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-gray-400 text-[10px] font-bold uppercase tracking-wider mb-1">Alasan</p>
                    <p class="text-sm text-gray-800 font-semibold leading-relaxed">{{ $dispensasi->alasan }}</p>
                </div>

                <div>
                    <p class="text-gray-400 text-[10px] font-bold uppercase tracking-wider mb-1">Tujuan</p>
                    <p class="text-sm text-gray-800 font-semibold">{{ $dispensasi->tujuan }}</p>
                </div>

                {{-- Waktu --}}
                <div class="grid grid-cols-2 gap-2.5">
                    <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-3">
                        <span class="text-blue-500 text-[10px] font-bold uppercase tracking-wider block mb-1">Jam Keluar</span>
                        <p class="font-black text-blue-800 text-sm">{{ $dispensasi->jam_keluar }}</p>
                        <p class="text-[10px] text-blue-600 mt-1">
                            <i class="far fa-clock mr-1"></i>{{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar) }}
                        </p>
                    </div>
                    <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-3">
                        <span class="text-blue-500 text-[10px] font-bold uppercase tracking-wider block mb-1">Jam Kembali</span>
                        <p class="font-black text-blue-800 text-sm">{{ $dispensasi->jam_kembali }}</p>
                        <p class="text-[10px] text-blue-600 mt-1">
                            <i class="far fa-clock mr-1"></i>{{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali) }}
                        </p>
                    </div>
                </div>

                {{-- Catatan Admin --}}
                @if($dispensasi->catatan_admin)
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5">
                        <span class="text-amber-700 text-[10px] font-bold uppercase tracking-wider block mb-1">Catatan Penolakan / Admin</span>
                        <p class="text-amber-800 text-sm font-medium">{{ $dispensasi->catatan_admin }}</p>
                    </div>
                @endif

                {{-- QR Code Section (Muncul jika sudah disetujui/keluar/selesai) --}}
                {{-- @if($dispensasi->qr_code && in_array($dispensasi->status, ['disetujui', 'keluar', 'selesai']))
                <div class="mt-6 p-6 bg-indigo-50 border-2 border-dashed border-indigo-300 rounded-2xl text-center">
                    <h4 class="text-base font-bold text-indigo-800 mb-2">
                        <i class="fas fa-qrcode mr-2"></i>QR Code Dispensasi
                    </h4>
                    <p class="text-xs text-indigo-600 mb-4">Tunjukkan atau cetak QR Code ini agar dapat discan oleh Petugas Satpam.</p>

                    <div class="bg-white p-4 rounded-xl shadow-sm inline-block mx-auto border border-gray-200">
                        <img src="{{ asset('storage/' . $dispensasi->qr_code) }}" alt="QR Code" class="w-48 h-48 mx-auto object-contain">
                    </div>

                    <p class="text-xs text-gray-500 mt-3 font-mono font-bold">No. Surat: {{ $dispensasi->nomor_surat }}</p>

                    <div class="mt-4 flex justify-center gap-2.5">
                        <a href="{{ asset('storage/' . $dispensasi->qr_code) }}" download class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-700 transition shadow-md shadow-indigo-500/20">
                            <i class="fas fa-download mr-1.5"></i> Download QR
                        </a> --}}
                        
                        {{-- TOMBOL CETAK OTOMATIS BERDASARKAN PERANGKAT (HP / PC) --}}
                        {{-- <a href="#" id="btnCetakOtomatis" onclick="handleCetakOtomatis(event)" class="px-4 py-2 bg-purple-600 text-white text-xs font-bold rounded-xl hover:bg-purple-700 transition shadow-md shadow-purple-500/20">
                            <i class="fas fa-print mr-1.5" id="iconCetak"></i> <span id="textCetak">Cetak Struk / PDF</span>
                        </a>
                    </div>
                </div>
                @endif --}}

                {{-- Action Buttons --}}
                <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-2.5">
                    @if($dispensasi->status === 'menunggu')
                        <form method="POST" action="{{ route('guru.pengajuan.approve', $dispensasi) }}" class="flex-1">
                            @csrf
                            <button type="submit" data-confirm="Setujui dispensasi {{ $dispensasi->siswa->nama_lengkap }} dan generate QR Code?"
                                    class="w-full inline-flex justify-center items-center px-4 py-3 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-500/30 active:scale-[0.98] transition-all">
                                <i class="fas fa-check mr-2"></i>Setujui & Generate QR
                            </button>
                        </form>
                        <button onclick="rejectDispensasi()"
                                class="flex-1 inline-flex justify-center items-center px-4 py-3 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 shadow-lg shadow-red-500/30 active:scale-[0.98] transition-all">
                            <i class="fas fa-times mr-2"></i>Tolak
                        </button>
                    @else
                        <div class="flex-1 px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-xs text-gray-600 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-blue-500 text-sm flex-shrink-0"></i>
                            <span>Status: <strong class="capitalize">{{ $displayStatus }}</strong>. <span class="text-gray-500 block sm:inline">(Konfirmasi keluar/kembali dilakukan oleh Satpam via Scan QR)</span></span>
                        </div>
                    @endif

                    <a href="{{ route('guru.pengajuan.index') }}"
                    class="inline-flex justify-center items-center px-4 py-3 rounded-xl text-sm font-bold text-gray-600 border-2 border-gray-200 hover:bg-gray-50 transition-colors sm:w-auto">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        {{-- ============ KOLOM KANAN: Info Siswa & Guru ============ --}}
        <div class="space-y-4">

            {{-- Data Siswa --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
                <h4 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-2.5 mb-3 flex items-center">
                    <i class="fas fa-user-graduate text-blue-600 mr-2"></i>Data Siswa
                </h4>
                <div class="space-y-2.5 text-xs">
                    <div>
                        <span class="text-gray-400 font-medium">Nama Lengkap</span>
                        <p class="font-bold text-gray-800 text-sm mt-0.5">{{ $dispensasi->siswa->nama_lengkap }}</p>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">NIS</span>
                        <p class="font-mono font-semibold text-gray-800 mt-0.5">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Kelas</span>
                        <p class="font-bold text-gray-800 mt-0.5">{{ $dispensasi->siswa->kelas->nama_kelas }}</p>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Jurusan</span>
                        <p class="text-gray-800 font-medium mt-0.5">{{ $dispensasi->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">No. Telepon</span>
                        <p class="text-gray-800 font-medium mt-0.5">{{ $dispensasi->siswa->no_telepon ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Guru Piket --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
                <h4 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-2.5 mb-3 flex items-center">
                    <i class="fas fa-user-tie text-blue-600 mr-2"></i>Guru Piket Penanggung Jawab
                </h4>
                <div class="space-y-2.5 text-xs">
                    <div>
                        <span class="text-gray-400 font-medium">Nama Guru</span>
                        <p class="font-bold text-gray-800 text-sm mt-0.5">{{ $dispensasi->guruPiket->guru->nama_lengkap ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Tanggal Piket</span>
                        <p class="text-gray-800 font-semibold mt-0.5">{{ $dispensasi->guruPiket->tanggal ? \Carbon\Carbon::parse($dispensasi->guruPiket->tanggal)->format('d M Y') : '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Shift</span>
                        <p class="capitalize text-gray-800 font-bold mt-0.5">
                            <span class="inline-block px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $dispensasi->guruPiket->shift ?? '-' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function rejectDispensasi() {
        Swal.fire({
            title: 'Tolak Dispensasi',
            text: 'Masukkan alasan penolakan untuk {{ $dispensasi->siswa->nama_lengkap }}:',
            input: 'textarea',
            inputPlaceholder: 'Contoh: Alasan tidak jelas, siswa masih bisa mengikuti pelajaran...',
            inputAttributes: { rows: 4 },
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'Ya, Tolak',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            inputValidator: (value) => {
                if (!value || value.trim() === '') return 'Alasan penolakan wajib diisi!';
            }
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('guru.pengajuan.reject', $dispensasi) }}';
                form.innerHTML = `@csrf <input type="hidden" name="catatan_admin" value="${result.value}">`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Konfirmasi approve dengan SweetAlert
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const form = this.closest('form');
            Swal.fire({
                title: 'Konfirmasi Persetujuan',
                text: this.dataset.confirm,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(result => { if (result.isConfirmed) form.submit(); });
        });
    });

    // Fungsi deteksi perangkat untuk tombol cetak otomatis
    function handleCetakOtomatis(event) {
        event.preventDefault();
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        if (isMobile) {
            // Jika di HP: Arahkan ke route PDF/Cetak versi Mobile yang gampang di-scan
            window.open("{{ route('guru.laporan.pdf', $dispensasi) }}", "_blank");
        } else {
            // Jika di Laptop/PC: Arahkan ke halaman cetak struk thermal
            window.open("{{ route('guru.cetak-struk', $dispensasi) }}", "_blank");
        }
    }

    // Ubah teks tombol secara otomatis saat halaman dimuat
    document.addEventListener("DOMContentLoaded", function() {
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        const textCetak = document.getElementById('textCetak');
        const iconCetak = document.getElementById('iconCetak');

        if (isMobile) {
            if(textCetak) textCetak.textContent = "Buka PDF / Scan (HP)";
            if(iconCetak) iconCetak.className = "fas fa-file-pdf mr-1.5";
        } else {
            if(textCetak) textCetak.textContent = "Cetak Struk (PC)";
            if(iconCetak) iconCetak.className = "fas fa-print mr-1.5";
        }
    });
    </script>
    @endpush
    @endsection