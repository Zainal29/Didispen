@extends('admin.layouts.app')

@section('title', 'Detail Jadwal Guru Piket')
@section('page-title', 'Detail Jadwal Guru Piket')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header Banner --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0">
                <i class="ti ti-calendar-event text-2xl"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 truncate">Detail Jadwal Piket</h3>
                <p class="text-xs text-slate-500 mt-0.5">Konfigurasi sesi jadwal, masa berlaku, dan daftar guru bertugas.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-center shrink-0 flex-wrap">
            <a href="{{ route('admin.jadwal-piket.edit', $jadwal) }}"
               class="uiverse-btn-tactile inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white transition-colors shadow-2xs">
                <i class="ti ti-edit text-base"></i>
                <span>Edit Jadwal</span>
            </a>
            <button type="button"
                    onclick="confirmDeleteJadwal()"
                    class="uiverse-btn-tactile inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white transition-colors shadow-2xs cursor-pointer">
                <i class="ti ti-trash text-base"></i>
                <span>Hapus Jadwal</span>
            </button>
            <a href="{{ route('admin.jadwal-piket.index') }}"
               class="uiverse-btn-tactile inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 transition-colors">
                <i class="ti ti-arrow-left text-base"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    @include('components.alert')

    @php
        $namaHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    @endphp

    {{-- Main Info Card --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="text-2xl font-black text-blue-600 tracking-tight">{{ $namaHari[$jadwal->hari] ?? 'Hari '.$jadwal->hari }}</span>
                    @if($jadwal->nama_sesi)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/70">
                            {{ $jadwal->nama_sesi }}
                        </span>
                    @endif
                    @if($jadwal->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1.5"></span>Nonaktif
                        </span>
                    @endif
                </div>
                <p class="text-xs sm:text-sm font-mono text-slate-500 mt-1.5 flex items-center gap-1.5">
                    <i class="ti ti-clock text-blue-600"></i>
                    {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                </p>
            </div>

            {{-- Toggle Button --}}
            <form method="POST" action="{{ route('admin.jadwal-piket.toggle', $jadwal) }}"
                  onsubmit="return confirm('Apakah Anda yakin ingin mengubah status aktif jadwal ini?');">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="uiverse-btn-tactile px-4 py-2 rounded-xl text-xs font-bold transition-colors border {{ $jadwal->is_active ? 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}">
                    <i class="ti {{ $jadwal->is_active ? 'ti-ban mr-1' : 'ti-check mr-1' }}"></i>
                    {{ $jadwal->is_active ? 'Nonaktifkan Jadwal' : 'Aktifkan Jadwal' }}
                </button>
            </form>
        </div>

        <div class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-3 gap-6 bg-slate-50/50">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Koordinator Guru Piket</p>
                @if($jadwal->koordinator)
                    <p class="text-sm font-bold text-slate-900 mt-1">{{ $jadwal->koordinator->nama_lengkap }}</p>
                    @php
                        $waService = $waService ?? app(\App\Services\WhatsappMessageService::class);
                        $koorWaLink = $waService->generatePiketWaLink($jadwal->koordinator, $jadwal);
                    @endphp
                    @if($koorWaLink)
                        <a href="{{ $koorWaLink }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 text-xs text-emerald-700 hover:text-emerald-950 font-mono mt-1.5 px-2.5 py-1 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors shadow-2xs group"
                           title="Kirim pesan WhatsApp pengingat ke Koordinator">
                            <i class="ti ti-brand-whatsapp text-emerald-600 text-sm group-hover:scale-110 transition-transform"></i>
                            <span class="font-bold">{{ $jadwal->koordinator->no_telepon }}</span>
                            <span class="text-[10px] bg-emerald-200/70 text-emerald-800 px-1.5 py-0.5 rounded-md font-sans font-semibold">Ingatkan WA</span>
                        </a>
                    @else
                        <p class="text-xs text-slate-400 font-mono mt-1 flex items-center gap-1">
                            <i class="ti ti-brand-whatsapp text-slate-300"></i> {{ $jadwal->koordinator->no_telepon ?? '-' }}
                        </p>
                    @endif
                @else
                    <p class="text-xs text-slate-400 italic mt-1">Tidak disetel khusus</p>
                @endif
            </div>

            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tanggal Mulai Berlaku</p>
                <p class="text-sm font-bold text-slate-900 mt-1 flex items-center gap-1.5">
                    <i class="ti ti-calendar text-slate-400"></i>
                    {{ \Carbon\Carbon::parse($jadwal->tanggal_mulai_berlaku)->isoFormat('D MMMM Y') }}
                </p>
            </div>

            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tanggal Selesai Berlaku</p>
                <p class="text-sm font-bold text-slate-900 mt-1 flex items-center gap-1.5">
                    @if($jadwal->tanggal_selesai_berlaku)
                        <i class="ti ti-calendar text-slate-400"></i>
                        {{ \Carbon\Carbon::parse($jadwal->tanggal_selesai_berlaku)->isoFormat('D MMMM Y') }}
                    @else
                        <span class="text-emerald-600 font-bold flex items-center gap-1">
                            <i class="ti ti-infinity"></i> Tanpa batas akhir
                        </span>
                    @endif
                </p>
            </div>
        </div>

        {{-- Helper Penjelasan Jadwal Berulang --}}
        <div class="px-5 sm:px-6 py-3.5 bg-blue-50/70 border-t border-blue-100 text-xs text-blue-900 flex items-start gap-2.5">
            <i class="ti ti-info-circle text-blue-600 text-base mt-0.5 shrink-0"></i>
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
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6">
        <h4 class="text-sm sm:text-base font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
            <i class="ti ti-users text-blue-600"></i> Guru Piket Resmi ({{ $jadwal->guru->count() }} Guru)
        </h4>

        @if($jadwal->guru->isEmpty())
            <div class="py-8 text-center border border-dashed border-slate-200 rounded-xl">
                <i class="ti ti-user-x text-3xl text-slate-300 mb-1"></i>
                <p class="text-xs text-amber-600 font-medium">Belum ada guru piket resmi yang ditugaskan pada jadwal ini.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($jadwal->guru as $index => $g)
                    @php
                        $waService = $waService ?? app(\App\Services\WhatsappMessageService::class);
                        $gWaLink = $waService->generatePiketWaLink($g, $jadwal);
                    @endphp
                    <div class="flex items-center p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 font-bold flex items-center justify-center mr-3 shrink-0 text-xs">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-bold text-slate-900 truncate">{{ $g->nama_lengkap }}</p>
                            <div class="flex flex-wrap items-center gap-x-2 text-xs font-mono mt-1">
                                <span class="text-slate-400">NIP: {{ $g->nip ?? '-' }}</span>
                                @if($gWaLink)
                                    <a href="{{ $gWaLink }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1 text-emerald-700 hover:text-emerald-950 bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded-lg border border-emerald-200 transition-colors text-[11px] group"
                                       title="Kirim pesan WhatsApp pengingat ke {{ $g->nama_lengkap }}">
                                        <i class="ti ti-brand-whatsapp text-emerald-600"></i>
                                        <span class="font-bold">{{ $g->no_telepon }}</span>
                                        <i class="ti ti-send text-[10px] text-emerald-500 opacity-70 group-hover:opacity-100"></i>
                                    </a>
                                @else
                                    <span class="inline-flex items-center text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded text-[11px]">
                                        <i class="ti ti-brand-whatsapp mr-1 text-slate-300"></i> {{ $g->no_telepon ?? 'No HP belum ada' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $g->status_aktif ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                            {{ $g->status_aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Riwayat Pertukaran / Penggantian Jadwal --}}
    @if($jadwal->pertukaranJadwalPikets->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6">
            <h4 class="text-sm sm:text-base font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                <i class="ti ti-arrows-exchange text-indigo-600"></i> Riwayat Penggantian Guru Piket ({{ $jadwal->pertukaranJadwalPikets->count() }})
            </h4>

            <div class="overflow-x-auto min-w-0 w-full touch-pan-x">
                <table class="w-full text-left text-xs text-slate-600 border-collapse">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-100">
                        <tr>
                            <th class="py-2.5 px-3">Tanggal</th>
                            <th class="py-2.5 px-3">Guru Asal</th>
                            <th class="py-2.5 px-3">Guru Pengganti</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-center">Aktif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($jadwal->pertukaranJadwalPikets as $p)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-2.5 px-3 font-medium text-slate-900 font-mono">
                                    {{ \Carbon\Carbon::parse($p->tanggal)->isoFormat('D MMM Y') }}
                                </td>
                                <td class="py-2.5 px-3 font-semibold text-slate-800">
                                    {{ $p->guruAsal?->nama_lengkap ?? '-' }}
                                </td>
                                <td class="py-2.5 px-3 font-semibold text-slate-800">
                                    {{ $p->guruPengganti?->nama_lengkap ?? '-' }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $p->status === 'disetujui' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                           ($p->status === 'ditolak' ? 'bg-rose-50 text-rose-700 border border-rose-200' :
                                           ($p->status === 'dibatalkan' ? 'bg-slate-100 text-slate-600 border border-slate-200' : 'bg-amber-50 text-amber-700 border border-amber-200')) }}">
                                        {{ $p->status }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    @if($p->is_active)
                                        <i class="ti ti-circle-check text-emerald-600 text-base"></i>
                                    @else
                                        <span class="text-slate-300 font-bold">-</span>
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

{{-- Form Hapus Jadwal Piket Hidden --}}
<form id="deleteJadwalForm" action="{{ route('admin.jadwal-piket.destroy', $jadwal) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDeleteJadwal() {
    Swal.fire({
        title: 'Hapus Jadwal Piket?',
        text: 'Apakah Anda yakin ingin menghapus jadwal piket ini? Seluruh penugasan guru pada sesi ini akan dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteJadwalForm').submit();
        }
    });
}
</script>
@endsection
