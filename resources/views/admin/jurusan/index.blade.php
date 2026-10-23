@extends('admin.layouts.app')
@section('title', 'Jurusan')
@section('page-title', 'Manajemen Konsentrasi Keahlian (Jurusan)')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 p-5 sm:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/70 flex items-center justify-center shrink-0">
                <i class="ti ti-briefcase text-2xl"></i>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Konsentrasi Keahlian (Jurusan)</h3>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola kode dan nama konsentrasi program keahlian SMK.</p>
            </div>
        </div>
        <button onclick="openModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white rounded-xl text-xs sm:text-sm font-bold transition-all shadow-sm shadow-blue-500/25">
            <i class="ti ti-plus text-base"></i>
            <span>Tambah Jurusan</span>
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto min-w-0 w-full sidebar-scroll">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase text-slate-500 font-bold tracking-wider">
                    <tr>
                        <th class="p-4 text-left w-14">No</th>
                        <th class="p-4 text-left">Kode Jurusan</th>
                        <th class="p-4 text-left">Nama Konsentrasi Keahlian</th>
                        <th class="p-4 text-left">Jumlah Kelas</th>
                        <th class="p-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($jurusans as $i => $j)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="p-4 text-xs font-mono text-slate-400">{{ $i + 1 }}</td>
                        <td class="p-4 font-mono text-xs font-bold text-blue-700">
                            <span class="px-2 py-0.5 rounded-md bg-blue-50 border border-blue-200/60">
                                {{ $j->kode_jurusan }}
                            </span>
                        </td>
                        <td class="p-4 font-bold text-xs sm:text-sm text-slate-900">{{ $j->nama_jurusan }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200/60">
                                {{ $j->kelas_count }} Kelas
                            </span>
                        </td>
                        <td class="p-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1">
                                <button onclick='openModal(@json($j))' class="w-8 h-8 inline-flex items-center justify-center text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                    <i class="ti ti-edit text-sm"></i>
                                </button>
                                <button onclick="deleteItem({{ $j->id }}, '{{ $j->nama_jurusan }}')" class="w-8 h-8 inline-flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
                                    <i class="ti ti-trash text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400 text-xs">
                            <i class="ti ti-briefcase text-3xl mb-2 text-slate-300 block"></i>
                            Belum ada data jurusan yang terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL --}}
<div id="modal" class="hidden fixed inset-0 bg-slate-900/60 z-50 p-4 flex items-center justify-center backdrop-blur-xs">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90dvh] flex flex-col overflow-hidden border border-slate-200">
        <form id="form" method="POST" class="flex flex-col min-h-0 flex-1">
            @csrf
            <input type="hidden" id="method" name="_method" value="POST">
            <div class="p-5 border-b border-slate-100 flex-shrink-0 bg-slate-50/50 flex items-center justify-between">
                <h3 id="modalTitle" class="text-base font-bold text-slate-900">Tambah Jurusan</h3>
                <button type="button" onclick="closeModal()" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition">
                    <i class="ti ti-x text-lg"></i>
                </button>
            </div>
            <div class="p-5 space-y-4 overflow-y-auto flex-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kode Jurusan</label>
                    <input type="text" name="kode_jurusan" id="kode_jurusan" required placeholder="Contoh: PPLG"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-sm text-slate-800 transition font-mono uppercase">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Jurusan</label>
                    <input type="text" name="nama_jurusan" id="nama_jurusan" required placeholder="Contoh: Pengembangan Perangkat Lunak dan Gim"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-sm text-slate-800 transition">
                </div>
            </div>
            <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-slate-200 bg-white rounded-xl text-slate-700 hover:bg-slate-100 text-xs font-bold transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-xs font-bold transition-colors shadow-sm shadow-blue-500/25">Simpan</button>
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
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl shadow-xl border border-slate-200',
            confirmButton: 'rounded-xl text-xs font-bold px-4 py-2.5',
            cancelButton: 'rounded-xl text-xs font-semibold px-4 py-2.5'
        }
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