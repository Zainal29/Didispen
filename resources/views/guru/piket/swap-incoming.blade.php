@extends('guru.layouts.app')
@section('title', 'Permintaan Tukar Jadwal Piket')
@section('page-title', 'Pertukaran Jadwal Piket')

@section('content')
@include('components.alert')

<div class="space-y-6">
    {{-- Header & Quick Action --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-handshake"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-gray-900">Kelola Penggantian Guru Piket</h3>
                <p class="text-xs text-gray-500">Tinjau permohonan penggantian yang ditujukan kepada Anda atau ajukan penggantian baru.</p>
            </div>
        </div>
        <div>
            <a href="{{ route('guru.piket.swap.create') }}"
               class="inline-flex items-center px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition-colors">
                <i class="fas fa-plus mr-1.5"></i>Ajukan Tukar Jadwal
            </a>
        </div>
    </div>

    {{-- BAGIAN 1: PERMINTAAN MASUK (Incoming) --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 bg-gray-50/50 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fas fa-inbox text-indigo-600"></i>
                <h4 class="text-sm font-bold text-gray-900">Permintaan Masuk Untuk Anda</h4>
            </div>
            <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-indigo-100 text-indigo-700">
                {{ $incomingRequests->count() }} Permintaan
            </span>
        </div>

        @if($incomingRequests->isEmpty())
            <div class="p-8 text-center text-gray-500 text-xs">
                <i class="fas fa-check-circle text-gray-300 text-3xl mb-2"></i>
                <p>Tidak ada permohonan penggantian tugas piket yang masuk untuk Anda.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 uppercase font-semibold text-[11px] border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3">Guru Asal</th>
                            <th class="px-4 py-3">Tanggal & Sesi</th>
                            <th class="px-4 py-3">Alasan</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($incomingRequests as $req)
                            @php
                                $statusBadge = match($req->status) {
                                    'disetujui' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'ditolak'   => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'dibatalkan'=> 'bg-gray-100 text-gray-600 border-gray-200',
                                    default     => 'bg-amber-50 text-amber-700 border-amber-200',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-4 py-3.5 font-bold text-gray-900">
                                    {{ $req->guruAsal->nama_lengkap ?? 'Guru' }}
                                    <div class="text-[11px] font-normal text-gray-400">{{ $req->guruAsal->nip ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-gray-900">
                                        <i class="far fa-calendar-alt text-gray-400 mr-1"></i>
                                        {{ \Carbon\Carbon::parse($req->tanggal)->translatedFormat('l, d M Y') }}
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        <i class="far fa-clock text-gray-400 mr-1"></i>
                                        {{ substr($req->jadwalPiket->jam_mulai, 0, 5) }} - {{ substr($req->jadwalPiket->jam_selesai, 0, 5) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 max-w-xs truncate" title="{{ $req->alasan }}">
                                    {{ $req->alasan ?: '-' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border {{ $statusBadge }}">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    @if($req->status === 'menunggu')
                                        <div class="inline-flex items-center gap-1.5">
                                            <form method="POST" action="{{ route('guru.piket.swap.accept', $req->id) }}" onsubmit="return confirm('Apakah Anda yakin bersedia menggantikan tugas piket ini?');">
                                                @csrf
                                                <button type="submit" class="px-3 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-colors">
                                                    <i class="fas fa-check mr-1"></i>Terima
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('guru.piket.swap.reject', $req->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menolak permohonan penggantian ini?');">
                                                @csrf
                                                <button type="submit" class="px-3 py-1 rounded bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition-colors">
                                                    <i class="fas fa-times mr-1"></i>Tolak
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- BAGIAN 2: RIWAYAT PENGAJUAN SAYA (Outgoing) --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 bg-gray-50/50 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fas fa-paper-plane text-blue-600"></i>
                <h4 class="text-sm font-bold text-gray-900">Riwayat Pengajuan Penggantian Saya</h4>
            </div>
            <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-blue-100 text-blue-700">
                {{ $outgoingRequests->count() }} Pengajuan
            </span>
        </div>

        @if($outgoingRequests->isEmpty())
            <div class="p-8 text-center text-gray-500 text-xs">
                <i class="fas fa-history text-gray-300 text-3xl mb-2"></i>
                <p>Anda belum pernah mengajukan penggantian jadwal piket.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 uppercase font-semibold text-[11px] border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3">Guru Pengganti</th>
                            <th class="px-4 py-3">Tanggal & Sesi</th>
                            <th class="px-4 py-3">Alasan</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($outgoingRequests as $out)
                            @php
                                $statusBadge = match($out->status) {
                                    'disetujui' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'ditolak'   => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'dibatalkan'=> 'bg-gray-100 text-gray-600 border-gray-200',
                                    default     => 'bg-amber-50 text-amber-700 border-amber-200',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-4 py-3.5 font-bold text-gray-900">
                                    {{ $out->guruPengganti->nama_lengkap ?? 'Guru' }}
                                    <div class="text-[11px] font-normal text-gray-400">{{ $out->guruPengganti->nip ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-gray-900">
                                        <i class="far fa-calendar-alt text-gray-400 mr-1"></i>
                                        {{ \Carbon\Carbon::parse($out->tanggal)->translatedFormat('l, d M Y') }}
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        <i class="far fa-clock text-gray-400 mr-1"></i>
                                        {{ substr($out->jadwalPiket->jam_mulai, 0, 5) }} - {{ substr($out->jadwalPiket->jam_selesai, 0, 5) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 max-w-xs truncate" title="{{ $out->alasan }}">
                                    {{ $out->alasan ?: '-' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border {{ $statusBadge }}">
                                        {{ ucfirst($out->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    @if($out->status === 'menunggu')
                                        <form method="POST" action="{{ route('guru.piket.swap.cancel', $out->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?');" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs border border-gray-300 transition-colors">
                                                <i class="fas fa-ban mr-1"></i>Batalkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 text-xs italic">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
