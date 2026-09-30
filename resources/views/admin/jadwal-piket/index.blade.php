@extends('admin.layouts.app')

@section('title', 'Jadwal Guru Piket')
@section('page-title', 'Kelola Jadwal Guru Piket')

@section('content')
<div class="space-y-6">
    {{-- HEADER CARD --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 sm:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-xl font-bold text-gray-800 tracking-tight">Daftar Jadwal Guru Piket</h3>
                <p class="text-sm text-gray-500 mt-1">Kelola pembagian sesi dan penugasan guru piket harian sekolah.</p>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('admin.jadwal-piket.create') }}"
                   class="flex-1 sm:flex-none min-h-[44px] bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-all shadow-sm hover:shadow flex items-center justify-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Jadwal
                </a>
            </div>
        </div>

        @include('components.alert')

        {{-- FILTER BAR --}}
        <div class="px-5 sm:px-6 py-4 bg-gray-50/80 border-t border-b border-gray-100">
            <form method="GET" action="{{ route('admin.jadwal-piket.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 items-end">
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Cari Guru</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fas fa-search text-sm"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau NIP guru..."
                               class="w-full pl-9 pr-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Hari</label>
                    @php
                        $namaHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                    @endphp
                    <select name="hari" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer">
                        <option value="">Semua Hari</option>
                        @foreach($namaHari as $no => $nama)
                            <option value="{{ $no }}" {{ request('hari') == $no ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors flex items-center justify-center gap-1.5">
                        <i class="fas fa-filter text-xs"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'hari', 'status']))
                        <a href="{{ route('admin.jadwal-piket.index') }}" class="py-2 px-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-semibold transition-colors flex items-center justify-center" title="Reset Filter">
                            <i class="fas fa-times text-xs"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- VIEW MODE TABS --}}
        <div class="px-5 sm:px-6 pt-3 pb-0 bg-white border-t border-gray-100 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center space-x-1 border-b border-gray-200">
                <a href="{{ route('admin.jadwal-piket.index', array_merge(request()->except('view'), ['view' => 'list'])) }}"
                   class="px-4 py-2.5 text-xs font-bold border-b-2 transition-colors flex items-center gap-2 {{ ($viewMode ?? 'list') === 'list' ? 'border-blue-600 text-blue-600 bg-blue-50/50 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-th-list"></i> Daftar Sesi
                </a>
                <a href="{{ route('admin.jadwal-piket.index', array_merge(request()->except('view'), ['view' => 'matriks'])) }}"
                   class="px-4 py-2.5 text-xs font-bold border-b-2 transition-colors flex items-center gap-2 {{ ($viewMode ?? '') === 'matriks' ? 'border-blue-600 text-blue-600 bg-blue-50/50 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-table"></i> Matriks Jadwal Sekolah (Format Resmi)
                </a>
            </div>
            <div class="text-xs text-gray-400 py-1">
                @if(($viewMode ?? '') === 'matriks')
                    <span class="inline-flex items-center text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-medium"><i class="fas fa-check-circle mr-1"></i> Format Matriks SK SMK N 1 Bangsri</span>
                @endif
            </div>
        </div>

        @if(($viewMode ?? '') === 'matriks')
            {{-- TAMPILAN MATRIKS SEKOLAH PERSIS DOKUMEN RESMI --}}
            <div class="p-4 sm:p-6 overflow-x-auto">
                <div class="border border-gray-300 rounded-lg overflow-hidden bg-white shadow-2xs">
                    <table class="w-full text-left text-xs text-gray-800 border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 font-bold uppercase tracking-wider text-[11px] border-b border-gray-300">
                                <th class="py-3 px-3 border-r border-gray-300 w-12 text-center">No.</th>
                                <th class="py-3 px-3 border-r border-gray-300 w-24">Hari</th>
                                <th class="py-3 px-4 border-r border-gray-300 w-64">Koordinator Guru Piket</th>
                                <th class="py-3 px-4 border-r border-gray-300 w-60">Pengaturan Sesi Piket</th>
                                <th class="py-3 px-4 border-r border-gray-300">Guru Piket</th>
                                <th class="py-3 px-4 border-r border-gray-300 w-48">No.. HP</th>
                                <th class="py-3 px-3 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @php
                                $hariNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                                $matrixGroup = $matriksData ?? collect();
                                $hariCounter = 1;
                                $waService = $waService ?? app(\App\Services\WhatsappMessageService::class);
                            @endphp

                            @forelse($matrixGroup as $hariNum => $sesiList)
                                @php
                                    $firstSesi = $sesiList->first();
                                    $koordinator = $sesiList->pluck('koordinator')->filter()->first();
                                    $totalSesi = $sesiList->count();
                                @endphp
                                @foreach($sesiList as $idx => $sesi)
                                    <tr class="hover:bg-blue-50/20 transition-colors {{ $idx === 0 ? 'border-t-2 border-gray-300' : '' }}">
                                        {{-- No. (Rowspan) --}}
                                        @if($idx === 0)
                                            <td rowspan="{{ $totalSesi }}" class="py-3 px-3 border-r border-gray-300 text-center font-bold align-top bg-gray-50/30">
                                                {{ $hariCounter++ }}
                                            </td>
                                            {{-- Hari (Rowspan) --}}
                                            <td rowspan="{{ $totalSesi }}" class="py-3 px-3 border-r border-gray-300 font-bold text-gray-900 align-top bg-gray-50/30">
                                                {{ $hariNames[$hariNum] ?? 'Hari '.$hariNum }}
                                            </td>
                                            {{-- Koordinator (Rowspan) --}}
                                            <td rowspan="{{ $totalSesi }}" class="py-3 px-4 border-r border-gray-300 align-top bg-gray-50/30">
                                                @if($koordinator)
                                                    <div class="font-bold text-gray-900">{{ $koordinator->nama_lengkap }}</div>
                                                    @php
                                                        $koorWaLink = $waService->generatePiketWaLink($koordinator, $firstSesi);
                                                    @endphp
                                                    @if($koorWaLink)
                                                        <a href="{{ $koorWaLink }}" target="_blank" rel="noopener noreferrer"
                                                           class="inline-flex items-center gap-1 text-[11px] text-emerald-700 hover:text-emerald-950 font-mono mt-1 px-2 py-0.5 rounded bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors group shadow-2xs"
                                                           title="Klik untuk kirim pesan WhatsApp pengingat ke Koordinator">
                                                            <i class="fab fa-whatsapp text-emerald-600 group-hover:scale-110 transition-transform"></i>
                                                            <span class="font-semibold">{{ $koordinator->no_telepon }}</span>
                                                            <i class="fas fa-paper-plane text-[9px] text-emerald-500 opacity-70 group-hover:opacity-100"></i>
                                                        </a>
                                                    @else
                                                        <div class="text-[11px] text-gray-400 font-mono mt-0.5 flex items-center">
                                                            <i class="fab fa-whatsapp text-gray-300 mr-1 text-xs"></i> {{ $koordinator->no_telepon ?? '-' }}
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400 italic">Belum disetel</span>
                                                @endif
                                            </td>
                                        @endif

                                        {{-- Pengaturan Sesi Piket --}}
                                        <td class="py-2.5 px-4 border-r border-gray-300 font-medium">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-blue-800">{{ $sesi->nama_sesi ?? 'Sesi' }}</span>
                                                <span class="text-[11px] text-gray-500 font-mono">({{ substr($sesi->jam_mulai, 0, 5) }} - {{ substr($sesi->jam_selesai, 0, 5) }})</span>
                                            </div>
                                            <div class="text-[10px] mt-0.5">
                                                @if($sesi->is_active)
                                                    <span class="text-emerald-700 font-semibold inline-flex items-center">
                                                        <i class="fas fa-check-circle text-emerald-500 mr-1 text-[10px]"></i> Aktif
                                                    </span>
                                                @else
                                                    <span class="text-gray-400 font-semibold inline-flex items-center">
                                                        <i class="fas fa-times-circle text-gray-400 mr-1 text-[10px]"></i> Nonaktif
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Guru Piket --}}
                                        <td class="py-2.5 px-4 border-r border-gray-300">
                                            @if($sesi->guru->isEmpty())
                                                <span class="text-xs text-amber-600 italic">Belum ada guru piket</span>
                                            @else
                                                <ul class="space-y-1">
                                                    @foreach($sesi->guru as $g)
                                                        <li class="font-semibold text-gray-900 flex items-center gap-1.5">
                                                            <i class="fas fa-user-check text-blue-500 text-[10px]"></i>
                                                            <span>{{ $g->nama_lengkap }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>

                                        {{-- No.. HP Guru Piket --}}
                                        <td class="py-2.5 px-4 border-r border-gray-300 font-mono text-[11px]">
                                            @if($sesi->guru->isEmpty())
                                                <span class="text-gray-400">-</span>
                                            @else
                                                <ul class="space-y-1.5">
                                                    @foreach($sesi->guru as $g)
                                                        @php
                                                            $gWaLink = $waService->generatePiketWaLink($g, $sesi);
                                                        @endphp
                                                        <li>
                                                            @if($gWaLink)
                                                                <a href="{{ $gWaLink }}" target="_blank" rel="noopener noreferrer"
                                                                   class="inline-flex items-center gap-1.5 text-emerald-700 hover:text-emerald-950 px-2 py-0.5 rounded bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors group shadow-2xs"
                                                                   title="Kirim pesan WhatsApp pengingat jadwal piket ke {{ $g->nama_lengkap }}">
                                                                    <i class="fab fa-whatsapp text-emerald-600 text-xs group-hover:scale-110 transition-transform"></i>
                                                                    <span class="font-semibold">{{ $g->no_telepon }}</span>
                                                                    <i class="fas fa-paper-plane text-[9px] text-emerald-500 opacity-70 group-hover:opacity-100"></i>
                                                                </a>
                                                            @else
                                                                <span class="inline-flex items-center gap-1 text-gray-400 py-0.5 px-1">
                                                                    <i class="fab fa-whatsapp text-gray-300 text-xs"></i>
                                                                    <span>{{ $g->no_telepon ?? '-' }}</span>
                                                                </span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="py-2.5 px-3 text-center">
                                            <div class="inline-flex items-center gap-1">
                                                <a href="{{ route('admin.jadwal-piket.show', $sesi) }}" class="p-1.5 text-gray-500 hover:text-blue-600 rounded" title="Lihat">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </a>
                                                <a href="{{ route('admin.jadwal-piket.edit', $sesi) }}" class="p-1.5 text-gray-500 hover:text-amber-600 rounded" title="Edit">
                                                    <i class="fas fa-pencil-alt text-xs"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-400">
                                        Belum ada jadwal piket yang dibuat. Silakan tambahkan jadwal baru.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            {{-- DESKTOP TABLE VIEW (md and up) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50/50 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="py-3 px-4">Hari & Sesi</th>
                            <th class="py-3 px-4">Koordinator</th>
                            <th class="py-3 px-4">Periode Berlaku</th>
                            <th class="py-3 px-4">Guru Bertugas & No HP</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($jadwals as $j)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                {{-- Hari & Jam --}}
                                <td class="py-3.5 px-4 font-medium text-gray-900">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-blue-700">{{ $namaHari[$j->hari] ?? 'Hari '.$j->hari }}</span>
                                        @if($j->nama_sesi)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                                {{ $j->nama_sesi }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 font-mono mt-0.5">
                                        <i class="far fa-clock text-[10px] mr-1 text-gray-400"></i>{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }} WIB
                                    </div>
                                </td>

                                {{-- Koordinator --}}
                                <td class="py-3.5 px-4 text-xs">
                                    @if($j->koordinator)
                                        <div class="font-bold text-gray-900">{{ $j->koordinator->nama_lengkap }}</div>
                                        @php
                                            $waService = $waService ?? app(\App\Services\WhatsappMessageService::class);
                                            $koorWaLink = $waService->generatePiketWaLink($j->koordinator, $j);
                                        @endphp
                                        @if($koorWaLink)
                                            <a href="{{ $koorWaLink }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1 text-[11px] text-emerald-700 hover:text-emerald-950 font-mono mt-1 px-2 py-0.5 rounded bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors group"
                                               title="Klik untuk kirim pesan WhatsApp pengingat ke Koordinator">
                                                <i class="fab fa-whatsapp text-emerald-600 group-hover:scale-110 transition-transform"></i>
                                                <span class="font-semibold">{{ $j->koordinator->no_telepon }}</span>
                                                <i class="fas fa-paper-plane text-[9px] text-emerald-500 opacity-70 group-hover:opacity-100"></i>
                                            </a>
                                        @else
                                            <div class="text-[11px] text-gray-400 font-mono mt-0.5 flex items-center">
                                                <i class="fab fa-whatsapp text-gray-300 mr-1"></i> {{ $j->koordinator->no_telepon ?? '-' }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-gray-400 italic">Tidak disetel</span>
                                    @endif
                                </td>

                                {{-- Periode Berlaku --}}
                                <td class="py-3.5 px-4 text-xs text-gray-600">
                                    <div><span class="text-gray-400">Mulai:</span> {{ \Carbon\Carbon::parse($j->tanggal_mulai_berlaku)->isoFormat('D MMM Y') }}</div>
                                    <div class="mt-0.5">
                                        <span class="text-gray-400">Selesai:</span>
                                        @if($j->tanggal_selesai_berlaku)
                                            {{ \Carbon\Carbon::parse($j->tanggal_selesai_berlaku)->isoFormat('D MMM Y') }}
                                        @else
                                            <span class="text-emerald-600 font-medium">Tanpa batas</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Guru Bertugas --}}
                                <td class="py-3.5 px-4">
                                    @if($j->guru->isEmpty())
                                        <span class="text-xs text-amber-600 italic">Belum ada guru ditugaskan</span>
                                    @else
                                        <ul class="space-y-2">
                                            @foreach($j->guru as $g)
                                                @php
                                                    $gWaLink = $waService->generatePiketWaLink($g, $j);
                                                @endphp
                                                <li class="text-xs text-gray-800">
                                                    <div class="font-semibold flex items-center gap-1">
                                                        <i class="fas fa-user-circle text-gray-400 text-[10px]"></i>
                                                        <span>{{ $g->nama_lengkap }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-[11px] font-mono text-gray-500 pl-4 mt-0.5">
                                                        @if($g->nip)
                                                            <span>NIP: {{ $g->nip }}</span>
                                                        @endif
                                                        @if($gWaLink)
                                                            <a href="{{ $gWaLink }}" target="_blank" rel="noopener noreferrer"
                                                               class="inline-flex items-center gap-1 text-emerald-700 hover:text-emerald-950 px-2 py-0.5 rounded bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors group"
                                                               title="Kirim pesan WhatsApp pengingat ke {{ $g->nama_lengkap }}">
                                                                <i class="fab fa-whatsapp text-emerald-600 group-hover:scale-110 transition-transform"></i>
                                                                <span class="font-semibold">{{ $g->no_telepon }}</span>
                                                                <i class="fas fa-paper-plane text-[9px] text-emerald-500 opacity-70 group-hover:opacity-100"></i>
                                                            </a>
                                                        @else
                                                            <span class="text-gray-400 flex items-center">
                                                                <i class="fab fa-whatsapp text-gray-300 mr-1 text-[10px]"></i> {{ $g->no_telepon ?? '-' }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>

                                {{-- Status Aktif/Nonaktif --}}
                                <td class="py-3.5 px-4 text-center">
                                    @if($j->is_active)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>Nonaktif
                                        </span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('admin.jadwal-piket.show', $j) }}"
                                           class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('admin.jadwal-piket.edit', $j) }}"
                                           class="p-2 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                                           title="Edit Jadwal">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>

                                        <form method="POST" action="{{ route('admin.jadwal-piket.toggle', $j) }}" class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin {{ $j->is_active ? 'menonaktifkan' : 'mengaktifkan' }} jadwal ini?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="p-2 {{ $j->is_active ? 'text-gray-400 hover:text-red-600 hover:bg-red-50' : 'text-gray-400 hover:text-emerald-600 hover:bg-emerald-50' }} rounded-lg transition-colors"
                                                    title="{{ $j->is_active ? 'Nonaktifkan Jadwal' : 'Aktifkan Jadwal' }}">
                                                <i class="fas {{ $j->is_active ? 'fa-toggle-on text-emerald-600' : 'fa-toggle-off text-gray-400' }} text-base"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-400">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3 text-gray-400">
                                        <i class="fas fa-calendar-times text-xl"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-600">Belum ada jadwal piket yang ditemukan.</p>
                                    <p class="text-xs text-gray-400 mt-1">Silakan sesuaikan filter pencarian atau buat jadwal baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE CARDS VIEW (Under md: 320px - 767px) --}}
            <div class="block md:hidden divide-y divide-gray-100">
                @forelse($jadwals as $j)
                    <div class="p-4 space-y-3 bg-white hover:bg-gray-50/50 transition-colors">
                        {{-- Header Card: Hari & Status --}}
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-base font-bold text-blue-700">{{ $namaHari[$j->hari] ?? 'Hari '.$j->hari }}</span>
                                    @if($j->nama_sesi)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                            {{ $j->nama_sesi }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-500 font-mono"><i class="far fa-clock mr-1 text-gray-400"></i>{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }} WIB</span>
                            </div>
                            <div>
                                @if($j->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>Nonaktif
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Koordinator if any --}}
                        @if($j->koordinator)
                            @php
                                $koorWaLink = $waService->generatePiketWaLink($j->koordinator, $j);
                            @endphp
                            <div class="text-xs bg-amber-50/60 p-2.5 rounded-lg border border-amber-100 text-amber-900">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 block mb-0.5">Koordinator Guru Piket:</span>
                                <div class="font-bold">{{ $j->koordinator->nama_lengkap }}</div>
                                @if($koorWaLink)
                                    <a href="{{ $koorWaLink }}" target="_blank" rel="noopener noreferrer"
                                       class="mt-1.5 min-h-[36px] inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-mono font-semibold text-emerald-800 bg-emerald-100/70 hover:bg-emerald-200/70 rounded-md border border-emerald-300 transition-colors">
                                        <i class="fab fa-whatsapp text-emerald-600 text-sm"></i>
                                        <span>{{ $j->koordinator->no_telepon }} (Ingatkan WA)</span>
                                    </a>
                                @else
                                    <div class="text-[11px] font-mono text-gray-500 mt-0.5 flex items-center">
                                        <i class="fab fa-whatsapp text-gray-400 mr-1"></i> {{ $j->koordinator->no_telepon ?? '-' }}
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Periode --}}
                        <div class="text-xs text-gray-600 bg-gray-50 p-2.5 rounded-lg border border-gray-100 space-y-0.5">
                            <div><span class="text-gray-400 font-medium">Mulai:</span> {{ \Carbon\Carbon::parse($j->tanggal_mulai_berlaku)->isoFormat('D MMM Y') }}</div>
                            <div>
                                <span class="text-gray-400 font-medium">Selesai:</span>
                                @if($j->tanggal_selesai_berlaku)
                                    {{ \Carbon\Carbon::parse($j->tanggal_selesai_berlaku)->isoFormat('D MMM Y') }}
                                @else
                                    <span class="text-emerald-600 font-semibold">Tanpa batas</span>
                                @endif
                            </div>
                        </div>

                        {{-- Guru Bertugas & No HP --}}
                        <div>
                            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Guru Bertugas & No HP:</span>
                            @if($j->guru->isEmpty())
                                <span class="text-xs text-amber-600 italic">Belum ada guru ditugaskan</span>
                            @else
                                <ul class="space-y-2">
                                    @foreach($j->guru as $g)
                                        @php
                                            $gWaLink = $waService->generatePiketWaLink($g, $j);
                                        @endphp
                                        <li class="text-xs text-gray-800 bg-gray-50 p-2.5 rounded-lg border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                            <div>
                                                <div class="font-semibold text-gray-900 flex items-center gap-1.5">
                                                    <i class="fas fa-user-circle text-blue-500 text-xs"></i>
                                                    <span>{{ $g->nama_lengkap }}</span>
                                                </div>
                                                <div class="text-[11px] font-mono text-gray-400 mt-0.5 pl-4">
                                                    NIP: {{ $g->nip ?? '-' }}
                                                </div>
                                            </div>
                                            <div>
                                                @if($gWaLink)
                                                    <a href="{{ $gWaLink }}" target="_blank" rel="noopener noreferrer"
                                                       class="min-h-[36px] px-3 py-1.5 text-xs font-semibold text-emerald-800 bg-emerald-100/80 hover:bg-emerald-200 rounded-md border border-emerald-300 inline-flex items-center gap-1.5 transition-colors">
                                                        <i class="fab fa-whatsapp text-emerald-600 text-sm"></i>
                                                        <span>{{ $g->no_telepon }}</span>
                                                        <i class="fas fa-paper-plane text-[9px] text-emerald-600 ml-0.5"></i>
                                                    </a>
                                                @else
                                                    <span class="text-[11px] font-mono text-gray-400 inline-flex items-center gap-1">
                                                        <i class="fab fa-whatsapp text-gray-300"></i> {{ $g->no_telepon ?? '-' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        {{-- Actions (Min 44px touch targets) --}}
                        <div class="pt-2 border-t border-gray-100 flex items-center justify-end gap-2">
                            <a href="{{ route('admin.jadwal-piket.show', $j) }}"
                               class="min-h-[44px] px-3.5 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center gap-1.5 transition-colors">
                                <i class="fas fa-eye text-blue-600"></i> Detail
                            </a>
                            <a href="{{ route('admin.jadwal-piket.edit', $j) }}"
                               class="min-h-[44px] px-3.5 py-2 text-xs font-semibold text-gray-700 bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200 rounded-lg flex items-center justify-center gap-1.5 transition-colors">
                                <i class="fas fa-pencil-alt text-amber-600"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('admin.jadwal-piket.toggle', $j) }}" class="inline"
                                  onsubmit="return confirm('Apakah Anda yakin ingin {{ $j->is_active ? 'menonaktifkan' : 'mengaktifkan' }} jadwal ini?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="min-h-[44px] px-3.5 py-2 text-xs font-semibold rounded-lg flex items-center justify-center gap-1.5 border transition-colors {{ $j->is_active ? 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}">
                                    <i class="fas {{ $j->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    {{ $j->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3 text-gray-400">
                            <i class="fas fa-calendar-times text-xl"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-600">Belum ada jadwal piket yang ditemukan.</p>
                        <p class="text-xs text-gray-400 mt-1">Silakan sesuaikan filter pencarian atau buat jadwal baru.</p>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- PAGINATION --}}
        @if($jadwals->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $jadwals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
