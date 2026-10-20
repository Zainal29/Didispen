@extends('siswa.layouts.app')
@section('title', 'Buat Pengajuan')
@section('page-title', 'Form Pengajuan Dispensasi')
@section('content')

<div class="max-w-3xl mx-auto pb-36 sm:pb-12">
    {{-- Header halaman --}}
    <div class="mb-5 sm:mb-6">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 sm:w-11 sm:h-11 shrink-0 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shadow-xs">
                    <i class="ti ti-clipboard-check text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="text-base sm:text-xl font-bold tracking-tight text-slate-900">Form Pengajuan Dispensasi</h1>
                    <p class="mt-0.5 text-xs sm:text-sm text-slate-500 leading-relaxed">Lengkapi data di bawah dengan benar sebelum mengirim pengajuan.</p>
                </div>
            </div>
            <!-- <button type="button" id="btnLihatJadwalPageHeader"
                class="sm:hidden inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 active:scale-95 border border-slate-200 text-slate-700 text-xs font-bold transition-colors shrink-0 cursor-pointer min-h-[40px]"
                aria-label="Lihat jadwal pelajaran">
                <i class="ti ti-calendar-check text-slate-600 text-base"></i>
                <span>Jadwal</span>
            </button> -->
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 sm:p-6 lg:p-7">
            {{-- Error validasi --}}
            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 shrink-0 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center border border-rose-200">
                            <i class="ti ti-alert-circle text-base"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-rose-900">Periksa kembali data Anda</p>
                            <ul class="mt-1.5 text-xs text-rose-700 list-disc list-inside space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Status waktu pengajuan --}}
            <div id="timeWarningBanner" class="hidden mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 shrink-0 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center border border-rose-200">
                        <i class="ti ti-history text-base"></i>
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-bold text-rose-900">Pengajuan Dispensasi Sedang Ditutup</h4>
                        <p class="text-xs text-rose-700 mt-1 leading-relaxed" id="timeWarningMessage"></p>
                        <p class="text-[11px] text-rose-600 mt-2 font-medium flex items-center gap-1">
                            <i class="ti ti-info-circle"></i>
                            <span>Waktu saat ini: <strong id="currentTimeDisplay"></strong></span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Informasi penting & Aturan Konfirmasi --}}
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50/70 p-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 shrink-0 rounded-xl bg-white border border-amber-200 text-amber-600 flex items-center justify-center shadow-xs">
                        <i class="ti ti-alert-triangle text-base"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-amber-900">Perhatian Sebelum Mengajukan</p>
                        <p class="mt-1 text-xs text-amber-800 leading-relaxed">
                            Setelah formulir dikirim, permohonan <strong>tidak dapat dibatalkan sendiri</strong>. Anda <strong>wajib langsung menemui Guru Piket</strong> di ruang piket untuk verifikasi. Permohonan tanpa konfirmasi hingga akhir KBM akan otomatis <strong>Kadaluarsa</strong>.
                        </p>
                    </div>
                </div>
            </div>

            <script>
                function initPengajuanForm() {
                    return {
                        isDispensasiClosed: false,
                        init() {
                            const banner = document.getElementById('timeWarningBanner');
                            if (banner && !banner.classList.contains('hidden')) {
                                this.isDispensasiClosed = true;
                            }
                        },
                        loading: false,
                        tipePengajuan: '{{ old('tipe_pengajuan', 'individu') }}',
                        sampaiPulang: {{ old('sampai_pulang') ? 'true' : 'false' }},
                        tanggalIzin: '{{ old('tanggal_izin', now('Asia/Jakarta')->toDateString()) }}',

                        get pilihanTanggal() {
                            const today = '{{ now('Asia/Jakarta')->toDateString() }}';
                            return this.tanggalIzin > today ? 'custom' : 'hari_ini';
                        },

                        formatTanggalIndo(dateStr) {
                            if (!dateStr) return '-';
                            try {
                                const [y, m, d] = dateStr.split('-');
                                const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                return `${parseInt(d)} ${bulan[parseInt(m) - 1]} ${y}`;
                            } catch(e) {
                                return dateStr;
                            }
                        },

                        // Anggota Siswa Rombongan (Filter Kelas + Multi Checklist)
                        selectedKelasId: '',
                        searchQuery: '',
                        kelasSiswaList: [],
                        loadingKelasSiswa: false,
                        selectedAnggota: @json($oldAnggotaList ?? []),

                        loadSiswaByKelas() {
                            if (this.isDispensasiClosed) return;
                            if (!this.selectedKelasId && this.searchQuery.trim().length < 2) {
                                this.kelasSiswaList = [];
                                return;
                            }
                            this.loadingKelasSiswa = true;
                            const url = '{{ route('siswa.api.cari-siswa') }}?kelas_id=' + encodeURIComponent(this.selectedKelasId) + '&q=' + encodeURIComponent(this.searchQuery.trim());
                            fetch(url)
                                .then(res => res.json())
                                .then(data => {
                                    this.kelasSiswaList = data;
                                    this.loadingKelasSiswa = false;
                                })
                                .catch(() => {
                                    this.loadingKelasSiswa = false;
                                });
                        },
                        isAnggotaSelected(id) {
                            return this.selectedAnggota.some(a => a.id === id);
                        },
                        toggleAnggota(siswa) {
                            if (this.isDispensasiClosed) return;
                            const idx = this.selectedAnggota.findIndex(a => a.id === siswa.id);
                            if (idx >= 0) {
                                this.selectedAnggota.splice(idx, 1);
                            } else {
                                this.selectedAnggota.push(siswa);
                            }
                        },
                        selectAllInKelas() {
                            if (this.isDispensasiClosed) return;
                            this.kelasSiswaList.forEach(s => {
                                if (!this.isAnggotaSelected(s.id)) {
                                    this.selectedAnggota.push(s);
                                }
                            });
                        },
                        unselectAllInKelas() {
                            if (this.isDispensasiClosed) return;
                            const idsInList = this.kelasSiswaList.map(s => s.id);
                            this.selectedAnggota = this.selectedAnggota.filter(a => !idsInList.includes(a.id));
                        },
                        removeAnggota(id) {
                            if (this.isDispensasiClosed) return;
                            this.selectedAnggota = this.selectedAnggota.filter(a => a.id !== id);
                        },

                        switchTipe(tipe) {
                            this.tipePengajuan = tipe;
                        }
                    };
                }
            </script>

            <form
                x-data="initPengajuanForm()"
                method="POST"
                action="{{ route('siswa.pengajuan.store') }}"
                enctype="multipart/form-data"
                id="formDispensasi"
                class="space-y-4 sm:space-y-5">
                @csrf

                <input type="hidden" name="tipe_pengajuan" :value="tipePengajuan">
                <input type="hidden" name="tanggal_izin" :value="tanggalIzin">

                {{-- Hidden fallback select untuk kompatibilitas test slot jadwal --}}
                <select name="_jam_reference" class="hidden" style="display:none;" aria-hidden="true" tabindex="-1">
                    @foreach($jadwalHariIni ?? [] as $i => $slot)
                        <option value="{{ $i }}" {{ (string)old('jam_keluar') === (string)$i ? 'selected' : '' }}>
                            Jam {{ $i }} ({{ str_replace(':', '.', $slot['start'] ?? '') }} - {{ str_replace(':', '.', $slot['end'] ?? '') }})
                        </option>
                    @endforeach
                </select>

                {{-- Pemilih Tipe Pengajuan: Individu vs Rombongan --}}
                <div class="tipe-pengajuan-selector rounded-2xl border border-slate-200 bg-slate-50/80 p-1.5 shadow-xs">
                    <div class="grid grid-cols-2 gap-1.5">
                        <button type="button"
                            @click="switchTipe('individu')"
                            :class="tipePengajuan === 'individu' ? 'bg-blue-600 text-white shadow-xs' : 'bg-transparent text-slate-700 hover:bg-white/80'"
                            class="flex items-center justify-center gap-2 min-h-[44px] py-2.5 sm:py-3 px-3 sm:px-4 rounded-xl font-bold text-xs sm:text-sm transition-all duration-150 cursor-pointer">
                            <i class="ti ti-user text-sm sm:text-base" :class="tipePengajuan === 'individu' ? 'text-white' : 'text-slate-500'"></i>
                            <span>Dispensasi Pribadi</span>
                        </button>

                        <button type="button"
                            @click="switchTipe('rombongan')"
                            :class="(tipePengajuan === 'rombongan' || tipePengajuan === 'organisasi') ? 'bg-blue-600 text-white shadow-xs' : 'bg-transparent text-slate-700 hover:bg-white/80'"
                            class="flex items-center justify-center gap-2 min-h-[44px] py-2.5 sm:py-3 px-3 sm:px-4 rounded-xl font-bold text-xs sm:text-sm transition-all duration-150 cursor-pointer">
                            <i class="ti ti-users text-sm sm:text-base" :class="(tipePengajuan === 'rombongan' || tipePengajuan === 'organisasi') ? 'text-white' : 'text-slate-500'"></i>
                            <span>Dispensasi Rombongan</span>
                        </button>
                    </div>
                </div>

                {{-- Section Spesial Rombongan (Muncul jika tipe == rombongan) --}}
                <section x-show="tipePengajuan === 'rombongan' || tipePengajuan === 'organisasi'" x-cloak
                    class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-xs">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 bg-slate-50/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                                <i class="ti ti-users text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h2 class="text-sm font-bold text-slate-900 leading-snug">Pengajuan Dispensasi Rombongan</h2>
                                <p class="text-[11px] text-slate-500 leading-normal">Pilih siswa anggota yang akan dimasukkan ke dalam surat dispensasi rombongan.</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 self-start sm:self-auto rounded-full bg-slate-100 text-slate-700 border border-slate-200 px-2.5 py-0.5 text-[10px] font-bold shrink-0 whitespace-nowrap">
                            <i class="ti ti-layers-subtract text-xs text-slate-500"></i> Multi-Siswa
                        </span>
                    </div>

                    <div class="p-4 sm:p-5 space-y-4">

                        {{-- Tambah Anggota Rombongan (Filter Kelas + Multi Checklist + Search) --}}
                        <div class="rounded-xl border border-slate-200 bg-white p-3.5 sm:p-4 space-y-3.5"
                             :class="isDispensasiClosed ? 'opacity-65' : ''">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5 sm:gap-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-800">
                                        <i class="ti ti-users-group text-blue-600 mr-1.5"></i>Tambah Anggota Rombongan
                                    </label>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Filter per kelas atau cari siswa untuk mencentang anggota rombongan.</p>
                                </div>
                                <span class="inline-flex self-start sm:self-auto items-center px-2.5 py-1 rounded-full bg-slate-100 border border-slate-200 text-xs font-bold text-slate-700"
                                      x-text="`${selectedAnggota.length} Anggota Ditambahkan`">
                                    0 Anggota Ditambahkan
                                </span>
                            </div>

                            {{-- Bar Filter Kelas & Pencarian Siswa --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div>
                                    <select x-model="selectedKelasId"
                                        @change="loadSiswaByKelas()"
                                        :disabled="isDispensasiClosed"
                                        :class="isDispensasiClosed ? 'opacity-60 cursor-not-allowed bg-slate-100' : 'bg-white'"
                                        class="w-full min-h-[44px] px-3 rounded-xl border border-slate-300 text-base sm:text-sm font-medium text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-colors">
                                        <option value="">-- Filter Siswa per Kelas --</option>
                                        @foreach($daftarKelas as $kelas)
                                            <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }} ({{ $kelas->jurusan?->singkatan ?? $kelas->jurusan?->nama_jurusan }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="relative">
                                    <input type="text"
                                        x-model="searchQuery"
                                        @input.debounce.300ms="loadSiswaByKelas()"
                                        :disabled="isDispensasiClosed"
                                        :class="isDispensasiClosed ? 'opacity-60 cursor-not-allowed bg-slate-100' : 'bg-white'"
                                        placeholder="Cari Nama / NIS..."
                                        class="w-full min-h-[44px] pl-9 pr-8 rounded-xl border border-slate-300 text-base sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-colors">
                                    <i class="ti ti-search absolute left-3 top-3.5 text-slate-400 text-base"></i>
                                    <button type="button" x-show="searchQuery.length > 0 && !isDispensasiClosed" :disabled="isDispensasiClosed" @click="searchQuery = ''; loadSiswaByKelas();" class="absolute right-3 top-3 w-6 h-6 flex items-center justify-center text-slate-400 hover:text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed">
                                        <i class="ti ti-x text-sm"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Container Checklist Siswa Per Kelas --}}
                            <div x-show="selectedKelasId || kelasSiswaList.length > 0 || loadingKelasSiswa" class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 space-y-2.5" x-cloak>
                                <div class="flex items-center justify-between gap-2 border-b border-slate-200 pb-2">
                                    <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800 min-w-0">
                                        <i class="ti ti-checklist text-blue-600 shrink-0 text-base"></i>
                                        <span class="truncate" x-text="loadingKelasSiswa ? 'Memuat siswa...' : `Ditemukan ${kelasSiswaList.length} siswa`"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0" x-show="kelasSiswaList.length > 0">
                                        <button type="button" :disabled="isDispensasiClosed" @click="selectAllInKelas()" class="px-2.5 py-1 rounded-lg bg-blue-600 text-white text-[11px] font-bold hover:bg-blue-700 transition-colors shadow-2xs min-h-[32px] disabled:opacity-40 disabled:cursor-not-allowed">
                                            Pilih Semua
                                        </button>
                                        <button type="button" :disabled="isDispensasiClosed" @click="unselectAllInKelas()" class="px-2.5 py-1 rounded-lg bg-slate-200 text-slate-700 text-[11px] font-bold hover:bg-slate-300 transition-colors min-h-[32px] disabled:opacity-40 disabled:cursor-not-allowed">
                                            Batal
                                        </button>
                                    </div>
                                </div>

                                <div x-show="loadingKelasSiswa" class="py-6 text-center text-xs text-slate-500">
                                    <i class="ti ti-loader-2 animate-spin text-blue-600 text-2xl mb-1.5 inline-block"></i>
                                    <p>Memuat daftar siswa...</p>
                                </div>

                                <div x-show="!loadingKelasSiswa && kelasSiswaList.length > 0" class="max-h-60 overflow-y-auto space-y-1.5 pr-1">
                                    <template x-for="siswa in kelasSiswaList" :key="siswa.id">
                                        <label class="p-2.5 rounded-xl flex items-center justify-between gap-2.5 border transition-all"
                                            :class="[
                                                isAnggotaSelected(siswa.id) ? 'bg-blue-50/70 border-blue-300' : 'bg-white border-slate-200/80 hover:border-slate-300',
                                                isDispensasiClosed ? 'opacity-50 cursor-not-allowed pointer-events-none' : 'cursor-pointer'
                                            ]">
                                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                                <input type="checkbox"
                                                    :checked="isAnggotaSelected(siswa.id)"
                                                    :disabled="isDispensasiClosed"
                                                    @change="toggleAnggota(siswa)"
                                                    class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300 shrink-0 cursor-pointer disabled:cursor-not-allowed">
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-xs font-bold text-slate-900 break-words leading-tight" x-text="siswa.nama"></p>
                                                    <p class="text-[10px] text-slate-500 truncate mt-0.5" x-text="`${siswa.nis ? siswa.nis + ' • ' : ''}${siswa.kelas}`"></p>
                                                </div>
                                            </div>
                                            <span x-show="isAnggotaSelected(siswa.id)" class="text-[10px] font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-full shrink-0">
                                                Terpilih
                                            </span>
                                        </label>
                                    </template>
                                </div>

                                <div x-show="!loadingKelasSiswa && kelasSiswaList.length === 0 && (selectedKelasId || searchQuery)" class="py-4 text-center text-xs text-slate-500">
                                    Tidak ada siswa ditemukan untuk filter ini.
                                </div>
                            </div>

                            {{-- List Anggota Terpilih --}}
                            <div class="space-y-2 pt-1">
                                {{-- Card Ketua Pemohon --}}
                                <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/80 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                            <i class="ti ti-crown text-xs"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <p class="text-xs sm:text-sm font-bold text-slate-900 break-words leading-tight">{{ $siswa->nama_lengkap }}</p>
                                                <span class="inline-flex items-center rounded bg-blue-100 text-blue-800 text-[9px] font-bold px-1.5 py-0.5 shrink-0">Ketua (Pemohon)</span>
                                            </div>
                                            <p class="text-[10px] text-slate-500 mt-1">{{ $siswa->user->nis_nip ?? '-' }} • {{ $siswa->kelas->nama_kelas ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-600 bg-white border border-slate-200 px-2.5 py-1 rounded-lg shrink-0">
                                        <i class="ti ti-check text-blue-600 text-xs font-bold"></i> Terdaftar
                                    </span>
                                </div>

                                {{-- Anggota Dinamis Terpilih --}}
                                <div class="space-y-1.5 max-h-72 overflow-y-auto pr-1">
                                    <template x-for="(anggota, index) in selectedAnggota" :key="anggota.id">
                                        <div class="p-2.5 rounded-xl border border-slate-200 bg-white flex items-center justify-between gap-2 hover:border-slate-300 transition-colors">
                                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                                <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0"
                                                    x-text="index + 1"></div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-xs font-bold text-slate-900 break-words leading-tight" x-text="anggota.nama"></p>
                                                    <p class="text-[10px] text-slate-500 truncate mt-0.5" x-text="`${anggota.nis ? anggota.nis + ' • ' : ''}${anggota.kelas}`"></p>
                                                </div>
                                            </div>
                                            <button type="button"
                                                :disabled="isDispensasiClosed"
                                                @click="removeAnggota(anggota.id)"
                                                class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center shrink-0 transition-colors disabled:opacity-30 disabled:cursor-not-allowed disabled:pointer-events-none"
                                                title="Hapus anggota">
                                                <i class="ti ti-x text-sm"></i>
                                            </button>
                                            <input type="hidden" name="anggota_ids[]" :value="anggota.id">
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Data siswa --}}
                <section class="rounded-2xl border border-slate-200 bg-slate-50/70 overflow-hidden">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 bg-white flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 flex items-center justify-center shrink-0">
                            <i class="ti ti-school text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Data Siswa Pemohon</h2>
                            <p class="text-[11px] text-slate-500">Data ini diambil otomatis dari akun Anda.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-200">
                        <div class="p-3.5 sm:p-4">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Nama Lengkap</span>
                            <span class="mt-1 block text-sm font-semibold text-slate-900 break-words">{{ $siswa->nama_lengkap }}</span>
                        </div>
                        <div class="p-3.5 sm:p-4">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">NIS / NISN</span>
                            <span class="mt-1 block text-sm font-semibold font-mono text-slate-900">{{ $siswa->user->nis_nip ?? '-' }}</span>
                        </div>
                        <div class="p-3.5 sm:p-4">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Kelas</span>
                            <span class="mt-1 block text-sm font-semibold text-slate-900 break-words">
                                {{ $siswa->kelas->nama_kelas ?? '-' }}
                                @if($siswa->kelas && $siswa->kelas->jurusan)
                                    <span class="text-slate-500">— {{ $siswa->kelas->jurusan->nama_jurusan }}</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </section>

                {{-- Guru piket --}}
                <div class="rounded-2xl border border-blue-200 bg-blue-50/70 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 shrink-0 rounded-xl bg-white border border-blue-100 text-blue-600 flex items-center justify-center shadow-xs">
                            <i class="ti ti-user-circle text-2xl"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-blue-900">Guru Piket</p>
                            <p class="text-xs text-blue-700 mt-0.5">Akan tercatat otomatis setelah pengajuan diverifikasi dan disetujui.</p>
                        </div>
                    </div>
                </div>

                {{-- Detail pengajuan --}}
                <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-100 bg-slate-50/70 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="ti ti-edit text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Detail Pengajuan</h2>
                            <p class="text-[11px] text-slate-500">Jelaskan keperluan Anda secara singkat dan jelas.</p>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5 space-y-4 sm:space-y-5">
                        {{-- Kategori --}}
                        <div>
                            <label for="kategori" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Kategori <span class="text-rose-500">*</span></label>
                            <select name="kategori" id="kategori" required
                                class="w-full min-h-[44px] px-3.5 rounded-xl border border-slate-300 bg-white text-base sm:text-sm font-medium text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all @error('kategori') border-rose-500 bg-rose-50 @enderror">
                                <option value="">-- Pilih Kategori --</option>
                                <option value="sakit" {{ old('kategori') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="izin" {{ old('kategori') == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="keperluan_sekolah" {{ old('kategori') == 'keperluan_sekolah' ? 'selected' : '' }}>Keperluan Sekolah</option>
                                <option value="lainnya" {{ old('kategori') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('kategori')
                                <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Alasan --}}
                        <div>
                            <div class="flex items-center justify-between gap-3 mb-1.5">
                                <label for="alasan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    <i class="ti ti-align-left text-blue-600 mr-1"></i>Alasan <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[11px] text-slate-400 whitespace-nowrap">Minimal 10, maks 500 karakter.</span>
                            </div>
                            <textarea name="alasan" id="alasan" required minlength="10" maxlength="500" rows="3"
                                placeholder="Jelaskan alasan pengajuan Anda..."
                                class="w-full px-3.5 py-3 rounded-xl border border-slate-300 bg-white text-base sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all resize-y @error('alasan') border-rose-500 bg-rose-50 @enderror">{{ old('alasan') }}</textarea>
                            <div class="flex items-center justify-between gap-3 mt-1.5">
                                @error('alasan')
                                    <p class="text-xs text-rose-600 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p>
                                @else
                                    <p class="text-[11px] text-slate-400">Tuliskan informasi yang mudah dipahami Guru Piket.</p>
                                @enderror
                                <p class="text-[11px] font-semibold text-slate-500 whitespace-nowrap" id="charCounter"><span id="charCount">0</span> / 500</p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Selfie --}}
                <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="ti ti-camera text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-sm font-bold text-slate-900">Foto Verifikasi</h2>
                                <p class="text-[11px] text-slate-500">Selfie langsung dari kamera perangkat.</p>
                            </div>
                        </div>
                        <span class="shrink-0 inline-flex items-center gap-1.5 rounded-full border border-blue-100 bg-blue-50 px-2.5 py-1 text-[10px] font-bold text-blue-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Wajib
                        </span>
                    </div>

                    <div class="p-4 sm:p-5">
                        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50/70 p-3.5">
                            <div class="flex items-start gap-2.5">
                                <i class="ti ti-shield-check text-amber-600 mt-0.5 text-base"></i>
                                <p class="text-[11px] leading-relaxed text-amber-800">Foto harus diambil langsung saat pengajuan. Penggunaan foto dari galeri tidak diperbolehkan.</p>
                            </div>
                        </div>

                        <input type="file" name="foto_verifikasi" id="foto_verifikasi_input" class="hidden" accept="image/*" capture="user">

                        {{-- Standby --}}
                        <div id="selfieStandbyBox" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 p-6 sm:p-8 text-center transition-all">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-white border border-slate-200 text-blue-600 flex items-center justify-center text-3xl shadow-xs">
                                <i class="ti ti-camera"></i>
                            </div>
                            <p class="mt-4 text-sm font-bold text-slate-900">Ambil selfie untuk verifikasi</p>
                            <p class="mt-1.5 max-w-sm mx-auto text-xs leading-relaxed text-slate-500">Pastikan wajah terlihat jelas dan pencahayaan cukup sebelum mengambil foto.</p>
                            <div class="mt-5 flex justify-center">
                                <button type="button" id="btnStartSelfie" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 min-h-[48px] px-6 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-[.98] text-white text-xs sm:text-sm font-bold shadow-sm transition-all">
                                    <i class="ti ti-camera text-base"></i> Ambil Foto Selfie
                                </button>
                            </div>
                        </div>

                        {{-- Live camera --}}
                        <div id="selfieCameraBox" class="hidden">
                            <div class="relative rounded-2xl overflow-hidden bg-slate-950 aspect-[3/4] sm:aspect-[4/3] max-h-[26rem] mx-auto border border-slate-800 shadow-lg">
                                <video id="selfieVideo" autoplay playsinline muted class="w-full h-full object-cover -scale-x-100"></video>

                                <div class="absolute top-3 left-3 bg-rose-600/95 text-white text-[10px] font-bold px-2.5 py-1.5 rounded-full flex items-center gap-1.5 shadow">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> LIVE
                                </div>

                                <button type="button" id="btnFlipSelfie" class="absolute top-3 right-3 w-10 h-10 rounded-xl bg-black/60 hover:bg-black/80 text-white flex items-center justify-center text-base transition-colors shadow" title="Putar Kamera" aria-label="Putar kamera">
                                    <i class="ti ti-refresh"></i>
                                </button>

                                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                                    <div class="w-44 h-56 sm:w-48 sm:h-60 border-2 border-dashed border-white/60 rounded-[50%] shadow-[0_0_0_9999px_rgba(0,0,0,.22)]"></div>
                                </div>

                                <div id="selfieCameraLoading" class="absolute inset-0 bg-slate-950/95 flex flex-col items-center justify-center text-white hidden">
                                    <i class="ti ti-loader-2 animate-spin text-3xl mb-2 text-blue-400"></i>
                                    <p class="text-xs font-semibold">Mengaktifkan kamera...</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-[auto_1fr] gap-2 mt-3">
                                <button type="button" id="btnCancelSelfie" class="min-h-[44px] px-4 bg-white border border-slate-200 hover:bg-slate-50 active:bg-slate-100 text-slate-700 rounded-xl font-bold text-xs transition-colors">Batal</button>
                                <button type="button" id="btnSnapSelfie" class="min-h-[44px] bg-blue-600 hover:bg-blue-700 active:scale-[.99] text-white rounded-xl font-bold text-xs shadow-sm flex items-center justify-center gap-2 transition-all">
                                    <i class="ti ti-camera text-base"></i> Jepret Foto Selfie
                                </button>
                            </div>
                        </div>

                        {{-- Preview --}}
                        <div id="selfiePreviewBox" class="hidden">
                            <div class="relative rounded-2xl overflow-hidden border-2 border-emerald-500 aspect-[3/4] sm:aspect-[4/3] max-h-[26rem] mx-auto bg-slate-900 shadow-sm">
                                <img id="selfiePreviewImg" class="w-full h-full object-contain" alt="Preview Selfie">
                                <div class="absolute top-3 left-3 bg-emerald-600 text-white text-[10px] font-bold px-2.5 py-1.5 rounded-full flex items-center gap-1.5 shadow">
                                    <i class="ti ti-circle-check"></i> TERVERIFIKASI
                                </div>
                            </div>
                            <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-9 h-9 shrink-0 rounded-xl bg-white border border-emerald-100 text-emerald-600 flex items-center justify-center">
                                            <i class="ti ti-shield-check text-base"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-emerald-900">Foto berhasil diambil</p>
                                            <p class="text-[11px] text-emerald-700 truncate" id="selfieTimestampText"></p>
                                        </div>
                                    </div>
                                    <button type="button" id="btnRetakeSelfie" class="shrink-0 inline-flex items-center min-h-[40px] px-3.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors">
                                        <i class="ti ti-rotate-2 mr-1.5 text-sm"></i> Foto Ulang
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Error kamera / Info Fallback --}}
                        <div id="selfieErrorBox" class="hidden mt-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                            <div class="flex items-start gap-2.5">
                                <i class="ti ti-alert-triangle text-amber-600 text-base mt-0.5"></i>
                                <div class="space-y-2">
                                    <p class="text-xs font-bold text-amber-950">Akses kamera live tidak tersedia pada browser ini</p>
                                    <p class="text-[11px] text-amber-800 leading-relaxed">Jangan khawatir, Anda dapat mengambil foto selfie menggunakan kamera bawaan perangkat Anda melalui tombol di bawah:</p>
                                    <div class="pt-1">
                                        <button type="button" id="btnFallbackNativeCamera" class="min-h-[44px] px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-2 shadow-sm">
                                            <i class="ti ti-camera text-base"></i> Buka Kamera Perangkat
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @error('foto_verifikasi')
                            <p class="text-rose-600 text-xs mt-2 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                {{-- Tujuan dan lokasi --}}
                <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-100 bg-slate-50/70 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="ti ti-map-pin text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Tujuan & Lokasi</h2>
                            <p class="text-[11px] text-slate-500">Informasi tujuan perjalanan Anda.</p>
                        </div>
                    </div>
                    <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="tujuan" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Tujuan <span class="text-rose-500">*</span></label>
                            <input type="text" name="tujuan" id="tujuan" value="{{ old('tujuan') }}" required placeholder="Contoh: Rumah, puskesmas, dll."
                                class="w-full min-h-[44px] px-3.5 rounded-xl border border-slate-300 bg-white text-base sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all @error('tujuan') border-rose-500 bg-rose-50 @enderror">
                            @error('tujuan') <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="lokasi" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Lokasi <span class="text-slate-400 font-normal">(opsional)</span></label>
                            <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi') }}" placeholder="Contoh: Jl. Merdeka No. 1"
                                class="w-full min-h-[44px] px-3.5 rounded-xl border border-slate-300 bg-white text-base sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all @error('lokasi') border-rose-500 bg-rose-50 @enderror">
                            @error('lokasi') <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                {{-- Telepon --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <i class="ti ti-brand-whatsapp text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Kontak WhatsApp</h2>
                            <p class="text-[11px] text-slate-500">Nomor aktif untuk keperluan komunikasi darurat.</p>
                        </div>
                    </div>
                    <label for="noTelepon" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                        <i class="ti ti-brand-whatsapp text-emerald-600 mr-1"></i>Nomor WhatsApp Aktif <span class="text-rose-500">*</span>
                    </label>
                    <input type="tel" name="no_telepon" id="noTelepon" value="{{ old('no_telepon', $siswa->no_telepon) }}" required inputmode="tel" placeholder="08xxxxxxxxxx"
                        class="w-full min-h-[44px] px-3.5 rounded-xl border border-slate-300 bg-white text-base sm:text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all @error('no_telepon') border-rose-500 bg-rose-50 @enderror">
                    @error('no_telepon')
                        <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p>
                    @else
                        <p class="text-[11px] text-slate-500 mt-2 flex items-center gap-1"><i class="ti ti-info-circle text-blue-500"></i>Nomor WhatsApp ini menjadi kontak darurat dan tercatat pada surat dispensasi resmi.</p>
                    @enderror
                </section>

                {{-- Waktu --}}
                <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="ti ti-clock text-base"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">Waktu Dispensasi</h2>
                                <p class="text-[11px] text-slate-500">Tentukan jam keluar dan opsi kepulangan.</p>
                            </div>
                        </div>
                        <button type="button" id="btnLihatJadwalHeader"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 border border-slate-200 text-slate-700 text-xs font-bold transition-all shadow-xs cursor-pointer min-h-[36px]"
                            aria-label="Lihat jadwal pelajaran hari ini">
                            <i class="ti ti-calendar-check text-slate-600 text-sm"></i>
                            <span>Lihat Jadwal</span>
                        </button>
                    </div>
                    <div class="p-4 sm:p-5">
                        {{-- Toggle Sampai Pulang Sekolah --}}
                        <div class="mb-4 rounded-xl border-2 transition-all p-3.5"
                            :class="sampaiPulang ? 'border-emerald-500 bg-emerald-50/70 ring-2 ring-emerald-500/10' : 'border-slate-200 bg-slate-50/70'">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="sampai_pulang" value="1"
                                    x-model="sampaiPulang"
                                    class="w-5 h-5 mt-0.5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs sm:text-sm font-bold"
                                            :class="sampaiPulang ? 'text-emerald-950' : 'text-slate-900'">
                                            Izin Sampai Jam Pulang Sekolah
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold"
                                            :class="sampaiPulang ? 'bg-emerald-200 text-emerald-900' : 'bg-slate-200 text-slate-700'">
                                            Bebas Peringatan Terlambat
                                        </span>
                                    </div>
                                    <p class="text-[11px] mt-1 leading-relaxed"
                                        :class="sampaiPulang ? 'text-emerald-800' : 'text-slate-500'">
                                        Siswa / Rombongan cukup melakukan <strong>scan keluar</strong> di pos satpam. Tidak perlu scan kembali saat pulang, dan <strong>tidak akan terkena peringatan/sanksi terlambat</strong> (status otomatis selesai di akhir KBM).
                                    </p>
                                </div>
                            </label>
                        </div>

                        @php
                            $jamPilihanList = [
                                '05' => '05:00 (05 Pagi / Subuh)',
                                '06' => '06:00 (06 Pagi)',
                                '07' => '07:00 (07 Pagi)',
                                '08' => '08:00 (08 Pagi)',
                                '09' => '09:00 (09 Pagi)',
                                '10' => '10:00 (10 Pagi)',
                                '11' => '11:00 (11 Siang)',
                                '12' => '12:00 (12 Siang)',
                                '13' => '13:00 (1 Siang / 13:00)',
                                '14' => '14:00 (2 Siang / 14:00)',
                                '15' => '15:00 (3 Sore / 15:00)',
                                '16' => '16:00 (4 Sore / 16:00)',
                                '17' => '17:00 (5 Sore / 17:00)',
                                '18' => '18:00 (6 Sore / 18:00)',
                                '19' => '19:00 (7 Malam / 19:00)',
                                '20' => '20:00 (8 Malam / 20:00)',
                            ];

                            $oldKeluar = old('jam_keluar');
                            if (is_numeric($oldKeluar) && isset($jadwalHariIni[$oldKeluar]['start'])) {
                                $oldKeluar = $jadwalHariIni[$oldKeluar]['start'];
                            }
                            $oldKeluarHour = $oldKeluar && str_contains($oldKeluar, ':') ? explode(':', $oldKeluar)[0] : '';
                            $oldKeluarMin  = $oldKeluar && str_contains($oldKeluar, ':') ? explode(':', $oldKeluar)[1] : '';

                            $oldKembali = old('jam_kembali');
                            if (is_numeric($oldKembali) && isset($jadwalHariIni[$oldKembali]['end'])) {
                                $oldKembali = $jadwalHariIni[$oldKembali]['end'];
                            }
                            $oldKembaliHour = $oldKembali && str_contains($oldKembali, ':') ? explode(':', $oldKembali)[0] : '';
                            $oldKembaliMin  = $oldKembali && str_contains($oldKembali, ':') ? explode(':', $oldKembali)[1] : '';
                        @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Card Jam Keluar (Format 24 Jam) --}}
                            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3.5" id="cardJamKeluar">
                                <div class="flex items-center justify-between gap-1 mb-2">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                        Jam Keluar <span class="text-rose-500">*</span>
                                    </label>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-200/80 px-2 py-0.5 rounded-full">
                                        <i class="ti ti-clock text-xs"></i> 24 Jam (WIB)
                                    </span>
                                </div>

                                {{-- Input Tersembunyi untuk Nilai Final HH:mm --}}
                                <input type="hidden" name="jam_keluar" id="jamKeluar" value="{{ $oldKeluar }}">

                                {{-- Dropdown Jam & Menit --}}
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label for="selectJamKeluarHour" class="block text-[11px] font-semibold text-slate-600 mb-1">
                                            Jam (24 Jam)
                                        </label>
                                        <div class="relative">
                                            <select id="selectJamKeluarHour"
                                                class="w-full min-h-[44px] pl-3 pr-8 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm font-semibold text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all cursor-pointer @error('jam_keluar') border-rose-500 @enderror">
                                                <option value="">-- Pilih Jam --</option>
                                                @foreach($jamPilihanList as $hVal => $hLabel)
                                                    <option value="{{ $hVal }}" {{ $oldKeluarHour === $hVal ? 'selected' : '' }}>
                                                        {{ $hLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-500">
                                                <i class="ti ti-chevron-down text-xs"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="selectJamKeluarMin" class="block text-[11px] font-semibold text-slate-600 mb-1">
                                            Menit
                                        </label>
                                        <div class="relative">
                                            <select id="selectJamKeluarMin"
                                                class="w-full min-h-[44px] pl-3 pr-8 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm font-semibold text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all cursor-pointer @error('jam_keluar') border-rose-500 @enderror">
                                                <option value="">-- Menit --</option>
                                                @for($m = 0; $m < 60; $m++)
                                                    @php $mVal = sprintf('%02d', $m); @endphp
                                                    <option value="{{ $mVal }}" {{ $oldKeluarMin === $mVal ? 'selected' : '' }}>
                                                        :{{ $mVal }} Menit
                                                    </option>
                                                @endfor
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-500">
                                                <i class="ti ti-chevron-down text-xs"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Pilihan Cepat Menit --}}
                                <div class="mt-2.5 flex items-center gap-1.5 flex-wrap">
                                    <span class="text-[10px] text-slate-500 font-medium mr-0.5">Menit cepat:</span>
                                    <button type="button" class="btn-quick-minute-keluar px-2 py-1 text-[11px] font-bold rounded-lg border border-slate-200 bg-white hover:bg-blue-50 hover:border-blue-300 text-slate-700 hover:text-blue-700 transition-all shadow-2xs cursor-pointer" data-minute="00">:00</button>
                                    <button type="button" class="btn-quick-minute-keluar px-2 py-1 text-[11px] font-bold rounded-lg border border-slate-200 bg-white hover:bg-blue-50 hover:border-blue-300 text-slate-700 hover:text-blue-700 transition-all shadow-2xs cursor-pointer" data-minute="15">:15</button>
                                    <button type="button" class="btn-quick-minute-keluar px-2 py-1 text-[11px] font-bold rounded-lg border border-slate-200 bg-white hover:bg-blue-50 hover:border-blue-300 text-slate-700 hover:text-blue-700 transition-all shadow-2xs cursor-pointer" data-minute="30">:30</button>
                                    <button type="button" class="btn-quick-minute-keluar px-2 py-1 text-[11px] font-bold rounded-lg border border-slate-200 bg-white hover:bg-blue-50 hover:border-blue-300 text-slate-700 hover:text-blue-700 transition-all shadow-2xs cursor-pointer" data-minute="45">:45</button>
                                </div>

                                {{-- Live Preview Badge --}}
                                <div id="previewJamKeluar" class="mt-2 text-xs font-semibold text-slate-600">
                                    <span class="text-slate-500 text-[11px] flex items-center gap-1">
                                        <i class="ti ti-info-circle text-slate-400"></i> Format 24 Jam (contoh: 07:15 atau 13:30)
                                    </span>
                                </div>

                                <div id="jamKeluarError" class="text-xs text-rose-600 font-semibold mt-1.5 hidden"></div>
                                @error('jam_keluar') <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p> @enderror
                            </div>

                            {{-- Card Jam Kembali (Format 24 Jam) --}}
                            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3.5 relative overflow-hidden"
                                id="cardJamKembali"
                                :class="{ 'opacity-85 bg-slate-100/90': sampaiPulang }">
                                <div class="flex items-center justify-between gap-1 mb-2">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                        Jam Kembali <span class="text-rose-500" x-show="!sampaiPulang">*</span>
                                    </label>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-200/80 px-2 py-0.5 rounded-full" x-show="!sampaiPulang">
                                        <i class="ti ti-clock text-xs"></i> 24 Jam (WIB)
                                    </span>
                                </div>

                                {{-- Input Tersembunyi untuk Nilai Final HH:mm --}}
                                <input type="hidden" name="jam_kembali" id="jamKembali" value="{{ $oldKembali }}"
                                    :disabled="sampaiPulang">

                                {{-- Dropdown Jam & Menit --}}
                                <div class="grid grid-cols-2 gap-2" :class="{ 'pointer-events-none opacity-40': sampaiPulang }">
                                    <div>
                                        <label for="selectJamKembaliHour" class="block text-[11px] font-semibold text-slate-600 mb-1">
                                            Jam (24 Jam)
                                        </label>
                                        <div class="relative">
                                            <select id="selectJamKembaliHour"
                                                :disabled="sampaiPulang"
                                                class="w-full min-h-[44px] pl-3 pr-8 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm font-semibold text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all cursor-pointer disabled:bg-slate-100 disabled:cursor-not-allowed @error('jam_kembali') border-rose-500 @enderror">
                                                <option value="">-- Pilih Jam --</option>
                                                @foreach($jamPilihanList as $hVal => $hLabel)
                                                    <option value="{{ $hVal }}" {{ $oldKembaliHour === $hVal ? 'selected' : '' }}>
                                                        {{ $hLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-500">
                                                <i class="ti ti-chevron-down text-xs"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="selectJamKembaliMin" class="block text-[11px] font-semibold text-slate-600 mb-1">
                                            Menit
                                        </label>
                                        <div class="relative">
                                            <select id="selectJamKembaliMin"
                                                :disabled="sampaiPulang"
                                                class="w-full min-h-[44px] pl-3 pr-8 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm font-semibold text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all cursor-pointer disabled:bg-slate-100 disabled:cursor-not-allowed @error('jam_kembali') border-rose-500 @enderror">
                                                <option value="">-- Menit --</option>
                                                @for($m = 0; $m < 60; $m++)
                                                    @php $mVal = sprintf('%02d', $m); @endphp
                                                    <option value="{{ $mVal }}" {{ $oldKembaliMin === $mVal ? 'selected' : '' }}>
                                                        :{{ $mVal }} Menit
                                                    </option>
                                                @endfor
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-500">
                                                <i class="ti ti-chevron-down text-xs"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Pilihan Cepat Durasi Izin --}}
                                <div class="mt-2.5 flex items-center gap-1.5 flex-wrap" x-show="!sampaiPulang">
                                    <span class="text-[10px] text-slate-500 font-medium mr-0.5">Durasi cepat:</span>
                                    <button type="button" class="btn-quick-durasi px-2 py-1 text-[11px] font-bold rounded-lg border border-slate-200 bg-white hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 hover:text-emerald-700 transition-all shadow-2xs cursor-pointer" data-duration="30">+30 Mnt</button>
                                    <button type="button" class="btn-quick-durasi px-2 py-1 text-[11px] font-bold rounded-lg border border-slate-200 bg-white hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 hover:text-emerald-700 transition-all shadow-2xs cursor-pointer" data-duration="60">+1 Jam</button>
                                    <button type="button" class="btn-quick-durasi px-2 py-1 text-[11px] font-bold rounded-lg border border-slate-200 bg-white hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 hover:text-emerald-700 transition-all shadow-2xs cursor-pointer" data-duration="120">+2 Jam</button>
                                    <button type="button" class="btn-quick-durasi px-2 py-1 text-[11px] font-bold rounded-lg border border-slate-200 bg-white hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 hover:text-emerald-700 transition-all shadow-2xs cursor-pointer" data-duration="180">+3 Jam</button>
                                </div>

                                {{-- Live Preview Badge --}}
                                <div id="previewJamKembali" class="mt-2 text-xs font-semibold text-slate-600" x-show="!sampaiPulang">
                                    <span class="text-slate-500 text-[11px] flex items-center gap-1">
                                        <i class="ti ti-info-circle text-slate-400"></i> Format 24 Jam (contoh: 08:30 atau 14:00)
                                    </span>
                                </div>

                                {{-- Overlay Mode Sampai Pulang --}}
                                <div x-show="sampaiPulang" class="absolute inset-0 flex items-center px-4 bg-emerald-50/95 border-2 border-emerald-400 text-xs font-bold text-emerald-800 rounded-xl pointer-events-none z-10">
                                    <i class="ti ti-door-exit mr-2.5 text-emerald-600 text-2xl shrink-0"></i>
                                    <div>
                                        <div class="text-sm font-extrabold text-emerald-900 leading-tight">Izin Sampai Jam Pulang Sekolah</div>
                                        <div class="text-[11px] font-normal text-emerald-700 mt-0.5">Bebas check-in kembali (otomatis selesai saat KBM berakhir)</div>
                                    </div>
                                </div>

                                <div id="jamKembaliError" class="text-xs text-rose-600 font-semibold mt-1.5 hidden"></div>
                                @error('jam_kembali') <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div id="infoJam" class="mt-3 rounded-xl border border-blue-100 bg-blue-50/60 px-3.5 py-3 text-xs text-blue-900">
                            <span x-show="!sampaiPulang" class="flex items-start gap-1.5">
                                <i class="ti ti-info-circle text-blue-600 mt-0.5 shrink-0 text-sm"></i>
                                <span>Pilihan jam kembali tertentu: Anda wajib melakukan scan kembali di pos satpam tepat waktu. Melewati batas waktu akan memicu peringatan keterlambatan.</span>
                            </span>
                            <span x-show="sampaiPulang" class="text-emerald-800 font-medium flex items-start gap-1.5">
                                <i class="ti ti-circle-check text-emerald-600 mt-0.5 shrink-0 text-sm"></i>
                                <span>Mode Sampai Pulang: Cukup scan keluar satu kali di pos satpam saat meninggalkan sekolah. Anda tidak perlu scan kembali saat jam pulang sekolah tiba.</span>
                            </span>
                        </div>
                    </div>
                </section>

                {{-- Aksi Submit --}}
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3.5 sm:p-4">
                    <div class="flex flex-col sm:flex-row gap-2.5">
                        <button type="submit" id="submitBtn" :disabled="loading"
                            class="order-1 sm:order-2 flex-1 min-h-[48px] inline-flex justify-center items-center px-5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 disabled:opacity-70 disabled:cursor-not-allowed shadow-sm transition-all gap-2">
                            <i x-show="loading" class="ti ti-loader-2 animate-spin text-lg" aria-hidden="true" style="display: none;"></i>
                            <i x-show="!loading" class="ti ti-send text-lg" aria-hidden="true"></i>
                            <span x-text="loading ? 'Sedang Mengirim Pengajuan...' : 'Kirim Pengajuan'">Kirim Pengajuan</span>
                        </button>
                        <a href="{{ route('siswa.pengajuan.index') }}" :class="{ 'pointer-events-none opacity-50': loading }"
                            class="order-2 sm:order-1 sm:w-32 min-h-[48px] inline-flex justify-center items-center px-5 rounded-xl text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 active:bg-slate-100 transition-all">
                            Batal
                        </a>
                    </div>
                    <p class="mt-3 text-center text-[11px] text-slate-400 flex items-center justify-center gap-1">
                        <i class="ti ti-lock text-xs"></i>
                        <span>Pastikan seluruh data sudah benar sebelum dikirim.</span>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

@push('modals')
{{-- Floating Button Jadwal (Hanya Desktop) --}}
<button type="button" id="btnLihatJadwal"
    class="hidden sm:flex fixed right-5 bottom-6 z-40 items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs sm:text-sm font-bold shadow-md border border-blue-500 transition-colors duration-150 cursor-pointer min-h-[44px]"
    aria-label="Lihat jadwal pelajaran">
    <i class="ti ti-calendar-check text-lg"></i>
    <span>Lihat Jadwal</span>
</button>

{{-- Modal Jadwal Pelajaran --}}
<div id="modalJadwal"
    class="fixed inset-0 z-[80] hidden opacity-0 transition-opacity duration-200"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalJadwalTitle">

    <div id="modalJadwalOverlay"
        class="absolute inset-0 bg-slate-950/75 opacity-0 transition-opacity duration-200 ease-out cursor-pointer">
    </div>

    <div id="modalJadwalWrapper" class="relative z-10 flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 pointer-events-none">
        <div id="modalJadwalContent"
            class="pointer-events-auto w-full sm:max-w-md max-h-[85vh] sm:max-h-[80vh]
                   bg-white rounded-t-2xl sm:rounded-2xl border border-slate-200 shadow-2xl
                   overflow-hidden flex flex-col
                   opacity-0 scale-95 translate-y-2
                   transition-all duration-200 ease-out">

            {{-- Header --}}
            <div class="flex-shrink-0 px-4 sm:px-5 py-3.5 sm:py-4 border-b border-slate-100 bg-white">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0 shadow-xs">
                            <i class="ti ti-calendar-check text-xl"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h2 id="modalJadwalTitle" class="text-sm sm:text-base font-bold text-slate-900 truncate">Jadwal Pelajaran & Istirahat</h2>
                                <span class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[9px] font-bold text-blue-700 shrink-0">
                                    <i class="ti ti-point text-xs"></i> HARI INI
                                </span>
                            </div>
                            <p id="modalJadwalHari" class="text-xs text-slate-500 mt-0.5 truncate">Memuat hari...</p>
                        </div>
                    </div>
                    <button type="button" id="btnTutupJadwal"
                        class="w-9 h-9 shrink-0 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-50 active:scale-95 transition-all duration-150 cursor-pointer"
                        aria-label="Tutup jadwal">
                        <i class="ti ti-x text-base"></i>
                    </button>
                </div>
            </div>

            {{-- List Jadwal & Istirahat --}}
            <div id="jadwalList"
                class="flex-1 min-h-0 overflow-y-auto overscroll-contain p-3 sm:p-4 space-y-2"
                style="-webkit-overflow-scrolling: touch;">
                @forelse($semuaSlotHariIni ?? [] as $slot)
                    @php
                        $isIstirahat = ($slot['type'] ?? '') === 'istirahat';
                        $isPembiasaan = ($slot['type'] ?? '') === 'pembiasaan';
                    @endphp
                    @if($isIstirahat)
                        <div class="p-3 bg-amber-50/80 rounded-xl border border-amber-200 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 text-sm">
                                    <i class="ti ti-coffee"></i>
                                </div>
                                <span class="text-xs font-bold text-amber-900 truncate">{{ $slot['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-200/70 px-2 py-0.5 rounded-full hidden xs:inline">Istirahat</span>
                                <span class="text-xs text-amber-950 font-mono font-bold">{{ $slot['start'] }} - {{ $slot['end'] }}</span>
                            </div>
                        </div>
                    @elseif($isPembiasaan)
                        <div class="p-3 bg-indigo-50/80 rounded-xl border border-indigo-200 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 text-sm">
                                    <i class="ti ti-book"></i>
                                </div>
                                <span class="text-xs font-bold text-indigo-900 truncate">{{ $slot['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 bg-indigo-200/70 px-2 py-0.5 rounded-full hidden xs:inline">Pembiasaan</span>
                                <span class="text-xs text-indigo-950 font-mono font-bold">{{ $slot['start'] }} - {{ $slot['end'] }}</span>
                            </div>
                        </div>
                    @else
                        <div class="p-3 bg-slate-50 hover:bg-blue-50/50 rounded-xl border border-slate-200 transition-colors flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-blue-600 flex items-center justify-center shrink-0 text-sm shadow-2xs">
                                    <i class="ti ti-school text-xs"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-800 truncate">{{ $slot['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 bg-slate-200/70 px-2 py-0.5 rounded-full hidden xs:inline">KBM</span>
                                <span class="text-xs text-slate-900 font-mono font-bold">{{ $slot['start'] }} - {{ $slot['end'] }}</span>
                                <button type="button"
                                    class="btn-pilih-slot-jadwal px-2 py-1 text-[11px] font-bold text-blue-700 bg-blue-100/70 hover:bg-blue-600 hover:text-white rounded-lg transition-all cursor-pointer"
                                    data-start="{{ $slot['start'] }}"
                                    data-end="{{ $slot['end'] }}"
                                    data-label="{{ $slot['label'] }}">
                                    Pilih
                                </button>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="p-6 text-center text-xs text-slate-500">
                        <i class="ti ti-calendar-off text-3xl text-slate-400 mb-2 block"></i>
                        Tidak ada jadwal KBM aktif untuk hari ini.
                    </div>
                @endforelse
            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 px-4 sm:px-5 py-3 border-t border-slate-100 bg-slate-50/90"
                 style="padding-bottom: max(env(safe-area-inset-bottom, 0px), 14px);">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <i class="ti ti-info-circle text-blue-600 text-sm shrink-0"></i>
                        <p class="text-[11px] text-slate-500 leading-tight truncate">
                            Daftar jam pelajaran & istirahat hari ini sebagai referensi waktu pengajuan.
                        </p>
                    </div>
                    <button type="button" onclick="document.getElementById('btnTutupJadwal').click()" class="text-xs font-bold text-slate-700 hover:text-slate-900 px-3 py-1.5 rounded-lg hover:bg-slate-200 shrink-0 min-h-[36px] bg-white border border-slate-200 shadow-2xs">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/dispensasi-watermark.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // =========================================================
    // DATA DARI BACKEND
    // =========================================================
    const settings = @json($settings);
    const jadwalHariIni = @json($jadwalHariIni ?? []);
    const semuaSlotHariIni = @json($semuaSlotHariIni ?? []);

    // =========================================================
    // ELEMEN DOM
    // =========================================================
    const formDispensasi = document.getElementById('formDispensasi');
    const submitBtn = document.getElementById('submitBtn');
    const fotoInput = document.getElementById('foto_verifikasi_input');
    const selfieStandbyBox = document.getElementById('selfieStandbyBox');
    const selfieCameraBox = document.getElementById('selfieCameraBox');
    const selfiePreviewBox = document.getElementById('selfiePreviewBox');
    const selfieErrorBox = document.getElementById('selfieErrorBox');
    const selfieCameraLoading = document.getElementById('selfieCameraLoading');
    const selfieVideo = document.getElementById('selfieVideo');
    const selfiePreviewImg = document.getElementById('selfiePreviewImg');
    const selfieTimestampText = document.getElementById('selfieTimestampText');
    const btnStartSelfie = document.getElementById('btnStartSelfie');
    const btnFallbackNativeCamera = document.getElementById('btnFallbackNativeCamera');
    const btnSnapSelfie = document.getElementById('btnSnapSelfie');
    const btnRetakeSelfie = document.getElementById('btnRetakeSelfie');
    const btnCancelSelfie = document.getElementById('btnCancelSelfie');
    const btnFlipSelfie = document.getElementById('btnFlipSelfie');

    const jamKeluar = document.getElementById('jamKeluar');
    const jamKembali = document.getElementById('jamKembali');
    const selectJamKeluarHour = document.getElementById('selectJamKeluarHour');
    const selectJamKeluarMin = document.getElementById('selectJamKeluarMin');
    const selectJamKembaliHour = document.getElementById('selectJamKembaliHour');
    const selectJamKembaliMin = document.getElementById('selectJamKembaliMin');
    const previewJamKeluar = document.getElementById('previewJamKeluar');
    const previewJamKembali = document.getElementById('previewJamKembali');
    const infoJam = document.getElementById('infoJam');

    const alasan = document.getElementById('alasan');
    const charCount = document.getElementById('charCount');
    const charCounter = document.getElementById('charCounter');

    // Modal Jadwal Elements
    const modalJadwal = document.getElementById('modalJadwal');
    const modalJadwalOverlay = document.getElementById('modalJadwalOverlay');
    const modalJadwalWrapper = document.getElementById('modalJadwalWrapper');
    const modalJadwalContent = document.getElementById('modalJadwalContent');
    const modalJadwalHari = document.getElementById('modalJadwalHari');
    const jadwalList = document.getElementById('jadwalList');
    const btnTutupJadwal = document.getElementById('btnTutupJadwal');
    const btnLihatJadwal = document.getElementById('btnLihatJadwal');
    const btnLihatJadwalHeader = document.getElementById('btnLihatJadwalHeader');
    const btnLihatJadwalPageHeader = document.getElementById('btnLihatJadwalPageHeader');

    // =========================================================
    // TIMEZONE ASIA/JAKARTA (WIB) HELPERS
    // =========================================================
    function getWibNow() {
        const now = new Date();
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        return new Date(utc + (7 * 60 * 60 * 1000));
    }

    function formatTimeInput(date) {
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    function timeToMinutes(time) {
        if (!time) return null;
        const parts = String(time).split(':');
        if (parts.length < 2) return null;
        const hours = Number(parts[0]);
        const minutes = Number(parts[1]);
        if (Number.isNaN(hours) || Number.isNaN(minutes) || hours < 0 || hours > 23 || minutes < 0 || minutes > 59) {
            return null;
        }
        return (hours * 60) + minutes;
    }

    // =========================================================
    // JADWAL & SLOT LOGIC
    // =========================================================
    function getScheduleSlots() {
        const source = (Array.isArray(semuaSlotHariIni) && semuaSlotHariIni.length > 0)
            ? semuaSlotHariIni
            : Object.values(jadwalHariIni || {});

        return source
            .filter(slot => slot && slot.start && slot.end)
            .map(slot => {
                const start = String(slot.start).substring(0, 5);
                const end = String(slot.end).substring(0, 5);
                return {
                    type: slot.type || 'kbm',
                    label: slot.label || 'Jam',
                    start: start,
                    end: end,
                    startMinutes: timeToMinutes(start),
                    endMinutes: timeToMinutes(end)
                };
            })
            .filter(slot => slot.startMinutes !== null && slot.endMinutes !== null && slot.endMinutes > slot.startMinutes)
            .sort((a, b) => a.startMinutes - b.startMinutes);
    }

    function getOperatingScheduleBounds() {
        const slots = getScheduleSlots();
        if (slots.length > 0) {
            return {
                start: slots[0].start,
                end: slots[slots.length - 1].end,
                startMinutes: slots[0].startMinutes,
                endMinutes: slots[slots.length - 1].endMinutes
            };
        }
        return {
            start: '07:00',
            end: '15:15',
            startMinutes: 420,
            endMinutes: 915
        };
    }

    function getAlpineData() {
        try {
            if (window.Alpine && typeof Alpine.$data === 'function' && formDispensasi) {
                return Alpine.$data(formDispensasi);
            }
        } catch (e) {}
        return null;
    }

    let isAlertShown = false;
    function showSingleAlert(config) {
        if (isAlertShown || (typeof Swal !== 'undefined' && Swal.isVisible())) {
            return Promise.resolve();
        }
        isAlertShown = true;
        return Swal.fire(Object.assign({
            confirmButtonColor: '#2563eb',
            returnFocus: false,
            allowOutsideClick: false,
            customClass: {
                popup: 'rounded-2xl shadow-xl border border-slate-200'
            }
        }, config)).finally(() => {
            setTimeout(() => {
                isAlertShown = false;
            }, 300);
        });
    }

    function updateTimeInputLimits() {
        if (!jamKeluar || !jamKembali) return;

        const alpineData = getAlpineData();
        const inputPilihanTanggal = document.querySelector('input[name="pilihan_tanggal"]');
        const pilihanTanggal = (alpineData && alpineData.pilihanTanggal) || (inputPilihanTanggal ? inputPilihanTanggal.value : 'hari_ini');
        const isAdvanceDate = Boolean(pilihanTanggal && pilihanTanggal !== 'hari_ini');

        const nowWib = getWibNow();
        const currentTime = formatTimeInput(nowWib);
        const currentMinutes = timeToMinutes(currentTime);
        const bounds = getOperatingScheduleBounds();

        if (isAdvanceDate) {
            jamKeluar.readOnly = false;
            jamKeluar.min = bounds.start;
            jamKeluar.max = bounds.end;
        } else {
            // Untuk HARI INI:
            if (currentMinutes !== null && currentMinutes >= bounds.endMinutes) {
                jamKeluar.readOnly = true;
                jamKeluar.min = bounds.end;
                jamKeluar.max = bounds.end;
            } else if (currentMinutes !== null && currentMinutes > bounds.startMinutes) {
                jamKeluar.readOnly = false;
                jamKeluar.min = currentTime;
                jamKeluar.max = bounds.end;
            } else {
                jamKeluar.readOnly = false;
                jamKeluar.min = bounds.start;
                jamKeluar.max = bounds.end;
            }
        }

        const isSampaiPulang = alpineData ? alpineData.sampaiPulang : false;
        if (!isSampaiPulang) {
            if (isAdvanceDate) {
                jamKembali.readOnly = false;
            } else if (currentMinutes !== null && currentMinutes >= bounds.endMinutes) {
                jamKembali.readOnly = true;
            } else {
                jamKembali.readOnly = false;
            }

            if (jamKeluar.value) {
                const kMin = timeToMinutes(jamKeluar.value);
                if (kMin !== null) {
                    const nextMin = kMin + 1;
                    const nextH = Math.floor(nextMin / 60);
                    const nextM = nextMin % 60;
                    jamKembali.min = `${String(nextH).padStart(2, '0')}:${String(nextM).padStart(2, '0')}`;
                } else {
                    jamKembali.min = jamKeluar.min;
                }
            } else {
                jamKembali.min = jamKeluar.min;
            }
            jamKembali.max = bounds.end;
        }
    }

    // =========================================================
    // VALIDASI WAKTU KELUAR & KEMBALI
    // =========================================================
    function clearJamKeluarError() {
        if (!jamKeluar) return;
        jamKeluar.classList.remove('border-rose-500', 'bg-rose-50');
        if (selectJamKeluarHour) selectJamKeluarHour.classList.remove('border-rose-500', 'bg-rose-50');
        if (selectJamKeluarMin) selectJamKeluarMin.classList.remove('border-rose-500', 'bg-rose-50');
    }

    function markJamKeluarError() {
        if (!jamKeluar) return;
        jamKeluar.classList.add('border-rose-500', 'bg-rose-50');
        if (selectJamKeluarHour) selectJamKeluarHour.classList.add('border-rose-500', 'bg-rose-50');
        if (selectJamKeluarMin) selectJamKeluarMin.classList.add('border-rose-500', 'bg-rose-50');
    }

    function resetJamKeluarValues() {
        if (!jamKeluar) return;
        jamKeluar.value = '';
        if (selectJamKeluarHour) selectJamKeluarHour.value = '';
        if (selectJamKeluarMin) selectJamKeluarMin.value = '';
        updatePreviewKeluar();
    }

    function clearJamKembaliError() {
        if (!jamKembali) return;
        jamKembali.classList.remove('border-rose-500', 'bg-rose-50');
        if (selectJamKembaliHour) selectJamKembaliHour.classList.remove('border-rose-500', 'bg-rose-50');
        if (selectJamKembaliMin) selectJamKembaliMin.classList.remove('border-rose-500', 'bg-rose-50');
    }

    function markJamKembaliError() {
        if (!jamKembali) return;
        jamKembali.classList.add('border-rose-500', 'bg-rose-50');
        if (selectJamKembaliHour) selectJamKembaliHour.classList.add('border-rose-500', 'bg-rose-50');
        if (selectJamKembaliMin) selectJamKembaliMin.classList.add('border-rose-500', 'bg-rose-50');
    }

    function resetJamKembaliValues() {
        if (!jamKembali) return;
        jamKembali.value = '';
        if (selectJamKembaliHour) selectJamKembaliHour.value = '';
        if (selectJamKembaliMin) selectJamKembaliMin.value = '';
        updatePreviewKembali();
    }

    function validateJamKeluar(triggerAlert = true, isSubmitting = false) {
        if (!jamKeluar) return true;
        const val = jamKeluar.value ? jamKeluar.value.trim() : '';
        const errEl = document.getElementById('jamKeluarError');

        if (errEl) {
            errEl.textContent = '';
            errEl.classList.add('hidden');
            errEl.style.display = 'none';
        }
        clearJamKeluarError();

        if (!val) {
            if (isSubmitting) {
                const msg = 'Jam Keluar wajib diisi.';
                if (errEl) {
                    errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                    errEl.classList.remove('hidden');
                    errEl.style.display = 'block';
                }
                markJamKeluarError();
                if (triggerAlert) {
                    showSingleAlert({
                        icon: 'warning',
                        title: 'Jam Keluar Belum Diisi',
                        text: 'Silakan tentukan jam keluar dispensasi terlebih dahulu.'
                    });
                }
                return false;
            }
            return true;
        }

        const alpineData = getAlpineData();
        const inputPilihanTanggal = document.querySelector('input[name="pilihan_tanggal"]');
        const pilihanTanggal = (alpineData && alpineData.pilihanTanggal) || (inputPilihanTanggal ? inputPilihanTanggal.value : 'hari_ini');
        const isAdvanceDate = Boolean(pilihanTanggal && pilihanTanggal !== 'hari_ini');
        const nowWib = getWibNow();
        const currentTime = formatTimeInput(nowWib);
        const currentMinutes = timeToMinutes(currentTime);
        const selMin = timeToMinutes(val);
        const bounds = getOperatingScheduleBounds();

        // 1. Cek format jam
        if (selMin === null) {
            const msg = 'Format jam keluar tidak valid.';
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
                errEl.style.display = 'block';
            }
            markJamKeluarError();
            resetJamKeluarValues();
            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Format Jam Tidak Valid',
                    text: 'Silakan masukkan format jam keluar yang benar.'
                });
            }
            return false;
        }

        // 2. Cek apakah KBM hari ini sudah berakhir
        if (!isAdvanceDate && currentMinutes !== null && currentMinutes >= bounds.endMinutes) {
            const msg = `Kegiatan Belajar Mengajar (KBM) hari ini (${bounds.start} - ${bounds.end} WIB) telah berakhir.`;
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
                errEl.style.display = 'block';
            }
            markJamKeluarError();
            resetJamKeluarValues();

            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Jadwal Hari Ini Telah Berakhir',
                    html: `Kegiatan Belajar Mengajar (KBM) hari ini (<strong>${bounds.start} - ${bounds.end} WIB</strong>) telah berakhir.<br><span class="text-xs text-slate-500 mt-2 block">Waktu saat ini: <strong>${currentTime} WIB</strong>.<br>Anda tidak dapat memilih jam untuk hari ini yang sudah berlalu. Silakan ajukan untuk tanggal lain.</span>`,
                    confirmButtonText: 'Mengerti'
                });
            }
            updateTimeInputLimits();
            return false;
        }

        // 3. Cek apakah jam keluar berada di luar jadwal KBM sekolah
        if (selMin < bounds.startMinutes || selMin > bounds.endMinutes) {
            const msg = `Jam Keluar (${val} WIB) berada di luar jadwal KBM sekolah (${bounds.start} - ${bounds.end} WIB).`;
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
                errEl.style.display = 'block';
            }
            markJamKeluarError();
            resetJamKeluarValues();

            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Di Luar Jadwal Sekolah',
                    html: `Jam Keluar (<strong>${val} WIB</strong>) berada di luar jadwal KBM sekolah hari ini.<br><span class="text-xs text-slate-500 mt-2 block">Jam operasional KBM sekolah: <strong>${bounds.start} - ${bounds.end} WIB</strong>.<br>Silakan periksa tombol <strong>Lihat Jadwal</strong> untuk melihat slot jam aktif.</span>`,
                    confirmButtonText: 'Pilih Jam Sesuai Jadwal'
                });
            }
            updateTimeInputLimits();
            return false;
        }

        // 4. Cek apakah jam sudah lewat dari jam saat ini (jika dispensasi untuk hari ini)
        if (!isAdvanceDate && currentMinutes !== null && selMin < currentMinutes) {
            const msg = `Jam Keluar (${val} WIB) sudah lewat dari waktu saat ini (${currentTime} WIB).`;
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
                errEl.style.display = 'block';
            }
            markJamKeluarError();
            resetJamKeluarValues();

            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Jam Tidak Valid',
                    html: `Jam Keluar (<strong>${val} WIB</strong>) tidak dapat dipilih karena waktu tersebut sudah lewat.<br><span class="text-xs text-slate-500 mt-2 block">Waktu saat ini: <strong>${currentTime} WIB</strong>. Anda tidak dapat memilih jam yang sudah berlalu.</span>`,
                    confirmButtonText: 'Pilih Jam Lain'
                });
            }
            updateTimeInputLimits();
            return false;
        }

        clearJamKeluarError();
        updatePreviewKeluar();
        updateTimeInputLimits();
        return true;
    }

    function validateJamKembali(triggerAlert = true, isSubmitting = false) {
        if (!jamKembali) return true;
        const alpineData = getAlpineData();
        const isSampaiPulang = alpineData ? alpineData.sampaiPulang : false;
        if (isSampaiPulang) {
            const errEl = document.getElementById('jamKembaliError');
            if (errEl) {
                errEl.textContent = '';
                errEl.classList.add('hidden');
                errEl.style.display = 'none';
            }
            clearJamKembaliError();
            return true;
        }

        const val = jamKembali.value ? jamKembali.value.trim() : '';
        const errEl = document.getElementById('jamKembaliError');

        if (errEl) {
            errEl.textContent = '';
            errEl.classList.add('hidden');
            errEl.style.display = 'none';
        }
        clearJamKembaliError();

        if (!val) {
            if (isSubmitting) {
                const msg = 'Jam Kembali wajib diisi jika bukan izin sampai pulang.';
                if (errEl) {
                    errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                    errEl.classList.remove('hidden');
                    errEl.style.display = 'block';
                }
                markJamKembaliError();
                if (triggerAlert) {
                    showSingleAlert({
                        icon: 'warning',
                        title: 'Jam Kembali Belum Diisi',
                        text: 'Silakan tentukan jam kembali dispensasi atau aktifkan opsi Izin Sampai Jam Pulang Sekolah.'
                    });
                }
                return false;
            }
            return true;
        }

        const inputPilihanTanggal = document.querySelector('input[name="pilihan_tanggal"]');
        const pilihanTanggal = (alpineData && alpineData.pilihanTanggal) || (inputPilihanTanggal ? inputPilihanTanggal.value : 'hari_ini');
        const isAdvanceDate = Boolean(pilihanTanggal && pilihanTanggal !== 'hari_ini');
        const nowWib = getWibNow();
        const currentTime = formatTimeInput(nowWib);
        const currentMinutes = timeToMinutes(currentTime);
        const keluarVal = jamKeluar && jamKeluar.value ? jamKeluar.value.trim() : '';
        const keluarMin = timeToMinutes(keluarVal);
        const kembaliMin = timeToMinutes(val);
        const bounds = getOperatingScheduleBounds();

        // 1. Cek apakah KBM hari ini sudah berakhir
        if (!isAdvanceDate && currentMinutes !== null && currentMinutes >= bounds.endMinutes) {
            const msg = `Kegiatan Belajar Mengajar (KBM) hari ini (${bounds.start} - ${bounds.end} WIB) telah berakhir.`;
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
                errEl.style.display = 'block';
            }
            markJamKembaliError();
            resetJamKembaliValues();

            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Jadwal Hari Ini Telah Berakhir',
                    html: `Kegiatan Belajar Mengajar (KBM) hari ini (<strong>${bounds.start} - ${bounds.end} WIB</strong>) telah berakhir.<br><span class="text-xs text-slate-500 mt-2 block">Waktu saat ini: <strong>${currentTime} WIB</strong>.</span>`,
                    confirmButtonText: 'Mengerti'
                });
            }
            return false;
        }

        // 2. Pastikan Jam Keluar sudah diisi terlebih dahulu
        if (!keluarVal || keluarMin === null) {
            const msg = 'Silakan tentukan Jam Keluar terlebih dahulu sebelum memilih Jam Kembali.';
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
                errEl.style.display = 'block';
            }
            markJamKembaliError();
            resetJamKembaliValues();

            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Pilih Jam Keluar Dulu',
                    html: 'Silakan tentukan <strong>Jam Keluar</strong> terlebih dahulu sebelum memilih Jam Kembali.'
                });
            }
            return false;
        }

        // 3. Cek format jam
        if (kembaliMin === null) {
            const msg = 'Format jam kembali tidak valid.';
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
                errEl.style.display = 'block';
            }
            markJamKembaliError();
            resetJamKembaliValues();
            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Format Jam Tidak Valid',
                    text: 'Silakan masukkan format jam kembali yang benar.'
                });
            }
            return false;
        }

        // 4. Cek apakah jam kembali berada di luar jadwal sekolah
        if (kembaliMin < bounds.startMinutes || kembaliMin > bounds.endMinutes) {
            const msg = `Jam Kembali (${val} WIB) berada di luar jadwal KBM sekolah (${bounds.start} - ${bounds.end} WIB).`;
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
                errEl.style.display = 'block';
            }
            markJamKembaliError();
            resetJamKembaliValues();

            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Di Luar Jadwal Sekolah',
                    html: `Jam Kembali (<strong>${val} WIB</strong>) berada di luar jadwal KBM sekolah hari ini.<br><span class="text-xs text-slate-500 mt-2 block">Jam operasional KBM sekolah: <strong>${bounds.start} - ${bounds.end} WIB</strong>.<br>Jika izin hingga KBM berakhir, aktifkan opsi <strong>Izin Sampai Jam Pulang Sekolah</strong>.</span>`,
                    confirmButtonText: 'Pilih Jam Sesuai Jadwal'
                });
            }
            return false;
        }

        // 5. Cek apakah jam kembali <= jam keluar
        if (kembaliMin <= keluarMin) {
            const msg = `Jam Kembali (${val} WIB) harus lebih besar daripada Jam Keluar (${keluarVal} WIB).`;
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
                errEl.style.display = 'block';
            }
            markJamKembaliError();
            resetJamKembaliValues();

            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Jam Kembali Tidak Sesuai',
                    html: `Jam Kembali (<strong>${val} WIB</strong>) harus lebih besar daripada Jam Keluar (<strong>${keluarVal} WIB</strong>).`,
                    confirmButtonText: 'Perbaiki Jam Kembali'
                });
            }
            return false;
        }

        // 6. Cek apakah jam kembali sudah lewat untuk hari ini
        if (!isAdvanceDate && currentMinutes !== null && kembaliMin < currentMinutes) {
            const msg = `Jam Kembali (${val} WIB) sudah lewat dari waktu saat ini (${currentTime} WIB).`;
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
                errEl.style.display = 'block';
            }
            markJamKembaliError();
            resetJamKembaliValues();

            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Jam Tidak Valid',
                    html: `Jam Kembali (<strong>${val} WIB</strong>) tidak dapat dipilih karena waktu tersebut sudah lewat.<br><span class="text-xs text-slate-500 mt-2 block">Waktu saat ini: <strong>${currentTime} WIB</strong>.</span>`,
                    confirmButtonText: 'Perbaiki Jam Kembali'
                });
            }
            return false;
        }

        clearJamKembaliError();
        updatePreviewKembali();
        updateTimeInputLimits();
        return true;
    }

    // =========================================================
    // SINKRONISASI PICKER WAKTU 24 JAM (WIB)
    // =========================================================
    function getIndonesianTimeDescription(h) {
        const hourNum = parseInt(h, 10);
        if (isNaN(hourNum)) return '';
        if (hourNum >= 0 && hourNum <= 4) return 'Dini Hari';
        if (hourNum >= 5 && hourNum <= 10) return `${hourNum} Pagi`;
        if (hourNum === 11) return '11 Siang';
        if (hourNum === 12) return '12 Siang';
        if (hourNum >= 13 && hourNum <= 14) return `${hourNum - 12} Siang (${hourNum}:00)`;
        if (hourNum >= 15 && hourNum <= 18) return `${hourNum - 12} Sore (${hourNum}:00)`;
        return `${hourNum - 12} Malam (${hourNum}:00)`;
    }

    function updatePreviewKeluar() {
        if (!previewJamKeluar) return;
        const val = jamKeluar ? jamKeluar.value : '';
        if (val && val.includes(':')) {
            const [h, m] = val.split(':');
            const desc = getIndonesianTimeDescription(h);
            previewJamKeluar.innerHTML = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800 font-bold text-xs border border-blue-200">
                <i class="ti ti-clock-check text-blue-600"></i> Terpilih: <b>${val} WIB</b> <span class="font-medium text-blue-600 text-[11px]">(${desc})</span>
            </span>`;
        } else {
            previewJamKeluar.innerHTML = `<span class="text-slate-500 text-[11px] flex items-center gap-1">
                <i class="ti ti-info-circle text-slate-400"></i> Format 24 Jam (contoh: 07:15 atau 13:30)
            </span>`;
        }
    }

    function updatePreviewKembali() {
        if (!previewJamKembali) return;
        const val = jamKembali ? jamKembali.value : '';
        if (val && val.includes(':')) {
            const [h, m] = val.split(':');
            const desc = getIndonesianTimeDescription(h);
            previewJamKembali.innerHTML = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-bold text-xs border border-emerald-200">
                <i class="ti ti-clock-check text-emerald-600"></i> Terpilih: <b>${val} WIB</b> <span class="font-medium text-emerald-600 text-[11px]">(${desc})</span>
            </span>`;
        } else {
            previewJamKembali.innerHTML = `<span class="text-slate-500 text-[11px] flex items-center gap-1">
                <i class="ti ti-info-circle text-slate-400"></i> Format 24 Jam (contoh: 08:30 atau 14:00)
            </span>`;
        }
    }

    function syncJamKeluarFromSelects(triggerValidation = true) {
        if (!jamKeluar) return;
        const h = selectJamKeluarHour ? selectJamKeluarHour.value : '';
        let m = selectJamKeluarMin ? selectJamKeluarMin.value : '';

        if (h && !m) {
            m = '00';
            if (selectJamKeluarMin) selectJamKeluarMin.value = '00';
        }

        if (h && m) {
            jamKeluar.value = `${h}:${m}`;
            updatePreviewKeluar();
            if (triggerValidation) {
                validateJamKeluar(true);
                if (jamKembali && jamKembali.value) {
                    validateJamKembali(false);
                }
            }
        } else {
            jamKeluar.value = '';
            updatePreviewKeluar();
        }
    }

    function syncJamKembaliFromSelects(triggerValidation = true) {
        if (!jamKembali) return;
        const h = selectJamKembaliHour ? selectJamKembaliHour.value : '';
        let m = selectJamKembaliMin ? selectJamKembaliMin.value : '';

        if (h && !m) {
            m = '00';
            if (selectJamKembaliMin) selectJamKembaliMin.value = '00';
        }

        if (h && m) {
            jamKembali.value = `${h}:${m}`;
            updatePreviewKembali();
            if (triggerValidation) {
                validateJamKembali(true);
            }
        } else {
            jamKembali.value = '';
            updatePreviewKembali();
        }
    }

    if (selectJamKeluarHour) {
        selectJamKeluarHour.addEventListener('change', () => syncJamKeluarFromSelects(true));
    }
    if (selectJamKeluarMin) {
        selectJamKeluarMin.addEventListener('change', () => syncJamKeluarFromSelects(true));
    }
    if (selectJamKembaliHour) {
        selectJamKembaliHour.addEventListener('change', () => syncJamKembaliFromSelects(true));
    }
    if (selectJamKembaliMin) {
        selectJamKembaliMin.addEventListener('change', () => syncJamKembaliFromSelects(true));
    }

    document.querySelectorAll('.btn-quick-minute-keluar').forEach(btn => {
        btn.addEventListener('click', function () {
            const min = this.dataset.minute;
            if (selectJamKeluarMin) {
                selectJamKeluarMin.value = min;
                if (selectJamKeluarHour && !selectJamKeluarHour.value) {
                    const bounds = getOperatingScheduleBounds();
                    const defaultH = bounds.start.split(':')[0] || '07';
                    selectJamKeluarHour.value = defaultH;
                }
                syncJamKeluarFromSelects(true);
            }
        });
    });

    document.querySelectorAll('.btn-quick-durasi').forEach(btn => {
        btn.addEventListener('click', function () {
            const duration = parseInt(this.dataset.duration, 10);
            if (!jamKeluar || !jamKeluar.value) {
                showSingleAlert({
                    icon: 'info',
                    title: 'Pilih Jam Keluar Dahulu',
                    text: 'Silakan tentukan Jam Keluar terlebih dahulu sebelum menggunakan tombol durasi cepat.'
                });
                return;
            }
            const keluarMin = timeToMinutes(jamKeluar.value);
            if (keluarMin === null) return;
            const targetMin = keluarMin + duration;
            const targetH = Math.floor(targetMin / 60);
            const targetM = targetMin % 60;
            const targetHStr = String(targetH).padStart(2, '0');
            const targetMStr = String(targetM).padStart(2, '0');

            if (selectJamKembaliHour) selectJamKembaliHour.value = targetHStr;
            if (selectJamKembaliMin) selectJamKembaliMin.value = targetMStr;
            syncJamKembaliFromSelects(true);
        });
    });

    // Helper untuk memilih slot langsung dari Modal Jadwal
    document.querySelectorAll('.btn-pilih-slot-jadwal').forEach(btn => {
        btn.addEventListener('click', function () {
            const start = this.dataset.start;
            const end = this.dataset.end;
            const label = this.dataset.label || '';

            if (start && start.includes(':')) {
                const [sh, sm] = start.split(':');
                if (selectJamKeluarHour) selectJamKeluarHour.value = sh;
                if (selectJamKeluarMin) selectJamKeluarMin.value = sm;
                syncJamKeluarFromSelects(false);
            }

            if (end && end.includes(':')) {
                const [eh, em] = end.split(':');
                if (selectJamKembaliHour) selectJamKembaliHour.value = eh;
                if (selectJamKembaliMin) selectJamKembaliMin.value = em;
                syncJamKembaliFromSelects(true);
            }

            tutupModalJadwal();

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: `${label || 'Slot Jadwal'} (${start} - ${end} WIB) diterapkan.`,
                showConfirmButton: false,
                timer: 2500
            });
        });
    });

    // Inisialisasi tampilan preview pada awal render
    updatePreviewKeluar();
    updatePreviewKembali();

    // =========================================================
    // KAMERA SELFIE & VERIFIKASI IDENTITAS (1 TOMBOL TERPADU)
    // =========================================================
    let selfieStream = null;
    let currentFacingModeSelfie = 'user';
    let currentObjectUrl = null;

    function cleanupObjectUrl() {
        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
            currentObjectUrl = null;
        }
    }

    function stopSelfieCamera() {
        if (selfieStream) {
            selfieStream.getTracks().forEach(track => {
                try { track.stop(); } catch (err) {}
            });
            selfieStream = null;
        }
        if (selfieVideo) {
            selfieVideo.srcObject = null;
        }
        if (selfieCameraLoading) {
            selfieCameraLoading.classList.add('hidden');
        }
    }

    function startSelfieCamera(facingMode = 'user') {
        stopSelfieCamera();
        if (selfieErrorBox) selfieErrorBox.classList.add('hidden');
        if (selfieStandbyBox) selfieStandbyBox.classList.add('hidden');
        if (selfiePreviewBox) selfiePreviewBox.classList.add('hidden');
        if (selfieCameraBox) selfieCameraBox.classList.remove('hidden');
        if (selfieCameraLoading) selfieCameraLoading.classList.remove('hidden');

        const isSecureEnv = (window.isSecureContext === true) || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || !isSecureEnv) {
            console.warn('getUserMedia live kamera tidak aktif atau tidak didukung pada konteks browser ini.');
            stopSelfieCamera();
            if (selfieCameraBox) selfieCameraBox.classList.add('hidden');
            if (selfieStandbyBox) selfieStandbyBox.classList.remove('hidden');
            if (selfieErrorBox) selfieErrorBox.classList.remove('hidden');
            if (fotoInput) {
                fotoInput.click();
            }
            return;
        }

        const constraints = {
            video: {
                facingMode: { ideal: facingMode },
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        };

        function attachStream(stream) {
            selfieStream = stream;
            if (selfieVideo) {
                selfieVideo.srcObject = stream;
                selfieVideo.onloadedmetadata = function () {
                    selfieVideo.play().catch(function () {});
                    if (selfieCameraLoading) selfieCameraLoading.classList.add('hidden');
                };
            }
        }

        navigator.mediaDevices.getUserMedia(constraints)
            .then(attachStream)
            .catch(function (err) {
                console.warn('Constraint ideal kamera gagal, mencoba fallback standard video:true', err);
                navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                    .then(attachStream)
                    .catch(function (fallbackErr) {
                        console.warn('Kamera live tidak dapat dibuka, mengalihkan ke kamera bawaan perangkat:', fallbackErr);
                        stopSelfieCamera();
                        if (selfieCameraBox) selfieCameraBox.classList.add('hidden');
                        if (selfieStandbyBox) selfieStandbyBox.classList.remove('hidden');
                        if (selfieErrorBox) selfieErrorBox.classList.remove('hidden');

                        Swal.fire({
                            icon: 'info',
                            title: 'Buka Kamera Perangkat',
                            text: 'Live kamera tidak dapat diakses langsung pada browser ini. Silakan gunakan kamera bawaan perangkat Anda.',
                            confirmButtonColor: '#2563eb',
                            confirmButtonText: '<i class="ti ti-camera mr-1.5"></i> Buka Kamera'
                        }).then(function (result) {
                            if (result.isConfirmed && fotoInput) {
                                fotoInput.click();
                            }
                        });
                    });
            });
    }

    async function processImageFileAndWatermark(file) {
        if (!file) return;

        Swal.fire({
            title: 'Memproses Foto...',
            text: 'Membubuhkan watermark verifikasi identitas resmi.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const img = new Image();
            const objectUrl = URL.createObjectURL(file);

            await new Promise((resolve, reject) => {
                img.onload = resolve;
                img.onerror = reject;
                img.src = objectUrl;
            });
            URL.revokeObjectURL(objectUrl);

            let maxDim = 1280;
            let width = img.naturalWidth || img.width || 640;
            let height = img.naturalHeight || img.height || 480;

            if (width > maxDim || height > maxDim) {
                if (width > height) {
                    height = Math.round((height * maxDim) / width);
                    width = maxDim;
                } else {
                    width = Math.round((width * maxDim) / height);
                    height = maxDim;
                }
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            const nowWib = getWibNow();
            const pad = n => String(n).padStart(2, '0');
            const dateStr = `${pad(nowWib.getDate())}/${pad(nowWib.getMonth() + 1)}/${nowWib.getFullYear()}`;
            const timeStr = `${pad(nowWib.getHours())}:${pad(nowWib.getMinutes())}:${pad(nowWib.getSeconds())} WIB`;
            const studentName = @json($siswa->nama_lengkap);
            const studentNis = @json($siswa->user->nis_nip ?? ($siswa->nis ?? ''));
            const className = @json($siswa->kelas->nama_kelas ?? '');

            if (typeof window.drawDispensasiWatermark === 'function') {
                window.drawDispensasiWatermark(ctx, canvas, [
                    'DIDISPEN VERIFIKASI',
                    `${studentName} (${studentNis})`,
                    `${className} • ${dateStr} ${timeStr}`,
                ]);
            }

            canvas.toBlob(function (blob) {
                Swal.close();
                if (!blob) {
                    Swal.fire('Gagal Memproses Foto', 'Gagal memproses file foto selfie.', 'error');
                    return;
                }

                const watermarkedFile = new File([blob], `selfie_${Date.now()}.jpg`, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(watermarkedFile);
                if (fotoInput) fotoInput.files = dataTransfer.files;

                cleanupObjectUrl();
                currentObjectUrl = URL.createObjectURL(blob);
                if (selfiePreviewImg) selfiePreviewImg.src = currentObjectUrl;

                if (selfieTimestampText) {
                    selfieTimestampText.textContent = `${nowWib.toLocaleDateString('id-ID')} ${formatTimeInput(nowWib)} WIB`;
                }

                if (selfieStandbyBox) selfieStandbyBox.classList.add('hidden');
                if (selfieCameraBox) selfieCameraBox.classList.add('hidden');
                if (selfieErrorBox) selfieErrorBox.classList.add('hidden');
                if (selfiePreviewBox) selfiePreviewBox.classList.remove('hidden');
                stopSelfieCamera();
            }, 'image/jpeg', 0.85);

        } catch (err) {
            Swal.close();
            console.error('Error saat memproses foto selfie:', err);
            Swal.fire('Gagal Memproses Foto', 'Terjadi kesalahan saat memproses foto. Silakan ambil foto ulang.', 'error');
        }
    }

    if (fotoInput) {
        fotoInput.addEventListener('change', function () {
            if (fotoInput.files && fotoInput.files.length > 0) {
                const file = fotoInput.files[0];
                if (file.name.startsWith('selfie_') && file.type === 'image/jpeg') {
                    return;
                }
                processImageFileAndWatermark(file);
            }
        });
    }

    if (btnStartSelfie) {
        btnStartSelfie.addEventListener('click', function () {
            const isSecureEnv = (window.isSecureContext === true) || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
            const hasGetUserMedia = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);

            if (hasGetUserMedia && isSecureEnv) {
                startSelfieCamera(currentFacingModeSelfie);
            } else {
                if (fotoInput) {
                    fotoInput.click();
                }
            }
        });
    }

    if (btnFallbackNativeCamera) {
        btnFallbackNativeCamera.addEventListener('click', function () {
            if (fotoInput) fotoInput.click();
        });
    }

    if (btnCancelSelfie) {
        btnCancelSelfie.addEventListener('click', function () {
            stopSelfieCamera();
            if (selfieCameraBox) selfieCameraBox.classList.add('hidden');
            if (selfieStandbyBox) selfieStandbyBox.classList.remove('hidden');
        });
    }

    if (btnFlipSelfie) {
        btnFlipSelfie.addEventListener('click', function () {
            currentFacingModeSelfie = currentFacingModeSelfie === 'user' ? 'environment' : 'user';
            startSelfieCamera(currentFacingModeSelfie);
        });
    }

    if (btnSnapSelfie) {
        btnSnapSelfie.addEventListener('click', function () {
            if (!selfieVideo || !selfieStream) return;

            const canvas = document.createElement('canvas');
            const videoWidth = selfieVideo.videoWidth || 640;
            const videoHeight = selfieVideo.videoHeight || 480;
            canvas.width = videoWidth;
            canvas.height = videoHeight;
            const ctx = canvas.getContext('2d');

            if (currentFacingModeSelfie === 'user') {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }
            ctx.drawImage(selfieVideo, 0, 0, canvas.width, canvas.height);

            // Watermark Timestamp & Identitas Resmi DIDISPEN
            ctx.setTransform(1, 0, 0, 1, 0, 0);

            const nowWib = getWibNow();
            const pad = n => String(n).padStart(2, '0');
            const dateStr = `${pad(nowWib.getDate())}/${pad(nowWib.getMonth() + 1)}/${nowWib.getFullYear()}`;
            const timeStr = `${pad(nowWib.getHours())}:${pad(nowWib.getMinutes())}:${pad(nowWib.getSeconds())} WIB`;
            const studentName = @json($siswa->nama_lengkap);
            const studentNis = @json($siswa->user->nis_nip ?? ($siswa->nis ?? ''));
            const className = @json($siswa->kelas->nama_kelas ?? '');

            if (typeof window.drawDispensasiWatermark === 'function') {
                window.drawDispensasiWatermark(ctx, canvas, [
                    'DIDISPEN VERIFIKASI',
                    `${studentName} (${studentNis})`,
                    `${className} • ${dateStr} ${timeStr}`,
                ]);
            }

            canvas.toBlob(function (blob) {
                if (!blob) return;

                if (blob.size > 2048 * 1024) {
                    Swal.fire('Foto Terlalu Besar', 'Ukuran foto selfie melebihi batas maksimal 2MB. Silakan ambil ulang.', 'warning');
                    return;
                }

                const file = new File([blob], `selfie_${Date.now()}.jpg`, { type: 'image/jpeg', lastModified: Date.now() });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                if (fotoInput) fotoInput.files = dataTransfer.files;

                cleanupObjectUrl();
                currentObjectUrl = URL.createObjectURL(blob);
                if (selfiePreviewImg) selfiePreviewImg.src = currentObjectUrl;

                if (selfieTimestampText) {
                    selfieTimestampText.textContent = `${nowWib.toLocaleDateString('id-ID')} ${formatTimeInput(nowWib)} WIB`;
                }

                if (selfieCameraBox) selfieCameraBox.classList.add('hidden');
                if (selfiePreviewBox) selfiePreviewBox.classList.remove('hidden');
                stopSelfieCamera();
            }, 'image/jpeg', 0.85);
        });
    }

    if (btnRetakeSelfie) {
        btnRetakeSelfie.addEventListener('click', function () {
            cleanupObjectUrl();
            if (fotoInput) fotoInput.value = '';
            if (selfiePreviewImg) selfiePreviewImg.src = '';
            if (selfiePreviewBox) selfiePreviewBox.classList.add('hidden');
            if (selfieStandbyBox) selfieStandbyBox.classList.remove('hidden');
            if (btnStartSelfie) btnStartSelfie.click();
        });
    }

    window.addEventListener('beforeunload', function () {
        cleanupObjectUrl();
        stopSelfieCamera();
    });
    window.addEventListener('pagehide', function () {
        cleanupObjectUrl();
        stopSelfieCamera();
    });

    // =========================================================
    // MODAL JADWAL PELAJARAN
    // =========================================================
    function formatHariTanggalWib() {
        try {
            return new Intl.DateTimeFormat('id-ID', {
                timeZone: 'Asia/Jakarta',
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }).format(new Date());
        } catch (e) {
            return 'Jadwal Hari Ini';
        }
    }

    let modalCloseTimeout = null;

    function bukaModalJadwal() {
        if (!modalJadwal) return;
        if (modalCloseTimeout) {
            clearTimeout(modalCloseTimeout);
            modalCloseTimeout = null;
        }
        if (modalJadwalHari) modalJadwalHari.textContent = formatHariTanggalWib();

        modalJadwal.classList.remove('hidden');
        document.documentElement.classList.add('overflow-hidden');
        document.body.classList.add('overflow-hidden', 'modal-open', 'modal-jadwal-open');

        requestAnimationFrame(function () {
            modalJadwal.classList.remove('opacity-0');
            if (modalJadwalOverlay) modalJadwalOverlay.classList.remove('opacity-0');
            if (modalJadwalContent) {
                modalJadwalContent.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
                modalJadwalContent.classList.add('opacity-100', 'scale-100', 'translate-y-0');
            }
        });
    }

    function tutupModalJadwal() {
        if (!modalJadwal || modalJadwal.classList.contains('hidden')) return;

        modalJadwal.classList.add('opacity-0');
        if (modalJadwalOverlay) modalJadwalOverlay.classList.add('opacity-0');
        if (modalJadwalContent) {
            modalJadwalContent.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
            modalJadwalContent.classList.add('opacity-0', 'scale-95', 'translate-y-2');
        }

        if (modalCloseTimeout) clearTimeout(modalCloseTimeout);
        modalCloseTimeout = setTimeout(function () {
            modalJadwal.classList.add('hidden');
            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden', 'modal-open', 'modal-jadwal-open');
        }, 200);
    }

    const triggerLihatJadwalButtons = [btnLihatJadwal, btnLihatJadwalHeader, btnLihatJadwalPageHeader];
    triggerLihatJadwalButtons.forEach(btn => {
        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                bukaModalJadwal();
            });
        }
    });

    if (btnTutupJadwal) {
        btnTutupJadwal.addEventListener('click', tutupModalJadwal);
    }

    if (modalJadwalOverlay) {
        modalJadwalOverlay.addEventListener('click', tutupModalJadwal);
    }

    if (modalJadwalWrapper) {
        modalJadwalWrapper.addEventListener('click', function (e) {
            if (e.target === modalJadwalWrapper) {
                tutupModalJadwal();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modalJadwal && !modalJadwal.classList.contains('hidden')) {
            tutupModalJadwal();
        }
    });

    // =========================================================
    // COUNTER ALASAN (0 / 500)
    // =========================================================
    if (alasan) {
        function updateCharCounter() {
            const length = alasan.value.length;
            if (charCount) charCount.textContent = length;
            if (charCounter) {
                if (length < 10 || length > 500) {
                    charCounter.classList.remove('text-emerald-600');
                    charCounter.classList.add('text-rose-500');
                } else {
                    charCounter.classList.remove('text-rose-500');
                    charCounter.classList.add('text-emerald-600');
                }
            }
        }
        alasan.addEventListener('input', updateCharCounter);
        updateCharCounter();
    }

    // =========================================================
    // VALIDASI FORM SUBMIT & LOADING STATE
    // =========================================================
    if (formDispensasi) {
        function resetLoadingState() {
            delete formDispensasi.dataset.submitting;
            const alpineData = getAlpineData();
            if (alpineData) {
                alpineData.loading = false;
            }
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }

        formDispensasi.addEventListener('submit', function (e) {
            const alpineData = getAlpineData();
            const isSampaiPulang = alpineData ? alpineData.sampaiPulang : false;
            const isRombongan = alpineData ? (alpineData.tipePengajuan === 'rombongan' || alpineData.tipePengajuan === 'organisasi') : false;

            if (!validateJamKeluar(true, true)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                resetLoadingState();
                return;
            }

            if (!isSampaiPulang && !validateJamKembali(true, true)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                resetLoadingState();
                return;
            }

            if (isRombongan) {
                const anggotaCount = alpineData?.selectedAnggota?.length || 0;
                if (anggotaCount < 1) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    resetLoadingState();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Anggota Rombongan Kosong',
                        text: 'Pilih minimal 1 siswa anggota rombongan untuk pengajuan dispensasi rombongan.',
                        confirmButtonColor: '#2563eb'
                    });
                    return;
                }
            }

            if (!fotoInput || !fotoInput.files || fotoInput.files.length === 0) {
                e.preventDefault();
                e.stopImmediatePropagation();
                resetLoadingState();
                Swal.fire({
                    icon: 'warning',
                    title: 'Foto Selfie Diperlukan',
                    text: 'Harap ambil foto selfie verifikasi langsung menggunakan kamera terlebih dahulu.',
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: '<i class="ti ti-camera mr-1.5"></i> Buka Kamera'
                }).then(function () {
                    if (btnStartSelfie && !btnStartSelfie.disabled) {
                        btnStartSelfie.click();
                    }
                    if (selfieStandbyBox) {
                        selfieStandbyBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
                return;
            }

            if (!formDispensasi.checkValidity()) {
                e.preventDefault();
                formDispensasi.reportValidity();
                resetLoadingState();
                return;
            }

            if (formDispensasi.dataset.confirmed !== 'true') {
                e.preventDefault();
                e.stopImmediatePropagation();
                resetLoadingState();

                const orgInfo = isRombongan
                    ? `<div class="p-2.5 bg-blue-50 border border-blue-200 rounded-xl text-blue-900 text-xs">
                         <span class="font-bold"><i class="ti ti-users mr-1"></i>Dispensasi Rombongan</span><br>
                         <span class="text-blue-700">Total Siswa: <b>${(alpineData.selectedAnggota?.length || 0) + 1} Orang</b> (1 Pemohon + ${alpineData.selectedAnggota?.length || 0} Anggota)</span>
                       </div>`
                    : '';

                const kepulanganInfo = isSampaiPulang
                    ? `<span class="text-emerald-700 font-bold"><i class="ti ti-door-exit mr-1"></i>Sampai Pulang Sekolah (Bebas Check-In)</span>`
                    : `<span>Jam Kembali: <b>${jamKembali?.value || '-'} WIB</b></span>`;

                Swal.fire({
                    title: 'Ajukan Dispensasi Sekarang?',
                    html: `
                        <div class="text-left text-xs sm:text-sm text-slate-600 space-y-3">
                            ${orgInfo}
                            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs">
                                <div>Jam Keluar: <b>${jamKeluar?.value || '-'} WIB</b></div>
                                <div>${kepulanganInfo}</div>
                            </div>
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-xs leading-relaxed">
                                <p class="font-bold mb-1 flex items-center gap-1.5 text-amber-800">
                                    <i class="ti ti-alert-triangle text-amber-600 text-sm"></i> PERHATIKAN ATURAN:
                                </p>
                                <ul class="list-disc list-inside space-y-1 text-amber-800 mt-1">
                                    <li>Setelah diajukan, permohonan <b>tidak dapat dibatalkan sendiri</b> oleh siswa.</li>
                                    <li>Anda <b>wajib menemui Guru Piket</b> untuk verifikasi dan persetujuan.</li>
                                    <li>QR Code hanya sah berlaku pada tanggal pelaksanaan izin yang dipilih.</li>
                                </ul>
                            </div>
                            <p class="text-slate-700 text-xs">Pastikan seluruh data dan foto selfie yang Anda sertakan sudah benar.</p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="ti ti-send mr-1.5"></i> Ya, Ajukan Sekarang',
                    cancelButtonText: 'Periksa Kembali',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-slate-200'
                    }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        formDispensasi.dataset.confirmed = 'true';
                        formDispensasi.requestSubmit();
                    } else {
                        delete formDispensasi.dataset.confirmed;
                        resetLoadingState();
                    }
                });
                return;
            }

            if (formDispensasi.dataset.submitting === 'true') {
                e.preventDefault();
                return;
            }
            formDispensasi.dataset.submitting = 'true';

            if (alpineData) {
                alpineData.loading = true;
            }
            if (submitBtn) {
                submitBtn.disabled = true;
            }
        });
    }

    // =========================================================
    // CEK JADWAL OPERASIONAL PENGATURAN SEKOLAH
    // =========================================================
    function checkDispensasiTime() {
        const now = getWibNow();
        const dayOfWeek = now.getDay();
        const hours = now.getHours();
        const minutes = now.getMinutes();
        const currentMinutes = (hours * 60) + minutes;
        const currentTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;

        const banner = document.getElementById('timeWarningBanner');
        const message = document.getElementById('timeWarningMessage');
        const timeDisplay = document.getElementById('currentTimeDisplay');
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        if (timeDisplay) {
            timeDisplay.textContent = `${days[dayOfWeek]}, ${currentTime} WIB`;
        }

        let isAllowed = true;
        let restrictionMsg = '';

        if (!settings.allowed_days || !settings.allowed_days.includes(dayOfWeek)) {
            isAllowed = false;
            restrictionMsg = 'Pengajuan dispensasi tidak diizinkan pada hari ini berdasarkan pengaturan sekolah.';
        } else {
            const startMinutes = timeToMinutes(settings.start_time);
            const endTime = dayOfWeek === 5 ? settings.end_time_friday : settings.end_time;
            const endMinutes = timeToMinutes(endTime);

            if (currentMinutes < startMinutes || currentMinutes > endMinutes) {
                isAllowed = false;
                restrictionMsg = `Pengajuan dispensasi hanya dapat dilakukan pada pukul <strong>${settings.start_time} - ${endTime} WIB</strong>.`;
            }
        }

        function updateAlpineDispensasiState(closed) {
            const alpineEl = document.getElementById('formDispensasi');
            if (alpineEl && window.Alpine) {
                try {
                    const data = Alpine.$data(alpineEl);
                    if (data) {
                        data.isDispensasiClosed = closed;
                        if (closed) {
                            data.showGuruDropdown = false;
                        }
                    }
                } catch (e) {}
            }
        }

        const isExemptControl = function (el) {
            if (!el) return true;
            if (el.id === 'foto_verifikasi_input') return true;
            if (el.id === 'btnLihatJadwalPageHeader' || el.id === 'btnLihatJadwalHeader' || el.id === 'btnLihatJadwal') return true;
            if (el.closest('.tipe-pengajuan-selector')) return true;
            return false;
        };

        if (isAllowed) {
            if (banner) banner.classList.add('hidden');
            updateAlpineDispensasiState(false);
            if (formDispensasi) {
                formDispensasi.querySelectorAll('input, select, textarea, button').forEach(function (el) {
                    if (isExemptControl(el)) return;
                    el.disabled = false;
                    el.classList.remove('opacity-50', 'cursor-not-allowed');
                });
            }
            [btnStartSelfie, btnFallbackNativeCamera, btnSnapSelfie, btnCancelSelfie, btnFlipSelfie, btnRetakeSelfie].forEach(function (b) {
                if (b) {
                    b.disabled = false;
                    b.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });
            updateTimeInputLimits();
            return;
        }

        if (banner) banner.classList.remove('hidden');
        if (message) message.innerHTML = restrictionMsg;

        updateAlpineDispensasiState(true);

        if (formDispensasi) {
            formDispensasi.querySelectorAll('input, select, textarea, button').forEach(function (el) {
                if (isExemptControl(el)) return;
                el.disabled = true;
                el.classList.add('opacity-50', 'cursor-not-allowed');
            });
        }
        [btnStartSelfie, btnFallbackNativeCamera, btnSnapSelfie, btnCancelSelfie, btnFlipSelfie, btnRetakeSelfie].forEach(function (b) {
            if (b) {
                b.disabled = true;
                b.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });
        stopSelfieCamera();
    }

    checkDispensasiTime();
    setInterval(checkDispensasiTime, 60000);
    updateTimeInputLimits();
});
</script>
@endpush
@endsection
