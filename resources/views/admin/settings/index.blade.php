@extends('admin.layouts.app')
@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Sistem')

@section('content')
<div class="bg-white rounded-lg shadow max-w-2xl">
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')
        <div class="p-5 border-b">
            <h3 class="text-lg font-bold">Konfigurasi Cetak Surat</h3>
            <p class="text-sm text-gray-500 mt-1">Atur batasan waktu dan jumlah cetak surat dispensasi.</p>
        </div>
        <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai Cetak</label>
                    <input type="time" name="print_start_time" value="{{ $print_start_time }}" required 
                           class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jam Akhir Cetak</label>
                    <input type="time" name="print_end_time" value="{{ $print_end_time }}" required 
                           class="w-full border rounded px-3 py-2">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Maksimal Jumlah Cetak per Surat</label>
                <input type="number" name="print_max_limit" value="{{ $print_max_limit }}" min="1" max="10" required 
                       class="w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">Setiap surat dispensasi hanya bisa dicetak maksimal X kali.</p>
            </div>
        </div>
        <div class="p-5 border-t bg-gray-50 flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                <i class="fas fa-save mr-1"></i> Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection