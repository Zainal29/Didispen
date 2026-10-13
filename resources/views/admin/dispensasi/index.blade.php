@extends('admin.layouts.app')

@section('title', 'Semua Pengajuan')
@section('page-title', 'Semua Pengajuan Dispensasi')

@section('content')

{{-- NOTIFIKASI SUKSES / ERROR --}}
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

{{-- TAB FILTER ARSIP (AKTIF / ARSIP / SEMUA) --}}
<div class="flex items-center gap-2 mb-4 overflow-x-auto pb-1">
    <a href="{{ request()->fullUrlWithQuery(['arsip' => 'aktif']) }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all shadow-xs {{ $arsipFilter === 'aktif' ? 'bg-blue-600 text-white shadow-blue-500/20' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }}">
        <i class="fas fa-check-circle"></i>
        <span>Aktif</span>
        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $arsipFilter === 'aktif' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600' }}">
            {{ $countAktif }}
        </span>
    </a>

    <a href="{{ request()->fullUrlWithQuery(['arsip' => 'arsip']) }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all shadow-xs {{ $arsipFilter === 'arsip' ? 'bg-amber-600 text-white shadow-amber-500/20' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }}">
        <i class="fas fa-archive"></i>
        <span>Arsip (Terhapus)</span>
        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $arsipFilter === 'arsip' ? 'bg-amber-500 text-white' : 'bg-amber-100 text-amber-700' }}">
            {{ $countArsip }}
        </span>
    </a>

    <a href="{{ request()->fullUrlWithQuery(['arsip' => 'semua']) }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all shadow-xs {{ $arsipFilter === 'semua' ? 'bg-gray-800 text-white shadow-gray-700/20' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }}">
        <i class="fas fa-layer-group"></i>
        <span>Semua</span>
        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $arsipFilter === 'semua' ? 'bg-gray-700 text-white' : 'bg-gray-100 text-gray-600' }}">
            {{ $countSemua }}
        </span>
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-gray-100">
        <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">Filter Pengajuan</h3>
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
            <input type="hidden" name="arsip" value="{{ $arsipFilter }}">

            <select name="status" class="w-full h-11 border border-gray-300 rounded-lg px-3.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">Semua Status</option>
                @foreach(['menunggu','disetujui','ditolak','keluar','selesai','dibatalkan','kadaluarsa'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="jurusan_id" class="w-full h-11 border border-gray-300 rounded-lg px-3.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">Semua Jurusan</option>
                @foreach($jurusans as $j)
                    <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                @endforeach
            </select>
            <select name="kelas_id" class="w-full h-11 border border-gray-300 rounded-lg px-3.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">Semua Kelas</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="w-full h-11 border border-gray-300 rounded-lg px-3.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <button type="submit" class="w-full h-11 min-h-[44px] bg-gray-800 text-white rounded-lg px-4 text-sm font-semibold hover:bg-gray-900 transition-colors flex items-center justify-center shadow-sm">
                <i class="fas fa-filter mr-1.5"></i> Filter
            </button>
        </form>
    </div>

    <div class="overflow-x-auto min-w-0 w-full">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                <tr>
                    <th class="p-3.5 text-left">No. Surat</th>
                    <th class="p-3.5 text-left">Siswa</th>
                    <th class="p-3.5 text-left">Kelas</th>
                    <th class="p-3.5 text-left">Kategori</th>
                    <th class="p-3.5 text-left">Tujuan</th>
                    <th class="p-3.5 text-left">Status</th>
                    <th class="p-3.5 text-left">Guru Piket</th>
                    <th class="p-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($dispensasi as $d)
                @php
                    $isTrashed = $d->trashed();
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors {{ $isTrashed ? 'bg-amber-50/30' : '' }}">
                    <td class="p-3.5 font-mono text-xs text-gray-600 font-semibold">
                        {{ $d->nomor_surat }}
                    </td>
                    <td class="p-3.5 font-semibold text-gray-900">{{ $d->siswa?->nama_lengkap ?? '-' }}</td>
                    <td class="p-3.5 text-sm text-gray-600">{{ $d->siswa?->kelas?->nama_kelas ?? '-' }}</td>
                    <td class="p-3.5 text-sm capitalize text-gray-600">{{ str_replace('_', ' ', $d->kategori) }}</td>
                    <td class="p-3.5 text-sm text-gray-600">{{ Str::limit($d->tujuan, 30) }}</td>
                    <td class="p-3.5">
                        <div class="flex flex-col gap-1 items-start">
                            @php
                                $badge = $d->status_badge;
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-semibold border {{ $badge['class'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                {{ $badge['text'] }}
                            </span>
                            @if($isTrashed)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fas fa-archive"></i> Arsip
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="p-3.5 text-sm text-gray-600">{{ $d->guru?->nama_lengkap ?? '-' }}</td>
                    <td class="p-3.5 text-center whitespace-nowrap">
                        <div class="flex items-center justify-center gap-1">
                            {{-- LIHAT DETAIL --}}
                            <a href="{{ route('admin.semua.pengajuan.show', $d->id) }}"
                               class="inline-flex items-center justify-center w-9 h-9 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg transition-colors"
                               title="Lihat Detail">
                                <i class="fas fa-eye text-sm"></i>
                            </a>

                            @if($isTrashed)
                                {{-- PULIHKAN DARI ARSIP --}}
                                <button type="button"
                                        onclick="confirmRestore({{ $d->id }}, '{{ $d->nomor_surat }}')"
                                        class="inline-flex items-center justify-center w-9 h-9 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-lg transition-colors"
                                        title="Pulihkan dari Arsip">
                                    <i class="fas fa-trash-restore text-sm"></i>
                                </button>

                                {{-- HAPUS PERMANEN --}}
                                <button type="button"
                                        onclick="confirmForceDelete({{ $d->id }}, '{{ $d->nomor_surat }}', '{{ addslashes($d->siswa?->nama_lengkap ?? '-') }}')"
                                        class="inline-flex items-center justify-center w-9 h-9 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-colors"
                                        title="Hapus Permanen">
                                    <i class="fas fa-times-circle text-sm"></i>
                                </button>
                            @else
                                {{-- SOFT DELETE (PINDAHKAN KE ARSIP) --}}
                                <button type="button"
                                        onclick="confirmSoftDelete({{ $d->id }}, '{{ $d->nomor_surat }}', '{{ addslashes($d->siswa?->nama_lengkap ?? '-') }}')"
                                        class="inline-flex items-center justify-center w-9 h-9 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg transition-colors"
                                        title="Pindahkan ke Arsip">
                                    <i class="fas fa-archive text-sm"></i>
                                </button>
                            @endif
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

{{-- SCRIPT SWEETALERT KONFIRMASI --}}
@push('scripts')
<script>
function confirmSoftDelete(id, nomorSurat, namaSiswa) {
    Swal.fire({
        title: 'Arsipkan Dispensasi?',
        html: `
            <div class="text-left">
                <p class="mb-2">Data dispensasi ini akan dipindahkan ke <strong>Arsip</strong> (Soft Delete).</p>
                <div class="bg-gray-50 p-3 rounded-lg text-sm border border-gray-200">
                    <p><strong>No. Surat:</strong> ${nomorSurat}</p>
                    <p><strong>Siswa:</strong> ${namaSiswa}</p>
                </div>
                <p class="text-amber-700 text-xs mt-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Data yang diarsipkan tetap tersimpan aman dan dapat dipulihkan sewaktu-waktu.
                </p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-archive mr-1"></i> Ya, Arsipkan',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            submitFormAction(`/admin/semua-pengajuan/${id}`, 'DELETE', 'Mengarsipkan...');
        }
    });
}

function confirmRestore(id, nomorSurat) {
    Swal.fire({
        title: 'Pulihkan Dispensasi?',
        html: `
            <div class="text-left">
                <p class="mb-2">Apakah Anda yakin ingin memulihkan dispensasi <strong>#${nomorSurat}</strong> kembali ke daftar aktif?</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash-restore mr-1"></i> Ya, Pulihkan',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            submitFormAction(`/admin/semua-pengajuan/${id}/restore`, 'POST', 'Memulihkan...');
        }
    });
}

function confirmForceDelete(id, nomorSurat, namaSiswa) {
    Swal.fire({
        title: 'Hapus Permanen?',
        html: `
            <div class="text-left">
                <p class="mb-2">Apakah Anda yakin ingin menghapus data ini <strong>secara permanen</strong>?</p>
                <div class="bg-red-50 p-3 rounded-lg text-sm border border-red-200 text-red-900">
                    <p><strong>No. Surat:</strong> ${nomorSurat}</p>
                    <p><strong>Siswa:</strong> ${namaSiswa}</p>
                </div>
                <p class="text-red-600 text-xs mt-3 font-semibold">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Tindakan ini tidak dapat dibatalkan! File foto bukti, verifikasi, dan QR code akan dihapus permanen dari server.
                </p>
            </div>
        `,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash mr-1"></i> Hapus Permanen',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            submitFormAction(`/admin/semua-pengajuan/${id}/force-delete`, 'DELETE', 'Menghapus permanen...');
        }
    });
}

function submitFormAction(url, method, loadingText) {
    Swal.fire({
        title: loadingText || 'Memproses...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;

    form.appendChild(csrfInput);

    if (method !== 'POST') {
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = method;
        form.appendChild(methodInput);
    }

    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
@endsection
