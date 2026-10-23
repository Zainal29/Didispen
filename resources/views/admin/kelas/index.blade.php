@extends('admin.layouts.app')
@section('title', 'Kelas')
@section('page-title', 'Manajemen Data Kelas')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 p-5 sm:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/70 flex items-center justify-center shrink-0">
                <i class="ti ti-layout-grid text-2xl"></i>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Data Rombongan Belajar (Kelas)</h3>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola jenjang tingkat, jurusan, dan rombel siswa di sekolah.</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <form method="GET" class="flex-1 sm:flex-none">
                <select name="jurusan_id" onchange="this.form.submit()" class="w-full sm:w-auto border border-slate-200 bg-slate-50 rounded-xl px-3.5 py-2 text-xs focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                    <option value="">Semua Jurusan</option>
                    @foreach($jurusans as $j)
                        <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                    @endforeach
                </select>
            </form>
            <button onclick="openModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white rounded-xl text-xs sm:text-sm font-bold transition-all shadow-sm shadow-blue-500/25">
                <i class="ti ti-plus text-base"></i>
                <span>Tambah Kelas</span>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto min-w-0 w-full sidebar-scroll">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase text-slate-500 font-bold tracking-wider">
                    <tr>
                        <th class="p-4 text-left w-14">No</th>
                        <th class="p-4 text-left">Nama Kelas</th>
                        <th class="p-4 text-left">Konsentrasi Keahlian (Jurusan)</th>
                        <th class="p-4 text-left">Tingkat</th>
                        <th class="p-4 text-left">Jumlah Siswa</th>
                        <th class="p-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($kelas as $i => $k)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="p-4 text-xs font-mono text-slate-400">{{ $i + 1 }}</td>
                        <td class="p-4 font-bold text-xs sm:text-sm text-slate-900">{{ $k->nama_kelas }}</td>
                        <td class="p-4 text-xs text-slate-600">{{ $k->jurusan->nama_jurusan }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60">
                                Tingkat {{ $k->tingkat }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200/60">
                                {{ $k->siswa_count }} Siswa
                            </span>
                        </td>
                        <td class="p-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1">
                                <button onclick='openModal(@json($k))' class="w-8 h-8 inline-flex items-center justify-center text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                    <i class="ti ti-edit text-sm"></i>
                                </button>
                                <button onclick="deleteItem({{ $k->id }}, '{{ $k->nama_kelas }}')" class="w-8 h-8 inline-flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
                                    <i class="ti ti-trash text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400 text-xs">
                            <i class="ti ti-layout-grid text-3xl mb-2 text-slate-300 block"></i>
                            Belum ada data kelas yang terdaftar.
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
                <h3 id="modalTitle" class="text-base font-bold text-slate-900">Tambah Kelas</h3>
                <button type="button" onclick="closeModal()" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition">
                    <i class="ti ti-x text-lg"></i>
                </button>
            </div>
            <div class="p-5 space-y-4 overflow-y-auto flex-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jurusan</label>
                    <select name="jurusan_id" id="jurusan_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-sm text-slate-800 transition">
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach($jurusans as $j)
                            <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Kelas</label>
                    <input type="text" name="nama_kelas" id="nama_kelas" required placeholder="Contoh: X RPL 1"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-sm text-slate-800 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tingkat</label>
                    <select name="tingkat" id="tingkat" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-sm text-slate-800 transition">
                        <option value="X">X (Kelas 10)</option>
                        <option value="XI">XI (Kelas 11)</option>
                        <option value="XII">XII (Kelas 12)</option>
                    </select>
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