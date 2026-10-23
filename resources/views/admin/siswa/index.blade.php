@extends('admin.layouts.app')

@section('title', 'Data Siswa')
@section('page-title', 'Manajemen Data Siswa')

@section('content')
<div class="space-y-5">
    
    {{-- HEADER CARD --}}
    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 overflow-hidden">
        <div class="p-5 sm:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-1.5 h-3.5 bg-blue-600 rounded-full"></span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Master Data</span>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Daftar Siswa</h3>
                <p class="text-xs text-slate-500 mt-0.5">Kelola data siswa, rombel kelas, dan integrasi SiPintu Gateway.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                {{-- Tombol Sync SiPintu --}}
                <form action="{{ route('admin.sipintu.sync-siswa') }}" method="POST" class="flex items-center gap-2 w-full sm:w-auto"
                      onsubmit="this.querySelectorAll('button').forEach(b => { b.disabled=true; b.innerHTML='<i class=\'ti ti-loader-2 animate-spin\'></i> Sinkronisasi...'; });">
                    @csrf
                    <button type="submit" class="flex-1 sm:flex-none min-h-[42px] bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm shadow-emerald-500/20 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="ti ti-refresh text-base"></i>
                        <span>Sinkron SiPintu</span>
                    </button>
                </form>
            </div>
        </div>

        @include('components.alert')

        {{-- FILTER BAR --}}
        <div class="px-5 sm:px-6 py-4 bg-slate-50/50 border-t border-b border-slate-100">
            <form method="GET" id="filterForm" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-end">
                <div class="md:col-span-4">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Cari Siswa</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="ti ti-search text-base"></i></span>
                        <input type="text" name="search" id="searchInput" value="{{ request('search') }}" placeholder="Nama, NIS, atau email..."
                               class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kelas</label>
                    <select name="kelas_id" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none cursor-pointer">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Hak Akses</label>
                    <select name="role_filter" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none cursor-pointer">
                        <option value="">Semua Siswa</option>
                        <option value="superadmin" {{ request('role_filter') == 'superadmin' ? 'selected' : '' }}>Hanya Super Admin</option>
                        <option value="regular" {{ request('role_filter') == 'regular' ? 'selected' : '' }}>Siswa Biasa</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Urutan</label>
                    <select name="sort" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none cursor-pointer">
                        @foreach($sortable as $key => $label)
                            <option value="{{ $key }}" {{ $sort == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2 flex gap-1.5">
                    <button type="submit" class="flex-1 min-h-[38px] px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm shadow-blue-500/20 flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="ti ti-filter text-sm"></i> Cari
                    </button>
                    <a href="{{ route('admin.siswa.index') }}" class="min-h-[38px] min-w-[38px] px-2.5 py-2 bg-white border border-slate-200 text-slate-500 hover:text-slate-800 rounded-xl hover:bg-slate-50 text-xs font-semibold transition-colors flex items-center justify-center" title="Reset Filter">
                        <i class="ti ti-rotate-2 text-base"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- TABLE AREA --}}
        <div class="overflow-x-auto min-w-0 w-full">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 text-[11px] uppercase text-slate-400 font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="p-3.5 pl-5 min-w-[120px]">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nis_nip', 'dir' => ($sort == 'nis_nip' && $dir == 'asc' ? 'desc' : 'asc')]) }}" class="hover:text-blue-600 transition-colors inline-flex items-center gap-1">
                                NIS / NISN @if($sort == 'nis_nip')<i class="ti ti-arrow-narrow-{{ $dir == 'asc' ? 'up' : 'down' }} text-blue-600"></i>@endif
                            </a>
                        </th>
                        <th class="p-3.5 min-w-[180px]">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nama_lengkap', 'dir' => ($sort == 'nama_lengkap' && $dir == 'asc' ? 'desc' : 'asc')]) }}" class="hover:text-blue-600 transition-colors inline-flex items-center gap-1">
                                Nama Lengkap @if($sort == 'nama_lengkap')<i class="ti ti-arrow-narrow-{{ $dir == 'asc' ? 'up' : 'down' }} text-blue-600"></i>@endif
                            </a>
                        </th>
                        <th class="p-3.5 min-w-[100px]">Kelas</th>
                        <th class="p-3.5 min-w-[140px]">Jurusan</th>
                        <th class="p-3.5 min-w-[180px]">Email</th>
                        <th class="p-3.5 min-w-[100px]">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'dir' => ($sort == 'created_at' && $dir == 'asc' ? 'desc' : 'asc')]) }}" class="hover:text-blue-600 transition-colors inline-flex items-center gap-1">
                                Terdaftar @if($sort == 'created_at')<i class="ti ti-arrow-narrow-{{ $dir == 'asc' ? 'up' : 'down' }} text-blue-600"></i>@endif
                            </a>
                        </th>
                        <th class="p-3.5 pr-5 text-center min-w-[100px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($siswas as $s)
                    <tr class="hover:bg-slate-50/70 transition-colors group">
                        <td class="p-3.5 pl-5 font-mono text-xs text-slate-500 font-bold align-middle">{{ $s->user?->nis_nip ?? '-' }}</td>

                        <td class="p-3.5 align-middle">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-bold text-slate-900">{{ $s->nama_lengkap }}</span>
                                @if($s->user?->isSuperAdmin())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200" title="Super Admin Sistem">
                                        <i class="ti ti-shield-check text-xs text-blue-600"></i> Super Admin
                                    </span>
                                @endif
                            </div>
                        </td>

                        <td class="p-3.5 align-middle whitespace-nowrap">
                            @if($s->kelas)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $s->kelas->nama_kelas }}
                                </span>
                            @else
                                <span class="text-slate-400 italic text-xs">Belum ada</span>
                            @endif
                        </td>

                        <td class="p-3.5 align-middle text-xs text-slate-600 whitespace-nowrap">
                            {{ $s->jurusan?->nama_jurusan ?? $s->kelas?->jurusan?->nama_jurusan ?? '-' }}
                        </td>

                        <td class="p-3.5 align-middle text-xs text-slate-500 font-mono break-all">{{ $s->user?->email ?? '-' }}</td>
                        <td class="p-3.5 align-middle text-xs text-slate-500 whitespace-nowrap">{{ $s->created_at?->format('d/m/Y') ?? '-' }}</td>

                        <td class="p-3.5 pr-5 text-center align-middle whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($s->user?->isSuperAdmin())
                                    <button type="button"
                                            onclick="toggleSuperadmin({{ $s->id }}, '{{ addslashes($s->nama_lengkap) }}', true)"
                                            class="w-8 h-8 inline-flex items-center justify-center text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl transition-all shadow-2xs"
                                            title="Cabut Status Super Admin">
                                        <i class="ti ti-shield-x text-base"></i>
                                    </button>
                                @else
                                    <button type="button"
                                            onclick="toggleSuperadmin({{ $s->id }}, '{{ addslashes($s->nama_lengkap) }}', false)"
                                            class="w-8 h-8 inline-flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all shadow-2xs"
                                            title="Jadikan Super Admin">
                                        <i class="ti ti-shield-plus text-base"></i>
                                    </button>
                                @endif
                                <button onclick="editItem({{ $s->id }})" class="w-8 h-8 inline-flex items-center justify-center text-blue-600 hover:bg-blue-600 hover:text-white bg-blue-50 rounded-xl transition-all shadow-2xs" title="Lihat Data">
                                    <i class="ti ti-eye text-base"></i>
                                </button>
                                <button onclick="deleteItem({{ $s->id }}, '{{ addslashes($s->nama_lengkap) }}')" class="w-8 h-8 inline-flex items-center justify-center text-rose-600 hover:bg-rose-600 hover:text-white bg-rose-50 rounded-xl transition-all shadow-2xs" title="Hapus Data">
                                    <i class="ti ti-trash text-base"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-400">
                            <i class="ti ti-user-x text-4xl mb-2 text-slate-300 block"></i>
                            <p class="text-xs font-semibold">Belum ada data siswa yang sesuai.</p>
                            <p class="text-[11px] text-slate-400 mt-1">Lakukan sinkronisasi SiPintu untuk memuat data terbaru.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($siswas->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $siswas->links() }}
        </div>
        @endif
    </div>
</div>

{{-- MODAL DETAIL / FORM SISWA --}}
<div id="formModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" aria-hidden="true" onclick="closeModal()"></div>

    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative z-10 w-full max-w-2xl bg-white rounded-3xl text-left shadow-2xl overflow-hidden max-h-[90dvh] flex flex-col my-8 border border-slate-200/80">
            <form id="siswaForm" method="POST" action="{{ route('admin.siswa.store') }}" class="flex flex-col min-h-0 flex-1">
                @csrf
                <input type="hidden" id="siswaId" name="siswa_id">
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <div class="bg-gradient-to-r from-slate-900 to-blue-950 px-6 py-4 flex justify-between items-center shrink-0 border-b border-slate-800 text-white">
                    <h3 id="modalTitle" class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="ti ti-user text-lg text-blue-400"></i> Detail Data Siswa
                    </h3>
                    <button type="button" onclick="closeModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                        <i class="ti ti-x text-lg"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    {{-- Info Box SiPintu --}}
                    <div class="p-3.5 bg-blue-50/80 border border-blue-200/80 rounded-2xl text-xs text-blue-800 leading-relaxed flex items-start gap-3">
                        <i class="ti ti-info-circle text-lg text-blue-600 shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="font-bold">Sinkronisasi Data Otomatis:</strong>
                            Data akun, password, dan rombel dikelola terpusat dari SiPintu Gateway. Field kontak dan data pribadi diperbarui mandiri oleh siswa.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" required readonly
                                   class="w-full border border-slate-200 bg-slate-50 rounded-xl px-3.5 py-2 text-xs text-slate-800 outline-none">
                            <span class="text-rose-600 text-[11px] mt-1 block" id="error_nama_lengkap"></span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">NIS / NISN</label>
                            <input type="text" id="nis_nip" name="nis_nip" required readonly
                                   class="w-full border border-slate-200 bg-slate-50 rounded-xl px-3.5 py-2 text-xs font-mono text-slate-800 outline-none">
                            <span class="text-rose-600 text-[11px] mt-1 block" id="error_nis_nip"></span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Akun</label>
                            <input type="email" id="email" name="email" required readonly
                                   class="w-full border border-slate-200 bg-slate-50 rounded-xl px-3.5 py-2 text-xs font-mono text-slate-800 outline-none">
                            <span class="text-rose-600 text-[11px] mt-1 block" id="error_email"></span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jurusan</label>
                            <select id="jurusan_id" name="jurusan_id" disabled class="w-full border border-slate-200 bg-slate-50 rounded-xl px-3.5 py-2 text-xs text-slate-700 outline-none">
                                <option value="">-- Jurusan --</option>
                                @foreach($jurusanList as $j)
                                    <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kelas</label>
                            <select id="kelas_id" name="kelas_id" disabled class="w-full border border-slate-200 bg-slate-50 rounded-xl px-3.5 py-2 text-xs text-slate-700 outline-none">
                                <option value="">-- Kelas --</option>
                                @foreach($kelasList as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Lahir</label>
                            <input type="date" id="tanggal_lahir" name="tanggal_lahir" readonly
                                   class="w-full border border-slate-200 bg-slate-50 rounded-xl px-3.5 py-2 text-xs text-slate-800 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. WhatsApp</label>
                            <input type="text" id="no_telepon_display" readonly
                                   class="w-full border border-slate-200 bg-slate-50 rounded-xl px-3.5 py-2 text-xs text-slate-800 font-mono outline-none">
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50/80 px-6 py-3.5 flex justify-end gap-2 border-t border-slate-100 shrink-0">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-700 text-xs font-bold hover:bg-slate-100 transition-colors">
                        Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editItem(siswaId) {
    fetch(`/admin/siswa/${siswaId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Gagal memuat data');
        return res.json();
    })
    .then(data => {
        document.getElementById('siswaForm').reset();
        document.getElementById('siswaId').value = data.id;
        document.getElementById('formMethod').value = 'PATCH';
        document.getElementById('modalTitle').innerHTML = '<i class="ti ti-user text-blue-400"></i> Detail Siswa: ' + (data.nama_lengkap || '');

        document.getElementById('nama_lengkap').value = data.nama_lengkap || '';
        document.getElementById('nis_nip').value = data.user?.nis_nip || '';
        document.getElementById('email').value = data.user?.email || '';
        document.getElementById('tanggal_lahir').value = data.tanggal_lahir || '';
        document.getElementById('no_telepon_display').value = data.no_telepon || 'Belum diisi';
        document.getElementById('jurusan_id').value = data.jurusan_id || '';
        document.getElementById('kelas_id').value = data.kelas_id || '';

        document.getElementById('formModal').classList.remove('hidden');
    })
    .catch(err => {
        console.error('Error:', err);
        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal memuat data siswa.' });
    });
}

function closeModal() {
    document.getElementById('formModal').classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

function deleteItem(id, name) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: `Apakah Anda yakin ingin menghapus data siswa <b>"${name}"</b>?<br><span class="text-xs text-slate-500 mt-2 block">Tindakan ini juga akan menghapus akun login siswa terkait.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="ti ti-trash mr-1"></i> Ya, Hapus',
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

function toggleSuperadmin(siswaId, namaSiswa, isSuperAdmin) {
    const actionText = isSuperAdmin ? 'Cabut Status Super Admin' : 'Jadikan Super Admin';
    const confirmText = isSuperAdmin ? 'Ya, Cabut Akses' : 'Ya, Jadikan Super Admin';
    const confirmColor = isSuperAdmin ? '#e11d48' : '#2563eb';
    const descText = isSuperAdmin
        ? `Apakah Anda yakin ingin mencabut hak akses Super Admin untuk siswa <b>"${namaSiswa}"</b>?`
        : `Apakah Anda yakin ingin mengangkat siswa <b>"${namaSiswa}"</b> sebagai <b>Super Admin</b>? Siswa akan memiliki hak Dual-Role di portal siswa dan admin.`;

    Swal.fire({
        title: actionText + '?',
        html: descText,
        icon: isSuperAdmin ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#64748b',
        confirmButtonText: confirmText,
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/siswa/${siswaId}/toggle-superadmin`;
            form.innerHTML = `@csrf @method('PATCH')`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection
