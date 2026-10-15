@extends('satpam.layouts.app')

@section('title', 'Verifikasi Gagal')
@section('page-title', 'QR Code Tidak Valid')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white border border-rose-200 rounded-2xl p-6 sm:p-8 text-center shadow-xs">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center text-3xl mb-4 shadow-2xs">
            <i class="ti ti-x"></i>
        </div>
        <h2 class="text-lg font-bold text-slate-900 mb-1.5">Verifikasi Gagal</h2>
        <p class="text-rose-600 text-sm mb-6">{{ $message }}</p>
        <a href="{{ route('satpam.scan') }}" class="inline-flex items-center justify-center min-h-[44px] px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-xs transition-colors">
            <i class="ti ti-reload mr-2 text-base"></i>Scan Ulang
        </a>
    </div>
</div>
@endsection
