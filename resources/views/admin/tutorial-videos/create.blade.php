@extends('admin.layouts.app')

@section('title', 'Tambah Video Tutorial')
@section('page-title', 'Tambah Video Tutorial')

@section('content')
@include('components.alert')

<div class="max-w-3xl mx-auto space-y-6">
    {{-- Header Banner --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0">
                <i class="ti ti-video-plus text-2xl"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 truncate">Tambah Video Tutorial Baru</h3>
                <p class="text-xs text-slate-500 mt-0.5">Sediakan materi edukasi YouTube yang disesuaikan per peran pengguna.</p>
            </div>
        </div>
        <a href="{{ route('admin.tutorial-videos.index') }}"
           class="uiverse-btn-tactile inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 bg-slate-50 hover:bg-slate-100 border border-slate-200 transition-colors shrink-0">
            <i class="ti ti-arrow-left text-sm"></i>
            <span class="hidden sm:inline">Kembali</span>
        </a>
    </div>

    {{-- Form Card --}}
    <form method="POST" action="{{ route('admin.tutorial-videos.store') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        @csrf
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
                            <option value="{{ $r }}" {{ old('role') == $r ? 'selected' : '' }}>
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
                    <input type="text" name="title" value="{{ old('title') }}" required maxlength="255"
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
                    <input type="url" name="youtube_url" id="youtube_url" value="{{ old('youtube_url') }}" required
                           placeholder="https://www.youtube.com/watch?v=..."
                           class="w-full h-11 pl-10 pr-3.5 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all font-mono @error('youtube_url') border-rose-500 @enderror">
                </div>
                @error('youtube_url')
                    <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1">
                        <i class="ti ti-alert-circle"></i> {{ $message }}
                    </p>
                @else
                    <p class="text-slate-400 text-xs mt-1.5 flex items-center gap-1">
                        <i class="ti ti-info-circle"></i> Gunakan tautan resmi YouTube (contoh: https://www.youtube.com/watch?v=xxxxx atau https://youtu.be/xxxxx).
                    </p>
                @enderror

                {{-- Live Video Preview --}}
                <div id="videoPreview" class="hidden mt-4 rounded-xl overflow-hidden border border-slate-200 bg-slate-900 aspect-video shadow-md">
                    <iframe id="previewFrame" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>
        </div>

        {{-- Footer Form --}}
        <div class="bg-slate-50/70 px-5 sm:px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row gap-2.5 sm:justify-end">
            <a href="{{ route('admin.tutorial-videos.index') }}"
               class="uiverse-btn-tactile min-h-[44px] inline-flex justify-center items-center px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-100 transition-colors">
                <i class="ti ti-x mr-1.5"></i> Batal
            </a>
            <button type="submit"
                    class="uiverse-btn-tactile min-h-[44px] inline-flex justify-center items-center px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-600 transition-colors shadow-sm shadow-blue-500/25">
                <i class="ti ti-device-floppy mr-1.5 text-base"></i> Simpan Video
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('youtube_url').addEventListener('input', function() {
    const url = this.value.trim();
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
});
</script>
@endpush
@endsection
