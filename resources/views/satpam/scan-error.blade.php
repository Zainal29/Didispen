@extends('satpam.layouts.app')

@section('title', 'Verifikasi Gagal')
@section('page-title', 'QR Code Tidak Valid')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-red-50 border border-red-200 rounded-xl p-8 text-center">
        <div class="w-16 h-16 mx-auto rounded-lg bg-red-600 text-white flex items-center justify-center text-2xl mb-4">
            <i class="fas fa-times"></i>
        </div>
        <h2 class="text-lg font-bold text-red-800 mb-2">Verifikasi Gagal</h2>
        <p class="text-red-600 text-sm mb-6">{{ $message }}</p>
        <a href="{{ route('satpam.scan') }}" class="inline-block px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors">
            <i class="fas fa-redo mr-2"></i>Scan Ulang
        </a>
    </div>
</div>
@endsection
