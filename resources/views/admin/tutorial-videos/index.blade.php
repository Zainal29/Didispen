@extends('admin.layouts.app')

@section('title', 'Kelola Video Tutorial')
@section('page-title', 'Kelola Video Tutorial')

@section('content')
@include('components.alert')

<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-2xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/70 flex items-center justify-center shrink-0">
                <i class="ti ti-video text-2xl"></i>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Video Panduan & Tutorial</h3>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola tautan panduan video resmi YouTube untuk setiap hak akses pengguna.</p>
            </div>
        </div>
        <a href="{{ route('admin.tutorial-videos.create') }}"
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white rounded-xl text-xs sm:text-sm font-bold transition-all shadow-sm shadow-blue-500/25">
            <i class="ti ti-plus text-base"></i>
            <span>Tambah Video</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto min-w-0 w-full sidebar-scroll">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase text-slate-500 font-bold tracking-wider">
                    <tr>
                        <th class="p-4 text-left">Role Pengguna</th>
                        <th class="p-4 text-left">Judul Video</th>
                        <th class="p-4 text-left">Tautan YouTube</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($videos as $video)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold inline-flex items-center gap-1.5 border
                                {{ $video->role === 'siswa' ? 'bg-blue-50 text-blue-700 border-blue-200/60' :
                                   ($video->role === 'guru' ? 'bg-blue-50 text-blue-700 border-blue-200/60' :
                                   ($video->role === 'satpam' ? 'bg-blue-50 text-blue-700 border-blue-200/60' : 'bg-blue-50 text-blue-700 border-blue-200/60')) }}">
                                <i class="ti ti-shield-check text-xs"></i>
                                {{ ucfirst($video->role) }}
                            </span>
                        </td>
                        <td class="p-4 font-bold text-xs sm:text-sm text-slate-900">{{ $video->title }}</td>
                        <td class="p-4">
                            <a href="{{ $video->youtube_url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 hover:underline text-xs inline-flex items-center gap-1 break-all">
                                <i class="ti ti-brand-youtube text-rose-600 text-sm"></i>
                                <span>{{ Str::limit($video->youtube_url, 40) }}</span>
                                <i class="ti ti-external-link text-[10px]"></i>
                            </a>
                        </td>
                        <td class="p-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold inline-flex items-center gap-1
                                {{ $video->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $video->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $video->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="p-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('admin.tutorial-videos.edit', $video) }}"
                                   class="w-8 h-8 inline-flex items-center justify-center text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                    <i class="ti ti-edit text-sm"></i>
                                </a>
                                <form action="{{ route('admin.tutorial-videos.destroy', $video) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Hapus video ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 inline-flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
                                        <i class="ti ti-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400 text-xs">
                            <i class="ti ti-video-off text-3xl mb-2 text-slate-300 block"></i>
                            Belum ada video tutorial yang ditambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
