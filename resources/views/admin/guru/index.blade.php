@extends('admin.layouts.app')

@section('title', 'Data Guru')
@section('page-title', 'Manajemen Guru')

@section('content')
<div class="space-y-6">
    {{-- HEADER CARD --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 sm:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-xl font-bold text-gray-800 tracking-tight">Daftar Guru</h3>
                <p class="text-sm text-gray-500 mt-1">Total {{ $gurus->total() }} guru terdaftar dalam sistem.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                {{-- Tombol Sync SiPintu --}}
                <form action="{{ route('admin.sipintu.sync-guru') }}" method="POST" class="flex items-center gap-2 w-full sm:w-auto" 
                      onsubmit="this.querySelectorAll('button').forEach(b => { b.disabled=true; b.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Memproses...'; });">
                    @csrf
                    <button type="submit" class="flex-1 sm:flex-none bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm hover:shadow flex items-center justify-center gap-2">
                        <i class="fas fa-sync-alt"></i> <span class="hidden sm:inline">Sinkronisasi</span><span class="sm:hidden">Sync</span>
                    </button>
                    <button type="submit" name="force" value="1" class="flex-1 sm:flex-none bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm hover:shadow flex items-center justify-center gap-2" title="Paksa ambil data terbaru (Bypass Cache)">
                        <i class="fas fa-bolt"></i> <span class="hidden sm:inline">Force Sync</span><span class="sm:hidden">Force</span>
                    </button>
                </form>

                {{-- Tombol Tambah --}}
                <button onclick="openModal()" class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm hover:shadow flex items-center justify-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Guru
                </button>
            </div>
        </div>

        @include('components.alert')

        {{-- FILTER BAR --}}
        <div class="px-5 sm:px-6 py-4 bg-gray-50/80 border-t border-b border-gray-100">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fas fa-search text-sm"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, NIP, Mapel, atau Alamat..." 
                               class="w-full pl-9 pr-3 py-2.5 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                    </div>
                </div>
                
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Urutkan Berdasarkan</label>
                    <select name="sort" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer">
                        @foreach($sortable as $key => $label)
                            <option value="{{ $key }}" {{ $sort == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Arah</label>
                    <select name="dir" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer">
                        <option value="asc" {{ $dir == 'asc' ? 'selected' : '' }}>Naik (A-Z)</option>
                        <option value="desc" {{ $dir == 'desc' ? 'selected' : '' }}>Turun (Z-A)</option>
                    </select>
                </div>

                <div class="md:col-span-3 flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.guru.index') }}" class="px-4 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- TABLE AREA --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                    <tr>
                        <th class="p-4 border-b border-gray-100 min-w-[100px]">NIP</th>
                        <th class="p-4 border-b border-gray-100 min-w-[160px]">Nama Lengkap</th>
                        <th class="p-4 border-b border-gray-100 min-w-[120px]">Mapel</th>
                        <th class="p-4 border-b border-gray-100 min-w-[130px]">Kontak</th>
                        <th class="p-4 border-b border-gray-100 min-w-[200px]">Alamat Lengkap</th>
                        <th class="p-4 border-b border-gray-100 min-w-[160px]">Email</th>
                        <th class="p-4 border-b border-gray-100 text-center min-w-[80px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($gurus as $g)
                    <tr class="hover:bg-blue-50/50 transition-colors group">
                        <td class="p-4 font-mono text-xs text-gray-500 align-top">{{ $g->nip }}</td>
                        
                        <td class="p-4 align-top font-semibold text-gray-900">{{ $g->nama_lengkap }}</td>

                        <td class="p-4 align-top">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                {{ $g->mata_pelajaran ?? 'Umum' }}
                            </span>
                        </td>
                        
                        <td class="p-4 align-top">
                            @if($g->no_telepon)
                                <a href="tel:{{ $g->no_telepon }}" class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-medium transition-colors bg-blue-50 px-2 py-1 rounded-md hover:bg-blue-100 whitespace-nowrap">
                                    <i class="fas fa-phone-alt text-[10px]"></i> {{ Str::limit($g->no_telepon, 15) }}
                                </a>
                            @else
                                <span class="text-gray-400 italic text-xs">-</span>
                            @endif
                        </td>

                        {{-- ✅ KOLOM ALAMAT LENGKAP DENGAN FITUR COPY --}}
                        <td class="p-4 align-top">
                            @if($g->alamat)
                                <div class="group/address relative max-w-[240px]">
                                    <p class="text-xs text-gray-600 leading-relaxed line-clamp-2" title="{{ $g->alamat }}">
                                        {{ $g->alamat }}
                                    </p>
                                    <button onclick="copyToClipboard('{{ addslashes($g->alamat) }}', this)" 
                                            class="mt-1.5 text-[10px] text-gray-400 hover:text-blue-600 flex items-center gap-1 transition-colors opacity-0 group-hover/address:opacity-100"
                                            title="Salin Alamat">
                                        <i class="far fa-copy"></i> <span class="copy-text">Salin</span>
                                    </button>
                                </div>
                            @else
                                <span class="text-gray-400 italic text-xs">-</span>
                            @endif
                        </td>
                        
                        <td class="p-4 align-top text-xs text-gray-500 font-mono break-all">{{ $g->user->email }}</td>

                        <td class="p-4 text-center align-middle whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity">
                                <button onclick='openModal(@json($g))' class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteItem({{ $g->id }}, '{{ addslashes($g->nama_lengkap) }}')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Data">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-10 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <i class="fas fa-users-slash text-4xl mb-3 opacity-50"></i>
                                <p class="text-sm font-medium">Belum ada data guru.</p>
                                <p class="text-xs mt-1">Lakukan sinkronisasi SiPintu atau tambah manual.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($gurus->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
            {{ $gurus->links() }}
        </div>
        @endif
    </div>
</div>

{{-- MODAL FORM --}}
<div id="modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form id="form" method="POST">
                @csrf
                <input type="hidden" id="method" name="_method" value="POST">
                
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex justify-between items-center">
                    <h3 id="modalTitle" class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fas fa-user-plus"></i> Tambah Guru Baru
                    </h3>
                    <button type="button" onclick="closeModal()" class="text-blue-100 hover:text-white transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg text-xs text-blue-800 leading-relaxed flex gap-3">
                        <i class="fas fa-info-circle mt-0.5 text-blue-500 text-sm"></i>
                        <div>
                            <strong class="block mb-1">Catatan Sinkronisasi SiPintu:</strong>
                            Field <em>No. Telepon</em>, <em>Alamat</em>, dan <em>Password</em> dikelola otomatis oleh sistem SiPintu/Sijuna. Data tersebut akan terupdate saat Anda menekan tombol "Sinkronisasi".
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_lengkap" id="nama_lengkap" required 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                   placeholder="Contoh: Budi Santoso, S.Pd.">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">NIP / ID Guru <span class="text-red-500">*</span></label>
                            <input type="text" name="nip" id="nip" required 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all font-mono"
                                   placeholder="19800101...">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email Sekolah <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" required 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                   placeholder="nip@smkn1bangsri.sch.id">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mata Pelajaran</label>
                            <input type="text" name="mata_pelajaran" id="mata_pelajaran" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                   placeholder="Contoh: Matematika, Bahasa Inggris">
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                    <button type="button" onclick="closeModal()" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 text-sm font-medium hover:bg-gray-100 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 shadow-sm transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Fungsi Salin Alamat ke Clipboard
function copyToClipboard(text, btnElement) {
    navigator.clipboard.writeText(text).then(() => {
        const originalHtml = btnElement.innerHTML;
        btnElement.innerHTML = '<i class="fas fa-check"></i> <span class="copy-text">Tersalin!</span>';
        btnElement.classList.add('text-emerald-600');
        
        setTimeout(() => {
            btnElement.innerHTML = originalHtml;
            btnElement.classList.remove('text-emerald-600');
        }, 2000);
    }).catch(err => {
        console.error('Gagal menyalin: ', err);
        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat menyalin teks.', timer: 1500, showConfirmButton: false });
    });
}

function openModal(data = null) {
    const modal = document.getElementById('modal');
    const title = document.getElementById('modalTitle');
    const form = document.getElementById('form');
    
    modal.classList.remove('hidden');
    
    if (data) {
        title.innerHTML = '<i class="fas fa-user-edit"></i> Edit Data Guru';
        form.action = `/admin/guru/${data.id}`;
        document.getElementById('method').value = 'PUT';
        
        document.getElementById('nama_lengkap').value = data.nama_lengkap;
        document.getElementById('nip').value = data.nip;
        document.getElementById('email').value = data.user.email;
        document.getElementById('mata_pelajaran').value = data.mata_pelajaran || '';
    } else {
        title.innerHTML = '<i class="fas fa-user-plus"></i> Tambah Guru Baru';
        form.action = '/admin/guru';
        document.getElementById('method').value = 'POST';
        form.reset();
    }
}

function closeModal() { 
    document.getElementById('modal').classList.add('hidden'); 
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

function deleteItem(id, name) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: `Apakah Anda yakin ingin menghapus data guru <b>"${name}"</b>?<br><span class="text-xs text-gray-500 mt-2 block">Tindakan ini juga akan menghapus akun login dan riwayat checklog terkait.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus Permanen',
        cancelButtonText: 'Batal',
        reverseButtons: true
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