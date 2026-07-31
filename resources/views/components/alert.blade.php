{{-- resources/views/components/alert.blade.php --}}

@if(session('success'))
<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded flex items-center">
    <i class="fas fa-check-circle mr-2"></i>
    <span>{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded flex items-center">
    <i class="fas fa-exclamation-circle mr-2"></i>
    <span>{{ session('error') }}</span>
</div>
@endif

@if($errors->any())
<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded">
    <div class="flex items-center mb-2">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>Terjadi kesalahan validasi:</strong>
    </div>
    <ul class="list-disc list-inside text-sm ml-6">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif