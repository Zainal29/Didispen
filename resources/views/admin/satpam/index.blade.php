@extends('admin.layouts.app')

@section('title', 'Manajemen Satpam')
@section('page-title', 'Kelola Petugas Satpam')

@section('content')
<div class="space-y-5">
    @include('components.alert')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 min-w-0">
        {{-- Form Tambah Satpam --}}
        <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 p-5 sm:p-6 h-fit min-w-0">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="ti ti-user-plus text-base"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 leading-tight">Tambah Petugas Satpam</h3>
                    <p class="text-[11px] text-slate-400">Buat akun pos keamanan baru</p>
                </div>
            </div>

            <form action="{{ route('admin.satpam.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required 
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
                           placeholder="Contoh: Budi Santoso">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email / Username Login</label>
                    <input type="email" name="email" value="{{ old('email') }}" required 
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
                           placeholder="satpam@smkn1bangsri.sch.id">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ID / NIP Satpam</label>
                    <input type="text" name="nis_nip" value="{{ old('nis_nip') }}" placeholder="Cth: SATPAM001" 
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required 
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
                           placeholder="Minimal 8 karakter">
                </div>
                <button type="submit" class="w-full min-h-[40px] bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-xl transition-all text-xs shadow-sm shadow-blue-500/20 flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="ti ti-device-floppy text-base"></i>
                    <span>Simpan Akun Satpam</span>
                </button>
            </form>
        </div>

        {{-- Tabel Daftar Satpam --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-2xs border border-slate-200/80 overflow-hidden min-w-0">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-3.5 bg-blue-600 rounded-full"></span>
                    <h3 class="text-sm font-bold text-slate-900 leading-tight">Daftar Petugas Satpam</h3>
                </div>
                <span class="text-xs font-semibold text-slate-400">Total {{ $satpams->count() }} Petugas</span>
            </div>
            <div class="overflow-x-auto min-w-0 w-full">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 text-[11px] uppercase text-slate-400 font-bold tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="p-3.5 pl-5">Nama Petugas</th>
                            <th class="p-3.5">Email / Username</th>
                            <th class="p-3.5">ID / NIP</th>
                            <th class="p-3.5 pr-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($satpams as $s)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="p-3.5 pl-5 font-bold text-slate-900">{{ $s->name }}</td>
                            <td class="p-3.5 text-slate-600 break-all font-mono">{{ $s->email }}</td>
                            <td class="p-3.5 text-slate-600 font-mono">{{ $s->nis_nip ?? '-' }}</td>
                            <td class="p-3.5 pr-5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button onclick="openEditModal('{{ $s->id }}', '{{ $s->name }}', '{{ $s->email }}', '{{ $s->nis_nip }}')" 
                                            class="w-8 h-8 inline-flex items-center justify-center text-blue-600 hover:bg-blue-600 hover:text-white bg-blue-50 rounded-xl transition-all shadow-2xs" 
                                            title="Edit Satpam">
                                        <i class="ti ti-edit text-base"></i>
                                    </button>

                                    <form action="{{ route('admin.satpam.destroy', $s->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus akun satpam ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="w-8 h-8 inline-flex items-center justify-center text-rose-600 hover:bg-rose-600 hover:text-white bg-rose-50 rounded-xl transition-all shadow-2xs" 
                                                title="Hapus Satpam">
                                            <i class="ti ti-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-12 text-center text-slate-400">
                                <i class="ti ti-shield-off text-4xl mb-2 text-slate-300 block"></i>
                                <p class="text-xs font-semibold">Belum ada data satpam terdaftar.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit Satpam --}}
<div id="editModal" class="fixed inset-0 bg-slate-950/60 hidden items-center justify-center p-4 z-50 backdrop-blur-xs">
    <div class="bg-white rounded-3xl max-w-md w-full max-h-[90dvh] flex flex-col shadow-2xl overflow-hidden border border-slate-200/80">
        <div class="bg-gradient-to-r from-slate-900 to-blue-950 px-6 py-4 flex justify-between items-center shrink-0 border-b border-slate-800 text-white">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="ti ti-shield-check text-lg text-blue-400"></i> Edit Data Satpam
            </h3>
            <button type="button" onclick="closeEditModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>

        <form id="editForm" method="POST" class="flex flex-col min-h-0 flex-1">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-3.5 overflow-y-auto flex-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" id="edit_name" name="name" required 
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email / Username</label>
                    <input type="email" id="edit_email" name="email" required 
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ID / NIP Satpam</label>
                    <input type="text" id="edit_nis_nip" name="nis_nip" 
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Password Baru <span class="text-[10px] text-slate-400 font-normal">(Kosongkan jika tidak diubah)</span></label>
                    <input type="password" name="password" 
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                           placeholder="••••••••">
                </div>
            </div>
            <div class="flex justify-end gap-2 px-6 py-3.5 border-t border-slate-100 bg-slate-50/80 shrink-0">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-700 text-xs font-bold hover:bg-slate-100 transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-xs font-bold transition-all shadow-sm shadow-blue-500/20">Perbarui Data</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditModal(id, name, email, nis_nip) {
    const modal = document.getElementById('editModal');
    const form = document.getElementById('editForm');

    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_nis_nip').value = nis_nip !== 'null' ? nis_nip : '';

    form.action = `{{ url('admin/satpam') }}/${id}`;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeEditModal();
});
</script>
@endpush
@endsection
