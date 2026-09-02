@extends('admin.layouts.app')

@section('title', 'Data Siswa')
@section('page-title', 'Manajemen Data Siswa')

@section('content')
<div class="space-y-6">
    {{-- HEADER CARD --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 sm:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-xl font-bold text-gray-800 tracking-tight">Daftar Siswa</h3>
                <p class="text-sm text-gray-500 mt-1">Kelola data siswa, kelas, dan jurusan dari SiPintu Gateway.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                {{-- Tombol Sync SiPintu --}}
                <form action="{{ route('admin.sipintu.sync-siswa') }}" method="POST" class="flex items-center gap-2 w-full sm:w-auto"
                      onsubmit="this.querySelectorAll('button').forEach(b => { b.disabled=true; b.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Memproses...'; });">
                    @csrf
                    <button type="submit" class="flex-1 sm:flex-none bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm hover:shadow flex items-center justify-center gap-2">
                        <i class="fas fa-sync-alt"></i> <span class="hidden sm:inline">Sinkronisasi</span><span class="sm:hidden">Sync</span>
                    </button>
                </form>

                {{-- Tombol Tambah --}}
                <button onclick="openModal()" class="flex-1 sm:flex-none bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm hover:shadow flex items-center justify-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Siswa
                </button>
            </div>
        </div>

        @include('components.alert')

        {{-- FILTER BAR --}}
        <div class="px-5 sm:px-6 py-4 bg-gray-50/80 border-t border-b border-gray-100">
            <form method="GET" id="filterForm" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Cari Siswa</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fas fa-search text-sm"></i></span>
                        <input type="text" name="search" id="searchInput" value="{{ request('search') }}" placeholder="Nama, NIS, atau email..."
                               class="w-full pl-9 pr-3 py-2.5 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow">
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Filter Kelas</label>
                    <select name="kelas_id" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($kelass as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} ({{ $k->jurusan?->kode_jurusan ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Urutkan</label>
                    <select name="sort" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer">
                        @foreach($sortable as $key => $label)
                            <option value="{{ $key }}" {{ $sort == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-1.5">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Arah</label>
                    <select name="dir" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer">
                        <option value="asc" {{ $dir == 'asc' ? 'selected' : '' }}>Naik</option>
                        <option value="desc" {{ $dir == 'desc' ? 'selected' : '' }}>Turun</option>
                    </select>
                </div>

                <div class="md:col-span-1.5 flex gap-2">
                    <button type="submit" class="flex-1 px-3 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition-colors flex items-center justify-center gap-1.5">
                        <i class="fas fa-filter"></i> Cari
                    </button>
                    <a href="{{ route('admin.siswa.index') }}" class="px-3 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors flex items-center justify-center gap-1.5" title="Reset Filter">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- TABLE AREA (STRUKTUR ASLI DIPERTAHANKAN) --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                    <tr>
                        <th class="p-4 border-b border-gray-100 min-w-[120px]">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nis_nip', 'dir' => ($sort == 'nis_nip' && $dir == 'asc' ? 'desc' : 'asc')]) }}" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
                                NIS / NISN @if($sort == 'nis_nip')<i class="fas fa-sort-{{ $dir == 'asc' ? 'up' : 'down' }}"></i>@endif
                            </a>
                        </th>
                        <th class="p-4 border-b border-gray-100 min-w-[180px]">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nama_lengkap', 'dir' => ($sort == 'nama_lengkap' && $dir == 'asc' ? 'desc' : 'asc')]) }}" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
                                Nama Lengkap @if($sort == 'nama_lengkap')<i class="fas fa-sort-{{ $dir == 'asc' ? 'up' : 'down' }}"></i>@endif
                            </a>
                        </th>
                        <th class="p-4 border-b border-gray-100 min-w-[100px]">Kelas</th>
                        <th class="p-4 border-b border-gray-100 min-w-[140px]">Jurusan</th>
                        <th class="p-4 border-b border-gray-100 min-w-[180px]">Email</th>
                        <th class="p-4 border-b border-gray-100 min-w-[100px]">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'dir' => ($sort == 'created_at' && $dir == 'asc' ? 'desc' : 'asc')]) }}" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
                                Terdaftar @if($sort == 'created_at')<i class="fas fa-sort-{{ $dir == 'asc' ? 'up' : 'down' }}"></i>@endif
                            </a>
                        </th>
                        <th class="p-4 border-b border-gray-100 text-center min-w-[80px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($siswas as $s)
                    <tr class="hover:bg-indigo-50/40 transition-colors group">
                        <td class="p-4 font-mono text-xs text-gray-500 align-top">{{ $s->user->nis_nip ?? '-' }}</td>

                        <td class="p-4 align-top">
                            <div class="font-semibold text-gray-900">{{ $s->nama_lengkap }}</div>
                        </td>

                        <td class="p-4 align-top">
                            @if($s->kelas)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100 whitespace-nowrap">
                                    {{ $s->kelas->nama_kelas }}
                                </span>
                            @else
                                <span class="text-gray-400 italic text-xs">Belum ada</span>
                            @endif
                        </td>

                        <td class="p-4 align-top text-xs text-gray-600">
                            {{ $s->jurusan?->nama_jurusan ?? $s->kelas?->jurusan?->nama_jurusan ?? '-' }}
                        </td>

                        <td class="p-4 align-top text-xs text-gray-500 font-mono break-all">{{ $s->user->email ?? '-' }}</td>
                        <td class="p-4 align-top text-xs text-gray-500">{{ $s->created_at->format('d/m/Y') }}</td>

                        <td class="p-4 text-center align-middle whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity">
                                <button onclick="editItem({{ $s->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteItem({{ $s->id }}, '{{ addslashes($s->nama_lengkap) }}')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Data">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-10 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <i class="fas fa-user-slash text-4xl mb-3 opacity-50"></i>
                                <p class="text-sm font-medium">Belum ada data siswa.</p>
                                <p class="text-xs mt-1">Lakukan sinkronisasi SiPintu atau tambah manual.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($siswas->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
            {{ $siswas->links() }}
        </div>
        @endif
    </div>
</div>

{{-- MODAL FORM SISWA --}}
<div id="formModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
            <form id="siswaForm" method="POST" action="{{ route('admin.siswa.store') }}">
                @csrf
                <input type="hidden" id="siswaId" name="siswa_id">
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4 flex justify-between items-center">
                    <h3 id="modalTitle" class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fas fa-user-plus"></i> Tambah Siswa Baru
                    </h3>
                    <button type="button" onclick="closeModal()" class="text-indigo-100 hover:text-white transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                    {{-- Info Box SiPintu --}}
                    <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg text-xs text-blue-800 leading-relaxed flex gap-3">
                        <i class="fas fa-info-circle mt-0.5 text-blue-500 text-sm"></i>
                        <div>
                            <strong class="block mb-1">Catatan Sinkronisasi SiPintu:</strong>
                            Field <em>Alamat</em> dan <em>Password</em> dikelola otomatis oleh sistem SiPintu/Sijuna. Field <em>No. Telepon</em> dan <em>Tanggal Lahir</em> dapat diisi langsung oleh siswa melalui halaman Profil mereka.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"
                                   placeholder="Contoh: Ahmad Fauzi">
                            <span class="text-red-600 text-xs mt-1 block" id="error_nama_lengkap"></span>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">NIS / NISN <span class="text-red-500">*</span></label>
                            <input type="text" id="nis_nip" name="nis_nip" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all font-mono"
                                   placeholder="1234567890">
                            <span class="text-red-600 text-xs mt-1 block" id="error_nis_nip"></span>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email Sekolah <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"
                                   placeholder="nis@smkn1bangsri.sch.id">
                            <span class="text-red-600 text-xs mt-1 block" id="error_email"></span>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jurusan</label>
                            <select id="jurusan_id" name="jurusan_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer bg-white">
                                <option value="">-- Pilih Jurusan --</option>
                                @foreach($jurusans as $j)
                                    <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                                @endforeach
                            </select>
                            <span class="text-red-600 text-xs mt-1 block" id="error_jurusan_id"></span>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kelas</label>
                            <select id="kelas_id" name="kelas_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer bg-white">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelass as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kelas }} ({{ $k->jurusan?->kode_jurusan ?? '-' }})</option>
                                @endforeach
                            </select>
                            <span class="text-red-600 text-xs mt-1 block" id="error_kelas_id"></span>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tanggal Lahir</label>
                            <input type="date" id="tanggal_lahir" name="tanggal_lahir"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                            <span class="text-red-600 text-xs mt-1 block" id="error_tanggal_lahir"></span>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">No. Telepon / WA</label>
                            <input type="text" id="no_telepon_display" readonly
                                   class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-sm text-gray-700 font-mono outline-none cursor-not-allowed"
                                   placeholder="Belum diisi siswa">
                            <p class="text-gray-400 text-[11px] mt-1">Diisi siswa melalui halaman Profil (read-only).</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                    <button type="button" onclick="closeModal()" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 text-sm font-medium hover:bg-gray-100 transition-colors">
                        Batal
                    </button>
                    <button type="submit" form="siswaForm" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 shadow-sm transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ==========================================
// 1. Modal Functions
// ==========================================
function openModal() {
    document.getElementById('siswaForm').reset();
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('siswaId').value = '';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Tambah Siswa Baru';
    document.getElementById('siswaForm').action = "{{ route('admin.siswa.store') }}";
    clearErrors();
    document.getElementById('formModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('formModal').classList.add('hidden');
}

// Tutup modal dengan tombol ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

function editItem(siswaId) {
    fetch(`/admin/siswa/${siswaId}/edit`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('siswaForm').reset();
            document.getElementById('siswaId').value = siswaId;
            document.getElementById('formMethod').value = 'PATCH';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Edit Data Siswa';

            // Mengisi field form berdasarkan data JSON
            document.getElementById('nama_lengkap').value = data.nama_lengkap || '';
            document.getElementById('nis_nip').value = data.user?.nis_nip || '';
            document.getElementById('email').value = data.user?.email || '';
            document.getElementById('tanggal_lahir').value = data.tanggal_lahir ? data.tanggal_lahir.substring(0, 10) : '';
            document.getElementById('no_telepon_display').value = data.no_telepon || '';
            document.getElementById('jurusan_id').value = data.jurusan_id || '';
            document.getElementById('kelas_id').value = data.kelas_id || '';

            document.getElementById('siswaForm').action = `/admin/siswa/${siswaId}`;
            clearErrors();
            document.getElementById('formModal').classList.remove('hidden');
        })
        .catch(err => {
            console.error('Error:', err);
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal memuat data siswa.' });
        });
}

function deleteItem(id, name) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: `Apakah Anda yakin ingin menghapus data siswa <b>"${name}"</b>?<br><span class="text-xs text-gray-500 mt-2 block">Tindakan ini juga akan menghapus akun login siswa terkait.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus Permanen',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/siswa/${id}`;
            form.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function clearErrors() {
    document.querySelectorAll('[id^="error_"]').forEach(el => el.textContent = '');
}

// Handle Form Submit via Fetch untuk validasi AJAX
document.getElementById('siswaForm').addEventListener('submit', function(e) {
    e.preventDefault();
    clearErrors();

    const formData = new FormData(this);
    const url = this.action;
    const method = document.getElementById('formMethod').value;

    fetch(url, {
        method: method === 'PATCH' ? 'POST' : 'POST', // Laravel override method handled by hidden input
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('[name="_token"]')?.value,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => {
        if (!res.ok) {
            if (res.status === 422) {
                return res.json().then(data => {
                    Object.keys(data.errors).forEach(key => {
                        const errorEl = document.getElementById(`error_${key}`);
                        if (errorEl) errorEl.textContent = data.errors[key][0];
                    });
                    throw new Error('Validation error');
                });
            }
            throw new Error('Request failed');
        }
        return res.text();
    })
    .then(() => {
        Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data siswa berhasil disimpan.', timer: 1500, showConfirmButton: false })
            .then(() => window.location.reload());
    })
    .catch(err => {
        if (err.message !== 'Validation error') {
            console.error('Error:', err);
            Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: 'Silakan coba lagi.' });
        }
    });
});

// Klik backdrop untuk menutup modal
document.getElementById('formModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endpush
@endsection
