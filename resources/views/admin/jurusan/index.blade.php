@extends('admin.layouts.app')
@section('title', 'Jurusan')
@section('page-title', 'Manajemen Jurusan')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <h3 class="text-base sm:text-lg font-bold text-gray-800">Daftar Jurusan</h3>
        <button onclick="openModal()" class="w-full sm:w-auto min-h-[44px] bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center transition-colors shadow-sm">
            <i class="fas fa-plus mr-1.5"></i> Tambah Jurusan
        </button>
    </div>

    <div class="overflow-x-auto min-w-0 w-full">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                <tr>
                    <th class="p-3.5 text-left">No</th>
                    <th class="p-3.5 text-left">Kode</th>
                    <th class="p-3.5 text-left">Nama Jurusan</th>
                    <th class="p-3.5 text-left">Jumlah Kelas</th>
                    <th class="p-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($jurusans as $i => $j)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="p-3.5 text-gray-500">{{ $i + 1 }}</td>
                    <td class="p-3.5 font-mono text-xs text-gray-600 font-semibold">{{ $j->kode_jurusan }}</td>
                    <td class="p-3.5 font-semibold text-gray-900">{{ $j->nama_jurusan }}</td>
                    <td class="p-3.5"><span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs font-medium">{{ $j->kelas_count }} kelas</span></td>
                    <td class="p-3.5 text-center whitespace-nowrap">
                        <div class="inline-flex items-center gap-1">
                            <button onclick='openModal(@json($j))' class="w-9 h-9 inline-flex items-center justify-center text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteItem({{ $j->id }}, '{{ $j->nama_jurusan }}')" class="w-9 h-9 inline-flex items-center justify-center text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-8 text-center text-gray-500">Belum ada data jurusan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL --}}
<div id="modal" class="hidden fixed inset-0 bg-black/60 z-50 p-4 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90dvh] flex flex-col overflow-hidden">
        <form id="form" method="POST" class="flex flex-col min-h-0 flex-1">
            @csrf
            <input type="hidden" id="method" name="_method" value="POST">
            <div class="p-5 border-b border-gray-100 flex-shrink-0">
                <h3 id="modalTitle" class="text-lg font-bold text-gray-800">Tambah Jurusan</h3>
            </div>
            <div class="p-5 space-y-4 overflow-y-auto flex-1">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Kode Jurusan</label>
                    <input type="text" name="kode_jurusan" id="kode_jurusan" required 
                           class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Jurusan</label>
                    <input type="text" name="nama_jurusan" id="nama_jurusan" required 
                           class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
            <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeModal()" class="min-h-[44px] px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 text-sm font-medium transition-colors">Batal</button>
                <button type="submit" class="min-h-[44px] px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold transition-colors shadow-sm">Simpan</button>
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