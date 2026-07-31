@extends('admin.layouts.app')
@section('title', 'Data Guru')
@section('page-title', 'Manajemen Guru')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b flex justify-between items-center">
        <h3 class="text-lg font-bold">Daftar Guru ({{ $gurus->total() }})</h3>
        <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
            <i class="fas fa-plus mr-1"></i> Tambah Guru
        </button>
        @include('components.alert')
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">NIP</th>
                    <th class="p-3 text-left">Nama</th>
                    <th class="p-3 text-left">Mata Pelajaran</th>
                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($gurus as $g)
                <tr class="hover:bg-gray-50">
                    <td class="p-3 font-mono text-sm">{{ $g->nip }}</td>
                    <td class="p-3 font-semibold">{{ $g->nama_lengkap }}</td>
                    <td class="p-3">{{ $g->mata_pelajaran ?? '-' }}</td>
                    <td class="p-3 text-sm text-gray-600">{{ $g->user->email }}</td>
                    <td class="p-3 text-center whitespace-nowrap">
                        <button onclick='openModal(@json($g))' class="text-blue-600 hover:text-blue-800 mx-1"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteItem({{ $g->id }}, '{{ $g->nama_lengkap }}')" class="text-red-600 hover:text-red-800 mx-1"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-5 text-center text-gray-500">Belum ada data guru.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($gurus->hasPages())
    <div class="p-4 border-t">{{ $gurus->links() }}</div>
    @endif
</div>

{{-- MODAL --}}
<div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <form id="form" method="POST">
            @csrf
            <input type="hidden" id="method" name="_method" value="POST">
            <div class="p-5 border-b"><h3 id="modalTitle" class="text-lg font-bold">Tambah Guru</h3></div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIP <span class="text-red-500">*</span></label>
                    <input type="text" name="nip" id="nip" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-xs text-gray-500">(kosongkan jika tidak diubah)</span></label>
                    <input type="password" name="password" id="password" class="w-full border rounded px-3 py-2" placeholder="Min. 6 karakter">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran</label>
                    <input type="text" name="mata_pelajaran" id="mata_pelajaran" class="w-full border rounded px-3 py-2">
                </div>
            </div>
            <div class="p-5 border-t bg-gray-50 flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-100">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openModal(data = null) {
    document.getElementById('modal').classList.remove('hidden');
    if (data) {
        document.getElementById('modalTitle').textContent = 'Edit Guru';
        document.getElementById('form').action = `/admin/guru/${data.id}`;
        document.getElementById('method').value = 'PUT';
        document.getElementById('nama_lengkap').value = data.nama_lengkap;
        document.getElementById('nip').value = data.nip;
        document.getElementById('email').value = data.user.email;
        document.getElementById('mata_pelajaran').value = data.mata_pelajaran || '';
    } else {
        document.getElementById('modalTitle').textContent = 'Tambah Guru';
        document.getElementById('form').action = '/admin/guru';
        document.getElementById('method').value = 'POST';
        document.getElementById('form').reset();
    }
}
function closeModal() { document.getElementById('modal').classList.add('hidden'); }

function deleteItem(id, name) {
    Swal.fire({
        title: 'Hapus Guru?',
        text: `Data "${name}" beserta akun login dan jadwal piketnya akan dihapus.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/guru/${id}`;
            form.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection