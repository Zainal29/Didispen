@extends('siswa.layouts.app')
@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi Siswa')
@section('content')
@include('components.alert')

<div class="max-w-3xl mx-auto">
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">

        {{-- Header --}}
        <div class="p-4 sm:p-5 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-bell text-gray-400"></i>Notifikasi
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Informasi terbaru pengajuan dispensasi Anda.</p>
            </div>
            @if($notifikasi->where('is_read', false)->count() > 0)
                <form method="POST" action="{{ route('siswa.notifikasi.readAll') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors flex-shrink-0">
                        <i class="fas fa-check-double mr-1.5"></i>Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>

        {{-- List Notifikasi --}}
        <div class="divide-y divide-gray-100">
            @forelse($notifikasi as $notif)
                @php
                    $isRead = $notif->is_read;
                    $message = $notif->message;

                    // Penentuan Icon & Warna berdasarkan isi notifikasi - ANTI SLOP: solid colors
                    $iconClass = 'fa-info-circle text-blue-600 bg-blue-100';
                    if (str_contains($message, 'TERLAMBAT') || str_contains($message, 'terlambat')) {
                        $iconClass = 'fa-exclamation-triangle text-red-600 bg-red-100';
                    } elseif (str_contains($message, 'DISETUJUI')) {
                        $iconClass = 'fa-check-circle text-emerald-600 bg-emerald-100';
                    } elseif (str_contains($message, 'DITOLAK')) {
                        $iconClass = 'fa-times-circle text-red-600 bg-red-100';
                    } elseif (str_contains($message, 'di-scan') || str_contains($message, 'Keluar')) {
                        $iconClass = 'fa-door-open text-sky-600 bg-sky-100';
                    } elseif (str_contains($message, 'SELESAI')) {
                        $iconClass = 'fa-flag-checkered text-gray-600 bg-gray-100';
                    }
                @endphp
                <div class="p-4 sm:p-5 flex items-start gap-3 transition-colors {{ !$isRead ? 'bg-blue-50/50 border-l-4 border-l-blue-600' : 'bg-white hover:bg-gray-50' }}">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 {{ explode(' ', $iconClass)[2] ?? 'bg-blue-100' }}">
                        <i class="fas {{ explode(' ', $iconClass)[0] }} {{ explode(' ', $iconClass)[1] }} text-base"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <p class="text-xs text-gray-500 font-medium">
                                <i class="far fa-clock mr-1"></i>{{ $notif->created_at->diffForHumans() }}
                            </p>
                            @if(!$isRead)
                                <span class="px-2 py-0.5 rounded-md bg-blue-600 text-white text-[10px] font-semibold uppercase tracking-wide">Baru</span>
                            @endif
                        </div>
                        <p class="text-sm font-medium text-gray-900 leading-relaxed">{{ $notif->message }}</p>
                        @if($notif->link)
                            @php
                                $linkUrl = $notif->link;
                                if (\Illuminate\Support\Str::startsWith($linkUrl, ['http://', 'https://'])) {
                                    $linkUrl = parse_url($linkUrl, PHP_URL_PATH);
                                }
                            @endphp
                            <div class="mt-2">
                                <a href="{{ $linkUrl }}" class="inline-flex items-center text-xs font-semibold text-blue-600 hover:text-blue-700">
                                    Lihat Detail Pengajuan <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                    @if(!$isRead)
                        <form method="POST" action="{{ route('siswa.notifikasi.read', $notif) }}" class="flex-shrink-0">
                            @csrf
                            <button type="submit" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors flex items-center justify-center" title="Tandai Sudah Dibaca">
                                <i class="fas fa-check text-xs"></i>
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center text-2xl mb-3">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <p class="text-gray-700 font-semibold text-sm mb-1">Belum Ada Notifikasi</p>
                    <p class="text-xs text-gray-500">Notifikasi mengenai pengajuan dispensasi Anda akan muncul di sini.</p>
                </div>
            @endforelse
        </div>

        @if($notifikasi->hasPages())
            <div class="p-4 border-t border-gray-200 bg-gray-50">{{ $notifikasi->links() }}</div>
        @endif
    </div>
</div>
@endsection
