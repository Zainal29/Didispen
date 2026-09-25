@extends('admin.layouts.app')

@section('title', 'Template WhatsApp')
@section('page-title', 'Manajemen Template Pesan WhatsApp')

@section('content')
@include('components.alert')

<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h3 class="text-base sm:text-lg font-bold text-gray-800">Template Pesan Notifikasi</h3>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Gunakan variabel seperti <code class="bg-gray-100 px-1 rounded">{nama_siswa}</code> atau <code class="bg-gray-100 px-1 rounded">{nomor_surat}</code></p>
        </div>
        <button onclick="openModal('add')" class="w-full sm:w-auto min-h-[44px] px-4 py-2.5 bg-green-600 text-white rounded-lg text-sm font-bold hover:bg-green-700 flex items-center justify-center transition-colors shadow-sm">
            <i class="fab fa-whatsapp mr-2"></i>Tambah Template
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($templates as $tpl)
        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-3 gap-2">
                <div class="min-w-0">
                    <h4 class="font-bold text-gray-900 truncate">{{ $tpl->name }}</h4>
                    <span class="text-xs text-gray-500">Slug: <code>{{ $tpl->slug }}</code></span>
                </div>
                <span class="px-2.5 py-0.5 rounded text-xs font-bold flex-shrink-0 {{ $tpl->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $tpl->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>

            <div class="bg-gray-50 p-3 rounded-lg text-xs text-gray-700 mb-4 font-mono whitespace-pre-wrap border border-gray-200 break-words">
                {{ Str::limit($tpl->content, 150) }}
            </div>

            <div class="flex gap-2">
                <button onclick="openModal('edit', {{ $tpl->id }})" class="flex-1 min-h-[44px] px-3 py-2 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold hover:bg-blue-100 flex items-center justify-center transition-colors">
                    <i class="fas fa-edit mr-1.5"></i>Edit
                </button>
                <form method="POST" action="{{ route('admin.whatsapp-templates.destroy', $tpl) }}" onsubmit="return confirm('Hapus template ini?')" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full min-h-[44px] px-3 py-2 bg-red-50 text-red-700 rounded-lg text-xs font-bold hover:bg-red-100 flex items-center justify-center transition-colors">
                        <i class="fas fa-trash mr-1.5"></i>Hapus
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Modal Form --}}
<div id="templateModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90dvh] flex flex-col overflow-hidden shadow-xl">
        <div class="p-5 sm:p-6 border-b border-gray-100 flex justify-between items-center flex-shrink-0">
            <h3 class="text-base sm:text-lg font-bold text-gray-900" id="modalTitle">Tambah Template</h3>
            <button type="button" onclick="closeModal()" class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-gray-600 rounded-lg">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>

        <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1">
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800">
                <strong>Variabel yang tersedia:</strong><br>
                <code>{nama_siswa}</code>, <code>{nomor_surat}</code>, <code>{catatan}</code>, <code>{waktu_aktual}</code>, <code>{jam_kembali}</code>, <code>{durasi_terlambat}</code>
            </div>

            <form id="templateForm" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Template</label>
                    <input type="text" name="name" id="tplName" required class="w-full h-11 px-3.5 py-2.5 border border-gray-300 rounded-lg text-base sm:text-sm focus:ring-2 focus:ring-green-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Isi Pesan</label>
                    <textarea name="content" id="tplContent" rows="5" required class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none font-mono text-base sm:text-sm"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="tplActive" checked class="w-5 h-5 rounded text-green-600">
                    <label for="tplActive" class="text-sm text-gray-700 cursor-pointer">Template Aktif</label>
                </div>

                {{-- Preview Area --}}
                <div class="mt-4">
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Preview Hasil:</label>
                    <div id="previewArea" class="w-full px-3.5 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-base sm:text-sm text-gray-800 min-h-[80px] whitespace-pre-wrap break-words"></div>
                </div>
            </form>
        </div>

        <div class="p-4 sm:p-5 border-t border-gray-100 bg-gray-50 flex gap-2 flex-shrink-0">
            <button type="button" onclick="closeModal()" class="flex-1 min-h-[44px] px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 flex items-center justify-center text-sm transition-colors">Batal</button>
            <button type="button" onclick="fetchPreview()" class="flex-1 min-h-[44px] px-4 py-2.5 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 flex items-center justify-center text-sm transition-colors shadow-sm">
                <i class="fas fa-eye mr-1.5"></i> Preview
            </button>
            <button type="submit" form="templateForm" class="flex-1 min-h-[44px] px-4 py-2.5 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700 flex items-center justify-center text-sm transition-colors shadow-sm">Simpan</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const templates = @json($templates);

function openModal(mode, id = null) {
    const modal = document.getElementById('templateModal');
    const form = document.getElementById('templateForm');
    const title = document.getElementById('modalTitle');

    modal.classList.remove('hidden');
    document.getElementById('previewArea').innerHTML = '';

    if (mode === 'edit' && id) {
        const tpl = templates.find(t => t.id === id);
        title.textContent = 'Edit Template';
        form.action = `/admin/whatsapp-templates/${id}`;
        // Hacky way to inject PUT method for Laravel
        form.innerHTML = '@csrf @method("PUT")' + form.innerHTML.replace(/@csrf.*/, '');
        document.getElementById('tplName').value = tpl.name;
        document.getElementById('tplContent').value = tpl.content;
        document.getElementById('tplActive').checked = tpl.is_active;
    } else {
        title.textContent = 'Tambah Template';
        form.action = '{{ route("admin.whatsapp-templates.store") }}';
        form.reset();
        // Restore CSRF after reset
        form.innerHTML = '@csrf' + form.innerHTML.replace(/@csrf.*/, '');
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
    });
}
</script>
@endpush
@endsection
