@extends('admin.layouts.app')

@section('title', 'Edit Jadwal Guru Piket')
@section('page-title', 'Edit Jadwal Guru Piket')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="text-xl font-bold text-gray-800 tracking-tight">Edit Jadwal Piket</h3>
            <p class="text-sm text-gray-500 mt-0.5">Perbarui hari mingguan, waktu sesi, rentang periode, atau penugasan guru piket.</p>
        </div>
        <a href="{{ route('admin.jadwal-piket.index') }}"
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors w-fit">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    @include('components.alert')

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 sm:p-6">
        <form id="formJadwalPiket" method="POST" action="{{ route('admin.jadwal-piket.update', $jadwal) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- 1. Hari Piket --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    Hari Piket Mingguan <span class="text-red-500">*</span>
                </label>
                @php
                    $namaHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                    $currentHari = (int) old('hari', $jadwal->hari);
                @endphp
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2.5">
                    @foreach($namaHari as $no => $nama)
                        <label for="hari_{{ $no }}"
                               class="hari-card cursor-pointer flex items-center p-3 rounded-xl border border-gray-200 hover:border-blue-500 hover:bg-blue-50/40 transition-all select-none has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 has-[:checked]:ring-2 has-[:checked]:ring-blue-500/20">
                            <input type="radio" name="hari" id="hari_{{ $no }}" value="{{ $no }}"
                                   {{ $currentHari === $no ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 mr-2.5 flex-shrink-0">
                            <div class="min-w-0">
                                <span class="text-sm font-bold text-gray-900 block leading-tight truncate">{{ $nama }}</span>
                                <span class="text-[10px] text-gray-500 block mt-0.5">Hari ke-{{ $no }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('hari')
                    <p class="text-xs text-red-600 mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- 2. Pengaturan Sesi Piket & Koordinator --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Pengaturan Sesi Piket <span class="text-gray-400 font-normal lowercase">(contoh: Sesi 1, Istirahat, dll)</span>
                    </label>
                    <div class="space-y-2">
                        <input type="text" name="nama_sesi" id="nama_sesi" value="{{ old('nama_sesi', $jadwal->nama_sesi ?? 'Sesi 1') }}"
                               placeholder="Contoh: Sesi 1 / Sesi 2 / Istirahat / Sesi 3"
                               class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                        {{-- Quick Presets --}}
                        <div class="flex flex-wrap gap-1.5 text-xs">
                            <span class="text-[11px] text-gray-400 py-1">Pilihan Cepat:</span>
                            <button type="button" class="btn-preset px-2.5 py-1 bg-gray-100 hover:bg-blue-50 hover:text-blue-700 rounded-md text-gray-600 font-medium transition-colors"
                                    data-sesi="Sesi 1" data-mulai="07:00" data-selesai="09:30">Sesi 1 (07:00-09:30)</button>
                            <button type="button" class="btn-preset px-2.5 py-1 bg-gray-100 hover:bg-blue-50 hover:text-blue-700 rounded-md text-gray-600 font-medium transition-colors"
                                    data-sesi="Sesi 2" data-mulai="09:30" data-selesai="12:00">Sesi 2 (09:30-12:00)</button>
                            <button type="button" class="btn-preset px-2.5 py-1 bg-gray-100 hover:bg-blue-50 hover:text-blue-700 rounded-md text-gray-600 font-medium transition-colors"
                                    data-sesi="Istirahat" data-mulai="12:00" data-selesai="13:00">Istirahat (12:00-13:00)</button>
                            <button type="button" class="btn-preset px-2.5 py-1 bg-gray-100 hover:bg-blue-50 hover:text-blue-700 rounded-md text-gray-600 font-medium transition-colors"
                                    data-sesi="Sesi 3" data-mulai="13:00" data-selesai="15:00">Sesi 3 (13:00-15:00)</button>
                        </div>
                    </div>
                    @error('nama_sesi')
                        <p class="text-xs text-red-600 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Koordinator Guru Piket <span class="text-gray-400 font-normal lowercase">(opsional / per hari)</span>
                    </label>
                    <select name="koordinator_guru_id" id="koordinator_guru_id"
                            class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow cursor-pointer">
                        <option value="">-- Pilih Koordinator Guru Piket --</option>
                        @foreach($gurus as $kg)
                            <option value="{{ $kg->id }}" {{ old('koordinator_guru_id', $jadwal->koordinator_guru_id) == $kg->id ? 'selected' : '' }}>
                                {{ $kg->nama_lengkap }} (No HP: {{ $kg->no_telepon ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">Koordinator piket akan ditampilkan bersama No HP pada jadwal harian.</p>
                    @error('koordinator_guru_id')
                        <p class="text-xs text-red-600 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 3. Waktu Sesi (Jam Mulai & Jam Selesai) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Jam Mulai <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none"><i class="fas fa-clock text-sm"></i></span>
                        <input type="time" name="jam_mulai" id="jam_mulai" required
                               value="{{ old('jam_mulai', substr($jadwal->jam_mulai, 0, 5)) }}"
                               class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                    </div>
                    @error('jam_mulai')
                        <p class="text-xs text-red-600 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Jam Selesai <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none"><i class="fas fa-clock text-sm"></i></span>
                        <input type="time" name="jam_selesai" id="jam_selesai" required
                               value="{{ old('jam_selesai', substr($jadwal->jam_selesai, 0, 5)) }}"
                               class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                    </div>
                    @error('jam_selesai')
                        <p class="text-xs text-red-600 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 4. Periode Berlaku --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Tanggal Mulai Berlaku <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_mulai_berlaku" id="tanggal_mulai_berlaku" required
                           value="{{ old('tanggal_mulai_berlaku', $jadwal->tanggal_mulai_berlaku ? \Carbon\Carbon::parse($jadwal->tanggal_mulai_berlaku)->toDateString() : '') }}"
                           class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                    @error('tanggal_mulai_berlaku')
                        <p class="text-xs text-red-600 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Tanggal Selesai Berlaku <span class="text-gray-400 font-normal lowercase">(kosongkan jika tanpa batas)</span>
                    </label>
                    <input type="date" name="tanggal_selesai_berlaku" id="tanggal_selesai_berlaku"
                           value="{{ old('tanggal_selesai_berlaku', $jadwal->tanggal_selesai_berlaku ? \Carbon\Carbon::parse($jadwal->tanggal_selesai_berlaku)->toDateString() : '') }}"
                           class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                    @error('tanggal_selesai_berlaku')
                        <p class="text-xs text-red-600 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- HELPER PENJELASAN PERIODE BERLAKU --}}
            <div class="p-3.5 bg-blue-50/70 border border-blue-100 rounded-xl text-xs text-blue-900 flex items-start gap-2.5">
                <i class="fas fa-info-circle text-blue-600 text-sm mt-0.5 flex-shrink-0"></i>
                <div class="leading-relaxed">
                    <span class="font-bold block text-blue-950 mb-0.5">Penjelasan Jadwal Berulang:</span>
                    <span id="helperJadwalText">Jadwal akan berulang setiap hari Senin selama periode berlaku.</span>
                </div>
            </div>

            {{-- 5. Penugasan Guru Piket Resmi --}}
            <div class="pt-2 border-t border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Pilih Guru Piket Yang Bertugas <span class="text-red-500">*</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-0.5">
                            <span id="guruTotalCount" class="font-bold text-gray-700">{{ $gurus->count() }} Guru aktif tersedia</span>. Penugasan guru tidak akan menghapus data guru asli di sistem.
                        </p>
                    </div>

                    {{-- Search Input Guru --}}
                    <div class="w-full sm:w-80">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-search text-xs"></i>
                            </span>
                            <input type="text" id="guruSearchInput" placeholder="Cari nama, NIP, atau No HP guru..." autocomplete="off"
                                   class="w-full pl-8 pr-8 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            <button type="button" id="btnResetSearch" class="hidden absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times-circle text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                @error('guru')
                    <div class="p-3 mb-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700 font-semibold flex items-center gap-2">
                        <i class="fas fa-exclamation-circle text-sm"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                @php
                    $checkedGurus = old('guru', $selectedGuruIds);
                @endphp
                <div class="border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
                    {{-- Counter Bar --}}
                    <div class="px-4 py-2 bg-gray-50/80 border-b border-gray-200 text-[11px] text-gray-500 flex items-center justify-between">
                        <span>Menampilkan <strong id="guruVisibleCount" class="text-gray-700">{{ $gurus->count() }}</strong> dari {{ $gurus->count() }} guru</span>
                        <span><strong id="guruSelectedCount" class="text-blue-700">{{ count($checkedGurus) }}</strong> guru dipilih</span>
                    </div>

                    {{-- List Guru Scrollable --}}
                    <div id="guruListContainer" class="max-h-80 overflow-y-auto divide-y divide-gray-100 bg-white">
                        @forelse($gurus as $g)
                            <label for="guru_cb_{{ $g->id }}"
                                   data-name="{{ strtolower($g->nama_lengkap) }}"
                                   data-nip="{{ strtolower($g->nip ?? '') }}"
                                   data-phone="{{ strtolower($g->no_telepon ?? '') }}"
                                   class="guru-item flex items-center px-4 py-3 min-h-[48px] hover:bg-blue-50/30 cursor-pointer transition-colors select-none">
                                <input type="checkbox" name="guru[]" id="guru_cb_{{ $g->id }}" value="{{ $g->id }}"
                                       {{ in_array($g->id, $checkedGurus) ? 'checked' : '' }}
                                       class="guru-checkbox w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 mr-3 flex-shrink-0">
                                <div class="flex-1 min-w-0 pr-2">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $g->nama_lengkap }}</p>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-0.5">
                                        <span class="text-xs text-gray-500 font-mono">NIP: {{ $g->nip ?? '-' }}</span>
                                        <span class="inline-flex items-center text-xs font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                            <i class="fab fa-whatsapp text-emerald-600 mr-1.5 text-xs"></i> {{ $g->no_telepon ?? 'No HP belum ada' }}
                                        </span>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 flex-shrink-0">
                                    Aktif
                                </span>
                            </label>
                        @empty
                            <div class="p-8 text-center text-xs text-gray-500">
                                Tidak ada data guru aktif yang tersedia di sistem.
                            </div>
                        @endforelse

                        {{-- Empty Search Result Placeholder --}}
                        <div id="guruEmptySearchState" class="hidden p-8 text-center text-xs text-gray-500">
                            <i class="fas fa-search text-gray-300 text-2xl mb-2 block"></i>
                            Tidak ditemukan guru dengan nama, NIP, atau No HP tersebut.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="pt-4 border-t border-gray-100 flex flex-col-reverse sm:flex-row items-center justify-end gap-3">
                <a href="{{ route('admin.jadwal-piket.index') }}"
                   class="w-full sm:w-auto px-5 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors text-center">
                    Batal
                </a>
                <button type="submit" id="submitBtn"
                        class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow-sm hover:shadow transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Perbarui Jadwal
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Live Helper Text Penjelasan Jadwal Berulang
    const namaHariMap = { 1: 'Senin', 2: 'Selasa', 3: 'Rabu', 4: 'Kamis', 5: 'Jumat', 6: 'Sabtu', 7: 'Minggu' };
    const helperText = document.getElementById('helperJadwalText');
    const jamMulaiInput = document.getElementById('jam_mulai');
    const jamSelesaiInput = document.getElementById('jam_selesai');
    const tglMulaiInput = document.getElementById('tanggal_mulai_berlaku');
    const tglSelesaiInput = document.getElementById('tanggal_selesai_berlaku');

    function updateHelperText() {
        const selectedHariInput = document.querySelector('input[name="hari"]:checked');
        const hariVal = selectedHariInput ? parseInt(selectedHariInput.value) : 1;
        const hariNama = namaHariMap[hariVal] || 'Senin';
        const namaSesiInput = document.getElementById('nama_sesi');
        const sesiPrefix = namaSesiInput && namaSesiInput.value.trim() ? `[${namaSesiInput.value.trim()}] ` : '';
        const jamMulai = jamMulaiInput ? jamMulaiInput.value : '07:00';
        const jamSelesai = jamSelesaiInput ? jamSelesaiInput.value : '09:30';
        const tglMulai = tglMulaiInput ? tglMulaiInput.value : '';
        const tglSelesai = tglSelesaiInput ? tglSelesaiInput.value : '';

        let text = `Jadwal ${sesiPrefix}akan berulang setiap hari ${hariNama} pukul ${jamMulai} - ${jamSelesai} WIB`;

        if (tglMulai && tglSelesai) {
            text += ` selama periode ${tglMulai} sampai ${tglSelesai}.`;
        } else if (tglMulai) {
            text += ` mulai ${tglMulai} tanpa batas tanggal akhir.`;
        } else {
            text += ` selama periode berlaku.`;
        }

        if (helperText) {
            helperText.textContent = text;
        }
    }

    // Quick Presets Click Handler
    document.querySelectorAll('.btn-preset').forEach(btn => {
        btn.addEventListener('click', function() {
            const sesi = this.getAttribute('data-sesi');
            const mulai = this.getAttribute('data-mulai');
            const selesai = this.getAttribute('data-selesai');
            const namaSesiInput = document.getElementById('nama_sesi');

            if (namaSesiInput && sesi) namaSesiInput.value = sesi;
            if (jamMulaiInput && mulai) jamMulaiInput.value = mulai;
            if (jamSelesaiInput && selesai) jamSelesaiInput.value = selesai;
            updateHelperText();
        });
    });

    const namaSesiField = document.getElementById('nama_sesi');
    if (namaSesiField) namaSesiField.addEventListener('input', updateHelperText);

    document.querySelectorAll('input[name="hari"]').forEach(radio => {
        radio.addEventListener('change', updateHelperText);
    });
    if (jamMulaiInput) jamMulaiInput.addEventListener('input', updateHelperText);
    if (jamSelesaiInput) jamSelesaiInput.addEventListener('input', updateHelperText);
    if (tglMulaiInput) tglMulaiInput.addEventListener('change', updateHelperText);
    if (tglSelesaiInput) tglSelesaiInput.addEventListener('change', updateHelperText);
    updateHelperText();

    // 2. Client-Side Live Search Guru (Case-Insensitive, Preserving Checked State)
    const searchInput = document.getElementById('guruSearchInput');
    const btnResetSearch = document.getElementById('btnResetSearch');
    const guruItems = document.querySelectorAll('.guru-item');
    const visibleCountEl = document.getElementById('guruVisibleCount');
    const emptyStateEl = document.getElementById('guruEmptySearchState');
    const selectedCountEl = document.getElementById('guruSelectedCount');

    function filterGurus() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        let visibleCount = 0;

        if (btnResetSearch) {
            btnResetSearch.classList.toggle('hidden', query === '');
        }

        guruItems.forEach(item => {
            const name = item.getAttribute('data-name') || '';
            const nip = item.getAttribute('data-nip') || '';
            const phone = item.getAttribute('data-phone') || '';

            if (query === '' || name.includes(query) || nip.includes(query) || phone.includes(query)) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (visibleCountEl) visibleCountEl.textContent = visibleCount;
        if (emptyStateEl) emptyStateEl.classList.toggle('hidden', visibleCount > 0);
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterGurus);
    }

    if (btnResetSearch) {
        btnResetSearch.addEventListener('click', function() {
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
                filterGurus();
            }
        });
    }

    // 3. Update Selected Guru Counter
    function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.guru-checkbox:checked').length;
        if (selectedCountEl) {
            selectedCountEl.textContent = checkedCount;
        }
    }

    document.querySelectorAll('.guru-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
    updateSelectedCount();

    // 4. Cegah Double Submit
    const form = document.getElementById('formJadwalPiket');
    if (form) {
        form.addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
            }
        });
    }
});
</script>
@endpush
@endsection
