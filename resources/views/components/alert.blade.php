{{-- resources/views/components/alert.blade.php --}}

@if(session('success'))
<div x-data="{ show: true }"
     x-show="show"
     x-init="setTimeout(() => show = false, 4500)"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform -translate-y-2 scale-95"
     class="mb-3.5 p-3.5 bg-emerald-50/95 backdrop-blur-xs border border-emerald-200 rounded-xl flex items-start gap-3 shadow-xs animate-slide-in">
    <div class="w-8 h-8 rounded-lg bg-emerald-100/80 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5">
        <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>
    <div class="flex-1 min-w-0 pt-0.5">
        <p class="font-bold text-emerald-900 text-xs sm:text-sm">Berhasil!</p>
        <p class="text-xs sm:text-sm text-emerald-700 leading-snug mt-0.5">{{ session('success') }}</p>

        @if(session('sync_stats'))
            @php $stats = session('sync_stats'); @endphp
            <div class="mt-2.5 p-2.5 bg-white/90 rounded-lg border border-emerald-200 text-xs text-emerald-900 grid grid-cols-2 sm:grid-cols-4 gap-2">
                <div><span class="font-bold">Total:</span> {{ $stats['total'] ?? 0 }}</div>
                <div><span class="font-bold text-green-600">Baru:</span> {{ $stats['inserted'] ?? 0 }}</div>
                <div><span class="font-bold text-blue-600">Update:</span> {{ $stats['updated'] ?? 0 }}</div>
                <div><span class="font-bold text-red-600">Gagal:</span> {{ $stats['failed'] ?? 0 }}</div>
            </div>
        @endif
    </div>
    <button @click="show = false" class="text-emerald-400 hover:text-emerald-700 transition-colors p-1 flex-shrink-0" title="Tutup">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
@endif

@if(session('error'))
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3 shadow-sm animate-slide-in">
    <div class="w-8 h-8 rounded-lg bg-red-100/80 text-red-600 flex items-center justify-center flex-shrink-0 mt-0.5">
        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-bold text-red-800 text-sm">Gagal!</p>
        <p class="text-sm text-red-700 leading-relaxed mt-0.5">{{ session('error') }}</p>
    </div>
    <button onclick="this.closest('.animate-slide-in').remove()" class="text-red-400 hover:text-red-600 transition-colors ml-auto p-0.5 flex-shrink-0" title="Tutup">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
@endif

@if(session('warning'))
<div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3 shadow-sm animate-slide-in">
    <div class="w-8 h-8 rounded-lg bg-amber-100/80 text-amber-600 flex items-center justify-center flex-shrink-0 mt-0.5">
        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-bold text-amber-800 text-sm">Perhatian!</p>
        <p class="text-sm text-amber-700 leading-relaxed mt-0.5">{{ session('warning') }}</p>
    </div>
    <button onclick="this.closest('.animate-slide-in').remove()" class="text-amber-400 hover:text-amber-600 transition-colors ml-auto p-0.5 flex-shrink-0" title="Tutup">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
@endif

@if(session('info'))
<div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-start gap-3 shadow-sm animate-slide-in">
    <div class="w-8 h-8 rounded-lg bg-blue-100/80 text-blue-600 flex items-center justify-center flex-shrink-0 mt-0.5">
        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-bold text-blue-800 text-sm">Informasi</p>
        <p class="text-sm text-blue-700 leading-relaxed mt-0.5">{{ session('info') }}</p>
    </div>
    <button onclick="this.closest('.animate-slide-in').remove()" class="text-blue-400 hover:text-blue-600 transition-colors ml-auto p-0.5 flex-shrink-0" title="Tutup">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
@endif

@if($errors->any())
<div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3 shadow-sm animate-slide-in">
    <div class="w-8 h-8 rounded-lg bg-amber-100/80 text-amber-600 flex items-center justify-center flex-shrink-0 mt-0.5">
        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-bold text-amber-800 text-sm mb-2">Terjadi {{ $errors->count() }} Kesalahan Validasi:</p>
        <ul class="space-y-1">
            @foreach($errors->all() as $error)
                <li class="text-sm text-amber-700 flex items-start gap-1.5">
                    <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                    <span>{{ $error }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    <button onclick="this.closest('.animate-slide-in').remove()" class="text-amber-400 hover:text-amber-600 transition-colors ml-auto p-0.5 flex-shrink-0" title="Tutup">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
@endif

<style>
@keyframes slide-in {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slide-in {
    animation: slide-in 0.25s ease-out;
}
</style>