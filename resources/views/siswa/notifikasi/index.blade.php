@extends('siswa.layouts.app')
@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi Siswa')
@section('content')

<div class="max-w-3xl mx-auto pb-24 sm:pb-8">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="ti ti-bell text-slate-500 text-lg"></i>Notifikasi
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Informasi terbaru pengajuan dispensasi Anda.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @if($notifikasi->where('is_read', false)->count() > 0)
                    <form method="POST" action="{{ route('siswa.notifikasi.readAll') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center min-h-[40px] px-3.5 py-2 rounded-xl text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 active:bg-blue-200 transition-colors flex-shrink-0 border border-blue-100">
                            <i class="ti ti-checks mr-1.5 text-sm"></i>Tandai Semua Dibaca
                        </button>
                    </form>
                @endif

                @if($notifikasi->count() > 0)
                    <form method="POST" action="{{ route('siswa.notifikasi.clearAll') }}" onsubmit="return confirm('Hapus semua notifikasi?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center min-h-[40px] px-3.5 py-2 rounded-xl text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 active:bg-rose-200 transition-colors flex-shrink-0 border border-rose-100" title="Hapus Semua Notifikasi">
                            <i class="ti ti-trash mr-1.5 text-sm"></i>Hapus Semua
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- List Notifikasi --}}
        <div class="divide-y divide-slate-100">
            @forelse($notifikasi as $notif)
                @php
                    $isRead = $notif->is_read;
                    $message = $notif->message;

                    // Penentuan Icon & Styling Tabler clean slate
                    $iconName = 'ti ti-info-circle';
                    $iconColor = 'text-blue-600 bg-blue-50 border-blue-100';

                    if (str_contains($message, 'TERLAMBAT') || str_contains($message, 'terlambat')) {
                        $iconName = 'ti ti-alert-triangle';
                        $iconColor = 'text-rose-600 bg-rose-50 border-rose-100';
                    } elseif (str_contains($message, 'DISETUJUI')) {
                        $iconName = 'ti ti-circle-check';
                        $iconColor = 'text-emerald-600 bg-emerald-50 border-emerald-100';
                    } elseif (str_contains($message, 'DITOLAK')) {
                        $iconName = 'ti ti-circle-x';
                        $iconColor = 'text-rose-600 bg-rose-50 border-rose-100';
                    } elseif (str_contains($message, 'di-scan') || str_contains($message, 'Keluar')) {
                        $iconName = 'ti ti-door-exit';
                        $iconColor = 'text-sky-600 bg-sky-50 border-sky-100';
                    } elseif (str_contains($message, 'SELESAI')) {
                        $iconName = 'ti ti-flag';
                        $iconColor = 'text-slate-600 bg-slate-100 border-slate-200';
                    }
                @endphp
                <div class="p-4 sm:p-5 flex items-start gap-3 transition-colors {{ !$isRead ? 'bg-blue-50/40 border-l-4 border-l-blue-600' : 'bg-white hover:bg-slate-50/70' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 border {{ $iconColor }}">
                        <i class="{{ $iconName }} text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <p class="text-[11px] text-slate-500 font-medium flex items-center gap-1">
                                <i class="ti ti-clock text-xs"></i>{{ $notif->created_at->diffForHumans() }}
                            </p>
                            @if(!$isRead)
                                <span class="px-2 py-0.5 rounded-full bg-blue-600 text-white text-[10px] font-bold uppercase tracking-wider">Baru</span>
                            @endif
                        </div>
                        <p class="text-xs sm:text-sm font-semibold text-slate-800 leading-relaxed">{{ $notif->message }}</p>
                        @if($notif->link)
                            @php
                                $linkUrl = $notif->link;
                                if (\Illuminate\Support\Str::startsWith($linkUrl, ['http://', 'https://'])) {
                                    $linkUrl = parse_url($linkUrl, PHP_URL_PATH);
                                }
                            @endphp
                            <!-- <div class="mt-2.5">
                                <a href="{{ $linkUrl }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-700 active:underline py-1">
                                    <span>Lihat Detail Pengajuan</span>
                                    <i class="ti ti-arrow-right text-xs"></i>
                                </a>
                            </div> -->
                        @endif
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        @if(!$isRead)
                            <form method="POST" action="{{ route('siswa.notifikasi.read', $notif) }}">
                                @csrf
                                <button type="submit" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-500 hover:text-blue-600 hover:bg-blue-50 active:bg-blue-100 transition-colors flex items-center justify-center border border-slate-200 cursor-pointer" title="Tandai Sudah Dibaca">
                                    <i class="ti ti-check text-sm font-bold"></i>
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('siswa.notifikasi.destroy', $notif) }}" onsubmit="return confirm('Hapus notifikasi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 active:bg-rose-200 transition-colors flex items-center justify-center border border-rose-100 cursor-pointer" title="Hapus Notifikasi">
                                <i class="ti ti-trash text-sm font-bold"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-3 border border-slate-200">
                        <i class="ti ti-bell-off"></i>
                    </div>
                    <p class="text-slate-800 font-bold text-sm mb-1">Belum Ada Notifikasi</p>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Notifikasi mengenai pengajuan dispensasi Anda akan muncul di sini secara otomatis.</p>
                </div>
            @endforelse
        </div>

        @if($notifikasi->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">{{ $notifikasi->links() }}</div>
        @endif
    </div>
</div>
@endsection
