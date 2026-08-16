@extends('admin.layouts.app')

@section('title', 'Jadwal Guru Piket')
@section('page-title', 'Jadwal Piket 7 Hari')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b">
        <h3 class="text-lg font-bold">Jadwal Piket (Senin - Minggu)</h3>
        <p class="text-sm text-gray-500 mt-1">
            Petugas Guru Piket Utama bertugas setiap hari (Senin - Minggu).
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">Hari</th>
                    <th class="p-3 text-left">Guru Piket Penanggung Jawab</th>
                    <th class="p-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($piket as $item)
                <tr class="hover:bg-gray-50 {{ $item->hari === strtolower(now()->format('l')) ? 'bg-blue-50/50' : '' }}">
                    <td class="p-3 font-semibold text-gray-800">
                        {{ ucfirst($item->hari) }}
                        @if($item->hari === strtolower(now()->format('l')))
                            <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">HARI INI</span>
                        @endif
                    </td>
                    <td class="p-3 font-bold text-gray-800">GURU PIKET UTAMA</td>
                    <td class="p-3 text-center">
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 font-bold text-xs rounded-full inline-flex items-center">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>Aktif Bertugas
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection