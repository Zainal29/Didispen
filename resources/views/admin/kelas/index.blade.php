@extends('admin.layouts.app')
@section('title', 'Kelas')
@section('page-title', 'Manajemen Kelas')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <h3 class="text-base sm:text-lg font-bold text-gray-800">Daftar Kelas</h3>
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <form method="GET" class="flex-1 sm:flex-none">
                <select name="jurusan_id" onchange="this.form.submit()" class="w-full sm:w-auto border border-gray-300 rounded-lg px-3.5 py-2.5 text-base sm:text-sm h-11 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Jurusan</option>
                    @foreach($jurusans as $j)
                        <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                    @endforeach
                </select>
            </form>
            <button onclick="openModal()" class="flex-1 sm:flex-none min-h-[44px] bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center transition-colors shadow-sm">
                <i class="fas fa-plus mr-1.5"></i> Tambah Kelas
            </button>
        </div>
    </div>

    <div class="overflow-x-auto min-w-0 w-full">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                <tr>
                    <th class="p-3.5 text-left">No</th>
                    <th class="p-3.5 text-left">Nama Kelas</th>
                    <th class="p-3.5 text-left">Jurusan</th>
                    <th class="p-3.5 text-left">Tingkat</th>
                    <th class="p-3.5 text-left">Jumlah Siswa</th>
                    <th class="p-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($kelas as $i => $k)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="p-3.5 text-gray-500">{{ $i + 1 }}</td>
                    <td class="p-3.5 font-semibold text-gray-900">{{ $k->nama_kelas }}</td>
                    <td class="p-3.5 text-gray-600">{{ $k->jurusan->nama_jurusan }}</td>
                    <td class="p-3.5"><span class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded text-xs font-medium">{{ $k->tingkat }}</span></td>
                    <td class="p-3.5"><span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs font-medium">{{ $k->siswa_count }} siswa</span></td>
                    <td class="p-3.5 text-center whitespace-nowrap">
                        <div class="inline-flex items-center gap-1">
                            <button onclick='openModal(@json($k))' class="w-9 h-9 inline-flex items-center justify-center text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteItem({{ $k->id }}, '{{ $k->nama_kelas }}')" class="w-9 h-9 inline-flex items-center justify-center text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-8 text-center text-gray-500">Belum ada data kelas.</td></tr>
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
            <div class="p-5 border-b border-gray-100 flex-shrink-0"><h3 id="modalTitle" class="text-lg font-bold text-gray-800">Tambah Kelas</h3></div>
            <div class="p-5 space-y-4 overflow-y-auto flex-1">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Jurusan</label>
                    <select name="jurusan_id" id="jurusan_id" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach($jurusans as $j)
                            <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Kelas</label>
                    <input type="text" name="nama_kelas" id="nama_kelas" required placeholder="Contoh: X RPL 1"
                           class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Tingkat</label>
                    <select name="tingkat" id="tingkat" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="X">X (Kelas 10)</option>
                        <option value="XI">XI (Kelas 11)</option>
                        <option value="XII">XII (Kelas 12)</option>
                    </select>
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