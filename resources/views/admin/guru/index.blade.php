@extends('admin.layouts.app')
@section('title', 'Data Guru')
@section('page-title', 'Manajemen Guru')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b flex justify-between items-center flex-wrap gap-2">
        <h3 class="text-lg font-bold text-gray-800">Daftar Guru ({{ $gurus->total() }})</h3>
        <div class="flex items-center gap-2">
            <form action="{{ route('admin.sipintu.sync-guru') }}" method="POST" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=\'fas fa-spinner fa-spin mr-1\'></i> Menyinkronkan...';">
                @csrf
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded text-sm font-medium transition shadow-sm flex items-center gap-1.5" title="Sinkronkan data guru dari SiPintu Gateway">
                    <i class="fas fa-sync-alt"></i> Sinkronisasi SiPintu
                </button>
            </form>
            <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium transition flex items-center gap-1.5">
                <i class="fas fa-plus"></i> Tambah Guru
            </button>
        </div>
    </div>
    @include('components.alert')

    {{-- Filter --}}
    <div class="p-4 bg-gray-50 border-b">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama / NIP / Mapel</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Masukkan kata kunci..." class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Urutkan</label>
                <select name="sort" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    @foreach($sortable as $key => $label)
                        <option value="{{ $key }}" {{ $sort == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[120px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Arah</label>
                <select name="dir" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="asc" {{ $dir == 'asc' ? 'selected' : '' }}>A - Z / Lama</option>
                    <option value="desc" {{ $dir == 'desc' ? 'selected' : '' }}>Z - A / Baru</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                <i class="fas fa-search mr-1"></i> Cari
            </button>
            <a href="{{ route('admin.guru.index') }}" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">Reset</a>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'nip', 'dir' => ($sort == 'nip' && $dir == 'asc' ? 'desc' : 'asc')]) }}" class="hover:text-blue-600">
                            NIP @if($sort == 'nip')<i class="fas fa-sort-{{ $dir == 'asc' ? 'up' : 'down' }} ml-1"></i>@endif
                        </a>
                    </th>
                    <th class="p-3 text-left">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'nama_lengkap', 'dir' => ($sort == 'nama_lengkap' && $dir == 'asc' ? 'desc' : 'asc')]) }}" class="hover:text-blue-600">
                            Nama @if($sort == 'nama_lengkap')<i class="fas fa-sort-{{ $dir == 'asc' ? 'up' : 'down' }} ml-1"></i>@endif
                        </a>
                    </th>
                    <th class="p-3 text-left">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'mata_pelajaran', 'dir' => ($sort == 'mata_pelajaran' && $dir == 'asc' ? 'desc' : 'asc')]) }}" class="hover:text-blue-600">
                            Mata Pelajaran @if($sort == 'mata_pelajaran')<i class="fas fa-sort-{{ $dir == 'asc' ? 'up' : 'down' }} ml-1"></i>@endif
                        </a>
                    </th>
                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-left">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'dir' => ($sort == 'created_at' && $dir == 'asc' ? 'desc' : 'asc')]) }}" class="hover:text-blue-600">
                            Terdaftar @if($sort == 'created_at')<i class="fas fa-sort-{{ $dir == 'asc' ? 'up' : 'down' }} ml-1"></i>@endif
                        </a>
                    </th>
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
                    <td class="p-3 text-sm">{{ $g->created_at->format('d/m/Y') }}</td>
                    <td class="p-3 text-center whitespace-nowrap">
                        <button onclick='openModal(@json($g))' class="text-blue-600 hover:text-blue-800 mx-1"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteItem({{ $g->id }}, '{{ $g->nama_lengkap }}')" class="text-red-600 hover:text-red-800 mx-1"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-5 text-center text-gray-500">Belum ada data guru.</td></tr>
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