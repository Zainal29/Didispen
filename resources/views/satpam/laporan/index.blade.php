@extends('satpam.layouts.app')

@section('title', 'Laporan Temuan Gerbang')
@section('page-title', 'Laporan Temuan Gerbang')

@section('content')
<div class="space-y-5 max-w-4xl mx-auto" x-data="laporanSatpam()">

    {{-- HEADER & TOMBOL BUAT LAPORAN --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="ti ti-shield-exclamation text-rose-600 text-xl"></i>
                Laporan & Temuan Gerbang
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Catat siswa rombongan yang tidak lengkap kembali, siswa terobos gerbang, atau pelanggaran di pos keamanan.
            </p>
        </div>
        <button type="button"
                @click="openModal()"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm shadow-rose-600/30 transition-all active:scale-95 shrink-0">
            <i class="ti ti-plus mr-1.5 text-base"></i> Buat Laporan Baru
        </button>
    </div>

    {{-- STATISTIK KARTU MINI --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Laporan</p>
            <p class="text-xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-rose-500">Hari Ini</p>
            <p class="text-xl font-extrabold text-rose-600 mt-1">{{ $stats['hari_ini'] }}</p>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-amber-200 bg-amber-50/30 shadow-2xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Menunggu Respon</p>
            <p class="text-xl font-extrabold text-amber-600 mt-1">{{ $stats['menunggu'] }}</p>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-emerald-200 bg-emerald-50/30 shadow-2xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Ditangani</p>
            <p class="text-xl font-extrabold text-emerald-600 mt-1">{{ $stats['ditindaklanjuti'] }}</p>
        </div>
    </div>

    {{-- KARTU CEPAT: SISWA YANG SEDANG DI LUAR HARI INI --}}
    @if($siswaKeluarHariIni->count() > 0)
        <div class="bg-gradient-to-r from-rose-50 to-orange-50 border border-rose-200/80 rounded-2xl p-4 shadow-2xs">
            <div class="flex items-center justify-between mb-2.5">
                <p class="text-xs font-bold text-rose-900 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-rose-600 animate-ping"></span>
                    Siswa Sedang Di Luar Gerbang Hari Ini ({{ $siswaKeluarHariIni->count() }} Surat)
                </p>
                <span class="text-[11px] text-rose-700 font-medium">Klik untuk lapor cepat</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($siswaKeluarHariIni as $disp)
                    @php
                        $isRombongan = $disp->isRombongan();
                        $total = $isRombongan ? ($disp->anggotaSiswa->count() + 1) : 1;
                    @endphp
                    <button type="button"
                            @click="selectDispensasiFast({{ $disp->id }}, {{ $disp->siswa?->id ?? 0 }}, '{{ addslashes($disp->siswa?->nama_lengkap ?? '') }}', '{{ $disp->nomor_surat }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-rose-200 text-slate-800 text-xs font-semibold hover:border-rose-400 hover:bg-rose-50/80 transition-all active:scale-95 shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <span>{{ $disp->siswa?->nama_lengkap }}</span>
                        @if($isRombongan)
                            <span class="px-1.5 py-0.2 rounded-md bg-rose-100 text-rose-700 text-[10px] font-bold">Rombongan ({{ $total }})</span>
                        @endif
                        <span class="text-[10px] text-slate-400 font-mono">#{{ $disp->nomor_surat }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- FILTER & PENCARIAN --}}
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row gap-2.5 sm:items-center sm:justify-between">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
            <a href="{{ route('satpam.laporan.index') }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors {{ !request('status') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua
            </a>
            <a href="{{ route('satpam.laporan.index', ['status' => 'menunggu']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors {{ request('status') === 'menunggu' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                Menunggu ({{ $stats['menunggu'] }})
            </a>
            <a href="{{ route('satpam.laporan.index', ['status' => 'ditindaklanjuti']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors {{ request('status') === 'ditindaklanjuti' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                Ditindaklanjuti
            </a>
            <a href="{{ route('satpam.laporan.index', ['status' => 'selesai']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors {{ request('status') === 'selesai' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                Selesai
            </a>
        </div>

        <form method="GET" action="{{ route('satpam.laporan.index') }}" class="relative sm:w-64">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="text"
                   name="q"
                   value="{{ request('q') }}"
                   placeholder="Cari siswa / no surat..."
                   class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-rose-600 focus:ring-1 focus:ring-rose-600 bg-slate-50">
            <i class="ti ti-search absolute left-3 top-2.5 text-slate-400 text-sm"></i>
        </form>
    </div>

    {{-- DAFTAR LAPORAN --}}
    <div class="space-y-3">
        @forelse($laporan as $lap)
            @php
                $statusColor = match($lap->status) {
                    'menunggu'        => 'bg-amber-50 text-amber-700 border-amber-200',
                    'ditindaklanjuti' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'selesai'        => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    default           => 'bg-slate-50 text-slate-700 border-slate-200',
                };
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusColor }}">
                                {{ $lap->status_label }}
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-semibold">
                                {{ $lap->kategori_label }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-medium">
                                {{ $lap->created_at->translatedFormat('d M Y, H:i') }} WIB
                            </span>
                        </div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 mt-1">
                            {{ $lap->siswa?->nama_lengkap ?? 'Siswa Tidak Diketahui' }}
                            <span class="text-xs font-normal text-slate-500">
                                ({{ $lap->siswa?->kelas?->nama_kelas ?? 'Kelas -' }} • NIS: {{ $lap->siswa?->nis_nip ?? $lap->siswa?->user?->nis_nip ?? '-' }})
                            </span>
                        </h3>
                    </div>

                    @if($lap->dispensasi)
                        <div class="text-right shrink-0">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">No. Surat</span>
                            <span class="text-xs font-mono font-bold text-slate-700">#{{ $lap->dispensasi->nomor_surat }}</span>
                        </div>
                    @endif
                </div>

                {{-- KETERANGAN SATPAM --}}
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-700 leading-relaxed">
                    <p class="font-semibold text-slate-900 mb-0.5 flex items-center gap-1.5 text-[11px]">
                        <i class="ti ti-shield text-rose-600"></i> Catatan Satpam Pos Gerbang ({{ $lap->satpam?->name ?? 'Satpam' }}):
                    </p>
                    <p class="whitespace-pre-line">{{ $lap->keterangan }}</p>
                </div>

                {{-- CATATAN TINDAK LANJUT ADMIN / GURU --}}
                @if($lap->catatan_tindak_lanjut)
                    <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 text-xs text-blue-900 leading-relaxed">
                        <p class="font-bold text-blue-950 mb-0.5 flex items-center gap-1.5 text-[11px]">
                            <i class="ti ti-circle-check text-blue-600"></i> Respon Tindak Lanjut ({{ $lap->petugasTindakLanjut?->name ?? 'Admin/Guru' }} • {{ $lap->tindak_lanjut_at?->format('H:i') }} WIB):
                        </p>
                        <p class="whitespace-pre-line">{{ $lap->catatan_tindak_lanjut }}</p>
                    </div>
                @else
                    <div class="flex items-center gap-1.5 text-[11px] text-amber-600 font-medium">
                        <i class="ti ti-clock"></i> Belum ada respon tindak lanjut dari Admin atau Guru Piket.
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="ti ti-inbox"></i>
                </div>
                <h4 class="font-bold text-slate-700 text-sm">Belum Ada Laporan Temuan</h4>
                <p class="text-xs text-slate-400 mt-1">Laporan temuan gerbang yang Anda buat akan muncul di sini dan otomatis terkirim ke Admin.</p>
            </div>
        @endforelse

        <div class="pt-2">
            {{ $laporan->links() }}
        </div>
    </div>

    {{-- ================================================== --}}
    {{-- MODAL BUAT LAPORAN TEMUAN GERBANG                   --}}
    {{-- ================================================== --}}
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="min-h-screen px-4 text-center flex items-center justify-center p-0">
            <div x-show="modalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="modalOpen = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

            <div x-show="modalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-lg p-5 sm:p-6 border border-slate-100 my-8">

                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center text-lg font-bold">
                            <i class="ti ti-shield-exclamation"></i>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900">Buat Laporan Temuan Gerbang</h3>
                            <p class="text-[10px] text-slate-500">Terkirim langsung ke Admin & Guru Piket</p>
                        </div>
                    </div>
                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100">
                        <i class="ti ti-x text-base"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('satpam.laporan.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="siswa_id" :value="form.siswa_id">
                    <input type="hidden" name="dispensasi_id" :value="form.dispensasi_id">

                    {{-- 1. PENCARIAN SISWA --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Pilih Siswa yang Bersangkutan <span class="text-rose-600">*</span>
                        </label>

                        <div x-show="form.siswa_id" class="p-3 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-rose-950" x-text="form.nama_lengkap"></p>
                                <p class="text-[10px] text-rose-700" x-text="form.kelas_info"></p>
                                <template x-if="form.no_surat">
                                    <p class="text-[10px] text-slate-500 font-mono mt-0.5" x-text="'No. Surat: #' + form.no_surat"></p>
                                </template>
                            </div>
                            <button type="button" @click="resetSiswa()" class="text-rose-600 hover:text-rose-800 text-xs font-bold px-2 py-1 rounded hover:bg-rose-100">
                                Ganti
                            </button>
                        </div>

                        <div x-show="!form.siswa_id" class="relative">
                            <input type="text"
                                   x-model="searchQuery"
                                   @input.debounce.300ms="searchSiswaAjax()"
                                   placeholder="Ketik nama atau NIS siswa..."
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-rose-600 focus:ring-1 focus:ring-rose-600">
                            
                            {{-- Dropdown hasil search --}}
                            <div x-show="searchResults.length > 0" class="absolute z-20 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                <template x-for="s in searchResults" :key="s.id">
                                    <button type="button"
                                            @click="selectSiswa(s)"
                                            class="w-full text-left p-2.5 hover:bg-rose-50 border-b border-slate-100 last:border-b-0 transition-colors">
                                        <p class="text-xs font-bold text-slate-900" x-text="s.nama_lengkap"></p>
                                        <p class="text-[10px] text-slate-500" x-text="s.kelas + ' • NIS: ' + s.nis"></p>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 2. KATEGORI TEMUAN --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Kategori Kejadian / Temuan <span class="text-rose-600">*</span>
                        </label>
                        <select name="kategori"
                                x-model="form.kategori"
                                required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-rose-600 focus:ring-1 focus:ring-rose-600 bg-white">
                            <option value="rombongan_tidak_kembali">Dispensasi Rombongan: Siswa Tidak Ikut Kembali</option>
                            <option value="terobos_gerbang">Keluar Tanpa Izin / Terobos Gerbang</option>
                            <option value="terlambat_kembali">Terlambat Kembali Tidak Wajar / Mencurigakan</option>
                            <option value="pelanggaran_lain">Pelanggaran Tata Tertib di Pos Gerbang</option>
                        </select>
                    </div>

                    {{-- 3. KETERANGAN / KRONOLOGI --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Keterangan Kronologi / Tanya Jawab Satpam <span class="text-rose-600">*</span>
                        </label>
                        <textarea name="keterangan"
                                  rows="3"
                                  x-model="form.keterangan"
                                  required
                                  placeholder="Contoh: Ditanya satpam, siswa ketua rombongan mengaku siswa ini langsung pulang ke rumah tanpa izin kembali ke sekolah..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-rose-600 focus:ring-1 focus:ring-rose-600"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Tuliskan keterangan objektif hasil tanya jawab satpam di pos.</p>
                    </div>

                    {{-- TOMBOL SUBMIT --}}
                    <div class="pt-3 flex gap-2">
                        <button type="button"
                                @click="modalOpen = false"
                                class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="!form.siswa_id || !form.keterangan"
                                class="flex-1 py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-xs">
                            <i class="ti ti-send mr-1"></i> Kirim Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function laporanSatpam() {
    return {
        modalOpen: false,
        searchQuery: '',
        searchResults: [],
        form: {
            siswa_id: '',
            dispensasi_id: '',
            nama_lengkap: '',
            kelas_info: '',
            no_surat: '',
            kategori: 'rombongan_tidak_kembali',
            keterangan: '',
        },
        openModal() {
            this.modalOpen = true;
        },
        selectDispensasiFast(dispId, siswaId, nama, noSurat) {
            this.form.dispensasi_id = dispId;
            this.form.siswa_id = siswaId;
            this.form.nama_lengkap = nama;
            this.form.kelas_info = 'Dispensasi Aktif Hari Ini';
            this.form.no_surat = noSurat;
            this.form.kategori = 'rombongan_tidak_kembali';
            this.form.keterangan = 'Siswa rombongan tidak ikut kembali saat waktu kembali tiba.';
            this.modalOpen = true;
        },
        selectSiswa(s) {
            this.form.siswa_id = s.id;
            this.form.nama_lengkap = s.nama_lengkap;
            this.form.kelas_info = s.kelas + ' • NIS: ' + s.nis;
            this.searchResults = [];
            this.searchQuery = '';
        },
        resetSiswa() {
            this.form.siswa_id = '';
            this.form.nama_lengkap = '';
            this.form.kelas_info = '';
            this.form.dispensasi_id = '';
            this.form.no_surat = '';
        },
        searchSiswaAjax() {
            if (this.searchQuery.trim().length < 2) {
                this.searchResults = [];
                return;
            }
            fetch('{{ route("satpam.laporan.search-siswa") }}?q=' + encodeURIComponent(this.searchQuery))
                .then(r => r.json())
                .then(data => {
                    this.searchResults = data || [];
                })
                .catch(() => {
                    this.searchResults = [];
                });
        }
    };
}
</script>
@endsection
