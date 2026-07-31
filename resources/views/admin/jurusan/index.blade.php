@extends('admin.layouts.app')
@section('title', 'Jurusan')
@section('page-title', 'Manajemen Jurusan')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b flex justify-between items-center">
        <h3 class="text-lg font-bold">Daftar Jurusan</h3>
        <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
            <i class="fas fa-plus mr-1"></i> Tambah Jurusan
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">No</th>
                    <th class="p-3 text-left">Kode</th>
                    <th class="p-3 text-left">Nama Jurusan</th>
                    <th class="p-3 text-left">Jumlah Kelas</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($jurusans as $i => $j)
                <tr class="hover:bg-gray-50">
                    <td class="p-3">{{ $i + 1 }}</td>
                    <td class="p-3 font-mono">{{ $j->kode_jurusan }}</td>
                    <td class="p-3">{{ $j->nama_jurusan }}</td>
                    <td class="p-3"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">{{ $j->kelas_count }} kelas</span></td>
                    <td class="p-3 text-center">
                        <button onclick='openModal(@json($j))' class="text-blue-600 hover:text-blue-800 mx-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteItem({{ $j->id }}, '{{ $j->nama_jurusan }}')" class="text-red-600 hover:text-red-800 mx-1" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-5 text-center text-gray-500">Belum ada data jurusan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL --}}
<div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <form id="form" method="POST">
            @csrf
            <input type="hidden" id="method" name="_method" value="POST">
            <div class="p-5 border-b">
                <h3 id="modalTitle" class="text-lg font-bold">Tambah Jurusan</h3>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Jurusan</label>
                    <input type="text" name="kode_jurusan" id="kode_jurusan" required 
                           class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jurusan</label>
                    <input type="text" name="nama_jurusan" id="nama_jurusan" required 
                           class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
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
        document.getElementById('modalTitle').textContent = 'Edit Jurusan';
        document.getElementById('form').action = `/admin/jurusan/${data.id}`;
        document.getElementById('method').value = 'PUT';
        document.getElementById('kode_jurusan').value = data.kode_jurusan;
        document.getElementById('nama_jurusan').value = data.nama_jurusan;
    } else {
        document.getElementById('modalTitle').textContent = 'Tambah Jurusan';
        document.getElementById('form').action = '/admin/jurusan';
        document.getElementById('method').value = 'POST';
        document.getElementById('form').reset();
    }
}

function closeModal() {
    document.getElementById('modal').classList.add('hidden');
}

function deleteItem(id, name) {
    Swal.fire({
        title: 'Hapus Jurusan?',
        text: `"${name}" akan dihapus permanen beserta kelas di dalamnya.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/jurusan/${id}`;
            form.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection