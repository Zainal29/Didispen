@extends('guru.layouts.app')
@section('title', 'Tanda Tangan Digital')
@section('page-title', 'Tanda Tangan Digital')

@section('content')
@include('components.alert')

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-bold mb-4">Upload Tanda Tangan Digital</h3>
        <p class="text-sm text-gray-600 mb-6">
            Upload tanda tangan Anda dalam format gambar (PNG/JPG) dengan ukuran maksimal 2MB. 
            Tanda tangan ini akan digunakan pada surat dispensasi yang Anda setujui.
        </p>

        @if($guru->digital_signature)
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center mb-6">
            <p class="text-sm text-gray-600 mb-4">Tanda Tangan Saat Ini:</p>
            <img src="{{ asset('storage/' . $guru->digital_signature) }}" alt="Tanda Tangan" class="mx-auto max-h-32">
        </div>

        <form method="POST" action="{{ route('guru.tanda-tangan.destroy') }}" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" onclick="return confirm('Hapus tanda tangan digital?')">
                <i class="fas fa-trash mr-2"></i>Hapus Tanda Tangan
            </button>
        </form>
        @else
        <form method="POST" action="{{ route('guru.tanda-tangan.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">File Tanda Tangan</label>
                <input type="file" name="signature" accept="image/png,image/jpeg" required 
                       class="w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">Format: PNG/JPG, Max: 2MB</p>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                <i class="fas fa-upload mr-2"></i>Upload Tanda Tangan
            </button>
        </form>
        @endif
    </div>

    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
        <h4 class="font-semibold text-blue-800 mb-2">
            <i class="fas fa-info-circle mr-2"></i>Informasi
        </h4>
        <ul class="text-sm text-blue-700 space-y-1">
            <li>• Tanda tangan akan otomatis tertera pada surat dispensasi yang Anda setujui</li>
            <li>• Pastikan tanda tangan jelas dan terbaca</li>
            <li>• Anda dapat mengganti tanda tangan kapan saja</li>
        </ul>
    </div>
</div>
@endsection