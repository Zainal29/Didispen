@extends('admin.layouts.app')

@section('title', 'Manajemen Satpam')
@section('page-title', 'Kelola Akun Satpam')

@section('content')
{{-- ✅ PERBAIKAN: Pastikan include alert berada di paling atas section content --}}
@include('components.alert')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 min-w-0">
    {{-- Form Tambah Satpam --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 sm:p-6 h-fit min-w-0">
        <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">Tambah Satpam Baru</h3>
        <form action="{{ route('admin.satpam.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email / Username Login</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">ID / NIP Satpam</label>
                <input type="text" name="nis_nip" value="{{ old('nis_nip') }}" placeholder="Cth: SATPAM001" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Password</label>
                <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <button type="submit" class="w-full min-h-[44px] bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors flex items-center justify-center text-sm shadow-sm">
                Simpan Satpam
            </button>
        </form>
    </div>

    {{-- Tabel Daftar Satpam --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden min-w-0">
        <div class="p-4 sm:p-5 border-b border-gray-100">
            <h3 class="text-base sm:text-lg font-bold text-gray-800">Daftar Petugas Satpam</h3>
        </div>
        <div class="overflow-x-auto min-w-0 w-full">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                    <tr>
                        <th class="p-3.5">Nama</th>
                        <th class="p-3.5">Email</th>
                        <th class="p-3.5">ID/NIP</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($satpams as $s)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="p-3.5 font-semibold text-gray-900">{{ $s->name }}</td>
                        <td class="p-3.5 text-gray-600 break-all">{{ $s->email }}</td>
                        <td class="p-3.5 text-gray-600 font-mono text-xs">{{ $s->nis_nip ?? '-' }}</td>
                        <td class="p-3.5 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1">
                                <button onclick="openEditModal('{{ $s->id }}', '{{ $s->name }}', '{{ $s->email }}', '{{ $s->nis_nip }}')" class="w-9 h-9 inline-flex items-center justify-center text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form action="{{ route('admin.satpam.destroy', $s->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus akun satpam ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-9 h-9 inline-flex items-center justify-center text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">Belum ada data satpam.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Edit Satpam --}}
<div id="editModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center p-4 z-50 backdrop-blur-sm">
    <div class="bg-white rounded-xl max-w-md w-full max-h-[90dvh] flex flex-col p-5 sm:p-6 shadow-xl overflow-hidden">
        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-4 flex-shrink-0">Edit Data Satpam</h3>
        <form id="editForm" method="POST" class="flex flex-col min-h-0 flex-1">
            @csrf
            @method('PUT')
            <div class="space-y-4 overflow-y-auto flex-1 pr-1">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Lengkap</label>
                    <input type="text" id="edit_name" name="name" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-base sm:text-sm outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Email</label>
                    <input type="email" id="edit_email" name="email" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-base sm:text-sm outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">ID / NIP Satpam</label>
                    <input type="text" id="edit_nis_nip" name="nis_nip" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-base sm:text-sm outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Password Baru <span class="text-xs text-gray-400 font-normal">(Kosongkan jika tidak ingin mengubah)</span></label>
                    <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-base sm:text-sm outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 mt-4 flex-shrink-0">
                <button type="button" onclick="closeEditModal()" class="min-h-[44px] px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition-colors">Batal</button>
                <button type="submit" class="min-h-[44px] px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold transition-colors shadow-sm">Update</button>
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
</script>
@endpush
@endsection
