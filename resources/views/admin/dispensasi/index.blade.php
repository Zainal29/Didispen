@extends('admin.layouts.app')

@section('title', 'Semua Pengajuan')
@section('page-title', 'Semua Pengajuan Dispensasi')

@section('content')

{{-- ✅ NOTIFIKASI SUKSES / ERROR --}}
@if(session('success'))
    <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3">
        <i class="fas fa-check-circle text-emerald-600 text-xl"></i>
        <div>
            <p class="font-bold text-emerald-800">Berhasil!</p>
            <p class="text-sm text-emerald-700">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3">
        <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
        <div>
            <p class="font-bold text-red-800">Gagal!</p>
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
    </div>
@endif

<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b">
        <h3 class="text-lg font-bold mb-4">Filter Pengajuan</h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <select name="status" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach(['menunggu','disetujui','ditolak','keluar','selesai'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="jurusan_id" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua Jurusan</option>
                @foreach($jurusans as $j)
                    <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                @endforeach
            </select>
            <select name="kelas_id" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua Kelas</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="border rounded px-3 py-2 text-sm">
            <button type="submit" class="bg-gray-800 text-white rounded px-4 py-2 text-sm hover:bg-gray-900 transition">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">No. Surat</th>
                    <th class="p-3 text-left">Siswa</th>
                    <th class="p-3 text-left">Kelas</th>
                    <th class="p-3 text-left">Kategori</th>
                    <th class="p-3 text-left">Tujuan</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Guru Piket</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($dispensasi as $d)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="p-3 font-mono text-sm">{{ $d->nomor_surat }}</td>
                    <td class="p-3 font-semibold">{{ $d->siswa->nama_lengkap }}</td>
                    <td class="p-3 text-sm">{{ $d->siswa->kelas?->nama_kelas ?? '-' }}</td>
                    <td class="p-3 text-sm capitalize">{{ str_replace('_', ' ', $d->kategori) }}</td>
                    <td class="p-3 text-sm">{{ Str::limit($d->tujuan, 30) }}</td>
                    <td class="p-3">
                        @php
                            $colors = [
                                'menunggu' => 'bg-yellow-100 text-yellow-800',
                                'disetujui' => 'bg-green-100 text-green-800',
                                'ditolak' => 'bg-red-100 text-red-800',
                                'keluar' => 'bg-indigo-100 text-indigo-800',
                                'selesai' => 'bg-gray-100 text-gray-800',
                            ];
                        @endphp
                        <span class="px-2 py-1 rounded text-xs font-bold {{ $colors[$d->status] ?? 'bg-gray-100' }}">
                            {{ ucfirst($d->status) }}
                        </span>
                    </td>
                    <td class="p-3 text-sm">{{ $d->guru?->nama_lengkap ?? '-' }}</td>
                    <td class="p-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            {{-- ✅ DIPERBAIKI: Menggunakan route 'admin.semua.pengajuan.show' --}}
                            <a href="{{ route('admin.semua.pengajuan.show', $d->id) }}"
                               class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg transition-colors"
                               title="Lihat Detail">
                                <i class="fas fa-eye text-sm"></i>
                            </a>

                            {{-- ✅ DIPERBAIKI: Menggunakan route 'admin.semua.pengajuan.destroy' --}}
                            <button type="button"
                                    onclick="confirmDelete({{ $d->id }}, '{{ $d->nomor_surat }}', '{{ $d->siswa->nama_lengkap }}')"
                                    class="inline-flex items-center justify-center w-8 h-8 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-colors"
                                    title="Hapus Dispensasi">
                                <i class="fas fa-trash-alt text-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="p-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                        <p>Tidak ada data pengajuan yang sesuai dengan filter.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($dispensasi->hasPages())
    <div class="p-4 border-t">{{ $dispensasi->links() }}</div>
    @endif
</div>

{{-- ✅ SCRIPT SWEETALERT UNTUK KONFIRMASI HAPUS --}}
@push('scripts')
<script>
function confirmDelete(id, nomorSurat, namaSiswa) {
    Swal.fire({
        title: 'Hapus Dispensasi?',
        html: `
            <div class="text-left">
                <p class="mb-2">Apakah Anda yakin ingin menghapus data ini?</p>
                <div class="bg-gray-50 p-3 rounded-lg text-sm border border-gray-200">
                    <p><strong>No. Surat:</strong> ${nomorSurat}</p>
                    <p><strong>Siswa:</strong> ${namaSiswa}</p>
                </div>
                <p class="text-red-600 text-xs mt-3 font-semibold">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Tindakan ini tidak dapat dibatalkan dan akan menghapus file QR/Foto terkait!
                </p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading
            Swal.fire({
                title: 'Menghapus...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Buat form dinamis untuk method DELETE
            const form = document.createElement('form');
            // ✅ DIPERBAIKI: URL disesuaikan dengan route 'semua-pengajuan'
            form.method = 'POST';
            form.action = `/admin/semua-pengajuan/${id}`;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';

            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection
