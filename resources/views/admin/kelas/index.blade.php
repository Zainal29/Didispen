@extends('admin.layouts.app')
@section('title', 'Kelas')
@section('page-title', 'Manajemen Kelas')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b flex justify-between items-center">
        <h3 class="text-lg font-bold">Daftar Kelas</h3>
        <div class="flex space-x-2">
            <form method="GET" class="flex space-x-2">
                <select name="jurusan_id" onchange="this.form.submit()" class="border rounded px-3 py-2 text-sm">
                    <option value="">Semua Jurusan</option>
                    @foreach($jurusans as $j)
                        <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                    @endforeach
                </select>
            </form>
            <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                <i class="fas fa-plus mr-1"></i> Tambah Kelas
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">No</th>
                    <th class="p-3 text-left">Nama Kelas</th>
                    <th class="p-3 text-left">Jurusan</th>
                    <th class="p-3 text-left">Tingkat</th>
                    <th class="p-3 text-left">Jumlah Siswa</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($kelas as $i => $k)
                <tr class="hover:bg-gray-50">
                    <td class="p-3">{{ $i + 1 }}</td>
                    <td class="p-3 font-semibold">{{ $k->nama_kelas }}</td>
                    <td class="p-3">{{ $k->jurusan->nama_jurusan }}</td>
                    <td class="p-3"><span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">{{ $k->tingkat }}</span></td>
                    <td class="p-3"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">{{ $k->siswa_count }} siswa</span></td>
                    <td class="p-3 text-center">
                        <button onclick='openModal(@json($k))' class="text-blue-600 hover:text-blue-800 mx-1"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteItem({{ $k->id }}, '{{ $k->nama_kelas }}')" class="text-red-600 hover:text-red-800 mx-1"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-5 text-center text-gray-500">Belum ada data kelas.</td></tr>
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
            <div class="p-5 border-b"><h3 id="modalTitle" class="text-lg font-bold">Tambah Kelas</h3></div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jurusan</label>
                    <select name="jurusan_id" id="jurusan_id" required class="w-full border rounded px-3 py-2">
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach($jurusans as $j)
                            <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kelas</label>
                    <input type="text" name="nama_kelas" id="nama_kelas" required placeholder="Contoh: X RPL 1"
                           class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tingkat</label>
                    <select name="tingkat" id="tingkat" required class="w-full border rounded px-3 py-2">
                        <option value="X">X (Kelas 10)</option>
                        <option value="XI">XI (Kelas 11)</option>
                        <option value="XII">XII (Kelas 12)</option>
                    </select>
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
        document.getElementById('modalTitle').textContent = 'Edit Kelas';
        document.getElementById('form').action = `/admin/kelas/${data.id}`;
        document.getElementById('method').value = 'PUT';
        document.getElementById('jurusan_id').value = data.jurusan_id;
        document.getElementById('nama_kelas').value = data.nama_kelas;
        document.getElementById('tingkat').value = data.tingkat;
    } else {
        document.getElementById('modalTitle').textContent = 'Tambah Kelas';
        document.getElementById('form').action = '/admin/kelas';
        document.getElementById('method').value = 'POST';
        document.getElementById('form').reset();
    }
}
function closeModal() { document.getElementById('modal').classList.add('hidden'); }

function deleteItem(id, name) {
    Swal.fire({
        title: 'Hapus Kelas?',
        text: `"${name}" akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/kelas/${id}`;
            form.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection