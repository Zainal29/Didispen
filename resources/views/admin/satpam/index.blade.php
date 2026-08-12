@extends('admin.layouts.app')

@section('title', 'Manajemen Satpam')
@section('page-title', 'Kelola Akun Satpam')

@section('content')
@include('components.alert')

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    {{-- Form Tambah Satpam --}}
    <div class="bg-white rounded-lg shadow p-6 h-fit">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Satpam Baru</h3>
        <form action="{{ route('admin.satpam.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email / Username Login</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">ID / NIP Satpam</label>
                <input type="text" name="nis_nip" value="{{ old('nis_nip') }}" placeholder="Cth: SATPAM001" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded transition">
                Simpan Satpam
            </button>
        </form>
    </div>

    {{-- Tabel Daftar Satpam --}}
    <div class="md:col-span-2 bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Daftar Petugas Satpam</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                    <tr>
                        <th class="p-3">Nama</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">ID/NIP</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-sm">
                    @forelse($satpams as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-semibold text-gray-800">{{ $s->name }}</td>
                        <td class="p-3 text-gray-600">{{ $s->email }}</td>
                        <td class="p-3 text-gray-600">{{ $s->nis_nip ?? '-' }}</td>
                        <td class="p-3 text-center space-x-2">
                            {{-- Tombol Edit Modal Sederhana atau Trigger --}}
                            <button onclick="openEditModal('{{ $s->id }}', '{{ $s->name }}', '{{ $s->email }}', '{{ $s->nis_nip }}')" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>

                            <form action="{{ route('admin.satpam.destroy', $s->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus akun satpam ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-6 text-center text-gray-500">Belum ada data satpam.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Edit Satpam --}}
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Edit Data Satpam</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" id="edit_name" name="name" required class="w-full border rounded px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="edit_email" name="email" required class="w-full border rounded px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">ID / NIP Satpam</label>
                <input type="text" id="edit_nis_nip" name="nis_nip" class="w-full border rounded px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru <span class="text-xs text-gray-400">(Kosongkan jika tidak ingin mengubah)</span></label>
                <input type="password" name="password" class="w-full border rounded px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update</button>
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