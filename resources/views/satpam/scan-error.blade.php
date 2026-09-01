@extends('satpam.layouts.app')

@section('title', 'Verifikasi Gagal')
@section('page-title', 'QR Code Tidak Valid')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-8 text-center">
        <div class="w-20 h-20 mx-auto rounded-full bg-red-500 text-white flex items-center justify-center text-4xl mb-4">
            <i class="fas fa-times"></i>
        </div>
        <h2 class="text-xl font-bold text-red-800 mb-2">Verifikasi Gagal</h2>
        <p class="text-red-600 text-sm mb-6">{{ $message }}</p>
        <a href="{{ route('satpam.scan') }}" class="inline-block px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-colors">
            <i class="fas fa-redo mr-2"></i>Scan Ulang
        </a>
    </div>
</div>
@endsection
