@extends('admin.layouts.app')
@section('title', 'Jadwal Piket')
@section('page-title', 'Jadwal Guru Piket')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b flex justify-between items-center">
        <h3 class="text-lg font-bold">Jadwal Piket Minggu Ini</h3>
        <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
            <i class="fas fa-plus mr-1"></i> Tambah Jadwal
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">Tanggal</th>
                    <th class="p-3 text-left">Hari</th>
                    <th class="p-3 text-left">Shift</th>
                    <th class="p-3 text-left">Guru</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($piket as $p)
                <tr class="hover:bg-gray-50 {{ $p->tanggal->isToday() ? 'bg-yellow-50' : '' }}">
                    <td class="p-3 font-mono">{{ $p->tanggal->format('d-m-Y') }}</td>
                    <td class="p-3">{{ $p->tanggal->isoFormat('dddd') }}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs font-bold {{ $p->shift == 'pagi' ? 'bg-orange-100 text-orange-800' : 'bg-indigo-100 text-indigo-800' }}">
                            {{ ucfirst($p->shift) }}
                        </span>
                    </td>
                    <td class="p-3 font-semibold">{{ $p->guru->nama_lengkap }}</td>
                    <td class="p-3 text-center">
                        <button onclick="deleteItem({{ $p->id }})" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-5 text-center text-gray-500">Belum ada jadwal piket minggu ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL --}}
<div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <form method="POST" action="{{ route('admin.piket.store') }}">
            @csrf
            <div class="p-5 border-b"><h3 class="text-lg font-bold">Tambah Jadwal Piket</h3></div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Guru</label>
                    <select name="guru_id" required class="w-full border rounded px-3 py-2">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($gurus as $g)
                            <option value="{{ $g->id }}">{{ $g->nama_lengkap }} ({{ $g->user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" required value="{{ now()->toDateString() }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
                    <select name="shift" required class="w-full border rounded px-3 py-2">
                        <option value="pagi">Pagi (07:00 - 12:00)</option>
                        <option value="siang">Siang (12:00 - 17:00)</option>
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
function openModal() { document.getElementById('modal').classList.remove('hidden'); }
function closeModal() { document.getElementById('modal').classList.add('hidden'); }

function deleteItem(id) {
    Swal.fire({
        title: 'Hapus Jadwal?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/piket/${id}`;
            form.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection