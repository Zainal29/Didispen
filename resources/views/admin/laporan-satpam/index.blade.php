@extends('admin.layouts.app')
@section('title', 'Laporan Temuan Satpam')
@section('page-title', 'Laporan Temuan Pos Satpam')

@section('content')
@include('components.alert')

<div class="space-y-6" x-data="adminLaporanSatpam()">

    {{-- HEADER --}}
    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/70 flex items-center justify-center shrink-0">
                    <i class="ti ti-shield-exclamation text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Laporan Temuan Gerbang Satpam</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pantau dan tindak lanjuti temuan satpam: siswa rombongan tidak lengkap kembali, terobos gerbang, atau pelanggaran izin.</p>
                </div>
            </div>
        </div>

        {{-- KARTU STATISTIK --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 border-t border-slate-100">
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/80">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Temuan</p>
                <p class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-amber-50/50 p-3 rounded-xl border border-amber-200/80">
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Menunggu Tindak Lanjut</p>
                <p class="text-xl font-extrabold text-amber-600 mt-0.5">{{ $stats['menunggu'] }}</p>
            </div>
            <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-200/80">
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-600">Sedang Ditangani</p>
                <p class="text-xl font-extrabold text-blue-600 mt-0.5">{{ $stats['ditindaklanjuti'] }}</p>
            </div>
            <div class="bg-emerald-50/50 p-3 rounded-xl border border-emerald-200/80">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Selesai / Sanksi</p>
                <p class="text-xl font-extrabold text-emerald-600 mt-0.5">{{ $stats['selesai'] }}</p>
            </div>
        </div>

        {{-- FILTER BAR --}}
        <form method="GET" class="flex flex-wrap gap-2.5 pt-2 border-t border-slate-100">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="ti ti-search text-sm"></i>
                </span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama siswa, NIS, atau no. surat..."
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-800 transition">
            </div>

            <select name="status" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 outline-none focus:bg-white focus:border-blue-500">
                <option value="">Semua Status</option>
                <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu ({{ $stats['menunggu'] }})</option>
                <option value="ditindaklanjuti" {{ request('status') === 'ditindaklanjuti' ? 'selected' : '' }}>Sedang Ditangani</option>
                <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
            </select>

            <select name="kategori" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 outline-none focus:bg-white focus:border-blue-500">
                <option value="">Semua Kategori</option>
                <option value="rombongan_tidak_kembali" {{ request('kategori') === 'rombongan_tidak_kembali' ? 'selected' : '' }}>Rombongan: Tidak Kembali</option>
                <option value="terobos_gerbang" {{ request('kategori') === 'terobos_gerbang' ? 'selected' : '' }}>Terobos Gerbang</option>
                <option value="terlambat_kembali" {{ request('kategori') === 'terlambat_kembali' ? 'selected' : '' }}>Terlambat Tidak Wajar</option>
                <option value="pelanggaran_lain" {{ request('kategori') === 'pelanggaran_lain' ? 'selected' : '' }}>Pelanggaran Lain</option>
            </select>

            <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                   class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 outline-none focus:bg-white focus:border-blue-500">

            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                Filter
            </button>
            @if(request()->anyFilled(['q', 'status', 'kategori', 'tanggal']))
                <a href="{{ route('admin.laporan-satpam.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- TABEL LAPORAN --}}
    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[10px] font-bold">
                        <th class="py-3 px-4">Waktu & Pelapor</th>
                        <th class="py-3 px-4">Siswa Terkait</th>
                        <th class="py-3 px-4">Kategori & No. Surat</th>
                        <th class="py-3 px-4">Kronologi Satpam</th>
                        <th class="py-3 px-4">Status & Respon</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($laporan as $lap)
                        @php
                            $statusBadge = match($lap->status) {
                                'menunggu'        => 'bg-amber-50 text-amber-700 border-amber-200',
                                'ditindaklanjuti' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'selesai'        => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                default           => 'bg-slate-50 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            {{-- WAKTU & PELAPOR --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <p class="font-bold text-slate-900">{{ $lap->created_at->format('d/m/Y') }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $lap->created_at->format('H:i') }} WIB</p>
                                <span class="inline-flex items-center gap-1 mt-1 text-[10px] text-slate-500">
                                    <i class="ti ti-shield text-slate-400"></i> {{ $lap->satpam?->name ?? 'Satpam' }}
                                </span>
                            </td>

                            {{-- SISWA --}}
                            <td class="py-3.5 px-4">
                                <p class="font-bold text-slate-900">{{ $lap->siswa?->nama_lengkap ?? 'Siswa' }}</p>
                                <p class="text-[11px] text-slate-500">{{ $lap->siswa?->kelas?->nama_kelas ?? '-' }} • NIS: {{ $lap->siswa?->nis_nip ?? $lap->siswa?->user?->nis_nip ?? '-' }}</p>
                            </td>

                            {{-- KATEGORI --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-semibold text-[10px]">
                                    {{ $lap->kategori_label }}
                                </span>
                                @if($lap->dispensasi)
                                    <p class="text-[10px] font-mono text-blue-600 font-bold mt-1">#{{ $lap->dispensasi->nomor_surat }}</p>
                                @endif
                            </td>

                            {{-- KRONOLOGI --}}
                            <td class="py-3.5 px-4 max-w-xs">
                                <p class="text-slate-700 line-clamp-3 leading-relaxed">{{ $lap->keterangan }}</p>
                            </td>

                            {{-- STATUS --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge }}">
                                    {{ $lap->status_label }}
                                </span>
                                @if($lap->catatan_tindak_lanjut)
                                    <p class="text-[10px] text-slate-500 mt-1 max-w-[160px] truncate" title="{{ $lap->catatan_tindak_lanjut }}">
                                        Oleh: {{ $lap->petugasTindakLanjut?->name ?? 'Admin' }}
                                    </p>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <button type="button"
                                        @click="openTindakLanjutModal({{ $lap->id }}, '{{ addslashes($lap->siswa?->nama_lengkap ?? '') }}', '{{ $lap->status }}', '{{ addslashes($lap->keterangan) }}', '{{ addslashes($lap->catatan_tindak_lanjut ?? '') }}')"
                                        class="px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 font-bold text-xs transition-colors shadow-2xs">
                                    <i class="ti ti-edit mr-1"></i> Tindak Lanjut
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i class="ti ti-inbox text-3xl mb-2 block"></i>
                                Belum ada laporan temuan gerbang yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($laporan->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $laporan->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL TINDAK LANJUT --}}
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="min-h-screen px-4 text-center flex items-center justify-center p-0">
            <div x-show="modalOpen" @click="modalOpen = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

            <div x-show="modalOpen"
                 class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-lg p-5 sm:p-6 border border-slate-100 my-8">

                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="ti ti-clipboard-check text-blue-600 text-lg"></i>
                        Tindak Lanjut Laporan Satpam
                    </h3>
                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                        <i class="ti ti-x text-base"></i>
                    </button>
                </div>

                <form :action="'{{ url('admin/laporan-satpam') }}/' + currentId + '/tindak-lanjuti'" method="POST" class="space-y-4">
                    @csrf

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Siswa Terkait</p>
                        <p class="text-xs font-bold text-slate-900 mt-0.5" x-text="siswaNama"></p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mt-2">Kronologi Satpam</p>
                        <p class="text-xs text-slate-700 mt-0.5 whitespace-pre-line" x-text="keteranganSatpam"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Tindak Lanjut</label>
                        <select name="status" x-model="statusForm" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white">
                            <option value="ditindaklanjuti">Sedang Ditindaklanjuti (Diproses BK / Dipanggil)</option>
                            <option value="selesai">Selesai (Sudah Dikonfirmasi / Diberikan Sanksi)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tindak Lanjut / Respon BK & Admin</label>
                        <textarea name="catatan_tindak_lanjut"
                                  rows="3"
                                  x-model="catatanForm"
                                  required
                                  placeholder="Contoh: Siswa sudah dipanggil ke ruang BK, orang tua telah dihubungi dan mengonfirmasi siswa sakit/izin..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"></textarea>
                    </div>

                    <div class="pt-3 flex gap-2">
                        <button type="button" @click="modalOpen = false" class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs">
                            Simpan Tindak Lanjut
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function adminLaporanSatpam() {
    return {
        modalOpen: false,
        currentId: '',
        siswaNama: '',
        statusForm: 'ditindaklanjuti',
        keteranganSatpam: '',
        catatanForm: '',
        openTindakLanjutModal(id, nama, status, ket, catatan) {
            this.currentId = id;
            this.siswaNama = nama;
            this.statusForm = (status === 'selesai') ? 'selesai' : 'ditindaklanjuti';
            this.keteranganSatpam = ket;
            this.catatanForm = catatan || '';
            this.modalOpen = true;
        }
    };
}
</script>
@endsection
