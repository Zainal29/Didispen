@extends('admin.layouts.app')

@section('title', 'Kelola Video Tutorial')
@section('page-title', 'Kelola Video Tutorial')

@section('content')
@include('components.alert')

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Video Tutorial</h3>
            <p class="text-sm text-gray-500">Kelola link video tutorial untuk setiap role</p>
        </div>
        <a href="{{ route('admin.tutorial-videos.create') }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold">
            <i class="fas fa-plus mr-2"></i>Tambah Video
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600 font-semibold">
                    <tr>
                        <th class="p-4 text-left">Role</th>
                        <th class="p-4 text-left">Judul</th>
                        <th class="p-4 text-left">Link YouTube</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($videos as $video)
                    <tr class="hover:bg-gray-50">
                        <td class="p-4">
                            <span class="px-2 py-1 rounded-md text-xs font-bold
                                {{ $video->role === 'siswa' ? 'bg-blue-100 text-blue-700' :
                                   ($video->role === 'guru' ? 'bg-indigo-100 text-indigo-700' :
                                   ($video->role === 'satpam' ? 'bg-red-100 text-red-700' : 'bg-purple-100 text-purple-700')) }}">
                                {{ ucfirst($video->role) }}
                            </span>
                        </td>
                        <td class="p-4 font-semibold text-gray-900">{{ $video->title }}</td>
                        <td class="p-4">
                            <a href="{{ $video->youtube_url }}" target="_blank" class="text-blue-600 hover:underline text-sm">
                                {{ Str::limit($video->youtube_url, 50) }}
                            </a>
                        </td>
                        <td class="p-4 text-center">
                            <span class="px-2 py-1 rounded-md text-xs font-bold
                                {{ $video->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $video->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="p-4 text-center space-x-2">
                            <a href="{{ route('admin.tutorial-videos.edit', $video) }}"
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.tutorial-videos.destroy', $video) }}"
                                  method="POST" class="inline"
                                  onsubmit="return confirm('Hapus video ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500">
                            Belum ada video tutorial
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
