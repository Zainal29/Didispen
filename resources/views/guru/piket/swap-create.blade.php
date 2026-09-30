@extends('guru.layouts.app')

@section('title', 'Tukar Jadwal Piket')
@section('page-title', 'Pengajuan Penggantian Guru Piket')

@section('content')

<div class="max-w-2xl mx-auto">


{{-- Header --}}
<div class="mb-5">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100
                    text-blue-600 flex items-center justify-center shrink-0">
            <i class="fas fa-exchange-alt"></i>
        </div>

        <div>
            <h2 class="text-base font-bold text-gray-900">
                Pengajuan Tukar Jadwal
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">
                Ajukan guru pengganti untuk jadwal piket Anda.
            </p>
        </div>
    </div>
</div>

{{-- Form Card --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

    <form method="POST"
          action="{{ route('guru.piket.swap.store') }}"
          class="p-5 sm:p-6">

        @csrf

        <div class="space-y-5">

            {{-- Jadwal --}}
            <div>
                <label for="jadwal_piket_id"
                       class="block text-xs font-bold text-gray-700 mb-1.5">
                    Jadwal Piket
                    <span class="text-red-500">*</span>
                </label>

                @if($jadwalList->isEmpty())

                    <div class="flex items-start gap-3 rounded-xl border border-amber-200
                                bg-amber-50 p-3.5">
                        <div class="w-8 h-8 shrink-0 rounded-lg bg-amber-100
                                    text-amber-600 flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-xs"></i>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-amber-900">
                                Jadwal piket belum tersedia
                            </p>
                            <p class="text-[11px] text-amber-700 mt-0.5 leading-relaxed">
                                Anda belum terdaftar pada jadwal resmi piket aktif.
                            </p>
                        </div>
                    </div>

                @else

                    @php
                        $namaHari = [
                            1 => 'Senin',
                            2 => 'Selasa',
                            3 => 'Rabu',
                            4 => 'Kamis',
                            5 => 'Jumat',
                            6 => 'Sabtu',
                            7 => 'Minggu',
                        ];
                    @endphp

                    <select id="jadwal_piket_id"
                            name="jadwal_piket_id"
                            required
                            class="w-full h-11 px-3.5 rounded-lg border border-gray-300
                                   bg-white text-sm text-gray-900
                                   focus:outline-none focus:border-blue-600
                                   focus:ring-2 focus:ring-blue-600/20
                                   transition-all">

                        <option value="">
                            -- Pilih Jadwal Piket --
                        </option>

                        @foreach($jadwalList as $j)
                            <option value="{{ $j->id }}"
                                {{ old('jadwal_piket_id', $selectedJadwalId) == $j->id ? 'selected' : '' }}>
                                {{ $namaHari[$j->hari] ?? 'Hari '.$j->hari }}
                                ({{ substr($j->jam_mulai, 0, 5) }}
                                - {{ substr($j->jam_selesai, 0, 5) }})
                            </option>
                        @endforeach

                    </select>

                @endif

                @error('jadwal_piket_id')
                    <p class="text-xs text-red-600 mt-1.5">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- Tanggal --}}
            <div>
                <label for="tanggal"
                       class="block text-xs font-bold text-gray-700 mb-1.5">
                    Tanggal Tugas
                    <span class="text-red-500">*</span>
                </label>

                <input type="date"
                       id="tanggal"
                       name="tanggal"
                       required
                       value="{{ old('tanggal', $selectedTanggal) }}"
                       class="w-full h-11 px-3.5 rounded-lg border border-gray-300
                              bg-white text-sm text-gray-900
                              focus:outline-none focus:border-blue-600
                              focus:ring-2 focus:ring-blue-600/20
                              transition-all">

                <p class="mt-1.5 text-[11px] text-gray-500">
                    Pastikan tanggal sesuai dengan hari pada jadwal piket yang dipilih.
                </p>

                @error('tanggal')
                    <p class="text-xs text-red-600 mt-1.5">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- Guru Pengganti --}}
            <div>
                <label for="guru_search"
                       class="block text-xs font-bold text-gray-700 mb-1.5">
                    Guru Pengganti
                    <span class="text-red-500">*</span>
                </label>

                {{-- Hidden ID yang dikirim ke controller --}}
                <input type="hidden"
                       name="guru_pengganti_id"
                       id="guru_pengganti_id"
                       value="{{ old('guru_pengganti_id') }}">

                {{-- Search Box --}}
                <div class="relative">

                    <div class="relative">
                        <i class="fas fa-search absolute left-3.5 top-1/2
                                  -translate-y-1/2 text-gray-400 text-xs"></i>

                        <input type="text"
                               id="guru_search"
                               autocomplete="off"
                               placeholder="Cari nama atau NIP guru..."
                               value=""
                               class="w-full h-11 pl-9 pr-10 rounded-lg border border-gray-300
                                      bg-white text-sm text-gray-900
                                      placeholder-gray-400
                                      focus:outline-none focus:border-blue-600
                                      focus:ring-2 focus:ring-blue-600/20
                                      transition-all">

                        <button type="button"
                                id="clearGuruSearch"
                                class="hidden absolute right-3 top-1/2
                                       -translate-y-1/2 text-gray-400
                                       hover:text-gray-600">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>

                    {{-- Dropdown --}}
                    <div id="guruDropdown"
                         class="hidden absolute z-30 left-0 right-0 mt-1
                                bg-white border border-gray-200 rounded-lg
                                shadow-lg overflow-hidden">

                        <div id="guruResults"
                             class="max-h-60 overflow-y-auto">
                        </div>

                        <div id="guruEmpty"
                             class="hidden px-4 py-5 text-center">
                            <i class="fas fa-user-slash text-gray-300 text-lg"></i>
                            <p class="text-xs text-gray-500 mt-2">
                                Guru tidak ditemukan.
                            </p>
                        </div>

                    </div>
                </div>

                {{-- Guru yang dipilih --}}
                <div id="selectedGuru"
                     class="hidden mt-2.5 rounded-lg border border-blue-100
                            bg-blue-50 px-3 py-2.5">

                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-blue-100
                                    text-blue-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-user-tie text-xs"></i>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p id="selectedGuruName"
                               class="text-xs font-bold text-gray-900 truncate">
                            </p>

                            <p id="selectedGuruNip"
                               class="text-[10px] text-gray-500 mt-0.5">
                            </p>
                        </div>

                        <button type="button"
                                id="changeGuru"
                                class="text-[10px] font-semibold text-blue-600
                                       hover:text-blue-800">
                            Ganti
                        </button>
                    </div>
                </div>

                <p class="mt-1.5 text-[11px] text-gray-500">
                    Ketik nama atau NIP untuk mencari guru pengganti.
                </p>

                @error('guru_pengganti_id')
                    <p class="text-xs text-red-600 mt-1.5">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- Alasan --}}
            <div>
                <label for="alasan"
                       class="block text-xs font-bold text-gray-700 mb-1.5">
                    Alasan Penggantian
                    <span class="text-gray-400 font-normal">
                        (Opsional)
                    </span>
                </label>

                <textarea id="alasan"
                          name="alasan"
                          rows="3"
                          placeholder="Contoh: Mengikuti dinas luar, pelatihan, atau keperluan lainnya..."
                          class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300
                                 bg-white text-sm text-gray-900
                                 placeholder-gray-400 resize-none
                                 focus:outline-none focus:border-blue-600
                                 focus:ring-2 focus:ring-blue-600/20
                                 transition-all">{{ old('alasan') }}</textarea>

                @error('alasan')
                    <p class="text-xs text-red-600 mt-1.5">
                        {{ $message }}
                    </p>
                @enderror
            </div>

        </div>


        {{-- Footer --}}
        <div class="mt-6 pt-5 border-t border-gray-100
                    flex flex-col-reverse sm:flex-row
                    sm:items-center sm:justify-end gap-2">

            <a href="{{ route('guru.dashboard') }}"
               class="inline-flex items-center justify-center gap-2
                      h-10 px-4 rounded-lg
                      border border-gray-300 bg-white
                      text-xs font-semibold text-gray-700
                      hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left text-[10px]"></i>
                Kembali
            </a>

            <button type="submit"
                    class="inline-flex items-center justify-center gap-2
                           h-10 px-5 rounded-lg
                           bg-blue-600 hover:bg-blue-700
                           text-white text-xs font-bold
                           shadow-sm transition-colors">
                <i class="fas fa-paper-plane text-xs"></i>
                Kirim Pengajuan
            </button>

        </div>

    </form>
</div>


</div>
@endsection


@php
    $guruSearchData = $guruPenggantiList->map(function ($guru) {
        return [
            'id' => $guru->id,
            'nama' => $guru->nama_lengkap,
            'nip' => $guru->nip ?? '-',
        ];
    })->values()->all();
@endphp



@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const guruData = @json($guruSearchData);
       

    const searchInput = document.getElementById('guru_search');
    const hiddenInput = document.getElementById('guru_pengganti_id');
    const dropdown = document.getElementById('guruDropdown');
    const results = document.getElementById('guruResults');
    const empty = document.getElementById('guruEmpty');
    const selectedGuru = document.getElementById('selectedGuru');
    const selectedGuruName = document.getElementById('selectedGuruName');
    const selectedGuruNip = document.getElementById('selectedGuruNip');
    const clearButton = document.getElementById('clearGuruSearch');
    const changeButton = document.getElementById('changeGuru');

    if (!searchInput) return;

    function normalize(value) {
        return String(value || '').toLowerCase().trim();
    }

    function renderResults(keyword = '') {
        const query = normalize(keyword);

        const filtered = guruData.filter(guru => {
            return normalize(guru.nama).includes(query)
                || normalize(guru.nip).includes(query);
        });

        results.innerHTML = '';

        if (filtered.length === 0) {
            empty.classList.remove('hidden');
            return;
        }

        empty.classList.add('hidden');

        filtered.forEach(guru => {
            const button = document.createElement('button');

            button.type = 'button';
            button.className =
                'w-full flex items-center gap-3 px-3.5 py-3 text-left ' +
                'hover:bg-blue-50 transition-colors border-b border-gray-100 last:border-0';

            button.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-500
                            flex items-center justify-center shrink-0">
                    <i class="fas fa-user text-xs"></i>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-gray-900 truncate">
                        ${escapeHtml(guru.nama)}
                    </p>
                    <p class="text-[10px] text-gray-500 mt-0.5">
                        NIP: ${escapeHtml(guru.nip)}
                    </p>
                </div>

                <i class="fas fa-chevron-right text-[9px] text-gray-300"></i>
            `;

            button.addEventListener('click', function () {
                selectGuru(guru);
            });

            results.appendChild(button);
        });
    }

    function selectGuru(guru) {
        hiddenInput.value = guru.id;

        searchInput.value = guru.nama;
        searchInput.classList.add('bg-gray-50');

        selectedGuruName.textContent = guru.nama;
        selectedGuruNip.textContent = 'NIP: ' + guru.nip;

        selectedGuru.classList.remove('hidden');
        clearButton.classList.remove('hidden');
        dropdown.classList.add('hidden');
    }

    function clearGuru() {
        hiddenInput.value = '';
        searchInput.value = '';
        searchInput.classList.remove('bg-gray-50');

        selectedGuru.classList.add('hidden');
        clearButton.classList.add('hidden');

        renderResults('');
        searchInput.focus();
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    searchInput.addEventListener('focus', function () {
        renderResults(searchInput.value);
        dropdown.classList.remove('hidden');
    });

    searchInput.addEventListener('input', function () {
        hiddenInput.value = '';

        selectedGuru.classList.add('hidden');

        const value = searchInput.value.trim();

        if (value.length > 0) {
            clearButton.classList.remove('hidden');
        } else {
            clearButton.classList.add('hidden');
        }

        renderResults(value);
        dropdown.classList.remove('hidden');
    });

    clearButton.addEventListener('click', function () {
        clearGuru();
    });

    changeButton.addEventListener('click', function () {
        clearGuru();
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('#guru_search') &&
            !event.target.closest('#guruDropdown')) {
            dropdown.classList.add('hidden');
        }
    });

    /*
     * Restore guru jika validasi sebelumnya gagal.
     */
    const oldGuruId = hiddenInput.value;

    if (oldGuruId) {
        const existingGuru = guruData.find(
            guru => String(guru.id) === String(oldGuruId)
        );

        if (existingGuru) {
            selectGuru(existingGuru);
        }
    }
});
</script>

@endpush
