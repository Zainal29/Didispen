@extends('admin.layouts.app')

@section('title', 'Template WhatsApp')
@section('page-title', 'Manajemen Template Pesan WhatsApp')

@section('content')
@include('components.alert')

<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Template Pesan Notifikasi</h3>
            <p class="text-sm text-gray-500 mt-1">Gunakan variabel seperti <code class="bg-gray-100 px-1 rounded">{nama_siswa}</code> atau <code class="bg-gray-100 px-1 rounded">{nomor_surat}</code></p>
        </div>
        <button onclick="openModal('add')" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-bold hover:bg-green-700">
            <i class="fab fa-whatsapp mr-2"></i>Tambah Template
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($templates as $tpl)
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h4 class="font-bold text-gray-900">{{ $tpl->name }}</h4>
                    <span class="text-xs text-gray-500">Slug: <code>{{ $tpl->slug }}</code></span>
                </div>
                <span class="px-2 py-1 rounded text-xs font-bold {{ $tpl->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $tpl->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>

            <div class="bg-gray-50 p-3 rounded-lg text-xs text-gray-700 mb-4 font-mono whitespace-pre-wrap border border-gray-200">
                {{ Str::limit($tpl->content, 150) }}
            </div>

            <div class="flex gap-2">
                <button onclick="openModal('edit', {{ $tpl->id }})" class="flex-1 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold hover:bg-blue-100">
                    <i class="fas fa-edit mr-1"></i>Edit
                </button>
                <form method="POST" action="{{ route('admin.whatsapp-templates.destroy', $tpl) }}" onsubmit="return confirm('Hapus template ini?')" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-xs font-bold hover:bg-red-100">
                        <i class="fas fa-trash mr-1"></i>Hapus
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Modal Form --}}
<div id="templateModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold mb-4" id="modalTitle">Tambah Template</h3>

        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800">
            <strong>Variabel yang tersedia:</strong><br>
            <code>{nama_siswa}</code>, <code>{nomor_surat}</code>, <code>{catatan}</code>, <code>{waktu_aktual}</code>, <code>{jam_kembali}</code>, <code>{durasi_terlambat}</code>
        </div>

        <form id="templateForm" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nama Template</label>
                    <input type="text" name="name" id="tplName" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Isi Pesan</label>
                    <textarea name="content" id="tplContent" rows="6" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none font-mono text-sm"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="tplActive" checked class="rounded text-green-600">
                    <label for="tplActive" class="text-sm text-gray-700">Template Aktif</label>
                </div>
            </div>

            {{-- Preview Area --}}
            <div class="mt-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Preview Hasil:</label>
                <div id="previewArea" class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-800 min-h-[80px] whitespace-pre-wrap"></div>
            </div>

            <div class="flex gap-2 mt-6">
                <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300">Batal</button>
                <button type="button" onclick="fetchPreview()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">
                    <i class="fas fa-eye mr-1"></i> Preview
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700">Simpan</button>
            </div>
        </form>
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
