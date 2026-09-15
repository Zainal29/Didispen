{{-- resources/views/components/alert.blade.php --}}

@if(session('success'))
<div class="mb-4 p-4 bg-gradient-to-r from-emerald-50 to-green-50 border-l-4 border-emerald-500 rounded-lg shadow-sm animate-slide-in">
    <div class="flex items-start space-x-3">
        <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center">
                <i class="fas fa-check text-white text-sm"></i>
            </div>
        </div>
        <div class="flex-1">
            <h4 class="text-sm font-bold text-emerald-800 mb-1">Berhasil!</h4>
            <p class="text-sm text-emerald-700 leading-relaxed">{{ session('success') }}</p>

            @if(session('sync_stats'))
                @php $stats = session('sync_stats'); @endphp
                <div class="mt-3 p-3 bg-white/80 rounded-md border border-emerald-200 text-xs text-emerald-900 grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <div><span class="font-bold">Total Data:</span> {{ $stats['total'] ?? 0 }}</div>
                    <div><span class="font-bold text-green-600">Baru (Inserted):</span> {{ $stats['inserted'] ?? 0 }}</div>
                    <div><span class="font-bold text-blue-600">Diperbarui (Updated):</span> {{ $stats['updated'] ?? 0 }}</div>
                    <div><span class="font-bold text-red-600">Gagal:</span> {{ $stats['failed'] ?? 0 }}</div>
                </div>
                @if(!empty($stats['errors']))
                    <div class="mt-2 text-xs text-red-600">
                        <strong class="block mb-1">Rincian Error:</strong>
                        <ul class="list-disc pl-4 space-y-0.5 max-h-32 overflow-y-auto">
                            @foreach($stats['errors'] as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 text-emerald-400 hover:text-emerald-600 transition">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

@if(session('error'))
<div class="mb-4 p-4 bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 rounded-lg shadow-sm animate-slide-in">
    <div class="flex items-start space-x-3">
        <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation text-white text-sm"></i>
            </div>
        </div>
        <div class="flex-1">
            <h4 class="text-sm font-bold text-red-800 mb-1">Terjadi Kesalahan!</h4>
            <p class="text-sm text-red-700 leading-relaxed">{{ session('error') }}</p>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 text-red-400 hover:text-red-600 transition">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

@if(session('warning'))
<div class="mb-4 p-4 bg-gradient-to-r from-amber-50 to-yellow-50 border-l-4 border-amber-500 rounded-lg shadow-sm animate-slide-in">
    <div class="flex items-start space-x-3">
        <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-amber-500 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-white text-sm"></i>
            </div>
        </div>
        <div class="flex-1">
            <h4 class="text-sm font-bold text-amber-800 mb-1">Perhatian!</h4>
            <p class="text-sm text-amber-700 leading-relaxed">{{ session('warning') }}</p>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 text-amber-400 hover:text-amber-600 transition">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

@if(session('info'))
<div class="mb-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 rounded-lg shadow-sm animate-slide-in">
    <div class="flex items-start space-x-3">
        <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <i class="fas fa-info text-white text-sm"></i>
            </div>
        </div>
        <div class="flex-1">
            <h4 class="text-sm font-bold text-blue-800 mb-1">Informasi</h4>
            <p class="text-sm text-blue-700 leading-relaxed">{{ session('info') }}</p>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 text-blue-400 hover:text-blue-600 transition">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

@if($errors->any())
<div class="mb-4 p-4 bg-gradient-to-r from-amber-50 to-orange-50 border-l-4 border-amber-500 rounded-lg shadow-sm animate-slide-in">
    <div class="flex items-start space-x-3">
        <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-amber-500 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-white text-sm"></i>
            </div>
        </div>
        <div class="flex-1">
            <h4 class="text-sm font-bold text-amber-800 mb-2">Terjadi {{ $errors->count() }} Kesalahan Validasi:</h4>
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li class="text-sm text-amber-700 flex items-start">
                        <i class="fas fa-chevron-right text-amber-500 text-xs mt-1 mr-2 flex-shrink-0"></i>
                        <span>{{ $error }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 text-amber-400 hover:text-amber-600 transition">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

<style>
@keyframes slide-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slide-in {
    animation: slide-in 0.3s ease-out;
}
</style>