@extends('siswa.layouts.app')
@section('title', 'Riwayat Pengajuan')
@section('page-title', 'Riwayat Pengajuan Dispensasi')
@section('content')

<div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
    {{-- Header + Filter --}}
    <div class="p-4 sm:p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/60">
        <div>
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="ti ti-history text-slate-400 text-base"></i>Riwayat Pengajuan
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Pantau seluruh riwayat izin dispensasi kamu di sini.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" class="flex-1 sm:flex-none">
                <select name="status" onchange="this.form.submit()" class="w-full sm:w-auto h-10 px-3 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 transition-all">
                    <option value="">Semua Status</option>
                    <option value="terlambat"  {{ request('status') == 'terlambat'  ? 'selected' : '' }}>Terlambat ({{ $totalTerlambatSiswa ?? 0 }})</option>
                    <option value="menunggu"   {{ request('status') == 'menunggu'   ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui"  {{ request('status') == 'disetujui'  ? 'selected' : '' }}>Disetujui</option>
                    <option value="keluar"     {{ request('status') == 'keluar'     ? 'selected' : '' }}>Sedang Keluar</option>
                    <option value="selesai"    {{ request('status') == 'selesai'    ? 'selected' : '' }}>Selesai</option>
                    <option value="ditolak"    {{ request('status') == 'ditolak'    ? 'selected' : '' }}>Ditolak</option>
                    <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    <option value="kadaluarsa" {{ request('status') == 'kadaluarsa' ? 'selected' : '' }}>Kadaluarsa</option>
                </select>
            </form>
            <a href="{{ route('siswa.pengajuan.create') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs flex-shrink-0 transition-colors">
                <i class="ti ti-plus mr-1.5 text-sm"></i>Buat
            </a>
        </div>
    </div>

    {{-- QUICK FILTER TABS: SEMUA vs TERLAMBAT --}}
    <div class="px-4 py-2.5 sm:px-5 border-b border-slate-200 bg-slate-50/40 flex items-center gap-2 overflow-x-auto">
        
        <a href="{{ route('siswa.pengajuan.index', ['status' => 'terlambat']) }}"
           class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 {{ request('status') === 'terlambat' ? 'bg-rose-600 text-white shadow-xs' : (($totalTerlambatSiswa ?? 0) > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100/70 font-bold' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50') }}">
            <i class="ti ti-alert-triangle text-xs"></i>
            <span>Pengajuan Terlambat ({{ $totalTerlambatSiswa ?? 0 }})</span>
        </a>
    </div>

    {{-- ============ BANNER EDUKASI / APRESIASI KEDISIPLINAN ============ --}}
    @if(($totalTerlambatSiswa ?? 0) > 0)
        {{-- Banner Catatan Evaluasi Kedisiplinan (Terlambat >= 1 Kali) --}}
        <div class="m-4 sm:m-5 p-4 sm:p-5 rounded-2xl bg-amber-50/90 border border-amber-200/90 shadow-xs text-amber-950">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 border border-amber-200 font-bold shadow-2xs">
                    <i class="ti ti-alert-triangle text-xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <h4 class="font-bold text-sm sm:text-base text-amber-950">Catatan Evaluasi Kedisiplinan</h4>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-200/80 text-amber-900 border border-amber-300">
                            Tercatat Terlambat: {{ $totalTerlambatSiswa }} Kali
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-amber-900/90 leading-relaxed mb-2.5">
                        Demi menjaga ketertiban proses belajar mengajar dan kedisiplinan sekolah, mohon perhatikan ketentuan penting berikut:
                    </p>
                    <div class="space-y-1.5 text-xs text-amber-950">
                        <div class="flex items-start gap-2">
                            <i class="ti ti-clock text-amber-700 mt-0.5 text-sm shrink-0"></i>
                            <span><strong>Perkirakan Waktu dengan Matang:</strong> Saat mengajukan izin, perhitungkan durasi perjalanan dan urusan agar kembali sebelum batas waktu izin berakhir.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="ti ti-qrcode text-amber-700 mt-0.5 text-sm shrink-0"></i>
                            <span><strong>Wajib Scan Kembali di Pos Satpam:</strong> Selalu lakukan scan QR kembali di pos satpam begitu tiba di sekolah agar status dispensasi tidak ditutup sebagai kelalaian.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="ti ti-camera text-amber-700 mt-0.5 text-sm shrink-0"></i>
                            <span><strong>Unggah Foto Bukti Kegiatan:</strong> Lampirkan foto bukti kegiatan di lokasi yang dituju sebagai bukti pertanggungjawaban izin keluar kamu ya.</span>
                        </div>
                    </div>
                    <p class="text-xs font-semibold text-amber-800 mt-3 pt-2.5 border-t border-amber-200/70 flex items-center gap-1.5">
                        <i class="ti ti-shield-check text-amber-600 text-sm"></i>
                        Yuk lebih perhatikan waktu saat izin agar tidak mengulangi keterlambatan ini ya!
                    </p>
                </div>
            </div>
        </div>
    @else
        {{-- Banner Apresiasi Kedisiplinan (0 Kali Terlambat) --}}
        <div class="m-4 sm:m-5 p-4 sm:p-5 rounded-2xl bg-emerald-50/90 border border-emerald-200/90 shadow-xs text-emerald-950">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-200 font-bold shadow-2xs">
                    <i class="ti ti-award text-xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <h4 class="font-bold text-sm sm:text-base text-emerald-950">
                            <span class="sr-only">Luar Biasa! Kedisiplinan Sangat Baik - </span>Keren Banget! Rekor Disiplin Kamu Bersih
                        </h4>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-200/80 text-emerald-900 border border-emerald-300">
                            0 Keterlambatan
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-emerald-800 leading-relaxed">
                        Keren, kamu selalu tepat waktu dan taat aturan izin. Pertahankan kedisiplinan dan tanggung jawab Anda, disiplin itu keren!
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- MOBILE: Kartu Riwayat (Dioptimalkan untuk HP) --}}
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($pengajuan as $p)
            @php
                $badge = $p->status_badge;
                $isAnggota = auth()->user()->siswa && auth()->user()->siswa->id !== $p->siswa_id;
            @endphp
            <div class="p-4 hover:bg-slate-50/70 transition-colors">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1.5 sm:gap-2 mb-2">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <p class="font-mono font-bold text-slate-900 text-xs sm:text-sm tracking-tight break-all">{{ $p->nomor_surat }}</p>
                            @if($p->isRombongan())
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                    <i class="ti ti-users text-[10px]"></i> Rombongan
                                </span>
                                @if($isAnggota)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-700 border border-purple-200">
                                        Anggota Rombongan
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                    <i class="ti ti-user text-[10px]"></i> Individu
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="self-start sm:self-auto flex-shrink-0">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badge['class'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                            {{ $badge['text'] }}
                        </span>
                    </div>
                </div>
                <p class="text-xs text-slate-500">
                    {{ $p->tanggal_izin ? $p->tanggal_izin->format('d/m/Y') : $p->created_at->format('d/m/Y') }} • 
                    <span class="capitalize">{{ str_replace('_', ' ', $p->kategori) }}</span>
                </p>
                <p class="text-xs text-slate-600 mt-1 truncate">
                    <i class="ti ti-clock mr-1 text-slate-400 text-xs inline"></i>
                    {{ \App\Helpers\TimeHelper::getWaktuAktual($p->jam_keluar, $p->created_at?->dayOfWeek) }} – 
                    @if($p->isSampaiPulang())
                        <span class="text-emerald-700 font-semibold">Sampai Pulang</span>
                    @else
                        {{ \App\Helpers\TimeHelper::getWaktuAktual($p->jam_kembali, $p->created_at?->dayOfWeek) }}
                    @endif
                    • {{ $p->tujuan }}
                </p>
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                    <a href="{{ route('siswa.pengajuan.show', $p) }}" class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                        <i class="ti ti-eye mr-1.5 text-sm"></i>Detail
                    </a>
                </div>
            </div>
        @empty
            <div class="p-10 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-3"><i class="ti ti-inbox text-3xl"></i></div>
                <p class="text-slate-800 font-bold text-sm">
                    @if(request('status') === 'terlambat')
                        Rekor Bersih, Gak Ada Keterlambatan!
                    @else
                        Belum ada riwayat izin nih
                    @endif
                </p>
                @if(request('status') === 'terlambat')
                    <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">Seluruh pengajuan izin kamu diselesaikan dengan tertib & tepat waktu.</p>
                @endif
            </div>
        @endforelse
    </div>

    {{-- DESKTOP: Tabel --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="p-4 text-left font-semibold">No. Surat</th>
                    <th class="p-4 text-left font-semibold">Tanggal Pelaksanaan</th>
                    <th class="p-4 text-left font-semibold">Kategori</th>
                    <th class="p-4 text-left font-semibold">Tujuan</th>
                    <th class="p-4 text-left font-semibold">Waktu</th>
                    <th class="p-4 text-left font-semibold">Status</th>
                    <th class="p-4 text-center font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pengajuan as $p)
                    @php
                        $badge = $p->status_badge;
                        $isAnggota = auth()->user()->siswa && auth()->user()->siswa->id !== $p->siswa_id;
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="p-4 text-xs">
                            <span class="font-mono font-bold text-slate-900">{{ $p->nomor_surat }}</span>
                            @if($p->isRombongan())
                                <div class="mt-1 flex items-center gap-1">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        <i class="ti ti-users text-[10px]"></i> Rombongan
                                    </span>
                                    @if($isAnggota)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-700 border border-purple-200">
                                            Anggota Rombongan
                                        </span>
                                    @endif
                                </div>
                            @else
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        <i class="ti ti-user text-[10px]"></i> Individu
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="p-4 text-slate-600 text-xs">
                            {{ $p->tanggal_izin ? $p->tanggal_izin->format('d/m/Y') : $p->created_at->format('d/m/Y') }}
                            @if($p->tanggal_izin && $p->tanggal_izin->isFuture())
                                <span class="block text-[10px] text-amber-600 font-bold">Mendatang</span>
                            @endif
                        </td>
                        <td class="p-4 capitalize text-slate-700 text-xs">{{ str_replace('_', ' ', $p->kategori) }}</td>
                        <td class="p-4 text-slate-700 text-xs">{{ $p->tujuan }}</td>
                        <td class="p-4 text-slate-600 text-xs">
                            {{ \App\Helpers\TimeHelper::getWaktuAktual($p->jam_keluar, $p->created_at?->dayOfWeek) }} – 
                            @if($p->isSampaiPulang())
                                <span class="text-emerald-700 font-bold">Sampai Pulang</span>
                            @else
                                {{ \App\Helpers\TimeHelper::getWaktuAktual($p->jam_kembali, $p->created_at?->dayOfWeek) }}
                            @endif
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badge['class'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                {{ $badge['text'] }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <a href="{{ route('siswa.pengajuan.show', $p) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                                <i class="ti ti-eye mr-1.5 text-sm"></i>Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center">
                            <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-3"><i class="ti ti-inbox text-3xl"></i></div>
                            <p class="text-slate-800 font-bold text-sm">
                                @if(request('status') === 'terlambat')
                                    Rekor Bersih, Gak Ada Keterlambatan!
                                @else
                                    Belum ada riwayat izin nih
                                @endif
                            </p>
                            @if(request('status') === 'terlambat')
                                <p class="text-xs text-slate-500 mt-1">Seluruh pengajuan izin kamu diselesaikan dengan tertib & tepat waktu.</p>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pengajuan->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">{{ $pengajuan->links() }}</div>
    @endif
</div>
@endsection
