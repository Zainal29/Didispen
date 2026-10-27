@extends('admin.layouts.app')

@section('title', 'Data Guru')
@section('page-title', 'Manajemen Guru')

@section('content')
<div class="space-y-5">
    {{-- HEADER CARD --}}
    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 overflow-hidden">
        <div class="p-5 sm:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-1.5 h-3.5 bg-blue-600 rounded-full"></span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Master Data</span>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Daftar Guru</h3>
                <p class="text-xs text-slate-500 mt-0.5">Total {{ $gurus->total() }} tenaga pengajar terdaftar dalam sistem.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                {{-- Tombol Sync SiPintu --}}
                <form action="{{ route('admin.sipintu.sync-guru') }}" method="POST" class="flex items-center gap-2 w-full sm:w-auto"
                      onsubmit="this.querySelectorAll('button').forEach(b => { b.disabled=true; b.innerHTML='<i class=\'ti ti-loader-2 animate-spin\'></i> Sinkronisasi...'; });">
                    @csrf
                    <button type="submit" class="uiverse-btn-tactile flex-1 sm:flex-none min-h-[42px] bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-600 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md shadow-emerald-500/20 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="ti ti-refresh text-base"></i>
                        <span>Sinkron SiPintu</span>
                    </button>
                </form>
            </div>
        </div>

        @include('components.alert')

        {{-- FILTER BAR --}}
        <div class="px-5 sm:px-6 py-4 bg-slate-50/50 border-t border-b border-slate-100">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3.5 items-end">
                <div class="sm:col-span-2 md:col-span-4">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="ti ti-search text-base"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, NIP, Mapel, atau Alamat..."
                               class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Urutan Berdasarkan</label>
                    <select name="sort" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none cursor-pointer">
                        @foreach($sortable as $key => $label)
                            <option value="{{ $key }}" {{ $sort == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Arah</label>
                    <select name="dir" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none cursor-pointer">
                        <option value="asc" {{ $dir == 'asc' ? 'selected' : '' }}>Naik (A-Z)</option>
                        <option value="desc" {{ $dir == 'desc' ? 'selected' : '' }}>Turun (Z-A)</option>
                    </select>
                </div>

                <div class="sm:col-span-2 md:col-span-3 flex gap-1.5">
                    <button type="submit" class="uiverse-btn-tactile flex-1 min-h-[38px] px-3 py-2 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="ti ti-filter text-sm"></i> Filter
                    </button>
                    <a href="{{ route('admin.guru.index') }}" class="uiverse-btn-tactile min-h-[38px] min-w-[38px] px-2.5 py-2 bg-white border border-slate-200 text-slate-500 hover:text-slate-800 rounded-xl hover:bg-slate-50 text-xs font-semibold transition-colors flex items-center justify-center" title="Reset Filter">
                        <i class="ti ti-rotate-2 text-base"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- TABLE AREA --}}
        <div class="overflow-x-auto min-w-0 w-full touch-pan-x">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 text-[11px] uppercase text-slate-400 font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="p-3.5 pl-5 min-w-[100px]">NIP</th>
                        <th class="p-3.5 min-w-[170px]">Nama Lengkap</th>
                        <th class="p-3.5 min-w-[130px]">Mata Pelajaran</th>
                        <th class="p-3.5 min-w-[130px]">Kontak Telepon</th>
                        <th class="p-3.5 min-w-[200px]">Alamat Domisili</th>
                        <th class="p-3.5 min-w-[200px]">Email Guru</th>
                        <th class="p-3.5 min-w-[200px]">Email Akun Sekolah</th>
                        <th class="p-3.5 pr-5 text-center min-w-[90px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($gurus as $g)
                    <tr class="hover:bg-slate-50/70 transition-colors group">
                        <td class="p-3.5 pl-5 font-mono text-xs text-slate-500 font-bold align-middle">{{ $g->nip ?? '-' }}</td>

                        <td class="p-3.5 align-middle font-bold text-slate-900">{{ $g->nama_lengkap ?? '-' }}</td>

                        <td class="p-3.5 align-middle whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                {{ $g->mata_pelajaran ?? 'Umum' }}
                            </span>
                        </td>

                        <td class="p-3.5 align-middle whitespace-nowrap">
                            @if($g->no_telepon)
                                <a href="tel:{{ $g->no_telepon }}" class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-medium transition-colors bg-blue-50/80 px-2 py-1 rounded-lg hover:bg-blue-100">
                                    <i class="ti ti-phone text-xs"></i> {{ Str::limit($g->no_telepon, 15) }}
                                </a>
                            @else
                                <span class="text-slate-400 italic text-xs">-</span>
                            @endif
                        </td>

                        <td class="p-3.5 align-middle">
                            @if($g->alamat)
                                <div class="group/address relative max-w-[240px]">
                                    <p class="text-xs text-slate-600 leading-relaxed line-clamp-2" title="{{ $g->alamat }}">
                                        {{ $g->alamat }}
                                    </p>
                                    <button onclick="copyToClipboard('{{ addslashes($g->alamat ?? '') }}', this)"
                                            class="mt-1 text-[10px] text-slate-400 hover:text-blue-600 inline-flex items-center gap-1 transition-colors opacity-0 group-hover/address:opacity-100 cursor-pointer"
                                            title="Salin Alamat">
                                        <i class="ti ti-copy text-xs"></i> <span class="copy-text">Salin</span>
                                    </button>
                                </div>
                            @else
                                <span class="text-slate-400 italic text-xs">-</span>
                            @endif
                        </td>

                        <td class="p-3.5 align-middle">
                            @if($g->email)
                                <div class="flex items-center gap-1.5">
                                    <i class="ti ti-mail text-slate-400 text-xs"></i>
                                    <a href="mailto:{{ $g->email }}" class="text-blue-600 hover:underline text-xs break-all font-medium">
                                        {{ $g->email }}
                                    </a>
                                </div>
                            @else
                                <span class="text-slate-400 italic text-xs">Belum tersedia</span>
                            @endif
                        </td>

                        <td class="p-3.5 align-middle">
                            @if($g->user?->email)
                                <div class="flex items-center gap-1.5">
                                    <i class="ti ti-school text-blue-600 text-xs"></i>
                                    <a href="mailto:{{ $g->user->email }}" class="text-slate-700 hover:text-blue-600 text-xs font-mono break-all font-medium">
                                        {{ $g->user->email }}
                                    </a>
                                </div>
                            @else
                                <span class="text-slate-400 italic text-xs">Belum tersedia</span>
                            @endif
                        </td>

                        <td class="p-3.5 pr-5 text-center align-middle whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button"
                                        data-guru="{{ json_encode($g) }}"
                                        onclick="openModalFromButton(this)"
                                        class="uiverse-btn-tactile w-8 h-8 inline-flex items-center justify-center text-blue-600 hover:bg-blue-600 hover:text-white bg-blue-50 rounded-xl transition-all shadow-2xs border border-blue-100"
                                        title="Lihat Data">
                                    <i class="ti ti-eye text-base"></i>
                                </button>
                                <button onclick="deleteItem({{ $g->id }}, '{{ addslashes($g->nama_lengkap ?? '') }}')" class="uiverse-btn-tactile w-8 h-8 inline-flex items-center justify-center text-rose-600 hover:bg-rose-600 hover:text-white bg-rose-50 rounded-xl transition-all shadow-2xs border border-rose-100" title="Hapus Data">
                                    <i class="ti ti-trash text-base"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-slate-400">
                            <i class="ti ti-users text-4xl mb-2 text-slate-300 block"></i>
                            <p class="text-xs font-semibold">Belum ada data guru yang sesuai.</p>
                            <p class="text-[11px] text-slate-400 mt-1">Lakukan sinkronisasi SiPintu untuk memuat data guru.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($gurus->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $gurus->links() }}
        </div>
        @endif
    </div>
</div>

{{-- MODAL DETAIL / FORM GURU --}}
<div id="modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" aria-hidden="true" onclick="closeModal()"></div>

    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative z-10 w-full max-w-lg bg-white rounded-3xl text-left shadow-2xl overflow-hidden max-h-[90dvh] flex flex-col my-8 border border-slate-200/80">
            <form id="form" method="POST" class="flex flex-col min-h-0 flex-1">
                @csrf
                <input type="hidden" id="method" name="_method" value="POST">

                <div class="bg-gradient-to-r from-slate-900 to-blue-950 px-6 py-4 flex justify-between items-center shrink-0 border-b border-slate-800 text-white">
                    <h3 id="modalTitle" class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="ti ti-user-check text-lg text-blue-400"></i> Detail Data Guru
                    </h3>
                    <button type="button" onclick="closeModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                        <i class="ti ti-x text-lg"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="p-3.5 bg-blue-50/80 border border-blue-200/80 rounded-2xl text-xs text-blue-800 leading-relaxed flex items-start gap-3">
                        <i class="ti ti-info-circle text-lg text-blue-600 shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="font-bold">Sinkronisasi Data SiPintu:</strong>
                            Field kontak, alamat, dan akun login diperbarui berkala via SiPintu Gateway.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" id="nama_lengkap" required readonly
                                   class="w-full border border-slate-200 bg-slate-50 rounded-xl px-3.5 py-2 text-xs text-slate-800 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">NIP / ID Guru</label>
                            <input type="text" name="nip" id="nip" required readonly
                                   class="w-full border border-slate-200 bg-slate-50 rounded-xl px-3.5 py-2 text-xs font-mono text-slate-800 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Guru</label>
                            <input type="email" name="email_guru" id="email_guru" readonly
                                   class="w-full border border-slate-200 bg-slate-50 rounded-xl px-3.5 py-2 text-xs text-slate-800 outline-none">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Akun Sekolah</label>
                            <input type="email" name="email" id="email" required readonly
                                   class="w-full border border-slate-200 bg-slate-50 rounded-xl px-3.5 py-2 text-xs font-mono text-slate-800 outline-none">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran</label>
                            <input type="text" name="mata_pelajaran" id="mata_pelajaran" readonly
                                   class="w-full border border-slate-200 bg-slate-50 rounded-xl px-3.5 py-2 text-xs text-slate-800 outline-none">
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50/80 px-6 py-3.5 flex justify-end gap-2 border-t border-slate-100 shrink-0">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-700 text-xs font-bold hover:bg-slate-100 transition-colors">
                        Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(text, btnElement) {
    navigator.clipboard.writeText(text).then(() => {
        const originalHtml = btnElement.innerHTML;
        btnElement.innerHTML = '<i class="ti ti-check text-emerald-600"></i> <span class="text-emerald-600">Tersalin!</span>';

        setTimeout(() => {
            btnElement.innerHTML = originalHtml;
        }, 2000);
    }).catch(err => {
        console.error('Gagal menyalin: ', err);
    });
}

function openModalFromButton(btn) {
    try {
        const raw = btn.getAttribute('data-guru');
        const data = JSON.parse(raw);
        openModal(data);
    } catch (err) {
        console.error('Gagal membaca data guru:', err);
    }
}

function openModal(data = null) {
    const modal = document.getElementById('modal');
    const title = document.getElementById('modalTitle');
    const form = document.getElementById('form');

    modal.classList.remove('hidden');

    if (data) {
        title.innerHTML = '<i class="ti ti-user text-blue-400"></i> Detail Guru: ' + (data.nama_lengkap || '');
        form.action = `/admin/guru/${data.id}`;
        document.getElementById('method').value = 'PUT';

        document.getElementById('nama_lengkap').value = data.nama_lengkap || '';
        document.getElementById('nip').value = data.nip || '';
        document.getElementById('email').value = (data.user && data.user.email) ? data.user.email : (data.nip ? (data.nip + '@smkn1bangsri.sch.id').toLowerCase() : '');
        document.getElementById('email_guru').value = data.email || '';
        document.getElementById('mata_pelajaran').value = data.mata_pelajaran || '';
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
        html: `Apakah Anda yakin ingin menghapus data guru <b>"${name}"</b>?<br><span class="text-xs text-slate-500 mt-2 block">Tindakan ini juga akan menghapus akun login dan riwayat checklog terkait.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="ti ti-trash mr-1"></i> Ya, Hapus',
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
