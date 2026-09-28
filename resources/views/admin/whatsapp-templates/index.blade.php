@extends('admin.layouts.app')

@section('title', 'Template WhatsApp')
@section('page-title', 'Manajemen Template Pesan WhatsApp')

@section('content')
@include('components.alert')

<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h3 class="text-base sm:text-lg font-bold text-gray-800">Template Pesan WhatsApp</h3>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Kelola template pesan otomatis WhatsApp (wa.me) untuk <strong>Pengingat Jadwal Guru Piket</strong> dan <strong>Notifikasi Dispensasi Siswa</strong>.
            </p>
        </div>
        <button onclick="openModal('add')" class="w-full sm:w-auto min-h-[44px] px-4 py-2.5 bg-green-600 text-white rounded-lg text-sm font-bold hover:bg-green-700 flex items-center justify-center transition-colors shadow-sm">
            <i class="fab fa-whatsapp mr-2 text-base"></i>Tambah Template Baru
        </button>
    </div>

    {{-- Kategori & Daftar Template --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($templates as $tpl)
            @php
                $isPiket = str_contains($tpl->slug, 'piket');
            @endphp
            <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 hover:shadow-md transition-shadow flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start mb-2.5 gap-2">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-gray-900 text-base truncate">{{ $tpl->name }}</h4>
                                @if($isPiket)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i class="fas fa-calendar-alt mr-1"></i>Piket Guru
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-user-graduate mr-1"></i>Dispensasi
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs text-gray-500 font-mono mt-0.5 block">Slug: <code>{{ $tpl->slug }}</code></span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded text-xs font-bold flex-shrink-0 {{ $tpl->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $tpl->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <div class="bg-gray-50 p-3.5 rounded-lg text-xs text-gray-700 mb-4 font-mono whitespace-pre-wrap border border-gray-200 break-words leading-relaxed">
                        {{ $tpl->content }}
                    </div>
                </div>

                <div class="flex gap-2 pt-2 border-t border-gray-100">
                    <button onclick="openModal('edit', {{ $tpl->id }})" class="flex-1 min-h-[40px] px-3 py-2 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold hover:bg-blue-100 flex items-center justify-center transition-colors">
                        <i class="fas fa-edit mr-1.5"></i>Edit Template
                    </button>
                    <form method="POST" action="{{ route('admin.whatsapp-templates.destroy', $tpl) }}" onsubmit="return confirm('Hapus template ini?')" class="flex-1">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full min-h-[40px] px-3 py-2 bg-red-50 text-red-700 rounded-lg text-xs font-bold hover:bg-red-100 flex items-center justify-center transition-colors">
                            <i class="fas fa-trash mr-1.5"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Modal Form Tambah / Edit Template --}}
<div id="templateModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90dvh] flex flex-col overflow-hidden shadow-2xl">
        <div class="p-5 sm:p-6 border-b border-gray-100 flex justify-between items-center flex-shrink-0">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900" id="modalTitle">Tambah Template WhatsApp</h3>
                <p class="text-xs text-gray-500 mt-0.5">Atur format pesan WhatsApp yang akan dikirim via tautan wa.me</p>
            </div>
            <button type="button" onclick="closeModal()" class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-gray-600 rounded-lg">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>

        <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1">
            {{-- Petunjuk Variabel Dinamis --}}
            <div class="p-3.5 bg-blue-50/80 border border-blue-200 rounded-xl space-y-2 text-xs text-blue-900">
                <div class="flex items-center justify-between">
                    <span class="font-bold flex items-center gap-1.5">
                        <i class="fas fa-magic text-blue-600"></i> Variabel Dinamis (Klik untuk menyisipkan):
                    </span>
                    <span class="text-[10px] text-blue-600 font-medium">Klik tag untuk masukkan ke teks</span>
                </div>

                <div>
                    <span class="font-semibold text-[11px] text-indigo-900 block mb-1">📅 Variabel Pengingat Piket Guru:</span>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(['{nama_guru}', '{hari}', '{nama_sesi}', '{jam_mulai}', '{jam_selesai}', '{koordinator}', '{tanggal}'] as $var)
                            <button type="button" onclick="insertVariable('{{ $var }}')" class="px-2 py-0.5 bg-white border border-indigo-200 text-indigo-700 font-mono text-[11px] rounded hover:bg-indigo-100 transition-colors">
                                {{ $var }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <span class="font-semibold text-[11px] text-emerald-900 block mb-1">🎓 Variabel Dispensasi Siswa:</span>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(['{nama_siswa}', '{nomor_surat}', '{catatan}', '{waktu_aktual}', '{jam_kembali}', '{durasi_terlambat}', '{tujuan}', '{alasan}'] as $var)
                            <button type="button" onclick="insertVariable('{{ $var }}')" class="px-2 py-0.5 bg-white border border-emerald-200 text-emerald-700 font-mono text-[11px] rounded hover:bg-emerald-100 transition-colors">
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
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Template</label>
                    <input type="text" name="name" id="tplName" required placeholder="Contoh: Pengingat Jadwal Guru Piket" class="w-full h-11 px-3.5 py-2.5 border border-gray-300 rounded-lg text-base sm:text-sm focus:ring-2 focus:ring-green-500 outline-none">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-gray-700">Isi Pesan WhatsApp</label>
                        <div class="flex gap-2">
                            <button type="button" onclick="loadPiketPreset()" class="text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold underline">
                                Gunakan Contoh Piket
                            </button>
                        </div>
                    </div>
                    <textarea name="content" id="tplContent" rows="6" required placeholder="Tulis template pesan di sini..." class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none font-mono text-xs sm:text-sm leading-relaxed"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="tplActive" value="1" checked class="w-5 h-5 rounded text-green-600 focus:ring-green-500">
                    <label for="tplActive" class="text-sm font-medium text-gray-700 cursor-pointer">Aktifkan template ini</label>
                </div>

                {{-- Live Preview Area --}}
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-gray-700">
                            <i class="fab fa-whatsapp text-emerald-600 mr-1"></i> Preview Render Pesan:
                        </label>
                        <span class="text-[10px] text-gray-400">Tampilan pesan nyata yang diterima guru/siswa</span>
                    </div>
                    <div id="previewArea" class="w-full px-3.5 py-2.5 bg-emerald-50/50 border border-emerald-200 rounded-xl text-xs sm:text-sm text-gray-800 min-h-[90px] whitespace-pre-wrap break-words leading-relaxed font-sans shadow-inner">
                        <span class="text-gray-400 italic">Klik tombol "Preview" di bawah untuk melihat simulasi pesan terisi variabel.</span>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-4 sm:p-5 border-t border-gray-100 bg-gray-50 flex gap-2 flex-shrink-0">
            <button type="button" onclick="closeModal()" class="flex-1 min-h-[44px] px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 flex items-center justify-center text-sm transition-colors">
                Batal
            </button>
            <button type="button" onclick="fetchPreview()" class="flex-1 min-h-[44px] px-4 py-2.5 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 flex items-center justify-center text-sm transition-colors shadow-sm">
                <i class="fas fa-eye mr-1.5"></i> Preview
            </button>
            <button type="submit" form="templateForm" class="flex-1 min-h-[44px] px-4 py-2.5 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700 flex items-center justify-center text-sm transition-colors shadow-sm">
                <i class="fas fa-check mr-1.5"></i> Simpan
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
    document.getElementById('tplContent').value = "Halo Yth. Bapak/Ibu *{nama_guru}*,\n\nKami mengingatkan bahwa Anda memiliki jadwal piket di sekolah pada:\n📅 Hari: *{hari}*\n⏰ Sesi: *{nama_sesi}* ({jam_mulai} - {jam_selesai} WIB)\n👤 Koordinator: {koordinator}\n\nMohon untuk hadir tepat waktu dan bertugas di pos piket untuk memantau kehadiran serta perizinan siswa.\n\nTerima kasih atas dedikasi dan kerjasamanya.\n- Admin DIDISPEN SMK N 1 Bangsri";
    fetchPreview();
}

function openModal(mode, id = null) {
    const modal = document.getElementById('templateModal');
    const form = document.getElementById('templateForm');
    const methodSpoof = document.getElementById('methodSpoof');
    const title = document.getElementById('modalTitle');

    modal.classList.remove('hidden');
    document.getElementById('previewArea').innerHTML = '<span class="text-gray-400 italic">Klik tombol "Preview" di bawah untuk melihat simulasi pesan terisi variabel.</span>';

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
    })
    .catch(err => {
        console.error(err);
    });
}
</script>
@endpush
@endsection
