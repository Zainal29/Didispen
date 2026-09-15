@extends('admin.layouts.app')
@section('title', 'Izin Guru')
@section('page-title', 'Log Keluar & Kembali Guru')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b">
        <h3 class="text-lg font-bold text-gray-800">Daftar Aktivitas Guru</h3>
        <p class="text-sm text-gray-500 mt-1">Guru yang sedang keluar akan ditandai dengan highlight merah.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">Nama Guru</th>
                    <th class="p-3 text-left">Tujuan & Alasan</th>
                    <th class="p-3 text-left">Jam Keluar</th>
                    <th class="p-3 text-left">Jam Kembali</th>
                    <th class="p-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50 {{ $log->status === 'keluar' ? 'bg-red-50' : '' }}">
                    <td class="p-3">
                        <div class="font-semibold text-gray-800">{{ $log->guru->nama_lengkap }}</div>
                        <div class="text-xs text-gray-500">{{ $log->guru->mata_pelajaran ?? 'Guru' }}</div>
                    </td>
                    <td class="p-3 text-sm">
                        <div class="font-medium text-gray-800">{{ $log->tujuan }}</div>
                        <div class="text-xs text-gray-500 italic mt-1">"{{ Str::limit($log->alasan, 60) }}"</div>
                        @if($log->lokasi)
                            <div class="text-xs text-gray-400 mt-1"><i class="fas fa-map-marker-alt mr-1"></i>{{ $log->lokasi }}</div>
                        @endif
                    </td>
                    {{-- FIX: konversi ke Asia/Jakarta supaya jam yang tampil ke admin sesuai real time WIB,
                         bukan waktu mentah dari DB (yang bisa UTC kalau config app.timezone belum diubah) --}}
                    <td class="p-3 text-sm font-mono text-gray-700">
                        {{ $log->jam_keluar->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                    </td>
                    <td class="p-3 text-sm font-mono text-gray-600">
                        {{ $log->jam_kembali ? $log->jam_kembali->timezone('Asia/Jakarta')->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="p-3">
                        @if($log->status === 'keluar')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 animate-pulse">
                                <span class="w-2 h-2 bg-red-600 rounded-full mr-1.5"></span>
                                Sedang Keluar
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1.5"></i> Selesai
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-500">
                        <i class="fas fa-clipboard-list text-3xl mb-2 text-gray-300"></i>
                        <p>Belum ada data log keluar/masuk guru.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="p-4 border-t bg-gray-50">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection