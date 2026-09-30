@extends('admin.layouts.app')

@section('title', 'Detail Jadwal Guru Piket')
@section('page-title', 'Detail Jadwal Guru Piket')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-gray-800 tracking-tight">Detail Jadwal Piket</h3>
            <p class="text-sm text-gray-500 mt-0.5">Informasi lengkap konfigurasi sesi jadwal dan daftar guru yang bertugas.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.jadwal-piket.edit', $jadwal) }}"
               class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm">
                <i class="fas fa-pencil-alt mr-2"></i> Edit
            </a>
            <a href="{{ route('admin.jadwal-piket.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
    </div>

    @include('components.alert')

    @php
        $namaHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    @endphp

    {{-- Main Info Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="text-2xl font-black text-blue-700">{{ $namaHari[$jadwal->hari] ?? 'Hari '.$jadwal->hari }}</span>
                    @if($jadwal->nama_sesi)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-100 text-blue-800">
                            {{ $jadwal->nama_sesi }}
                        </span>
                    @endif
                    @if($jadwal->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>Nonaktif
                        </span>
                    @endif
                </div>
                <p class="text-sm font-mono text-gray-500 mt-1">
                    <i class="far fa-clock mr-1 text-gray-400"></i> {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                </p>
            </div>

            {{-- Toggle Button --}}
            <form method="POST" action="{{ route('admin.jadwal-piket.toggle', $jadwal) }}"
                  onsubmit="return confirm('Apakah Anda yakin ingin mengubah status aktif jadwal ini?');">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition-colors border {{ $jadwal->is_active ? 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}">
                    <i class="fas {{ $jadwal->is_active ? 'fa-ban mr-1.5' : 'fa-check mr-1.5' }}"></i>
                    {{ $jadwal->is_active ? 'Nonaktifkan Jadwal' : 'Aktifkan Jadwal' }}
                </button>
            </form>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-6 bg-gray-50/50">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Koordinator Guru Piket</p>
                @if($jadwal->koordinator)
                    <p class="text-sm font-bold text-gray-800 mt-1">{{ $jadwal->koordinator->nama_lengkap }}</p>
                    @php
                        $waService = $waService ?? app(\App\Services\WhatsappMessageService::class);
                        $koorWaLink = $waService->generatePiketWaLink($jadwal->koordinator, $jadwal);
                    @endphp
                    @if($koorWaLink)
                        <a href="{{ $koorWaLink }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 text-xs text-emerald-700 hover:text-emerald-950 font-mono mt-1 px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors shadow-2xs group"
                           title="Kirim pesan WhatsApp pengingat ke Koordinator">
                            <i class="fab fa-whatsapp text-emerald-600 text-sm group-hover:scale-110 transition-transform"></i>
                            <span class="font-bold">{{ $jadwal->koordinator->no_telepon }}</span>
                            <span class="text-[10px] bg-emerald-200/70 text-emerald-800 px-1.5 py-0.5 rounded font-sans font-semibold">Ingatkan WA</span>
                        </a>
                    @else
                        <p class="text-xs text-gray-400 font-mono mt-0.5 flex items-center">
                            <i class="fab fa-whatsapp mr-1 text-gray-300"></i> {{ $jadwal->koordinator->no_telepon ?? '-' }}
                        </p>
                    @endif
                @else
                    <p class="text-xs text-gray-400 italic mt-1">Tidak disetel khusus</p>
                @endif
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal Mulai Berlaku</p>
                <p class="text-sm font-bold text-gray-800 mt-1">
                    {{ \Carbon\Carbon::parse($jadwal->tanggal_mulai_berlaku)->isoFormat('D MMMM Y') }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal Selesai Berlaku</p>
                <p class="text-sm font-bold text-gray-800 mt-1">
                    @if($jadwal->tanggal_selesai_berlaku)
                        {{ \Carbon\Carbon::parse($jadwal->tanggal_selesai_berlaku)->isoFormat('D MMMM Y') }}
                    @else
                        <span class="text-emerald-600 font-semibold">Berlaku tanpa batas akhir</span>
                    @endif
                </p>
            </div>
        </div>

        {{-- Helper Penjelasan Jadwal Berulang --}}
        <div class="px-6 py-3.5 bg-blue-50/70 border-t border-blue-100/60 text-xs text-blue-900 flex items-start gap-2.5">
            <i class="fas fa-info-circle text-blue-600 text-sm mt-0.5 flex-shrink-0"></i>
            <div class="leading-relaxed">
                <span class="font-bold text-blue-950">Penjelasan Jadwal Berulang:</span>
                Jadwal @if($jadwal->nama_sesi)<strong>[{{ $jadwal->nama_sesi }}]</strong>@endif ini berulang setiap hari <strong>{{ $namaHari[$jadwal->hari] ?? 'Hari '.$jadwal->hari }}</strong> pukul <strong>{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB</strong>
                @if($jadwal->tanggal_selesai_berlaku)
                    selama periode {{ \Carbon\Carbon::parse($jadwal->tanggal_mulai_berlaku)->isoFormat('D MMMM Y') }} sampai {{ \Carbon\Carbon::parse($jadwal->tanggal_selesai_berlaku)->isoFormat('D MMMM Y') }}.
                @else
                    mulai {{ \Carbon\Carbon::parse($jadwal->tanggal_mulai_berlaku)->isoFormat('D MMMM Y') }} tanpa batas tanggal akhir.
                @endif
            </div>
        </div>
    </div>

    {{-- Guru Piket Resmi Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h4 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 mb-4 flex items-center">
            <i class="fas fa-users text-blue-600 mr-2"></i> Guru Piket Resmi ({{ $jadwal->guru->count() }} Guru)
        </h4>

        @if($jadwal->guru->isEmpty())
            <p class="text-sm text-amber-600 italic">Belum ada guru piket resmi yang ditugaskan pada jadwal ini.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($jadwal->guru as $index => $g)
                    @php
                        $waService = $waService ?? app(\App\Services\WhatsappMessageService::class);
                        $gWaLink = $waService->generatePiketWaLink($g, $jadwal);
                    @endphp
                    <div class="flex items-center p-3.5 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center mr-3 flex-shrink-0">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-800 truncate">{{ $g->nama_lengkap }}</p>
                            <div class="flex flex-wrap items-center gap-x-2.5 text-xs font-mono mt-1">
                                <span class="text-gray-400">NIP: {{ $g->nip ?? '-' }}</span>
                                @if($gWaLink)
                                    <a href="{{ $gWaLink }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 text-emerald-700 hover:text-emerald-950 bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded border border-emerald-200 transition-colors shadow-2xs group"
                                       title="Kirim pesan WhatsApp pengingat ke {{ $g->nama_lengkap }}">
                                        <i class="fab fa-whatsapp text-emerald-600 group-hover:scale-110 transition-transform"></i>
                                        <span class="font-bold">{{ $g->no_telepon }}</span>
                                        <i class="fas fa-paper-plane text-[9px] text-emerald-500 opacity-70 group-hover:opacity-100"></i>
                                    </a>
                                @else
                                    <span class="inline-flex items-center text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">
                                        <i class="fab fa-whatsapp mr-1 text-gray-300"></i> {{ $g->no_telepon ?? 'No HP belum ada' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $g->status_aktif ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600' }}">
                            {{ $g->status_aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Riwayat Pertukaran / Penggantian Jadwal --}}
    @if($jadwal->pertukaranJadwalPikets->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h4 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 mb-4 flex items-center">
                <i class="fas fa-exchange-alt text-indigo-600 mr-2"></i> Riwayat Penggantian Guru Piket ({{ $jadwal->pertukaranJadwalPikets->count() }})
            </h4>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50 text-gray-500 font-semibold uppercase border-b border-gray-100">
                        <tr>
                            <th class="py-2.5 px-3">Tanggal</th>
                            <th class="py-2.5 px-3">Guru Asal</th>
                            <th class="py-2.5 px-3">Guru Pengganti</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-center">Aktif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($jadwal->pertukaranJadwalPikets as $p)
                            <tr>
                                <td class="py-2.5 px-3 font-medium text-gray-900">
                                    {{ \Carbon\Carbon::parse($p->tanggal)->isoFormat('D MMM Y') }}
                                </td>
                                <td class="py-2.5 px-3 font-semibold text-gray-800">
                                    {{ $p->guruAsal?->nama_lengkap ?? '-' }}
                                </td>
                                <td class="py-2.5 px-3 font-semibold text-gray-800">
                                    {{ $p->guruPengganti?->nama_lengkap ?? '-' }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                        {{ $p->status === 'disetujui' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                           ($p->status === 'ditolak' ? 'bg-red-50 text-red-700 border border-red-200' :
                                           ($p->status === 'dibatalkan' ? 'bg-gray-100 text-gray-600 border border-gray-200' : 'bg-amber-50 text-amber-700 border border-amber-200')) }}">
                                        {{ $p->status }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    @if($p->is_active)
                                        <i class="fas fa-check-circle text-emerald-600 text-sm"></i>
                                    @else
                                        <span class="text-gray-300 font-bold">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
