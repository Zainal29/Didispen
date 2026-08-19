@extends('siswa.layouts.app')

@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi Siswa')

@section('content')
@include('components.alert')

<div class="max-w-4xl mx-auto space-y-4">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-bell text-blue-600"></i>Notifikasi
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Informasi terbaru pengajuan dispensasi Anda.</p>
            </div>
            @if($notifikasi->where('is_read', false)->count() > 0)
                <form method="POST" action="{{ route('siswa.notifikasi.readAll') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-bold text-blue-600 bg-blue-50 border border-blue-100 hover:bg-blue-100 transition-colors flex-shrink-0">
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

                    // Penentuan Icon & Warna berdasarkan isi notifikasi
                    $iconClass = 'fa-info-circle text-blue-500 bg-blue-50';
                    if (str_contains($message, 'DISETUJUI')) {
                        $iconClass = 'fa-check-circle text-emerald-600 bg-emerald-50';
                    } elseif (str_contains($message, 'DITOLAK')) {
                        $iconClass = 'fa-times-circle text-red-600 bg-red-50';
                    } elseif (str_contains($message, 'di-scan') || str_contains($message, 'Keluar')) {
                        $iconClass = 'fa-door-open text-sky-600 bg-sky-50';
                    } elseif (str_contains($message, 'SELESAI')) {
                        $iconClass = 'fa-flag-checkered text-gray-600 bg-gray-100';
                    }
                @endphp
                <div class="p-4 sm:p-5 flex items-start gap-3.5 transition-colors {{ !$isRead ? 'bg-blue-50/40 border-l-4 border-l-blue-600' : 'bg-white hover:bg-gray-50/50' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-base shadow-sm {{ explode(' ', $iconClass)[2] ?? 'bg-blue-50' }}">
                        <i class="fas {{ explode(' ', $iconClass)[0] }} {{ explode(' ', $iconClass)[1] }}"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs text-gray-400 font-medium">
                                <i class="far fa-clock mr-1"></i>{{ $notif->created_at->diffForHumans() }}
                            </p>
                            @if(!$isRead)
                                <span class="px-2 py-0.5 rounded-full bg-blue-600 text-white text-[9px] font-bold tracking-wider uppercase">Baru</span>
                            @endif
                        </div>

                        <p class="text-sm font-semibold text-gray-800 mt-1 leading-relaxed">{{ $notif->message }}</p>

                        @if($notif->link)
                            @php
                                $linkUrl = $notif->link;
                                if (\Illuminate\Support\Str::startsWith($linkUrl, ['http://', 'https://'])) {
                                    $linkUrl = parse_url($linkUrl, PHP_URL_PATH);
                                }
                            @endphp
                            <div class="mt-2.5 flex items-center gap-2">
                                <a href="{{ $linkUrl }}" class="inline-flex items-center text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline">
                                    Lihat Detail Pengajuan <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                                </a>
                            </div>
                        @endif
                    </div>

                    @if(!$isRead)
                        <form method="POST" action="{{ route('siswa.notifikasi.read', $notif) }}" class="flex-shrink-0">
                            @csrf
                            <button type="submit" class="w-8 h-8 rounded-xl bg-white border border-gray-200 text-gray-400 hover:text-blue-600 hover:border-blue-300 transition-colors flex items-center justify-center" title="Tandai Sudah Dibaca">
                                <i class="fas fa-check text-xs"></i>
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-300 flex items-center justify-center text-2xl mb-3">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <p class="text-gray-500 font-semibold text-sm">Belum Ada Notifikasi</p>
                    <p class="text-xs text-gray-400 mt-1">Notifikasi mengenai pengajuan dispensasi Anda akan muncul di sini.</p>
                </div>
            @endforelse
        </div>

        @if($notifikasi->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">{{ $notifikasi->links() }}</div>
        @endif
    </div>
</div>
@endsection