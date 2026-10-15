@extends('admin.layouts.app')

@section('title', 'Organisasi & Ekstrakurikuler')
@section('page-title', 'Manajemen Organisasi & Ekstrakurikuler')

@section('content')
<div class="space-y-6">
    {{-- HEADER CARD --}}
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
        <div class="p-5 sm:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h3 class="text-xl font-bold text-gray-900 tracking-tight">Organisasi & Ekstrakurikuler</h3>
                    @if($isSuperAdmin)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                            <i class="fas fa-shield-halved text-purple-600"></i> Super Admin Mode
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            <i class="fas fa-eye text-blue-600"></i> Mode Pantau
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Kelola data ekstra dan penunjukan siswa sebagai Ketua resmi untuk dispensasi rombongan/organisasi.
                </p>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                @if($isSuperAdmin)
                    <button onclick="openCreateModal()" class="w-full sm:w-auto min-h-[44px] bg-purple-600 hover:bg-purple-700 active:scale-95 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-xs flex items-center justify-center gap-2">
                        <i class="fas fa-plus"></i> Tambah Ekstra / Organisasi
                    </button>
                @endif
            </div>
        </div>

        @include('components.alert')

        {{-- FILTER BAR --}}
        <div class="px-5 sm:px-6 py-4 bg-gray-50/70 border-t border-b border-gray-100">
            <form method="GET" action="{{ route('admin.organisasi.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                <div class="sm:col-span-6 md:col-span-7">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <i class="fas fa-search text-sm"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari nama organisasi, ketua siswa, atau guru pembina..."
                               class="w-full pl-9 pr-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-shadow">
                    </div>
                </div>

                <div class="sm:col-span-4 md:col-span-3">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Kategori</label>
                    <select name="kategori" onchange="this.form.submit()"
                            class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 outline-none cursor-pointer">
                        <option value="">Semua Kategori</option>
                        <option value="osis" {{ request('kategori') == 'osis' ? 'selected' : '' }}>OSIS</option>
                        <option value="ekstrakurikuler" {{ request('kategori') == 'ekstrakurikuler' ? 'selected' : '' }}>Ekstrakurikuler</option>
                        <option value="kejuruan" {{ request('kategori') == 'kejuruan' ? 'selected' : '' }}>Kejuruan</option>
                        <option value="komunitas" {{ request('kategori') == 'komunitas' ? 'selected' : '' }}>Komunitas</option>
                        <option value="lainnya" {{ request('kategori') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                <div class="sm:col-span-2 flex items-center gap-2">
                    <button type="submit" class="w-full py-2.5 px-3 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl transition-colors min-h-[42px] flex items-center justify-center">
                        <i class="fas fa-filter mr-1.5"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'kategori']))
                        <a href="{{ route('admin.organisasi.index') }}" class="py-2.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-xl transition-colors min-h-[42px] flex items-center justify-center" title="Reset filter">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- TABEL DATA ORGANISASI --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="p-4 pl-6">Organisasi / Ekstra</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4">Ketua Terpilih (Siswa)</th>
                        <th class="p-4">Guru Pembina</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 pr-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($organisasis as $org)
                    <tr class="hover:bg-purple-50/30 transition-colors group">
                        {{-- Nama & Singkatan --}}
                        <td class="p-4 pl-6 align-top">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                    {{ $org->singkatan ? substr($org->singkatan, 0, 3) : substr($org->nama_organisasi, 0, 2) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 leading-snug">{{ $org->nama_organisasi }}</h4>
                                    @if($org->singkatan)
                                        <span class="text-xs font-mono text-purple-700 font-semibold uppercase">{{ $org->singkatan }}</span>
                                    @endif
                                    @if($org->deskripsi)
                                        <p class="text-xs text-gray-400 mt-0.5 line-clamp-1 max-w-xs">{{ $org->deskripsi }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Kategori --}}
                        <td class="p-4 align-top">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                {{ $org->kategori === 'osis' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' :
                                   ($org->kategori === 'ekstrakurikuler' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                   ($org->kategori === 'kejuruan' ? 'bg-cyan-50 text-cyan-700 border border-cyan-200' : 'bg-gray-100 text-gray-700')) }}">
                                {{ ucfirst($org->kategori) }}
                            </span>
                        </td>

                        {{-- Ketua Siswa --}}
                        <td class="p-4 align-top">
                            @if($org->ketua)
                                <div class="flex items-start gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-xs shrink-0 mt-0.5">
                                        <i class="fas fa-crown text-[11px]"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 leading-tight">{{ $org->ketua->nama_lengkap }}</p>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="text-xs text-gray-500 font-mono">{{ $org->ketua->nis_nip }}</span>
                                            <span class="text-gray-300">&bull;</span>
                                            <span class="text-xs font-semibold text-blue-600">{{ $org->ketua->kelas?->nama_kelas ?? 'Kelas -' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-500 border border-dashed border-gray-300">
                                    <i class="fas fa-circle-question text-gray-400"></i> Belum Ada Ketua
                                </span>
                            @endif
                        </td>

                        {{-- Guru Pembina --}}
                        <td class="p-4 align-top">
                            @if($org->pembina)
                                <div class="text-xs">
                                    <p class="font-semibold text-gray-800">{{ $org->pembina->nama_lengkap }}</p>
                                    @if($org->pembina->nip)
                                        <p class="font-mono text-gray-400 text-[11px] mt-0.5">NIP: {{ $org->pembina->nip }}</p>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic">Belum ditentukan</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="p-4 text-center align-top">
                            @if($org->status_aktif)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-green-50 text-green-700 border border-green-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-500 border border-gray-200">
                                    Nonaktif
                                </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="p-4 pr-6 text-center align-middle whitespace-nowrap">
                            @if($isSuperAdmin)
                                <div class="flex items-center justify-center gap-1.5">
                                    <button onclick="openEditModal({{ $org->id }})"
                                            class="w-9 h-9 inline-flex items-center justify-center text-purple-700 hover:bg-purple-100 rounded-xl transition-colors cursor-pointer"
                                            title="Edit Organisasi & Tunjuk Ketua">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button onclick="confirmDelete({{ $org->id }}, '{{ addslashes($org->nama_organisasi) }}')"
                                            class="w-9 h-9 inline-flex items-center justify-center text-red-600 hover:bg-red-50 rounded-xl transition-colors cursor-pointer"
                                            title="Hapus Organisasi">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            @else
                                <button onclick="openEditModal({{ $org->id }}, true)"
                                        class="w-9 h-9 inline-flex items-center justify-center text-blue-600 hover:bg-blue-50 rounded-xl transition-colors cursor-pointer"
                                        title="Lihat Detail">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <div class="w-16 h-16 rounded-2xl bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-400 text-2xl mb-3">
                                    <i class="fas fa-users-slash"></i>
                                </div>
                                <p class="text-sm font-semibold text-gray-700">Belum ada organisasi yang terdaftar.</p>
                                <p class="text-xs text-gray-400 mt-1">Gunakan tombol tambah organisasi untuk mendaftarkan ekstrakurikuler sekolah.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($organisasis->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $organisasis->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL FORM (CREATE / EDIT) --}}
<div id="modalOrganisasi" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeModal()"></div>

        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full border border-gray-200">
            <form id="formOrganisasi" method="POST" action="{{ route('admin.organisasi.store') }}">
                @csrf
                <div id="methodContainer"></div>

                <div class="p-5 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-purple-50 to-indigo-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-xs">
                            <i class="fas fa-users-gear text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900" id="modalTitle">Tambah Organisasi</h3>
                            <p class="text-xs text-gray-500" id="modalSubtitle">Daftarkan nama ekstra dan tentukan ketuanya.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeModal()" class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition-colors">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="p-5 sm:p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    {{-- Nama Organisasi --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Nama Organisasi / Ekstrakurikuler <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_organisasi" id="inputNamaOrganisasi" required
                               placeholder="Contoh: Gerakan Pramuka"
                               class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-shadow">
                    </div>

                    {{-- Singkatan & Kategori --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Singkatan <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                            </label>
                            <input type="text" name="singkatan" id="inputSingkatan"
                                   placeholder="Contoh: PRAMUKA"
                                   class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-shadow">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Kategori <span class="text-red-500">*</span>
                            </label>
                            <select name="kategori" id="inputKategori" required
                                    class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 outline-none cursor-pointer">
                                <option value="ekstrakurikuler">Ekstrakurikuler</option>
                                <option value="osis">OSIS</option>
                                <option value="kejuruan">Kejuruan</option>
                                <option value="komunitas">Komunitas</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>

                    {{-- KETUA ORGANISASI (SISWA) - AUTOCOMPLETE --}}
                    <div class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/40 space-y-2">
                        <label class="block text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <i class="fas fa-crown text-amber-500"></i> Siswa Ketua Ekstra
                            </span>
                            <span class="text-[10px] text-amber-700 font-normal">Hanya ketua yang bisa ajukan dispen</span>
                        </label>

                        {{-- Hidden field untuk ID siswa --}}
                        <input type="hidden" name="ketua_siswa_id" id="inputKetuaSiswaId">

                        {{-- Kotak Terpilih (jika ada ketua) --}}
                        <div id="boxKetuaSelected" class="hidden items-center justify-between p-2.5 bg-white border border-amber-300 rounded-xl shadow-2xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-gray-900 truncate" id="textKetuaSelectedName"></p>
                                    <p class="text-[10px] text-gray-500" id="textKetuaSelectedClass"></p>
                                </div>
                            </div>
                            <button type="button" onclick="clearKetuaSiswa()" class="text-gray-400 hover:text-red-600 p-1 rounded-lg hover:bg-red-50 text-xs transition-colors" title="Lepas Ketua">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        {{-- Input Cari Siswa --}}
                        <div id="boxCariSiswa" class="relative">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                    <i class="fas fa-search text-xs"></i>
                                </span>
                                <input type="text" id="inputCariSiswa"
                                       placeholder="Ketik nama lengkap atau NIS siswa..."
                                       oninput="handleSearchSiswa(this.value)"
                                       autocomplete="off"
                                       class="w-full pl-8 pr-3 py-2 bg-white border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                            </div>

                            {{-- Dropdown Hasil Pencarian --}}
                            <div id="dropdownHasilSiswa" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-30 max-h-48 overflow-y-auto divide-y divide-gray-100">
                                {{-- Diisi via JS --}}
                            </div>
                        </div>
                    </div>

                    {{-- Guru Pembina --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Guru Pembina <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <select name="pembina_guru_id" id="inputPembinaGuruId"
                                class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 outline-none cursor-pointer">
                            <option value="">-- Pilih Guru Pembina --</option>
                            @foreach($gurus as $g)
                                <option value="{{ $g->id }}">{{ $g->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Deskripsi / Keterangan
                        </label>
                        <textarea name="deskripsi" id="inputDeskripsi" rows="2"
                                  placeholder="Catatan mengenai ekstra ini..."
                                  class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 outline-none"></textarea>
                    </div>

                    {{-- Status Aktif --}}
                    <div class="flex items-center gap-2 pt-1">
                        <input type="hidden" name="status_aktif" value="0">
                        <input type="checkbox" name="status_aktif" id="inputStatusAktif" value="1" checked
                               class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 cursor-pointer">
                        <label for="inputStatusAktif" class="text-sm font-semibold text-gray-800 cursor-pointer">
                            Status Organisasi Aktif
                        </label>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="p-4 sm:p-5 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-100 text-xs font-semibold transition-colors">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitModal" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-xs transition-colors flex items-center gap-1.5">
                        <i class="fas fa-save"></i> <span>Simpan Data</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SCRIPT INTERAKSI MODAL & AUTOCOMPLETE --}}
<script>
let searchTimeout = null;

function openCreateModal() {
    document.getElementById('formOrganisasi').reset();
    document.getElementById('formOrganisasi').action = "{{ route('admin.organisasi.store') }}";
    document.getElementById('methodContainer').innerHTML = '';
    document.getElementById('modalTitle').textContent = 'Tambah Ekstra / Organisasi';
    document.getElementById('modalSubtitle').textContent = 'Daftarkan nama ekstra baru dan tentukan siswa ketuanya.';
    document.getElementById('inputStatusAktif').checked = true;
    clearKetuaSiswa();

    setFormReadOnly(false);
    document.getElementById('btnSubmitModal').classList.remove('hidden');
    document.getElementById('modalOrganisasi').classList.remove('hidden');
}

function openEditModal(id, readOnly = false) {
    fetch(`{{ url('admin/organisasi') }}/${id}/edit`)
        .then(res => res.json())
        .then(data => {
            const form = document.getElementById('formOrganisasi');
            form.action = `{{ url('admin/organisasi') }}/${id}`;
            document.getElementById('methodContainer').innerHTML = '@method("PUT")';

            document.getElementById('inputNamaOrganisasi').value = data.nama_organisasi || '';
            document.getElementById('inputSingkatan').value = data.singkatan || '';
            document.getElementById('inputKategori').value = data.kategori || 'ekstrakurikuler';
            document.getElementById('inputPembinaGuruId').value = data.pembina_guru_id || '';
            document.getElementById('inputDeskripsi').value = data.deskripsi || '';
            document.getElementById('inputStatusAktif').checked = !!data.status_aktif;

            if (data.ketua_siswa_id && data.ketua_nama) {
                setKetuaSiswa(data.ketua_siswa_id, data.ketua_nama, '');
            } else {
                clearKetuaSiswa();
            }

            if (readOnly) {
                document.getElementById('modalTitle').textContent = 'Detail Organisasi';
                document.getElementById('modalSubtitle').textContent = 'Mode pantau data organisasi.';
                setFormReadOnly(true);
                document.getElementById('btnSubmitModal').classList.add('hidden');
            } else {
                document.getElementById('modalTitle').textContent = 'Edit Organisasi & Tunjuk Ketua';
                document.getElementById('modalSubtitle').textContent = 'Perbarui informasi dan pilih ketua resmi.';
                setFormReadOnly(false);
                document.getElementById('btnSubmitModal').classList.remove('hidden');
            }

            document.getElementById('modalOrganisasi').classList.remove('hidden');
        })
        .catch(err => {
            Swal.fire('Error', 'Gagal memuat data organisasi', 'error');
        });
}

function setFormReadOnly(isReadOnly) {
    document.querySelectorAll('#formOrganisasi input:not([type=hidden]), #formOrganisasi select, #formOrganisasi textarea')
        .forEach(el => {
            el.disabled = isReadOnly;
        });
}

function closeModal() {
    document.getElementById('modalOrganisasi').classList.add('hidden');
    document.getElementById('dropdownHasilSiswa').classList.add('hidden');
}

function handleSearchSiswa(val) {
    clearTimeout(searchTimeout);
    const q = val.trim();
    const dropdown = document.getElementById('dropdownHasilSiswa');

    if (q.length < 2) {
        dropdown.classList.add('hidden');
        dropdown.innerHTML = '';
        return;
    }

    searchTimeout = setTimeout(() => {
        fetch(`{{ route('admin.api.cari-siswa-ketua') }}?q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => {
                if (!data.length) {
                    dropdown.innerHTML = '<div class="p-3 text-xs text-gray-400 text-center">Siswa tidak ditemukan</div>';
                    dropdown.classList.remove('hidden');
                    return;
                }

                let html = '';
                data.forEach(s => {
                    html += `
                        <div onclick="setKetuaSiswa(${s.id}, '${escapeJs(s.nama)}', 'NIS: ${s.nis} &bull; ${escapeJs(s.kelas)}')"
                             class="p-2.5 hover:bg-purple-50 cursor-pointer transition-colors flex items-center justify-between text-xs">
                            <div>
                                <p class="font-bold text-gray-900">${s.nama}</p>
                                <p class="text-[11px] text-gray-500 font-mono">NIS: ${s.nis} &bull; <span class="text-blue-600 font-semibold">${s.kelas}</span></p>
                            </div>
                            <span class="text-purple-600 font-semibold text-[11px] flex items-center gap-1">
                                <i class="fas fa-check"></i> Pilih
                            </span>
                        </div>
                    `;
                });
                dropdown.innerHTML = html;
                dropdown.classList.remove('hidden');
            })
            .catch(() => {
                dropdown.classList.add('hidden');
            });
    }, 250);
}

function setKetuaSiswa(id, name, details) {
    document.getElementById('inputKetuaSiswaId').value = id;
    document.getElementById('textKetuaSelectedName').textContent = name;
    document.getElementById('textKetuaSelectedClass').innerHTML = details;
    document.getElementById('boxKetuaSelected').classList.remove('hidden');
    document.getElementById('boxKetuaSelected').classList.add('flex');
    document.getElementById('boxCariSiswa').classList.add('hidden');
    document.getElementById('dropdownHasilSiswa').classList.add('hidden');
    document.getElementById('inputCariSiswa').value = '';
}

function clearKetuaSiswa() {
    document.getElementById('inputKetuaSiswaId').value = '';
    document.getElementById('textKetuaSelectedName').textContent = '';
    document.getElementById('textKetuaSelectedClass').textContent = '';
    document.getElementById('boxKetuaSelected').classList.add('hidden');
    document.getElementById('boxKetuaSelected').classList.remove('flex');
    document.getElementById('boxCariSiswa').classList.remove('hidden');
}

function escapeJs(str) {
    return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function confirmDelete(id, name) {
    Swal.fire({
        title: 'Hapus Organisasi?',
        html: `Apakah Anda yakin ingin menghapus ekstra <b>${name}</b>?<br><small class="text-gray-400">Riwayat dispen yang sudah ada tidak akan hilang.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('admin/organisasi') }}/${id}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endsection
