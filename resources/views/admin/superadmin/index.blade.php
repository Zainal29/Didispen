@extends('admin.layouts.app')

@section('title', 'Kelola Super Admin')
@section('page-title', 'Hak Akses Super Admin (Dual-Role)')

@section('content')
<div class="space-y-6">
    {{-- HEADER CARD --}}
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
        <div class="p-5 sm:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                        <i class="fas fa-shield-alt text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 tracking-tight">Manajemen Super Admin & Hak Akses</h3>
                        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                            Atur siswa atau staf yang berhak menjadi Super Admin (Dual-Role Siswa & Admin).
                        </p>
                    </div>
                </div>
            </div>

            <button onclick="document.getElementById('formBeriAkses').scrollIntoView({ behavior: 'smooth' })"
                    class="w-full sm:w-auto min-h-[44px] bg-purple-600 hover:bg-purple-700 active:scale-95 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-xs flex items-center justify-center gap-2">
                <i class="fas fa-user-plus"></i> Beri Hak Super Admin
            </button>
        </div>

        @include('components.alert')

        {{-- PENJELASAN HAK AKSES DUAL-ROLE --}}
        <div class="px-5 sm:px-6 py-4 bg-purple-50/60 border-t border-purple-100 text-xs sm:text-sm text-purple-900 flex items-start gap-3">
            <i class="fas fa-info-circle text-purple-600 mt-0.5 text-base shrink-0"></i>
            <div class="space-y-1">
                <strong class="font-bold">Apa itu Fitur Super Admin (Dual-Role)?</strong>
                <p class="text-xs text-purple-800 leading-relaxed">
                    Siswa yang diberikan status <strong>Super Admin</strong> dapat mengakses <strong>Portal Siswa</strong> sekaligus <strong>Panel Administrator</strong> dengan akun yang sama. Selain itu, di formulir dispensasi siswa, akun Super Admin memiliki izin khusus untuk mengajukan dispensasi atas nama organisasi mana pun tanpa harus menjadi Ketua resmi.
                </p>
            </div>
        </div>

        {{-- STATS CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-100 border-t border-gray-100">
            <div class="p-5 flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 text-lg">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <div>
                    <span class="text-2xl font-bold text-gray-900">{{ $totalSuperadmin }}</span>
                    <p class="text-xs text-gray-500 font-medium">Total Akun Super Admin</p>
                </div>
            </div>

            <div class="p-5 flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 text-lg">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <span class="text-2xl font-bold text-gray-900">{{ $totalSiswaSuperadmin }}</span>
                    <p class="text-xs text-gray-500 font-medium">Siswa Super Admin (Dual-Role)</p>
                </div>
            </div>

            <div class="p-5 flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 text-lg">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <span class="text-2xl font-bold text-gray-900">{{ $totalNonSiswaSuperadmin }}</span>
                    <p class="text-xs text-gray-500 font-medium">Admin & Staf Utama</p>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD FORM TAMBAH SUPER ADMIN --}}
    <div id="formBeriAkses" class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5 sm:p-6 space-y-4">
        <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
            <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs">
                <i class="fas fa-plus"></i>
            </div>
            <div>
                <h4 class="font-bold text-gray-900 text-sm">Tambahkan Hak Super Admin ke Siswa / Akun</h4>
                <p class="text-xs text-gray-500">Cari nama siswa atau masukkan NIS/Email akun yang ingin dijadikan Super Admin.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.superadmin.store') }}" id="formStoreSuperadmin" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                <div class="md:col-span-8 relative">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">
                        Cari Siswa / Akun Pengguna <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search text-sm"></i>
                        </span>
                        <input type="text"
                               id="inputCariSiswa"
                               placeholder="Ketik nama siswa atau NIS (contoh: Zainal / 4717)..."
                               autocomplete="off"
                               class="w-full pl-10 pr-10 py-2.5 bg-gray-50 focus:bg-white border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 rounded-xl text-sm outline-none transition-all">
                        <button type="button" id="btnClearCari" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>

                    {{-- Hidden input identifier yang dikirimkan ke backend --}}
                    <input type="hidden" name="identifier" id="hiddenIdentifier" required>

                    {{-- Dropdown hasil autocomplete siswa --}}
                    <div id="autocompleteDropdown"
                         class="hidden absolute z-30 left-0 right-0 top-full mt-1.5 bg-white border border-gray-200 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-gray-100">
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Anda juga dapat mengetikkan langsung NIS atau Email lalu klik tombol simpan.</p>
                </div>

                <div class="md:col-span-4 flex items-end pt-0 md:pt-6">
                    <button type="submit"
                            id="btnSubmitSuperadmin"
                            class="w-full min-h-[44px] bg-purple-600 hover:bg-purple-700 active:scale-95 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-xs flex items-center justify-center gap-2">
                        <i class="fas fa-shield-alt"></i> Jadikan Super Admin
                    </button>
                </div>
            </div>

            {{-- Preview Siswa Terpilih --}}
            <div id="previewCard" class="hidden p-3.5 rounded-xl border border-purple-200 bg-purple-50/70 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center text-sm font-bold shrink-0">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-xs sm:text-sm font-bold text-purple-950 truncate" id="previewNama"></p>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white text-purple-800 border border-purple-200" id="previewKelas"></span>
                        </div>
                        <p class="text-[11px] text-purple-700" id="previewDetail"></p>
                    </div>
                </div>
                <button type="button" onclick="batalPilihan()" class="text-xs font-bold text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50">
                    Batal
                </button>
            </div>
        </form>
    </div>

    {{-- DAFTAR SUPER ADMIN AKTIF --}}
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h4 class="font-bold text-gray-900 text-base">Daftar Akun Super Admin Saat Ini</h4>
                <p class="text-xs text-gray-500">Semua akun yang memegang status Super Admin aktif.</p>
            </div>

            {{-- Form Cari Super Admin --}}
            <form method="GET" action="{{ route('admin.superadmin.index') }}" class="w-full sm:w-72">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Filter daftar super admin..."
                           class="w-full pl-8 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 outline-none">
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="p-4 min-w-[200px]">Nama Pengguna</th>
                        <th class="p-4 min-w-[140px]">Role Dasar</th>
                        <th class="p-4 min-w-[140px]">NIS / NIP</th>
                        <th class="p-4 min-w-[180px]">Email</th>
                        <th class="p-4 min-w-[120px]">Kelas / Info</th>
                        <th class="p-4 min-w-[100px] text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($superadmins as $adminUser)
                    <tr class="hover:bg-purple-50/30 transition-colors">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($adminUser->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-bold text-gray-900 text-xs sm:text-sm truncate">{{ $adminUser->name }}</span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                            <i class="fas fa-shield-alt text-[9px]"></i> Super Admin
                                        </span>
                                    </div>
                                    @if($adminUser->id === auth()->id())
                                        <span class="text-[10px] font-semibold text-emerald-600 block">(Akun Anda Sendiri)</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="p-4">
                            @if($adminUser->role === 'siswa')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    <i class="fas fa-user-graduate text-[10px]"></i> Siswa (Dual-Role)
                                </span>
                            @elseif($adminUser->role === 'guru')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fas fa-chalkboard-teacher text-[10px]"></i> Guru
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    <i class="fas fa-user-cog text-[10px]"></i> Administrator
                                </span>
                            @endif
                        </td>

                        <td class="p-4 font-mono text-xs text-gray-600">
                            {{ $adminUser->nis_nip ?? '-' }}
                        </td>

                        <td class="p-4 text-xs text-gray-600 font-mono">
                            {{ $adminUser->email }}
                        </td>

                        <td class="p-4 text-xs">
                            @if($adminUser->siswa && $adminUser->siswa->kelas)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $adminUser->siswa->kelas->nama_kelas }}
                                </span>
                            @elseif($adminUser->guru)
                                <span class="text-gray-500">{{ $adminUser->guru->mata_pelajaran ?? 'Guru' }}</span>
                            @else
                                <span class="text-gray-400 italic">Sistem Admin</span>
                            @endif
                        </td>

                        <td class="p-4 text-center">
                            @if($adminUser->id === auth()->id() && $totalSuperadmin <= 1)
                                <span class="text-[11px] text-gray-400 italic" title="Minimal 1 akun Super Admin harus tetap aktif">Terkunci</span>
                            @else
                                <button type="button"
                                        onclick="cabutSuperadmin({{ $adminUser->id }}, '{{ addslashes($adminUser->name) }}')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-red-200 bg-white hover:bg-red-50 text-red-600 text-xs font-bold transition-colors"
                                        title="Cabut status Super Admin">
                                    <i class="fas fa-user-minus"></i> Cabut
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400 text-xs">
                            <i class="fas fa-shield-alt text-2xl mb-2 text-gray-300 block"></i>
                            Tidak ada akun super admin yang sesuai filter pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($superadmins->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $superadmins->links() }}
        </div>
        @endif
    </div>
</div>

{{-- SCRIPT AUTOCOMPLETE & SWEETALERT CABUT --}}
@push('scripts')
<script>
    const inputCari = document.getElementById('inputCariSiswa');
    const hiddenId = document.getElementById('hiddenIdentifier');
    const dropdown = document.getElementById('autocompleteDropdown');
    const btnClear = document.getElementById('btnClearCari');
    const previewCard = document.getElementById('previewCard');
    const previewNama = document.getElementById('previewNama');
    const previewKelas = document.getElementById('previewKelas');
    const previewDetail = document.getElementById('previewDetail');

    let debounceTimer;

    inputCari.addEventListener('input', function() {
        const query = this.value.trim();
        btnClear.classList.toggle('hidden', query.length === 0);

        // Jika user mengetik manual, set hiddenId = nilai ketikan
        hiddenId.value = query;

        clearTimeout(debounceTimer);
        if (query.length < 2) {
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('admin.superadmin.search-siswa') }}?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    dropdown.innerHTML = '';
                    if (data.length === 0) {
                        dropdown.innerHTML = `<div class="p-3 text-center text-xs text-gray-500">Siswa tidak ditemukan. Anda dapat tetap mengetikkan NIS/Email manual.</div>`;
                        dropdown.classList.remove('hidden');
                        return;
                    }

                    data.forEach(item => {
                        const row = document.createElement('div');
                        row.className = 'p-3 hover:bg-purple-50 flex items-center justify-between cursor-pointer transition-colors';
                        row.innerHTML = `
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-gray-900 truncate">${item.nama}</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700">${item.kelas}</span>
                                    ${item.is_superadmin ? '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700">Sudah Super Admin</span>' : ''}
                                </div>
                                <p class="text-[11px] text-gray-500">NIS: ${item.nis} • Email: ${item.email}</p>
                            </div>
                            <span class="text-xs font-bold text-purple-600 shrink-0 ml-2">Pilih &rarr;</span>
                        `;
                        row.addEventListener('click', () => pilihSiswa(item));
                        dropdown.appendChild(row);
                    });

                    dropdown.classList.remove('hidden');
                })
                .catch(err => {
                    console.error('Error autocomplete:', err);
                });
        }, 250);
    });

    btnClear.addEventListener('click', function() {
        inputCari.value = '';
        hiddenId.value = '';
        dropdown.classList.add('hidden');
        this.classList.add('hidden');
        previewCard.classList.add('hidden');
    });

    document.addEventListener('click', function(e) {
        if (!inputCari.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    function pilihSiswa(item) {
        hiddenId.value = item.nis || item.id;
        inputCari.value = `${item.nama} (${item.nis})`;
        dropdown.classList.add('hidden');

        previewNama.textContent = item.nama;
        previewKelas.textContent = item.kelas;
        previewDetail.textContent = `NIS: ${item.nis} • Email: ${item.email}`;
        previewCard.classList.remove('hidden');
    }

    function batalPilihan() {
        hiddenId.value = '';
        inputCari.value = '';
        previewCard.classList.add('hidden');
        btnClear.classList.add('hidden');
    }

    function cabutSuperadmin(userId, userName) {
        Swal.fire({
            title: 'Cabut Hak Super Admin?',
            html: `Apakah Anda yakin ingin mencabut hak akses Super Admin dari <b>${userName}</b>?<br><span class="text-xs text-gray-500 mt-2 block">Akun ini tidak akan lagi dapat mengakses panel admin atau fitur bypass ekstra.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Cabut Akses',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/superadmin/${userId}`;
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
@endpush
@endsection
