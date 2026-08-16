@extends('admin.layouts.app')

@section('title', 'Jadwal Guru Piket')
@section('page-title', 'Jadwal Piket 7 Hari')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b">
        <h3 class="text-lg font-bold">Jadwal Piket (Senin - Minggu)</h3>
        <p class="text-sm text-gray-500 mt-1">
            Jadwal otomatis dibuat. Pilih guru yang bertugas di setiap hari.
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">Hari</th>
                    <th class="p-3 text-left">Guru Piket</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($piket as $item)
                <tr class="hover:bg-gray-50 {{ $item->hari === strtolower(now()->format('l')) ? 'bg-yellow-50' : '' }}">
                    <td class="p-3 font-semibold">
                        {{ ucfirst($item->hari) }}
                        @if($item->hari === strtolower(now()->format('l')))
                            <span class="ml-2 px-2 py-0.5 bg-yellow-200 text-yellow-800 text-xs rounded">HARI INI</span>
                        @endif
                    </td>
                    <td class="p-3">{{ $item->guru->nama_lengkap ?? '-' }}</td>
                    <td class="p-3 text-center">
                        <form method="POST" action="{{ route('admin.piket.update', $item->id) }}" class="flex items-center justify-center gap-2">
                            @csrf
                            @method('PUT')
                            <select name="guru_id" class="border rounded px-3 py-1 text-sm">
                                @foreach($gurus as $guru)
                                    <option value="{{ $guru->id }}" {{ $item->guru_id == $guru->id ? 'selected' : '' }}>
                                        {{ $guru->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                Simpan
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection