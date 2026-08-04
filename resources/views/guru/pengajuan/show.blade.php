@extends('guru.layouts.app')
@section('title', 'Detail Dispensasi')
@section('page-title', 'Detail Pengajuan')

@section('content')
@include('components.alert')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Detail Dispensasi --}}
    <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
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
            <span class="px-3 py-1 rounded-full text-sm font-bold {{ $statusColors[$dispensasi->status] }}">
                {{ ucfirst($dispensasi->status) }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm mb-6">
            <div><span class="text-gray-500">Kategori:</span><br><strong class="capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</strong></div>
            <div><span class="text-gray-500">Lokasi:</span><br><strong>{{ $dispensasi->lokasi ?? '-' }}</strong></div>
            <div class="col-span-2"><span class="text-gray-500">Alasan:</span><br><strong>{{ $dispensasi->alasan }}</strong></div>
            <div class="col-span-2"><span class="text-gray-500">Tujuan:</span><br><strong>{{ $dispensasi->tujuan }}</strong></div>

            <div>
                <span class="text-gray-500">Jam Keluar:</span><br>
                <strong class="text-indigo-700">
                    {{ $dispensasi->jam_keluar }}
                    <span class="text-xs text-gray-500 block mt-1 font-normal">
                        <i class="far fa-clock mr-1"></i>
                        {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar) }}
                    </span>
                </strong>
            </div>
            <div>
                <span class="text-gray-500">Jam Kembali:</span><br>
                <strong class="text-indigo-700">
                    {{ $dispensasi->jam_kembali }}
                    <span class="text-xs text-gray-500 block mt-1 font-normal">
                        <i class="far fa-clock mr-1"></i>
                        {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali) }}
                    </span>
                </strong>
            </div>

            @if($dispensasi->catatan_admin)
            <div class="col-span-2 bg-yellow-50 p-3 rounded border-l-4 border-yellow-400">
                <span class="text-gray-500 text-xs">Catatan:</span><br>
                <strong>{{ $dispensasi->catatan_admin }}</strong>
            </div>
            @endif

            {{-- ✅ TAMBAHKAN BAGIAN INI: Tampilan QR Code yang Mencolok --}}
            @if($dispensasi->qr_code && in_array($dispensasi->status, ['disetujui', 'keluar', 'selesai']))
            <div class="col-span-2 mt-6 p-6 bg-indigo-50 border-2 border-dashed border-indigo-300 rounded-xl text-center">
                <h4 class="text-lg font-bold text-indigo-800 mb-2">
                    <i class="fas fa-qrcode mr-2"></i>QR Code Dispensasi
                </h4>
                <p class="text-sm text-indigo-600 mb-4">Tunjukkan atau cetak QR Code ini agar dapat discan oleh Petugas Satpam.</p>
                
                <div class="bg-white p-4 rounded-lg shadow-sm inline-block mx-auto border border-gray-200">
                    <img src="{{ asset('storage/' . $dispensasi->qr_code) }}" alt="QR Code" class="w-64 h-64 mx-auto object-contain">
                </div>
                
                <p class="text-xs text-gray-500 mt-4 font-mono">No. Surat: {{ $dispensasi->nomor_surat }}</p>
                
                <div class="mt-4 flex justify-center gap-3">
                    <a href="{{ asset('storage/' . $dispensasi->qr_code) }}" download class="px-4 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700 transition shadow-sm">
                        <i class="fas fa-download mr-1"></i> Download QR
                    </a>
                    <button onclick="window.print()" class="px-4 py-2 bg-gray-700 text-white text-sm rounded hover:bg-gray-800 transition shadow-sm">
                        <i class="fas fa-print mr-1"></i> Cetak Halaman
                    </button>
                </div>
            </div>
            @endif
        </div>

              {{-- Action Buttons --}}
        <div class="border-t pt-4 flex space-x-3">
            @if($dispensasi->status === 'menunggu')
            <form method="POST" action="{{ route('guru.pengajuan.approve', $dispensasi) }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    <i class="fas fa-check mr-2"></i>Setujui & Generate QR
                </button>
            </form>
            <button onclick="rejectForm()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                <i class="fas fa-times mr-2"></i>Tolak
            </button>
            @else
            {{-- Jika sudah disetujui, keluarkan, atau selesai, guru hanya bisa melihat --}}
            <div class="px-4 py-2 bg-gray-100 text-gray-600 rounded text-sm flex items-center">
                <i class="fas fa-info-circle mr-2"></i> 
                Status saat ini: <strong class="ml-1 capitalize">{{ $dispensasi->status }}</strong>. 
                <span class="ml-2 text-xs text-gray-500">(Konfirmasi keluar/kembali dilakukan oleh Satpam via Scan QR)</span>
            </div>
            @endif

            <a href="{{ route('guru.pengajuan.index') }}" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    {{-- Info Siswa --}}
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-bold mb-4 text-gray-800">Data Siswa</h4>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-gray-500">Nama:</span>
                    <p class="font-semibold">{{ $dispensasi->siswa->nama_lengkap }}</p>
                </div>
                <div>
                    <span class="text-gray-500">NIS:</span>
                    <p class="font-mono">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Kelas:</span>
                    <p class="font-semibold">{{ $dispensasi->siswa->kelas->nama_kelas }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Jurusan:</span>
                    <p>{{ $dispensasi->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">No. Telepon:</span>
                    <p>{{ $dispensasi->siswa->no_telepon ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-bold mb-4 text-gray-800">Guru Piket</h4>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-gray-500">Nama:</span>
                    <p class="font-semibold">{{ $dispensasi->guruPiket->guru->nama_lengkap ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Tanggal:</span>
                    <p>{{ $dispensasi->guruPiket->tanggal ? \Carbon\Carbon::parse($dispensasi->guruPiket->tanggal)->format('d M Y') : '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Shift:</span>
                    <p class="capitalize">{{ $dispensasi->guruPiket->shift ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-bold mb-4">Tolak Dispensasi</h3>
        <form method="POST" action="{{ route('guru.pengajuan.reject', $dispensasi) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan</label>
                <textarea name="catatan_admin" required rows="4" class="w-full border rounded px-3 py-2" placeholder="Masukkan alasan penolakan..."></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-100">Batal</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Tolak</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function rejectForm() {
    document.getElementById('rejectModal').classList.remove('hidden');
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>
@endpush
@endsection