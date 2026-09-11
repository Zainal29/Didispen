@extends('guru.layouts.app')

@section('title', 'Detail Dispensasi')
@section('page-title', 'Detail Pengajuan')

@section('content')
@include('components.alert')

@php
    $displayStatus = $dispensasi->status === 'keluar' ? 'disetujui' : $dispensasi->status;
    $statusColors = [
        'menunggu'  => 'bg-amber-100 text-amber-700 border border-amber-200',
        'disetujui' => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
        'ditolak'   => 'bg-red-100 text-red-700 border border-red-200',
        'selesai'   => 'bg-gray-100 text-gray-700 border border-gray-200',
    ];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- ============ KOLOM KIRI: Detail Dispensasi ============ --}}
    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="bg-blue-600 p-5 text-white">
            <div class="flex justify-between items-start gap-3">
                <div class="min-w-0">
                    <p class="text-blue-100 text-[10px] font-semibold uppercase tracking-wider">Surat Dispensasi</p>
                    <h2 class="text-lg sm:text-xl font-bold font-mono tracking-tight truncate mt-0.5">{{ $dispensasi->nomor_surat }}</h2>
                    <p class="text-blue-100 text-xs mt-1.5">
                        <i class="far fa-calendar-plus mr-1"></i>Diajukan: {{ $dispensasi->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                    </p>
                </div>
                <span class="px-3 py-1.5 rounded-md text-xs font-semibold flex-shrink-0 {{ $statusColors[$dispensasi->status] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">
                    {{ ucfirst($displayStatus) }}
                </span>
            </div>
        </div>

        <div class="p-5 sm:p-6 space-y-5">

            {{-- Foto Verifikasi (Jika Ada) --}}
            @if($dispensasi->foto_verifikasi)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-4">
                    <div class="w-20 h-20 rounded-lg overflow-hidden border border-blue-200 flex-shrink-0 bg-white">
                        <img src="{{ Storage::url($dispensasi->foto_verifikasi) }}" alt="Foto {{ $dispensasi->siswa->nama_lengkap }}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-blue-900 mb-1 flex items-center">
                            <i class="fas fa-camera mr-1.5"></i> Foto Verifikasi Siswa
                        </h4>
                        <p class="text-xs text-blue-700 mb-2">
                            Gunakan foto ini untuk memverifikasi identitas siswa secara visual sebelum melakukan scan QR Code.
                        </p>
                        <p class="text-[10px] text-blue-600">
                            <i class="fas fa-info-circle mr-1"></i> Foto akan dihapus otomatis setelah siswa kembali.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Foto Bukti Siswa --}}
            @if($dispensasi->foto_bukti)
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                <div class="flex items-start gap-4">
                    <div class="w-20 h-20 rounded-lg overflow-hidden border border-emerald-200 flex-shrink-0 bg-white">
                        <img src="{{ Storage::url($dispensasi->foto_bukti) }}" alt="Foto Bukti {{ $dispensasi->siswa->nama_lengkap }}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-emerald-900 mb-1 flex items-center">
                            <i class="fas fa-image mr-1.5"></i> Foto Bukti Kedatangan
                        </h4>
                        <p class="text-xs text-emerald-700 mb-2">
                            Foto ini diupload oleh siswa sebagai bukti kedatangan di lokasi tujuan.
                        </p>
                        @if($dispensasi->foto_bukti_uploaded_at)
                        <p class="text-[10px] text-emerald-600">
                            <i class="far fa-clock mr-1"></i> Diupload: {{ $dispensasi->foto_bukti_uploaded_at->isoFormat('D MMMM Y, HH:mm') }} WIB
                        </p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Info Grid --}}
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <p class="text-gray-500 text-[10px] font-semibold uppercase mb-0.5">Kategori</p>
                    <p class="font-semibold text-gray-900 capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <p class="text-gray-500 text-[10px] font-semibold uppercase mb-0.5">Lokasi</p>
                    <p class="font-semibold text-gray-900 truncate">{{ $dispensasi->lokasi ?? '-' }}</p>
                </div>
            </div>

            <div>
                <p class="text-gray-500 text-[10px] font-semibold uppercase tracking-wider mb-1">Alasan</p>
                <p class="text-sm text-gray-800 font-medium leading-relaxed">{{ $dispensasi->alasan }}</p>
            </div>

            <div>
                <p class="text-gray-500 text-[10px] font-semibold uppercase tracking-wider mb-1">Tujuan</p>
                <p class="text-sm text-gray-800 font-medium">{{ $dispensasi->tujuan }}</p>
            </div>

            {{-- Waktu --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-blue-50 border border-blue-100 rounded-lg p-3">
                    <span class="text-blue-600 text-[10px] font-semibold uppercase tracking-wider block mb-1">Jam Keluar</span>
                    <p class="font-bold text-blue-900 text-sm">{{ $dispensasi->jam_keluar }}</p>
                    <p class="text-[10px] text-blue-600 mt-1">
                        <i class="far fa-clock mr-1"></i>{{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar) }}
                    </p>
                </div>
                <div class="bg-blue-50 border border-blue-100 rounded-lg p-3">
                    <span class="text-blue-600 text-[10px] font-semibold uppercase tracking-wider block mb-1">Jam Kembali</span>
                    <p class="font-bold text-blue-900 text-sm">{{ $dispensasi->jam_kembali }}</p>
                    <p class="text-[10px] text-blue-600 mt-1">
                        <i class="far fa-clock mr-1"></i>{{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali) }}
                    </p>
                </div>
            </div>

            {{-- Catatan Admin --}}
            @if($dispensasi->catatan_admin)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3.5">
                    <span class="text-amber-700 text-[10px] font-semibold uppercase tracking-wider block mb-1">Catatan Penolakan / Admin</span>
                    <p class="text-amber-800 text-sm font-medium">{{ $dispensasi->catatan_admin }}</p>
                </div>
            @endif

            {{-- Cetak Struk Thermal 58mm --}}
            @if(in_array($dispensasi->status, ['disetujui', 'keluar', 'selesai']))
            @php
                $maxPrint = \App\Helpers\PrintHelper::maxTeacherLimit();
                $currentPrint = $dispensasi->teacher_print_count ?? 0;
                $sisaCetak = $maxPrint - $currentPrint;
                $startTime = \App\Helpers\PrintHelper::startTime();
                $endTime = \App\Helpers\PrintHelper::endTime();
                $currentTime = \App\Helpers\PrintHelper::currentTime();
                $isWithinTime = \App\Helpers\PrintHelper::isWithinOperatingHours($currentTime);
                $canPrintStruk = $sisaCetak > 0 && $isWithinTime;
            @endphp
            <div class="mt-2 p-5 bg-emerald-50 border border-emerald-200 rounded-lg">
                <div class="flex items-center justify-between mb-1">
                    <h4 class="text-sm font-bold text-emerald-900 flex items-center">
                        <i class="fas fa-print mr-1.5"></i> Cetak Struk Dispensasi
                    </h4>
                    <span class="px-2.5 py-1 rounded-md text-xs font-semibold {{ $sisaCetak > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ $currentPrint }} / {{ $maxPrint }} kali
                    </span>
                </div>
                <p class="text-xs text-emerald-700 mb-3">Cetak PDF Struk Thermal (Ukuran Kertas 58mm).</p>

                {{-- Progress Bar --}}
                <div class="w-full bg-emerald-200 rounded-full h-1.5 mb-3">
                    <div class="bg-emerald-600 h-1.5 rounded-full transition-all" style="width: {{ min(($currentPrint / $maxPrint) * 100, 100) }}%"></div>
                </div>

                {{-- Info Jam Cetak --}}
                <div class="bg-white rounded-md p-2.5 mb-3 border border-emerald-100 text-left">
                    <p class="text-emerald-800 text-xs">
                        <i class="fas fa-clock mr-1"></i>
                        <strong>Jam Cetak:</strong> {{ $startTime }} - {{ $endTime }} WIB
                    </p>
                    <p class="text-emerald-600 text-[10px] mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Saat ini: {{ $currentTime }} WIB -
                        @if($isWithinTime)
                            <span class="text-emerald-600 font-semibold"><i class="fas fa-check mr-1"></i>Dalam jam operasional</span>
                        @else
                            <span class="text-red-600 font-semibold"><i class="fas fa-times mr-1"></i>Di luar jam operasional</span>
                        @endif
                    </p>
                </div>

                <div class="flex justify-center">
                    @if($canPrintStruk)
                        <a href="{{ route('guru.cetak-pdf', [$dispensasi, 'format' => 'thermal']) }}" target="_blank"
                           class="px-5 py-2.5 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 transition-colors inline-flex items-center">
                            <i id="iconCetak" class="fas fa-file-pdf mr-1.5 text-sm"></i> <span id="textCetak">Cetak PDF Thermal (58mm)</span>
                        </a>
                    @else
                        <button disabled
                                title="{{ $sisaCetak <= 0 ? 'Batas cetak tercapai (' . $maxPrint . ' kali)' : 'Pencetakan hanya diperbolehkan pukul ' . $startTime . ' - ' . $endTime . ' WIB' }}"
                                class="px-5 py-2.5 bg-gray-200 text-gray-500 text-xs font-semibold rounded-lg cursor-not-allowed inline-flex items-center">
                            <i class="fas fa-lock mr-1.5 text-sm"></i>
                            @if($sisaCetak <= 0) Batas Cetak Tercapai @else Di Luar Jam Cetak @endif
                        </button>
                    @endif
                </div>

                @if(!$canPrintStruk)
                    <p class="text-center text-[11px] text-gray-500 mt-2">
                        @if($sisaCetak <= 0)
                            <i class="fas fa-info-circle mr-1"></i>Batas maksimal cetak telah tercapai. Hubungi admin jika membutuhkan cetak ulang.
                        @else
                            <i class="fas fa-info-circle mr-1"></i>Pencetakan hanya diperbolehkan pada pukul {{ $startTime }} - {{ $endTime }} WIB.
                        @endif
                    </p>
                @endif
            </div>
            @endif

            {{-- PERINGATAN TERLAMBAT --}}
            @php
                $isTerlambat = $dispensasi->status === 'keluar' &&
                               $dispensasi->batas_waktu_kembali &&
                               now()->greaterThan($dispensasi->batas_waktu_kembali);

                if ($isTerlambat) {
                    $terlambatMenit = \App\Helpers\DispensasiTimeHelper::hitungMenitTerlambat($dispensasi->batas_waktu_kembali);
                    $terlambatJam = floor($terlambatMenit / 60);
                    $terlambatSisaMenit = $terlambatMenit % 60;
                }
            @endphp

            @if($isTerlambat)
            <div class="bg-red-50 border border-red-200 rounded-lg p-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-lg bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-red-900 font-bold text-sm mb-1 flex items-center gap-2">
                            SISWA TERLAMBAT KEMBALI
                        </h3>
                        <p class="text-red-700 text-sm mb-3">
                            Siswa telah melewati batas waktu kembali selama
                            <span class="font-semibold text-red-900 bg-red-100 px-2 py-0.5 rounded">
                                @if($terlambatJam > 0) {{ $terlambatJam }} jam {{ $terlambatSisaMenit }} menit @else {{ $terlambatMenit }} menit @endif
                            </span>
                        </p>

                        <div class="bg-white rounded-md p-3 mb-3 border border-red-100">
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div>
                                    <p class="text-red-500 text-[10px] font-semibold uppercase mb-0.5">Batas Kembali</p>
                                    <p class="font-bold text-red-900">
                                        <i class="far fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($dispensasi->batas_waktu_kembali)->format('H:i') }} WIB
                                    </p>
                                </div>
                                <div>
                                    <p class="text-red-500 text-[10px] font-semibold uppercase mb-0.5">Status Saat Ini</p>
                                    <p class="font-bold text-red-900">
                                        <i class="fas fa-walking mr-1"></i> Keluar (Belum Kembali)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('guru.scan') }}"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors text-sm">
                                <i class="fas fa-qrcode mr-1.5"></i> Scan QR Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @else
                @if($dispensasi->status === 'keluar' && $dispensasi->batas_waktu_kembali)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-amber-900 font-semibold text-sm">Siswa Sedang Keluar</p>
                            <p class="text-amber-700 text-xs">
                                Batas kembali: <strong>{{ \Carbon\Carbon::parse($dispensasi->batas_waktu_kembali)->format('H:i') }} WIB</strong>
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            @endif

            {{-- Action Buttons --}}
            <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-3">
                @if($dispensasi->status === 'menunggu')
                    <form method="POST" action="{{ route('guru.pengajuan.approve', $dispensasi) }}" class="flex-1">
                        @csrf
                        <button type="submit" data-confirm="Setujui dispensasi {{ $dispensasi->siswa->nama_lengkap }} dan generate QR Code?"
                                class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                            <i class="fas fa-check mr-2"></i>Setujui & Generate QR
                        </button>
                    </form>
                    <button onclick="rejectDispensasi()"
                            class="flex-1 inline-flex justify-center items-center px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors">
                        <i class="fas fa-times mr-2"></i>Tolak
                    </button>
                @else
                    <div class="flex-1 px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-xs text-gray-600 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-500 text-sm flex-shrink-0"></i>
                        <span>Status: <strong class="capitalize">{{ $displayStatus }}</strong>. <span class="text-gray-500 block sm:inline mt-1 sm:mt-0">(Konfirmasi keluar/kembali dilakukan oleh Satpam via Scan QR)</span></span>
                    </div>
                @endif

                <a href="{{ route('guru.pengajuan.index') }}"
                class="inline-flex justify-center items-center px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors sm:w-auto">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- ============ KOLOM KANAN: Info Siswa & Guru ============ --}}
    <div class="space-y-4">

        {{-- Data Siswa --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h4 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5 mb-3 flex items-center">
                <i class="fas fa-user-graduate text-blue-600 mr-2"></i>Data Siswa
            </h4>
            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-gray-500 font-medium block mb-0.5">Nama Lengkap</span>
                    <p class="font-semibold text-gray-900 text-sm">{{ $dispensasi->siswa->nama_lengkap }}</p>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block mb-0.5">NIS</span>
                    <p class="font-mono font-semibold text-gray-900">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block mb-0.5">Kelas</span>
                    <p class="font-semibold text-gray-900">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block mb-0.5">Jurusan</span>
                    <p class="text-gray-700 font-medium">{{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block mb-0.5">No. Telepon</span>
                    <p class="text-gray-700 font-medium">{{ $dispensasi->siswa->no_telepon ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Guru Piket --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h4 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5 mb-3 flex items-center">
                <i class="fas fa-user-tie text-blue-600 mr-2"></i>Guru Piket Penanggung Jawab
            </h4>
            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-gray-500 font-medium block mb-0.5">Nama Guru</span>
                    <p class="font-semibold text-gray-900 text-sm">{{ $dispensasi->guru?->nama_lengkap ?? 'Menunggu persetujuan' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block mb-0.5">Tanggal Piket</span>
                    <p class="text-gray-700 font-medium">{{ $dispensasi->created_at ? $dispensasi->created_at->format('d M Y') : '-' }}</p>
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
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            const reason = document.createElement('input');
            reason.type = 'hidden';
            reason.name = 'catatan_admin';
            reason.value = result.value;
            form.append(csrf, reason);
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
        window.open("{{ route('guru.laporan.pdf', $dispensasi) }}", "_blank");
    } else {
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
