@extends('admin.layouts.app')
@section('title', 'Audit Log')
@section('page-title', 'Audit Log Aktivitas')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b flex justify-between items-center">
        <div>
            <h3 class="text-lg font-bold">Log Aktivitas Sistem</h3>
            <p class="text-sm text-gray-500">Catatan semua aktivitas pengguna di sistem.</p>
        </div>
        <form method="GET" class="flex space-x-2">
            <select name="action" onchange="this.form.submit()" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua Aksi</option>
                @foreach(['approve','reject','konfirmasi_keluar','konfirmasi_kembali','upload_signature','delete_signature','create_siswa','create_guru'] as $a)
                    <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">Waktu</th>
                    <th class="p-3 text-left">User</th>
                    <th class="p-3 text-left">Aksi</th>
                    <th class="p-3 text-left">Tabel</th>
                    <th class="p-3 text-left">Record ID</th>
                    <th class="p-3 text-left">IP</th>
                    <th class="p-3 text-left">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    {{-- FIX: konversi ke Asia/Jakarta supaya waktu log sesuai real time WIB,
                         bukan waktu mentah dari DB (bisa UTC kalau config app.timezone belum diubah) --}}
                    <td class="p-3 text-sm">{{ $log->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') }}</td>
                    <td class="p-3 text-sm font-semibold">{{ $log->user->name ?? 'Unknown' }}</td>
                    <td class="p-3">
                        @php
                            $colors = ['approve'=>'green','reject'=>'red','konfirmasi_keluar'=>'blue','konfirmasi_kembali'=>'purple','upload_signature'=>'indigo','delete_signature'=>'orange'];
                        @endphp
                        <span class="px-2 py-1 rounded text-xs font-bold bg-{{ $colors[$log->action] ?? 'gray' }}-100 text-{{ $colors[$log->action] ?? 'gray' }}-800">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="p-3 text-sm font-mono">{{ $log->table_name }}</td>
                    <td class="p-3 text-sm">{{ $log->record_id }}</td>
                    <td class="p-3 text-sm text-gray-600">{{ $log->ip_address }}</td>
                    <td class="p-3 text-xs">
                        @if($log->new_value)
                            <details>
                                <summary class="cursor-pointer text-blue-600">Lihat</summary>
                                <pre class="mt-1 bg-gray-100 p-2 rounded text-xs overflow-auto">{{ json_encode($log->new_value, JSON_PRETTY_PRINT) }}</pre>
                            </details>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="p-5 text-center text-gray-500">Belum ada log aktivitas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="p-4 border-t">{{ $logs->links() }}</div>
    @endif
</div>
@endsection