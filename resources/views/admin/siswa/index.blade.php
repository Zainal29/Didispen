@extends('admin.layouts.app')
@section('title', 'Siswa')
@section('page-title', 'Manajemen Siswa')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h3 class="text-lg font-bold">Daftar Siswa ({{ $siswas->total() }})</h3>
            <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                <i class="fas fa-plus mr-1"></i> Tambah Siswa
            </button>
        </div>
        
        {{-- FILTER --}}
        <form method="GET" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa..." 
                   class="border rounded px-3 py-2 text-sm">
            <select name="jurusan_id" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua Jurusan</option>
                @foreach($jurusans as $j)
                    <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                @endforeach
            </select>
            <select name="kelas_id" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua Kelas</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-gray-800 text-white rounded px-4 py-2 text-sm hover:bg-gray-900">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">NIS</th>
                    <th class="p-3 text-left">Nama</th>
                    <th class="p-3 text-left">Kelas</th>
                    <th class="p-3 text-left">No. Telepon</th>
                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($siswas as $s)
                <tr class="hover:bg-gray-50">
                    <td class="p-3 font-mono text-sm">{{ $s->user->nis_nip }}</td>
                    <td class="p-3 font-semibold">{{ $s->nama_lengkap }}</td>
                    <td class="p-3">
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                            {{ $s->kelas->nama_kelas }}
                        </span>
                    </td>
                    <td class="p-3 text-sm">{{ $s->no_telepon ?? '-' }}</td>
                    <td class="p-3 text-sm text-gray-600">{{ $s->user->email }}</td>
                    <td class="p-3 text-center whitespace-nowrap">
                        <button onclick='openModal(@json($s))' class="text-blue-600 hover:text-blue-800 mx-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteItem({{ $s->id }}, '{{ $s->nama_lengkap }}')" class="text-red-600 hover:text-red-800 mx-1" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-5 text-center text-gray-500">Tidak ada data siswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($siswas->hasPages())
    <div class="p-4 border-t">
        {{ $siswas->links() }}
    </div>
    @endif
</div>

{{-- MODAL --}}
<div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <form id="form" method="POST">
            @csrf
            <input type="hidden" id="method" name="_method" value="POST">
            <div class="p-5 border-b sticky top-0 bg-white">
                <h3 id="modalTitle" class="text-lg font-bold">Tambah Siswa</h3>
            </div>
            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIS <span class="text-red-500">*</span></label>
                    <input type="text" name="nis_nip" id="nis_nip" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span id="pwdHint" class="text-xs text-gray-500">(kosongkan jika tidak diubah)</span></label>
                    <input type="password" name="password" id="password" class="w-full border rounded px-3 py-2" placeholder="Min. 6 karakter">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jurusan <span class="text-red-500">*</span></label>
                    <select name="jurusan_id" id="jurusan_id" required class="w-full border rounded px-3 py-2">
                        <option value="">-- Pilih --</option>
                        @foreach($jurusans as $j)
                            <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kelas <span class="text-red-500">*</span></label>
                    <select name="kelas_id" id="kelas_id" required class="w-full border rounded px-3 py-2">
                        <option value="">-- Pilih Jurusan dulu --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" data-jurusan="{{ $k->jurusan_id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                    <input type="text" name="no_telepon" id="no_telepon" class="w-full border rounded px-3 py-2">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea name="alamat" id="alamat" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                </div>
            </div>
            <div class="p-5 border-t bg-gray-50 flex justify-end space-x-2 sticky bottom-0">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-100">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const allKelas = @json($kelas);

document.getElementById('jurusan_id').addEventListener('change', function() {
    const kelasSelect = document.getElementById('kelas_id');
    const jurusanId = this.value;
    kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
    allKelas.filter(k => k.jurusan_id == jurusanId).forEach(k => {
        kelasSelect.innerHTML += `<option value="${k.id}">${k.nama_kelas}</option>`;
    });
});

function openModal(data = null) {
    document.getElementById('modal').classList.remove('hidden');
    if (data) {
        document.getElementById('modalTitle').textContent = 'Edit Siswa';
        document.getElementById('form').action = `/admin/siswa/${data.id}`;
        document.getElementById('method').value = 'PUT';
        document.getElementById('nama_lengkap').value = data.nama_lengkap;
        document.getElementById('nis_nip').value = data.user.nis_nip;
        document.getElementById('email').value = data.user.email;
        document.getElementById('jurusan_id').value = data.jurusan_id;
        document.getElementById('jurusan_id').dispatchEvent(new Event('change'));
        setTimeout(() => {
            document.getElementById('kelas_id').value = data.kelas_id;
        }, 100);
        document.getElementById('tanggal_lahir').value = data.tanggal_lahir || '';
        document.getElementById('no_telepon').value = data.no_telepon || '';
        document.getElementById('alamat').value = data.alamat || '';
    } else {
        document.getElementById('modalTitle').textContent = 'Tambah Siswa';
        document.getElementById('form').action = '/admin/siswa';
        document.getElementById('method').value = 'POST';
        document.getElementById('form').reset();
    }
}
function closeModal() { document.getElementById('modal').classList.add('hidden'); }

function deleteItem(id, name) {
    Swal.fire({
        title: 'Hapus Siswa?',
        text: `Data "${name}" beserta akun loginnya akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/siswa/${id}`;
            form.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection