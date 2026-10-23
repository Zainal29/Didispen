@extends('admin.layouts.app')

@section('title', 'Semua Pengajuan')
@section('page-title', 'Semua Pengajuan Dispensasi')

@section('content')
<div class="space-y-5">

    {{-- NOTIFIKASI SUKSES / ERROR --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200/80 rounded-2xl flex items-center gap-3 text-emerald-800 shadow-2xs">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                <i class="ti ti-check text-lg"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold leading-none">Berhasil!</p>
                <p class="text-xs text-emerald-700 mt-1">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200/80 rounded-2xl flex items-center gap-3 text-rose-800 shadow-2xs">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                <i class="ti ti-alert-triangle text-lg"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold leading-none">Gagal!</p>
                <p class="text-xs text-rose-700 mt-1">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- TAB FILTER ARSIP (AKTIF / ARSIP / SEMUA) --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <a href="{{ request()->fullUrlWithQuery(['arsip' => 'aktif']) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-2xs {{ $arsipFilter === 'aktif' ? 'bg-blue-600 text-white shadow-blue-500/20' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80' }}">
            <i class="ti ti-circle-check text-base"></i>
            <span>Aktif</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $arsipFilter === 'aktif' ? 'bg-blue-500 text-white' : 'bg-slate-100 text-slate-600' }}">
                {{ $countAktif }}
            </span>
        </a>

        <a href="{{ request()->fullUrlWithQuery(['arsip' => 'arsip']) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-2xs {{ $arsipFilter === 'arsip' ? 'bg-amber-600 text-white shadow-amber-500/20' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80' }}">
            <i class="ti ti-archive text-base"></i>
            <span>Arsip (Terhapus)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $arsipFilter === 'arsip' ? 'bg-amber-500 text-white' : 'bg-amber-100 text-amber-800' }}">
                {{ $countArsip }}
            </span>
        </a>

        <a href="{{ request()->fullUrlWithQuery(['arsip' => 'semua']) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-2xs {{ $arsipFilter === 'semua' ? 'bg-slate-800 text-white shadow-slate-700/20' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80' }}">
            <i class="ti ti-layers-linked text-base"></i>
            <span>Semua Data</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $arsipFilter === 'semua' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600' }}">
                {{ $countSemua }}
            </span>
        </a>
    </div>

    {{-- MAIN CARD: FILTER & TABLE --}}
    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 overflow-hidden">
        
        {{-- Filter Section --}}
        <div class="p-5 border-b border-slate-100 bg-slate-50/40">
            <div class="flex items-center justify-between mb-3.5">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-3.5 bg-blue-600 rounded-full"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600">Penyaringan Data Pengajuan</h3>
                </div>
            </div>

            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                <input type="hidden" name="arsip" value="{{ $arsipFilter }}">

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status</label>
                    <select name="status" class="w-full h-10 border border-slate-200 rounded-xl px-3 text-xs bg-white text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                        <option value="">Semua Status</option>
                        @foreach(['menunggu','disetujui','ditolak','keluar','selesai','dibatalkan','kadaluarsa'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Jurusan</label>
                    <select name="jurusan_id" class="w-full h-10 border border-slate-200 rounded-xl px-3 text-xs bg-white text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusans as $j)
                            <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kelas</label>
                    <select name="kelas_id" class="w-full h-10 border border-slate-200 rounded-xl px-3 text-xs bg-white text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                        <option value="">Semua Kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Dari Tanggal</label>
                    <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="w-full h-10 border border-slate-200 rounded-xl px-3 text-xs bg-white text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full h-10 bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-4 text-xs font-bold transition-all shadow-sm shadow-blue-500/20 flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="ti ti-filter text-sm"></i>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Table Area --}}
        <div class="overflow-x-auto min-w-0 w-full">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 text-[11px] uppercase text-slate-400 font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="p-3.5 pl-5 text-left">No. Surat</th>
                        <th class="p-3.5 text-left">Siswa Pemohon</th>
                        <th class="p-3.5 text-left">Kelas</th>
                        <th class="p-3.5 text-left">Kategori</th>
                        <th class="p-3.5 text-left">Tujuan & Alasan</th>
                        <th class="p-3.5 text-left">Status</th>
                        <th class="p-3.5 text-left">Guru Piket</th>
                        <th class="p-3.5 pr-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($dispensasi as $d)
                    @php
                        $isTrashed = $d->trashed();
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors {{ $isTrashed ? 'bg-amber-50/30' : '' }}">
                        <td class="p-3.5 pl-5 font-mono text-xs text-slate-700 font-bold whitespace-nowrap">
                            <div>{{ $d->nomor_surat }}</div>
                            @if($d->isRombongan())
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 mt-1">
                                    <i class="ti ti-users text-xs"></i> Rombongan
                                    @php $tot = ($d->anggota_siswa_count ?? $d->anggotaSiswa?->count() ?? 0) + 1; @endphp
                                    @if($tot > 1)
                                        <span class="bg-blue-200/80 text-blue-900 text-[9px] px-1 rounded-sm ml-0.5 font-mono">{{ $tot }}</span>
                                    @endif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200 mt-1">
                                    <i class="ti ti-user text-xs"></i> Individu
                                </span>
                            @endif
                        </td>
                        <td class="p-3.5 font-bold text-slate-900">{{ $d->siswa?->nama_lengkap ?? '-' }}</td>
                        <td class="p-3.5 text-slate-600 whitespace-nowrap">{{ $d->siswa?->kelas?->nama_kelas ?? '-' }}</td>
                        <td class="p-3.5 capitalize text-slate-600 whitespace-nowrap">{{ str_replace('_', ' ', $d->kategori) }}</td>
                        <td class="p-3.5 text-slate-600 max-w-xs">{{ Str::limit($d->tujuan, 35) }}</td>
                        <td class="p-3.5 whitespace-nowrap">
                            <div class="flex flex-col gap-1 items-start">
                                @php
                                    $badge = $d->status_badge;
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-bold border {{ $badge['class'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                    {{ $badge['text'] }}
                                </span>
                                @if($isTrashed)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="ti ti-archive text-xs"></i> Arsip
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="p-3.5 text-slate-600 whitespace-nowrap">{{ $d->guru?->nama_lengkap ?? '-' }}</td>
                        <td class="p-3.5 pr-5 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- LIHAT DETAIL --}}
                                <a href="{{ route('admin.semua.pengajuan.show', $d->id) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white rounded-xl transition-all shadow-2xs"
                                   title="Lihat Detail">
                                    <i class="ti ti-eye text-base"></i>
                                </a>

                                @if($isTrashed)
                                    {{-- PULIHKAN DARI ARSIP --}}
                                    <button type="button"
                                            onclick="confirmRestore({{ $d->id }}, '{{ $d->nomor_surat }}')"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white rounded-xl transition-all shadow-2xs"
                                            title="Pulihkan dari Arsip">
                                        <i class="ti ti-rotate-clockwise-2 text-base"></i>
                                    </button>

                                    {{-- HAPUS PERMANEN --}}
                                    <button type="button"
                                            onclick="confirmForceDelete({{ $d->id }}, '{{ $d->nomor_surat }}', '{{ addslashes($d->siswa?->nama_lengkap ?? '-') }}')"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white rounded-xl transition-all shadow-2xs"
                                            title="Hapus Permanen">
                                        <i class="ti ti-trash-x text-base"></i>
                                    </button>
                                @else
                                    {{-- SOFT DELETE (PINDAHKAN KE ARSIP) --}}
                                    <button type="button"
                                            onclick="confirmSoftDelete({{ $d->id }}, '{{ $d->nomor_surat }}', '{{ addslashes($d->siswa?->nama_lengkap ?? '-') }}')"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-amber-50 hover:bg-amber-600 text-amber-600 hover:text-white rounded-xl transition-all shadow-2xs"
                                            title="Pindahkan ke Arsip">
                                        <i class="ti ti-archive text-base"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-slate-400">
                            <i class="ti ti-inbox text-4xl mb-2 text-slate-300 block"></i>
                            <p class="text-xs font-semibold">Tidak ada data pengajuan yang sesuai dengan filter.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dispensasi->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/40">
            {{ $dispensasi->links() }}
        </div>
        @endif
    </div>

</div>

{{-- SCRIPT SWEETALERT KONFIRMASI --}}
@push('scripts')
<script>
function confirmSoftDelete(id, nomorSurat, namaSiswa) {
    Swal.fire({
        title: 'Arsipkan Dispensasi?',
        html: `
            <div class="text-left">
                <p class="mb-2 text-sm text-slate-600">Data dispensasi ini akan dipindahkan ke <strong>Arsip</strong> (Soft Delete).</p>
                <div class="bg-slate-50 p-3 rounded-xl text-xs border border-slate-200">
                    <p><strong>No. Surat:</strong> ${nomorSurat}</p>
                    <p class="mt-1"><strong>Siswa:</strong> ${namaSiswa}</p>
                </div>
                <p class="text-amber-700 text-xs mt-3 flex items-center gap-1.5 font-medium">
                    <i class="ti ti-info-circle text-base"></i>
                    <span>Data yang diarsipkan tetap tersimpan aman dan dapat dipulihkan sewaktu-waktu.</span>
                </p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="ti ti-archive mr-1"></i> Ya, Arsipkan',
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
                <p class="mb-2 text-sm text-slate-600">Apakah Anda yakin ingin memulihkan dispensasi <strong>#${nomorSurat}</strong> kembali ke daftar aktif?</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="ti ti-rotate-clockwise-2 mr-1"></i> Ya, Pulihkan',
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
                <p class="mb-2 text-sm text-slate-600">Apakah Anda yakin ingin menghapus data ini <strong>secara permanen</strong>?</p>
                <div class="bg-rose-50 p-3 rounded-xl text-xs border border-rose-200 text-rose-900">
                    <p><strong>No. Surat:</strong> ${nomorSurat}</p>
                    <p class="mt-1"><strong>Siswa:</strong> ${namaSiswa}</p>
                </div>
                <p class="text-rose-600 text-xs mt-3 font-semibold flex items-center gap-1.5">
                    <i class="ti ti-alert-triangle text-base"></i>
                    <span>Tindakan ini tidak dapat dibatalkan! File foto bukti, verifikasi, dan QR code akan dihapus permanen.</span>
                </p>
            </div>
        `,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="ti ti-trash mr-1"></i> Hapus Permanen',
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
