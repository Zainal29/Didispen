@extends('admin.layouts.app')

@section('title', 'Edit Video Tutorial')
@section('page-title', 'Edit Video Tutorial')

@section('content')
@include('components.alert')

<div class="max-w-3xl mx-auto space-y-6">
    {{-- Header Banner --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center shrink-0">
                <i class="ti ti-edit text-2xl"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 truncate">Edit Video Tutorial</h3>
                <p class="text-xs text-slate-500 mt-0.5">Perbarui informasi video panduan untuk peran <span class="font-semibold text-slate-700 capitalize">{{ $tutorialVideo->role }}</span>.</p>
            </div>
        </div>
        <a href="{{ route('admin.tutorial-videos.index') }}"
           class="uiverse-btn-tactile inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 bg-slate-50 hover:bg-slate-100 border border-slate-200 transition-colors shrink-0">
            <i class="ti ti-arrow-left text-sm"></i>
            <span class="hidden sm:inline">Kembali</span>
        </a>
    </div>

    {{-- Form Card --}}
    <form id="updateTutorialVideo"
          method="POST"
          action="{{ route('admin.tutorial-videos.update', $tutorialVideo) }}"
          class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        @csrf
        @method('PUT')

        <div class="p-5 sm:p-6 space-y-5">
            {{-- Pilih Role --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Role Sasaran <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select name="role" required class="w-full h-11 px-3.5 pr-10 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all appearance-none cursor-pointer @error('role') border-rose-500 @enderror">
                        <option value="">-- Pilih Role Sasaran --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" {{ old('role', $tutorialVideo->role) == $r ? 'selected' : '' }}>
                                {{ ucfirst($r) }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                        <i class="ti ti-chevron-down text-base"></i>
                    </div>
                </div>
                @error('role')
                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1">
                        <i class="ti ti-alert-circle"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Judul Video --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Judul Video Tutorial <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="ti ti-typography text-base"></i>
                    </div>
                    <input type="text" name="title" value="{{ old('title', $tutorialVideo->title) }}" required maxlength="255"
                           placeholder="Contoh: Panduan Mengajukan Izin Dispensasi Mandiri"
                           class="w-full h-11 pl-10 pr-3.5 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all @error('title') border-rose-500 @enderror">
                </div>
                @error('title')
                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1">
                        <i class="ti ti-alert-circle"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Link YouTube --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    URL Video YouTube <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-rose-500">
                        <i class="ti ti-brand-youtube text-lg"></i>
                    </div>
                    <input type="url" name="youtube_url" id="youtube_url" value="{{ old('youtube_url', $tutorialVideo->youtube_url) }}" required
                           placeholder="https://www.youtube.com/watch?v=..."
                           class="w-full h-11 pl-10 pr-3.5 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all font-mono @error('youtube_url') border-rose-500 @enderror">
                </div>
                @error('youtube_url')
                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1">
                        <i class="ti ti-alert-circle"></i> {{ $message }}
                    </p>
                @else
                    <p class="text-slate-400 text-xs mt-1.5 flex items-center gap-1">
                        <i class="ti ti-info-circle"></i> Gunakan format URL YouTube standar (watch?v=... atau youtu.be/...).
                    </p>
                @enderror

                {{-- Live Preview --}}
                <div id="videoPreview" class="hidden mt-4 rounded-xl overflow-hidden border border-slate-200 bg-slate-900 aspect-video shadow-md">
                    <iframe id="previewFrame" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>

            {{-- Status Switch --}}
            <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/80">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', $tutorialVideo->is_active) ? 'checked' : '' }}
                           class="w-5 h-5 text-blue-600 rounded-lg border-slate-300 focus:ring-blue-600/20">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-800">Publikasikan Video</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Jika dinonaktifkan, video ini tidak akan tampil di portal pengguna.</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Footer Form --}}
        <div class="bg-slate-50/70 px-5 sm:px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row gap-2.5 sm:justify-between items-center">
            <button type="button"
                    onclick="confirmDeleteVideo()"
                    class="uiverse-btn-tactile w-full sm:w-auto min-h-[44px] inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors">
                <i class="ti ti-trash mr-1.5 text-base"></i> Hapus Video
            </button>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('admin.tutorial-videos.index') }}"
                   class="uiverse-btn-tactile flex-1 sm:flex-none min-h-[44px] inline-flex justify-center items-center px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-100 transition-colors">
                    <i class="ti ti-x mr-1.5"></i> Batal
                </a>
                <button type="submit"
                        class="uiverse-btn-tactile flex-1 sm:flex-none min-h-[44px] inline-flex justify-center items-center px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-600 transition-colors shadow-sm shadow-blue-500/25">
                    <i class="ti ti-device-floppy mr-1.5 text-base"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    {{-- Hidden Delete Form --}}
    <form id="deleteTutorialVideo" method="POST" action="{{ route('admin.tutorial-videos.destroy', $tutorialVideo) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

@push('scripts')
<script>
function updatePreview() {
    const url = document.getElementById('youtube_url').value.trim();
    const preview = document.getElementById('videoPreview');
    const frame = document.getElementById('previewFrame');

    const match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/);

    if (match && match[1]) {
        frame.src = `https://www.youtube.com/embed/${match[1]}`;
        preview.classList.remove('hidden');
    } else {
        frame.src = '';
        preview.classList.add('hidden');
    }
}

const youtubeInput = document.getElementById('youtube_url');
if (youtubeInput) {
    youtubeInput.addEventListener('input', updatePreview);
    updatePreview();
}

function confirmDeleteVideo() {
    Swal.fire({
        title: 'Hapus Video Tutorial?',
        text: 'Video tutorial ini akan dihapus secara permanen dari daftar materi panduan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="ti ti-trash mr-1"></i> Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl shadow-xl border border-slate-200',
            confirmButton: 'rounded-xl text-xs font-bold px-4 py-2.5',
            cancelButton: 'rounded-xl text-xs font-semibold px-4 py-2.5'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteTutorialVideo').submit();
        }
    });
}
</script>
@endpush
@endsection
