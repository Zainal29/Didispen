@extends('siswa.layouts.app')

@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')

@section('content')
@include('components.alert')

<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800">Notifikasi</h3>
        @if($notifikasi->where('is_read', false)->count() > 0)
        <form method="POST" action="{{ route('siswa.notifikasi.readAll') }}" class="inline">
            @csrf
            <button type="submit" class="text-sm text-indigo-600 hover:underline font-medium">
                <i class="fas fa-check-double mr-1"></i> Tandai Semua Dibaca
            </button>
        </form>
        @endif
    </div>

    <div class="divide-y">
        @forelse($notifikasi as $notif)
        <div class="p-4 {{ !$notif->is_read ? 'bg-blue-50' : 'bg-white' }} hover:bg-gray-50 transition">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <p class="font-semibold text-gray-800">{{ $notif->title }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ $notif->message }}</p>
                    <p class="text-xs text-gray-400 mt-2">
                        <i class="fas fa-clock mr-1"></i>{{ $notif->created_at->diffForHumans() }}
                    </p>
                </div>
                @if(!$notif->is_read)
                <form method="POST" action="{{ route('siswa.notifikasi.read', $notif) }}" class="ml-4">
                    @csrf
                    <button type="submit" class="text-indigo-600 hover:text-indigo-800" title="Tandai Dibaca">
                        <i class="fas fa-envelope"></i>
                    </button>
                </form>
                @else
                <i class="fas fa-envelope-open text-gray-400 ml-4"></i>
                @endif
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-gray-500">
            <i class="fas fa-bell-slash text-4xl mb-2 text-gray-300"></i>
            <p>Tidak ada notifikasi</p>
        </div>
        @endforelse
    </div>

    @if($notifikasi->hasPages())
    <div class="p-4 border-t">{{ $notifikasi->links() }}</div>
    @endif
</div>
@endsection