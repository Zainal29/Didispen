{{-- resources/views/components/alert.blade.php --}}

@if(session('success'))
<div x-data="{ show: true }"
     x-show="show"
     x-init="setTimeout(() => show = false, 4500)"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform -translate-y-2 scale-95"
     class="mb-3.5 p-3.5 bg-emerald-50/95 backdrop-blur-xs border border-emerald-200 rounded-xl flex items-start gap-3 shadow-xs animate-slide-in">
    <div class="w-8 h-8 rounded-lg  text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5">
        <i class="fas fa-check-circle text-base"></i>
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
        <i class="fas fa-times text-xs"></i>
    </button>
</div>
@endif

@if(session('error'))
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3 shadow-sm animate-slide-in">
    <i class="fas fa-exclamation-circle text-red-600 text-xl flex-shrink-0 mt-0.5"></i>
    <div class="flex-1 min-w-0">
        <p class="font-bold text-red-800 text-sm">Gagal!</p>
        <p class="text-sm text-red-700 leading-relaxed mt-0.5">{{ session('error') }}</p>
    </div>
    <button onclick="this.closest('.animate-slide-in').remove()" class="text-red-400 hover:text-red-600 transition-colors ml-auto p-0.5 flex-shrink-0" title="Tutup">
        <i class="fas fa-times text-xs"></i>
    </button>
</div>
@endif

@if(session('warning'))
<div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3 shadow-sm animate-slide-in">
    <i class="fas fa-exclamation-triangle text-amber-600 text-xl flex-shrink-0 mt-0.5"></i>
    <div class="flex-1 min-w-0">
        <p class="font-bold text-amber-800 text-sm">Perhatian!</p>
        <p class="text-sm text-amber-700 leading-relaxed mt-0.5">{{ session('warning') }}</p>
    </div>
    <button onclick="this.closest('.animate-slide-in').remove()" class="text-amber-400 hover:text-amber-600 transition-colors ml-auto p-0.5 flex-shrink-0" title="Tutup">
        <i class="fas fa-times text-xs"></i>
    </button>
</div>
@endif

@if(session('info'))
<div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-start gap-3 shadow-sm animate-slide-in">
    <i class="fas fa-info-circle text-blue-600 text-xl flex-shrink-0 mt-0.5"></i>
    <div class="flex-1 min-w-0">
        <p class="font-bold text-blue-800 text-sm">Informasi</p>
        <p class="text-sm text-blue-700 leading-relaxed mt-0.5">{{ session('info') }}</p>
    </div>
    <button onclick="this.closest('.animate-slide-in').remove()" class="text-blue-400 hover:text-blue-600 transition-colors ml-auto p-0.5 flex-shrink-0" title="Tutup">
        <i class="fas fa-times text-xs"></i>
    </button>
</div>
@endif

@if($errors->any())
<div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3 shadow-sm animate-slide-in">
    <i class="fas fa-exclamation-triangle text-amber-600 text-xl flex-shrink-0 mt-0.5"></i>
    <div class="flex-1 min-w-0">
        <p class="font-bold text-amber-800 text-sm mb-2">Terjadi {{ $errors->count() }} Kesalahan Validasi:</p>
        <ul class="space-y-1">
            @foreach($errors->all() as $error)
                <li class="text-sm text-amber-700 flex items-start">
                    <i class="fas fa-chevron-right text-amber-500 text-xs mt-1 mr-2 flex-shrink-0"></i>
                    <span>{{ $error }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    <button onclick="this.closest('.animate-slide-in').remove()" class="text-amber-400 hover:text-amber-600 transition-colors ml-auto p-0.5 flex-shrink-0" title="Tutup">
        <i class="fas fa-times text-xs"></i>
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