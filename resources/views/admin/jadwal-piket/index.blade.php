@extends('admin.layouts.app')

@section('title', 'Jadwal Guru Piket')
@section('page-title', 'Kelola Jadwal Guru Piket')

@section('content')
<div class="space-y-5">
    {{-- HEADER CARD --}}
    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 overflow-hidden">
        <div class="p-5 sm:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-1.5 h-3.5 bg-blue-600 rounded-full"></span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Operasional Sekolah</span>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Daftar Jadwal Guru Piket</h3>
                <p class="text-xs text-slate-500 mt-0.5">Kelola pembagian sesi dan penugasan guru piket harian sekolah.</p>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('admin.jadwal-piket.create') }}"
                   class="uiverse-btn-tactile flex-1 sm:flex-none min-h-[40px] bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md shadow-blue-500/20 flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="ti ti-plus text-base"></i>
                    <span>Tambah Jadwal</span>
                </a>
            </div>
        </div>

        @include('components.alert')

        {{-- FILTER BAR --}}
        <div class="px-5 sm:px-6 py-4 bg-slate-50/50 border-t border-b border-slate-100">
            <form method="GET" action="{{ route('admin.jadwal-piket.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3.5 items-end">
                <div class="md:col-span-4">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Guru</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="ti ti-search text-base"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau NIP guru..."
                               class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Hari</label>
                    @php
                        $namaHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                    @endphp
                    <select name="hari" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none cursor-pointer">
                        <option value="">Semua Hari</option>
                        @foreach($namaHari as $no => $nama)
                            <option value="{{ $no }}" {{ request('hari') == $no ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex gap-1.5">
                    <button type="submit" class="uiverse-btn-tactile flex-1 min-h-[38px] py-2 px-3 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-blue-500/20 flex items-center justify-center gap-1 cursor-pointer">
                        <i class="ti ti-filter text-sm"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'hari', 'status']))
                        <a href="{{ route('admin.jadwal-piket.index') }}" class="uiverse-btn-tactile min-h-[38px] min-w-[38px] py-2 px-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-500 rounded-xl text-xs font-semibold transition-colors flex items-center justify-center" title="Reset Filter">
                            <i class="ti ti-rotate-2 text-base"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- VIEW MODE TABS --}}
        <div class="px-5 sm:px-6 pt-3 pb-0 bg-white border-t border-slate-100 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center space-x-1 border-b border-slate-200">
                <a href="{{ route('admin.jadwal-piket.index', array_merge(request()->except('view'), ['view' => 'list'])) }}"
                   class="px-4 py-2.5 text-xs font-bold border-b-2 transition-colors flex items-center gap-2 {{ ($viewMode ?? 'list') === 'list' ? 'border-blue-600 text-blue-600 bg-blue-50/50 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    <i class="ti ti-list text-base"></i> Daftar Sesi
                </a>
                <a href="{{ route('admin.jadwal-piket.index', array_merge(request()->except('view'), ['view' => 'matriks'])) }}"
                   class="px-4 py-2.5 text-xs font-bold border-b-2 transition-colors flex items-center gap-2 {{ ($viewMode ?? '') === 'matriks' ? 'border-blue-600 text-blue-600 bg-blue-50/50 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    <i class="ti ti-table text-base"></i> Matriks Jadwal Sekolah
                </a>
            </div>
            <div class="text-xs text-slate-400 py-1">
                @if(($viewMode ?? '') === 'matriks')
                    <span class="inline-flex items-center text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md font-medium text-[11px] border border-emerald-200/60">
                        <i class="ti ti-circle-check mr-1 text-xs"></i> Format Matriks SK SMK N 1 Bangsri
                    </span>
                @endif
            </div>
        </div>

        @if(($viewMode ?? '') === 'matriks')
            {{-- HINT GESER UNTUK MOBILE --}}
            <div class="px-5 pt-3 text-xs text-blue-600 sm:hidden flex items-center gap-1.5 font-medium">
                <i class="ti ti-arrows-horizontal text-blue-500 animate-pulse text-base"></i>
                <span>Geser ke samping untuk melihat seluruh kolom tabel</span>
            </div>

            {{-- TAMPILAN MATRIKS SEKOLAH PERSIS DOKUMEN RESMI --}}
            <div class="p-4 sm:p-6">
                <div class="w-full overflow-x-auto border border-slate-200 rounded-xl bg-white shadow-2xs touch-pan-x">
                    <table class="w-full min-w-[960px] text-left text-xs text-slate-800 border-collapse">
                        <thead>
                            <tr class="bg-slate-100/80 text-slate-700 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                                <th class="py-3 px-3 border-r border-slate-200 w-12 text-center whitespace-nowrap">No.</th>
                                <th class="py-3 px-3 border-r border-slate-200 w-24 whitespace-nowrap">Hari</th>
                                <th class="py-3 px-4 border-r border-slate-200 w-64 whitespace-nowrap">Koordinator Guru Piket</th>
                                <th class="py-3 px-4 border-r border-slate-200 w-60 whitespace-nowrap">Pengaturan Sesi Piket</th>
                                <th class="py-3 px-4 border-r border-slate-200 min-w-[200px]">Guru Piket</th>
                                <th class="py-3 px-4 border-r border-slate-200 w-48 whitespace-nowrap">No. HP</th>
                                <th class="py-3 px-3 text-center w-24 whitespace-nowrap sticky right-0 bg-slate-100 z-10 border-l border-slate-200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
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
                                    <tr class="hover:bg-slate-50/70 transition-colors {{ $idx === 0 ? 'border-t-2 border-slate-300' : '' }}">
                                        @if($idx === 0)
                                            <td rowspan="{{ $totalSesi }}" class="py-3 px-3 border-r border-slate-200 text-center font-bold align-top bg-slate-50/50 whitespace-nowrap">
                                                {{ $hariCounter++ }}
                                            </td>
                                            <td rowspan="{{ $totalSesi }}" class="py-3 px-3 border-r border-slate-200 font-bold text-slate-900 align-top bg-slate-50/50 whitespace-nowrap">
                                                {{ $hariNames[$hariNum] ?? 'Hari '.$hariNum }}
                                            </td>
                                            <td rowspan="{{ $totalSesi }}" class="py-3 px-4 border-r border-slate-200 align-top bg-slate-50/50">
                                                @if($koordinator)
                                                    <div class="font-bold text-slate-900">{{ $koordinator->nama_lengkap }}</div>
                                                    @php
                                                        $koorWaLink = $waService->generatePiketWaLink($koordinator, $firstSesi);
                                                    @endphp
                                                    @if($koorWaLink)
                                                        <a href="{{ $koorWaLink }}" target="_blank" rel="noopener noreferrer"
                                                           class="inline-flex items-center gap-1 text-[11px] text-emerald-700 hover:text-emerald-950 font-mono mt-1 px-2 py-0.5 rounded-md bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors group shadow-2xs whitespace-nowrap"
                                                           title="Klik untuk kirim pesan WhatsApp pengingat ke Koordinator">
                                                            <i class="ti ti-brand-whatsapp text-emerald-600 text-sm"></i>
                                                            <span class="font-semibold">{{ $koordinator->no_telepon }}</span>
                                                            <i class="ti ti-send text-[10px] text-emerald-500"></i>
                                                        </a>
                                                    @else
                                                        <div class="text-[11px] text-slate-400 font-mono mt-0.5 flex items-center whitespace-nowrap">
                                                            <i class="ti ti-brand-whatsapp text-slate-300 mr-1 text-xs"></i> {{ $koordinator->no_telepon ?? '-' }}
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-slate-400 italic">Belum disetel</span>
                                                @endif
                                            </td>
                                        @endif

                                        <td class="py-2.5 px-4 border-r border-slate-200 font-medium">
                                            <div class="flex items-center gap-1.5 whitespace-nowrap">
                                                <span class="font-bold text-blue-700">{{ $sesi->nama_sesi ?? 'Sesi' }}</span>
                                                <span class="text-[11px] text-slate-500 font-mono">({{ substr($sesi->jam_mulai, 0, 5) }} - {{ substr($sesi->jam_selesai, 0, 5) }})</span>
                                            </div>
                                            <div class="text-[10px] mt-0.5">
                                                @if($sesi->is_active)
                                                    <span class="text-emerald-700 font-semibold inline-flex items-center">
                                                        <i class="ti ti-circle-check text-emerald-500 mr-1 text-xs"></i> Aktif
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 font-semibold inline-flex items-center">
                                                        <i class="ti ti-circle-x text-slate-400 mr-1 text-xs"></i> Nonaktif
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <td class="py-2.5 px-4 border-r border-slate-200">
                                            @if($sesi->guru->isEmpty())
                                                <span class="text-xs text-amber-600 italic">Belum ada guru piket</span>
                                            @else
                                                <ul class="space-y-1">
                                                    @foreach($sesi->guru as $g)
                                                        <li class="font-semibold text-slate-900 flex items-center gap-1.5 whitespace-nowrap">
                                                            <i class="ti ti-user-check text-blue-600 text-xs"></i>
                                                            <span>{{ $g->nama_lengkap }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>

                                        <td class="py-2.5 px-4 border-r border-slate-200 font-mono text-[11px]">
                                            @if($sesi->guru->isEmpty())
                                                <span class="text-slate-400">-</span>
                                            @else
                                                <ul class="space-y-1.5">
                                                    @foreach($sesi->guru as $g)
                                                        @php
                                                            $gWaLink = $waService->generatePiketWaLink($g, $sesi);
                                                        @endphp
                                                        <li>
                                                            @if($gWaLink)
                                                                <a href="{{ $gWaLink }}" target="_blank" rel="noopener noreferrer"
                                                                   class="inline-flex items-center gap-1.5 text-emerald-700 hover:text-emerald-950 px-2 py-0.5 rounded-md bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors group shadow-2xs whitespace-nowrap"
                                                                   title="Kirim pesan WhatsApp pengingat jadwal piket ke {{ $g->nama_lengkap }}">
                                                                    <i class="ti ti-brand-whatsapp text-emerald-600 text-sm"></i>
                                                                    <span class="font-semibold">{{ $g->no_telepon }}</span>
                                                                    <i class="ti ti-send text-[10px] text-emerald-500"></i>
                                                                </a>
                                                            @else
                                                                <span class="inline-flex items-center gap-1 text-slate-400 py-0.5 px-1 whitespace-nowrap">
                                                                    <i class="ti ti-brand-whatsapp text-slate-300 text-xs"></i>
                                                                    <span>{{ $g->no_telepon ?? '-' }}</span>
                                                                </span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>

                                        <td class="py-2.5 px-3 text-center sticky right-0 bg-white/95 backdrop-blur-xs border-l border-slate-200">
                                            <div class="inline-flex items-center gap-1">
                                                <a href="{{ route('admin.jadwal-piket.show', $sesi) }}" class="w-7 h-7 inline-flex items-center justify-center text-blue-600 hover:bg-blue-50 rounded-lg" title="Lihat">
                                                    <i class="ti ti-eye text-sm"></i>
                                                </a>
                                                <a href="{{ route('admin.jadwal-piket.edit', $sesi) }}" class="w-7 h-7 inline-flex items-center justify-center text-amber-600 hover:bg-amber-50 rounded-lg" title="Edit">
                                                    <i class="ti ti-edit text-sm"></i>
                                                </a>
                                                <button type="button"
                                                        onclick="confirmDeleteJadwal('{{ $sesi->id }}', '{{ addslashes($hariNames[$hariNum] ?? 'Hari '.$hariNum) }} - {{ addslashes($sesi->nama_sesi ?? 'Sesi') }}')"
                                                        class="w-7 h-7 inline-flex items-center justify-center text-rose-600 hover:bg-rose-50 rounded-lg cursor-pointer"
                                                        title="Hapus Jadwal">
                                                    <i class="ti ti-trash text-sm"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
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
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4 pl-5">Hari & Sesi</th>
                            <th class="py-3.5 px-4">Koordinator</th>
                            <th class="py-3.5 px-4">Periode Berlaku</th>
                            <th class="py-3.5 px-4">Guru Bertugas & No HP</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 pr-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($jadwals as $j)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 pl-5 font-medium text-slate-900">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-blue-700">{{ $namaHari[$j->hari] ?? 'Hari '.$j->hari }}</span>
                                        @if($j->nama_sesi)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                                {{ $j->nama_sesi }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 font-mono mt-0.5 flex items-center gap-1">
                                        <i class="ti ti-clock text-xs text-slate-400"></i>
                                        <span>{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }} WIB</span>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 text-xs">
                                    @if($j->koordinator)
                                        <div class="font-bold text-slate-900">{{ $j->koordinator->nama_lengkap }}</div>
                                        @php
                                            $waService = $waService ?? app(\App\Services\WhatsappMessageService::class);
                                            $koorWaLink = $waService->generatePiketWaLink($j->koordinator, $j);
                                        @endphp
                                        @if($koorWaLink)
                                            <a href="{{ $koorWaLink }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1 text-[11px] text-emerald-700 hover:text-emerald-950 font-mono mt-1 px-2 py-0.5 rounded-md bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors group"
                                               title="Klik untuk kirim pesan WhatsApp pengingat ke Koordinator">
                                                <i class="ti ti-brand-whatsapp text-emerald-600 text-sm"></i>
                                                <span class="font-semibold">{{ $j->koordinator->no_telepon }}</span>
                                                <i class="ti ti-send text-[10px] text-emerald-500"></i>
                                            </a>
                                        @else
                                            <div class="text-[11px] text-slate-400 font-mono mt-0.5 flex items-center">
                                                <i class="ti ti-brand-whatsapp text-slate-300 mr-1 text-xs"></i> {{ $j->koordinator->no_telepon ?? '-' }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-slate-400 italic">Tidak disetel</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-xs text-slate-600">
                                    <div><span class="text-slate-400">Mulai:</span> {{ \Carbon\Carbon::parse($j->tanggal_mulai_berlaku)->isoFormat('D MMM Y') }}</div>
                                    <div class="mt-0.5">
                                        <span class="text-slate-400">Selesai:</span>
                                        @if($j->tanggal_selesai_berlaku)
                                            {{ \Carbon\Carbon::parse($j->tanggal_selesai_berlaku)->isoFormat('D MMM Y') }}
                                        @else
                                            <span class="text-emerald-600 font-semibold">Tanpa batas</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    @if($j->guru->isEmpty())
                                        <span class="text-xs text-amber-600 italic">Belum ada guru ditugaskan</span>
                                    @else
                                        <ul class="space-y-1.5">
                                            @foreach($j->guru as $g)
                                                @php
                                                    $gWaLink = $waService->generatePiketWaLink($g, $j);
                                                @endphp
                                                <li class="text-xs text-slate-800">
                                                    <div class="font-bold flex items-center gap-1 text-slate-900">
                                                        <i class="ti ti-user-check text-blue-600 text-xs"></i>
                                                        <span>{{ $g->nama_lengkap }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-[11px] font-mono text-slate-500 pl-4 mt-0.5">
                                                        @if($g->nip)
                                                            <span>NIP: {{ $g->nip }}</span>
                                                        @endif
                                                        @if($gWaLink)
                                                            <a href="{{ $gWaLink }}" target="_blank" rel="noopener noreferrer"
                                                               class="inline-flex items-center gap-1 text-emerald-700 hover:text-emerald-950 px-2 py-0.5 rounded-md bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors group"
                                                               title="Kirim pesan WhatsApp pengingat ke {{ $g->nama_lengkap }}">
                                                                <i class="ti ti-brand-whatsapp text-emerald-600 text-xs"></i>
                                                                <span class="font-semibold">{{ $g->no_telepon }}</span>
                                                                <i class="ti ti-send text-[9px] text-emerald-500"></i>
                                                            </a>
                                                        @else
                                                            <span class="text-slate-400 flex items-center">
                                                                <i class="ti ti-brand-whatsapp text-slate-300 mr-1 text-xs"></i> {{ $g->no_telepon ?? '-' }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    @if($j->is_active)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1.5"></span>Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 pr-5 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('admin.jadwal-piket.show', $j) }}"
                                           class="w-8 h-8 inline-flex items-center justify-center text-blue-600 hover:bg-blue-600 hover:text-white bg-blue-50 rounded-xl transition-all shadow-2xs"
                                           title="Lihat Detail">
                                            <i class="ti ti-eye text-base"></i>
                                        </a>

                                        <a href="{{ route('admin.jadwal-piket.edit', $j) }}"
                                           class="w-8 h-8 inline-flex items-center justify-center text-amber-600 hover:bg-amber-600 hover:text-white bg-amber-50 rounded-xl transition-all shadow-2xs"
                                           title="Edit Jadwal">
                                            <i class="ti ti-edit text-base"></i>
                                        </a>

                                        <form method="POST" action="{{ route('admin.jadwal-piket.toggle', $j) }}" class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin {{ $j->is_active ? 'menonaktifkan' : 'mengaktifkan' }} jadwal ini?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-xl transition-all shadow-2xs {{ $j->is_active ? 'text-rose-600 hover:bg-rose-600 hover:text-white bg-rose-50' : 'text-emerald-600 hover:bg-emerald-600 hover:text-white bg-emerald-50' }}"
                                                    title="{{ $j->is_active ? 'Nonaktifkan Jadwal' : 'Aktifkan Jadwal' }}">
                                                <i class="ti {{ $j->is_active ? 'ti-toggle-right' : 'ti-toggle-left' }} text-lg"></i>
                                            </button>
                                        </form>

                                        <button type="button"
                                                onclick="confirmDeleteJadwal('{{ $j->id }}', '{{ addslashes($namaHari[$j->hari] ?? 'Hari '.$j->hari) }} - {{ addslashes($j->nama_sesi ?? 'Sesi') }}')"
                                                class="w-8 h-8 inline-flex items-center justify-center text-rose-600 hover:bg-rose-600 hover:text-white bg-rose-50 rounded-xl transition-all shadow-2xs cursor-pointer"
                                                title="Hapus Jadwal">
                                            <i class="ti ti-trash text-base"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                        <i class="ti ti-calendar-off text-2xl"></i>
                                    </div>
                                    <p class="text-xs font-bold text-slate-600">Belum ada jadwal piket yang ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE CARDS VIEW (Under md: 320px - 767px) --}}
            <div class="block md:hidden divide-y divide-slate-100">
                @forelse($jadwals as $j)
                    <div class="p-4 space-y-3 bg-white hover:bg-slate-50/50 transition-colors">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm font-bold text-blue-700">{{ $namaHari[$j->hari] ?? 'Hari '.$j->hari }}</span>
                                    @if($j->nama_sesi)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                            {{ $j->nama_sesi }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xs text-slate-500 font-mono flex items-center gap-1 mt-0.5">
                                    <i class="ti ti-clock text-slate-400"></i>{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }} WIB
                                </span>
                            </div>
                            <div>
                                @if($j->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($j->koordinator)
                            @php
                                $koorWaLink = $waService->generatePiketWaLink($j->koordinator, $j);
                            @endphp
                            <div class="text-xs bg-amber-50/60 p-2.5 rounded-xl border border-amber-200/80 text-amber-900">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 block mb-0.5">Koordinator Guru Piket:</span>
                                <div class="font-bold text-slate-900">{{ $j->koordinator->nama_lengkap }}</div>
                                @if($koorWaLink)
                                    <a href="{{ $koorWaLink }}" target="_blank" rel="noopener noreferrer"
                                       class="mt-1.5 min-h-[34px] inline-flex items-center gap-1.5 px-3 py-1 text-xs font-mono font-semibold text-emerald-800 bg-emerald-100/70 hover:bg-emerald-200 rounded-lg border border-emerald-300 transition-colors">
                                        <i class="ti ti-brand-whatsapp text-emerald-600 text-sm"></i>
                                        <span>{{ $j->koordinator->no_telepon }} (Ingatkan WA)</span>
                                    </a>
                                @endif
                            </div>
                        @endif

                        <div class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100 space-y-0.5">
                            <div><span class="text-slate-400 font-medium">Mulai:</span> {{ \Carbon\Carbon::parse($j->tanggal_mulai_berlaku)->isoFormat('D MMM Y') }}</div>
                            <div>
                                <span class="text-slate-400 font-medium">Selesai:</span>
                                @if($j->tanggal_selesai_berlaku)
                                    {{ \Carbon\Carbon::parse($j->tanggal_selesai_berlaku)->isoFormat('D MMM Y') }}
                                @else
                                    <span class="text-emerald-600 font-semibold">Tanpa batas</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Guru Bertugas:</span>
                            @if($j->guru->isEmpty())
                                <span class="text-xs text-amber-600 italic">Belum ada guru ditugaskan</span>
                            @else
                                <ul class="space-y-1.5">
                                    @foreach($j->guru as $g)
                                        @php
                                            $gWaLink = $waService->generatePiketWaLink($g, $j);
                                        @endphp
                                        <li class="text-xs text-slate-800 bg-slate-50 p-2 rounded-xl border border-slate-100 flex items-center justify-between gap-2">
                                            <div>
                                                <div class="font-bold text-slate-900 flex items-center gap-1">
                                                    <i class="ti ti-user-check text-blue-600 text-xs"></i>
                                                    <span>{{ $g->nama_lengkap }}</span>
                                                </div>
                                                <div class="text-[10px] font-mono text-slate-400 pl-4">
                                                    NIP: {{ $g->nip ?? '-' }}
                                                </div>
                                            </div>
                                            <div>
                                                @if($gWaLink)
                                                    <a href="{{ $gWaLink }}" target="_blank" rel="noopener noreferrer"
                                                       class="px-2.5 py-1 text-xs font-semibold text-emerald-800 bg-emerald-100/80 rounded-lg border border-emerald-300 inline-flex items-center gap-1">
                                                        <i class="ti ti-brand-whatsapp text-emerald-600 text-sm"></i>
                                                        <span class="font-mono text-[11px]">{{ $g->no_telepon }}</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2 flex-wrap">
                            <a href="{{ route('admin.jadwal-piket.show', $j) }}"
                               class="min-h-[36px] px-3 py-1.5 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl flex items-center justify-center gap-1.5 transition-colors">
                                <i class="ti ti-eye text-blue-600"></i> Detail
                            </a>
                            <a href="{{ route('admin.jadwal-piket.edit', $j) }}"
                               class="min-h-[36px] px-3 py-1.5 text-xs font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl flex items-center justify-center gap-1.5 transition-colors">
                                <i class="ti ti-edit text-amber-600"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('admin.jadwal-piket.toggle', $j) }}" class="inline"
                                  onsubmit="return confirm('Apakah Anda yakin ingin {{ $j->is_active ? 'menonaktifkan' : 'mengaktifkan' }} jadwal ini?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="min-h-[36px] px-3 py-1.5 text-xs font-bold rounded-xl flex items-center justify-center gap-1.5 border transition-colors {{ $j->is_active ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                                    <i class="ti {{ $j->is_active ? 'ti-toggle-right' : 'ti-toggle-left' }}"></i>
                                    {{ $j->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            <button type="button"
                                    onclick="confirmDeleteJadwal('{{ $j->id }}', '{{ addslashes($namaHari[$j->hari] ?? 'Hari '.$j->hari) }} - {{ addslashes($j->nama_sesi ?? 'Sesi') }}')"
                                    class="min-h-[36px] px-3 py-1.5 text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
                                    title="Hapus Jadwal">
                                <i class="ti ti-trash text-rose-600"></i> Hapus
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400">
                        <i class="ti ti-calendar-off text-3xl mb-2 text-slate-300 block"></i>
                        <p class="text-xs font-bold text-slate-600">Belum ada jadwal piket yang ditemukan.</p>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- PAGINATION --}}
        @if($jadwals->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $jadwals->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Form Hapus Jadwal Piket Hidden --}}
<form id="deleteJadwalForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDeleteJadwal(id, label) {
    Swal.fire({
        title: 'Hapus Jadwal Piket?',
        text: `Apakah Anda yakin ingin menghapus jadwal piket "${label}"? Seluruh penugasan guru pada sesi jadwal ini akan ikut terhapus.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('deleteJadwalForm');
            form.action = '{{ url('admin/jadwal-piket') }}/' + id;
            form.submit();
        }
    });
}
</script>
@endsection
