{{-- resources/views/components/alert.blade.php --}}

@if(session('success'))
<div class="mb-3 px-3 py-2.5 bg-emerald-500/10 border-l-4 border-emerald-500 text-emerald-300 rounded-xl flex items-center space-x-2 text-xs backdrop-blur-md">
    <i class="fas fa-check-circle text-emerald-400 text-sm flex-shrink-0"></i>
    <span class="font-medium">{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div class="mb-3 px-3 py-2.5 bg-red-500/10 border-l-4 border-red-500 text-red-300 rounded-xl flex items-center space-x-2 text-xs backdrop-blur-md">
    <i class="fas fa-exclamation-circle text-red-400 text-sm flex-shrink-0"></i>
    <span class="font-medium">{{ session('error') }}</span>
</div>
@endif

@if($errors->any())
<div class="mb-3 px-3 py-2.5 bg-amber-500/10 border-l-4 border-amber-500 text-amber-300 rounded-xl text-xs backdrop-blur-md">
    <div class="flex items-center space-x-2 mb-1">
        <i class="fas fa-exclamation-triangle text-amber-400 text-sm flex-shrink-0"></i>
        <span class="font-bold">Terjadi kesalahan validasi:</span>
    </div>
    <ul class="list-disc list-inside space-y-0.5 text-amber-200/90 ml-5 text-[11px]">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif