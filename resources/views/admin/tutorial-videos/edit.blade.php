@extends('admin.layouts.app')

@section('title', 'Edit Video Tutorial')
@section('page-title', 'Edit Video Tutorial')

@section('content')
@include('components.alert')

<div class="max-w-3xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-edit text-lg"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900">Edit Video Tutorial</h3>
                <p class="text-xs text-gray-500">Perbarui informasi video tutorial untuk role {{ ucfirst($tutorialVideo->role) }}.</p>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('admin.tutorial-videos.update', $tutorialVideo) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @csrf
        @method('PUT')
        <div class="p-5 sm:p-6 space-y-5">

            {{-- Pilih Role --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                    Role Pengguna <span class="text-red-500">*</span>
                </label>
                <select name="role" required class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-base sm:text-sm text-gray-900 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all @error('role') border-red-500 @enderror">
                    <option value="">-- Pilih Role --</option>
                    @foreach($roles as $r)
                        <option value="{{ $r }}" {{ old('role', $tutorialVideo->role) == $r ? 'selected' : '' }}>
                            {{ ucfirst($r) }}
                        </option>
                    @endforeach
                </select>
                @error('role')
                    <p class="text-red-500 text-xs mt-1 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Judul Video --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                    Judul Video <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title', $tutorialVideo->title) }}" required maxlength="255"
                       placeholder="Contoh: Cara Membuat Pengajuan Dispensasi"
                       class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-base sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="text-red-500 text-xs mt-1 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Link YouTube --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                    Link YouTube <span class="text-red-500">*</span>
                </label>
                <input type="url" name="youtube_url" id="youtube_url" value="{{ old('youtube_url', $tutorialVideo->youtube_url) }}" required
                       placeholder="https://www.youtube.com/watch?v=..."
                       class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-base sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all @error('youtube_url') border-red-500 @enderror">
                @error('youtube_url')
                    <p class="text-red-500 text-xs mt-1 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                    </p>
                @else
                    <p class="text-gray-400 text-xs mt-1">
                        <i class="fas fa-info-circle mr-1"></i>Gunakan link lengkap YouTube (contoh: https://www.youtube.com/watch?v=xxxxx)
                    </p>
                @enderror

                {{-- Preview Video --}}
                <div id="videoPreview" class="hidden mt-4 rounded-lg overflow-hidden border border-gray-200 bg-black aspect-video">
                    <iframe id="previewFrame" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>

            {{-- Status Aktif --}}
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', $tutorialVideo->is_active) ? 'checked' : '' }}
                           class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-600/20">
                    <label for="is_active" class="flex-1 cursor-pointer">
                        <p class="text-sm font-bold text-gray-900">Video Aktif</p>
                        <p class="text-xs text-gray-500">Jika tidak dicentang, video tidak akan ditampilkan di halaman Panduan.</p>
                    </label>
                </div>
            </div>
        </div>

        {{-- Footer Form --}}
        <div class="bg-gray-50 px-5 sm:px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row gap-2 sm:justify-between">
            {{-- Tombol Hapus di kiri --}}
            <form method="POST" action="{{ route('admin.tutorial-videos.destroy', $tutorialVideo) }}"
                  onsubmit="return confirm('Yakin ingin menghapus video tutorial ini?')" class="sm:order-first">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full sm:w-auto min-h-[44px] inline-flex justify-center items-center px-5 py-2.5 rounded-lg text-sm font-semibold text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 transition-colors">
                    <i class="fas fa-trash mr-1.5"></i>Hapus Video
                </button>
            </form>

            {{-- Tombol Aksi di kanan --}}
            <div class="flex gap-2 sm:order-last">
                <a href="{{ route('admin.tutorial-videos.index') }}"
                   class="flex-1 sm:flex-none min-h-[44px] inline-flex justify-center items-center px-5 py-2.5 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-times mr-1.5"></i>Batal
                </a>
                <button type="submit"
                        class="flex-1 sm:flex-none min-h-[44px] inline-flex justify-center items-center px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-sm">
                    <i class="fas fa-save mr-1.5"></i>Perbarui Video
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Preview YouTube video saat URL diisi
function updatePreview() {
    const url = document.getElementById('youtube_url').value.trim();
    const preview = document.getElementById('videoPreview');
    const frame = document.getElementById('previewFrame');

    // Extract video ID
    const match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/);

    if (match && match[1]) {
        frame.src = `https://www.youtube.com/embed/${match[1]}`;
        preview.classList.remove('hidden');
    } else {
        frame.src = '';
        preview.classList.add('hidden');
    }
}

document.getElementById('youtube_url').addEventListener('input', updatePreview);

// Jalankan preview saat halaman dimuat (untuk edit)
updatePreview();
</script>
@endpush
@endsection
