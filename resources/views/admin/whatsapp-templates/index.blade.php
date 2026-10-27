@extends('admin.layouts.app')

@section('title', 'Template WhatsApp')
@section('page-title', 'Template WhatsApp')

@section('content')
@include('components.alert')

<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-2xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/70 flex items-center justify-center shrink-0">
                <i class="ti ti-brand-whatsapp text-2xl"></i>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Template Pesan WhatsApp</h3>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Kelola template pesan otomatis WhatsApp (wa.me) untuk pengingat piket guru, satpam, dan dispensasi siswa.
                </p>
            </div>
        </div>
        <button onclick="openModal('add')" class="uiverse-btn-tactile w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-white transition-all shadow-sm shadow-blue-500/25">
            <i class="ti ti-plus text-base"></i>
            <span>Tambah Template Baru</span>
        </button>
    </div>

    {{-- Kategori & Daftar Template --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($templates as $tpl)
            @php
                $isSatpam = str_contains($tpl->slug, 'satpam');
                $isPiket = str_contains($tpl->slug, 'piket') || str_contains($tpl->slug, 'tukar');
            @endphp
            <div class="uiverse-card-lift bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs hover:shadow-md transition-shadow flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start mb-3 gap-2">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="font-bold text-slate-900 text-sm sm:text-base truncate">{{ $tpl->name }}</h4>
                                @if($isSatpam)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60 inline-flex items-center gap-1">
                                        <i class="ti ti-shield-check text-xs"></i> Satpam Gerbang
                                    </span>
                                @elseif($isPiket)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60 inline-flex items-center gap-1">
                                        <i class="ti ti-calendar text-xs"></i> Piket Guru
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60 inline-flex items-center gap-1">
                                        <i class="ti ti-school text-xs"></i> Dispensasi
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs text-slate-400 font-mono mt-0.5 block">Slug: <code class="text-slate-600 bg-slate-100 px-1 py-0.5 rounded">{{ $tpl->slug }}</code></span>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold flex-shrink-0 {{ $tpl->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-500' }}">
                            {{ $tpl->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <div class="bg-slate-50/80 p-3.5 rounded-xl text-xs text-slate-700 mb-4 font-mono whitespace-pre-wrap border border-slate-200/80 break-words leading-relaxed max-h-48 overflow-y-auto sidebar-scroll">
                        {{ $tpl->content }}
                    </div>
                </div>

                <div class="flex gap-2 pt-3 border-t border-slate-100">
                    <button onclick="openModal('edit', {{ $tpl->id }})" class="uiverse-btn-tactile flex-1 min-h-[38px] px-3 py-2 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 flex items-center justify-center gap-1.5 transition-colors">
                        <i class="ti ti-edit text-sm"></i> Edit
                    </button>
                    <form method="POST" action="{{ route('admin.whatsapp-templates.destroy', $tpl) }}" onsubmit="return confirm('Hapus template ini?')" class="flex-1">
                        @csrf @method('DELETE')
                        <button type="submit" class="uiverse-btn-tactile w-full min-h-[38px] px-3 py-2 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-bold hover:bg-rose-100 flex items-center justify-center gap-1.5 transition-colors">
                            <i class="ti ti-trash text-sm"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Modal Form Tambah / Edit Template --}}
<div id="templateModal" class="hidden fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4 backdrop-blur-xs">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90dvh] flex flex-col overflow-hidden shadow-2xl border border-slate-200">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex justify-between items-center flex-shrink-0 bg-slate-50/50">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-slate-900" id="modalTitle">Tambah Template WhatsApp</h3>
                <p class="text-xs text-slate-500 mt-0.5">Atur format pesan WhatsApp yang akan dikirim via tautan wa.me</p>
            </div>
            <button type="button" onclick="closeModal()" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>

        <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1">
            {{-- Petunjuk Variabel Dinamis --}}
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2.5 text-xs text-slate-800">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900 flex items-center gap-1.5">
                        <i class="ti ti-sparkles text-blue-600"></i> Variabel Dinamis:
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium">Klik tag untuk menyisipkan ke teks</span>
                </div>

                <div>
                    <span class="font-semibold text-[11px] text-slate-600 block mb-1">
                        Piket Guru:
                    </span>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(['{nama_guru}', '{hari}', '{nama_sesi}', '{jam_mulai}', '{jam_selesai}', '{koordinator}', '{nama_koordinator}', '{daftar_petugas}', '{tanggal}'] as $var)
                            <button type="button" onclick="insertVariable('{{ $var }}')" class="px-2 py-0.5 bg-white border border-slate-200 text-blue-700 font-mono text-[11px] rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors">
                                {{ $var }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <span class="font-semibold text-[11px] text-slate-600 block mb-1">
                        Dispensasi Siswa:
                    </span>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(['{nama_siswa}', '{nis}', '{kelas}', '{jurusan}', '{nomor_surat}', '{kategori}', '{catatan}', '{waktu_aktual}', '{jam_keluar}', '{jam_kembali}', '{durasi_terlambat}', '{tujuan}', '{lokasi}', '{alasan}', '{link_dispensasi}', '{link_web}'] as $var)
                            <button type="button" onclick="insertVariable('{{ $var }}')" class="px-2 py-0.5 bg-white border border-slate-200 text-blue-700 font-mono text-[11px] rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors">
                                {{ $var }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <span class="font-semibold text-[11px] text-slate-600 block mb-1">
                        Satpam Pos Gerbang:
                    </span>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(['{nama_sesi}', '{jam_mulai}', '{jam_selesai}', '{daftar_petugas}', '{koordinator}', '{link_scan_satpam}'] as $var)
                            <button type="button" onclick="insertVariable('{{ $var }}')" class="px-2 py-0.5 bg-white border border-slate-200 text-blue-700 font-mono text-[11px] rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors">
                                {{ $var }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <form id="templateForm" method="POST" class="space-y-4">
                @csrf
                <div id="methodSpoof"></div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Template</label>
                    <input type="text" name="name" id="tplName" required placeholder="Contoh: Pengingat Jadwal Guru Piket" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-sm text-slate-800 transition">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700">Isi Pesan WhatsApp</label>
                        <div class="flex gap-2">
                            <button type="button" onclick="loadPiketPreset()" class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold underline">
                                Gunakan Contoh Piket
                            </button>
                        </div>
                    </div>
                    <textarea name="content" id="tplContent" rows="6" required placeholder="Tulis template pesan di sini..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none font-mono text-xs sm:text-sm text-slate-800 leading-relaxed"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="tplActive" value="1" checked class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500/20 border-slate-300">
                    <label for="tplActive" class="text-xs sm:text-sm font-semibold text-slate-700 cursor-pointer">Aktifkan template ini</label>
                </div>

                {{-- Live Preview Area --}}
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <i class="ti ti-brand-whatsapp text-emerald-600"></i> Preview Render Pesan:
                        </label>
                        <span class="text-[10px] text-slate-400">Simulasi pesan nyata</span>
                    </div>
                    <div id="previewArea" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 min-h-[90px] whitespace-pre-wrap break-words leading-relaxed font-sans">
                        <span class="text-slate-400 italic">Klik tombol "Preview" di bawah untuk melihat simulasi pesan terisi variabel.</span>
                    </div>
                    <div id="previewWarning" class="hidden mt-2 px-3 py-2 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 font-medium"></div>
                </div>
            </form>
        </div>

        <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50 flex gap-2 flex-shrink-0">
            <button type="button" onclick="closeModal()" class="uiverse-btn-tactile flex-1 min-h-[42px] px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-100 flex items-center justify-center text-xs sm:text-sm transition-colors">
                Batal
            </button>
            <button type="button" onclick="fetchPreview()" class="uiverse-btn-tactile flex-1 min-h-[42px] px-4 py-2.5 bg-slate-100 border border-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-200 flex items-center justify-center text-xs sm:text-sm transition-colors gap-1.5">
                <i class="ti ti-eye text-sm"></i> Preview
            </button>
            <button type="submit" form="templateForm" class="uiverse-btn-tactile flex-1 min-h-[42px] px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-white rounded-xl font-bold flex items-center justify-center text-xs sm:text-sm transition-colors shadow-sm shadow-blue-500/25 gap-1.5">
                <i class="ti ti-check text-sm"></i> Simpan
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const templates = @json($templates);

function insertVariable(variableText) {
    const textarea = document.getElementById('tplContent');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + variableText + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + variableText.length;
    textarea.focus();
}

function loadPiketPreset() {
    document.getElementById('tplName').value = 'Pengingat Jadwal Guru Piket';
    document.getElementById('tplContent').value = "Halo Yth. Bapak/Ibu *{nama_guru}*,\n\nKami mengingatkan bahwa Anda memiliki jadwal piket di sekolah pada:\nHari: *{hari}*\nSesi: *{nama_sesi}* ({jam_mulai} - {jam_selesai} WIB)\nKoordinator: {koordinator}\n\nMohon untuk hadir tepat waktu dan bertugas di pos piket untuk memantau kehadiran serta perizinan siswa.\n\nTerima kasih atas dedikasi dan kerjasamanya.\n- Admin DIDISPEN SMK N 1 Bangsri";
    fetchPreview();
}

function openModal(mode, id = null) {
    const modal = document.getElementById('templateModal');
    const form = document.getElementById('templateForm');
    const methodSpoof = document.getElementById('methodSpoof');
    const title = document.getElementById('modalTitle');

    modal.classList.remove('hidden');
    document.getElementById('previewArea').innerHTML = '<span class="text-slate-400 italic">Klik tombol "Preview" di bawah untuk melihat simulasi pesan terisi variabel.</span>';

    if (mode === 'edit' && id) {
        const tpl = templates.find(t => t.id === id);
        title.textContent = 'Edit Template';
        form.action = `/admin/whatsapp-templates/${id}`;
        methodSpoof.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('tplName').value = tpl.name;
        document.getElementById('tplContent').value = tpl.content;
        document.getElementById('tplActive').checked = Boolean(tpl.is_active);
        fetchPreview();
    } else {
        title.textContent = 'Tambah Template Baru';
        form.action = '{{ route("admin.whatsapp-templates.store") }}';
        methodSpoof.innerHTML = '';
        form.reset();
        document.getElementById('tplActive').checked = true;
    }
}

function closeModal() {
    document.getElementById('templateModal').classList.add('hidden');
}

function fetchPreview() {
    const content = document.getElementById('tplContent').value;
    if (!content) return;

    fetch('{{ route("admin.whatsapp-templates.preview") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ content: content })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('previewArea').innerHTML = data.preview;

        // Tampilkan warning jika ada placeholder tidak dikenal
        const warnEl = document.getElementById('previewWarning');
        if (data.unknown_placeholders && data.unknown_placeholders.length > 0) {
            warnEl.innerHTML = '<i class="ti ti-alert-triangle mr-1"></i> Placeholder tidak dikenal: <span class="font-mono">' + data.unknown_placeholders.map(p => '{' + p + '}').join(', ') + '</span>';
            warnEl.classList.remove('hidden');
        } else {
            warnEl.innerHTML = '';
            warnEl.classList.add('hidden');
        }
    })
    .catch(err => {
        console.error(err);
    });
}
</script>
@endpush
@endsection
