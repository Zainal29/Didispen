@extends('admin.layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Sistem')

@section('content')
@include('components.alert')

<div class="max-w-4xl mx-auto space-y-6">

    {{-- ========================================== --}}
    {{-- 1. JAM OPERASIONAL CETAK (Kode Lama Anda) --}}
    {{-- ========================================== --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
        <div class="p-5 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">
                <i class="fas fa-clock text-blue-600 mr-2"></i>Jam Operasional Cetak
            </h3>
            <p class="text-sm text-gray-500 mt-1">Atur waktu kapan siswa dapat mencetak surat dispensasi.</p>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-gray-700 mb-1.5">Jam Mulai Cetak</label>
                        <input type="time" name="print_start_time" value="{{ old('print_start_time', $print_start_time ?? '06:00') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-base sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-gray-700 mb-1.5">Jam Akhir Cetak</label>
                        <input type="time" name="print_end_time" value="{{ old('print_end_time', $print_end_time ?? '17:00') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-base sm:text-sm">
                    </div>
                </div>
            </div>

            <div class="p-5 border-t border-gray-100">
                <div class="space-y-5">
                    <div class="p-4 rounded-xl bg-emerald-50 border-2 border-emerald-100">
                        <h4 class="font-bold text-emerald-900 mb-2">Batas Cetak Siswa</h4>
                        <div class="flex items-center gap-4">
                            <input type="number" name="student_print_limit" value="{{ old('student_print_limit', $student_print_limit ?? 3) }}" min="1" max="20" required class="flex-1 px-4 py-2.5 border-2 border-emerald-200 rounded-xl outline-none text-base sm:text-sm font-bold text-emerald-700">
                            <span class="text-sm font-bold text-emerald-800">kali cetak</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-blue-50 border-2 border-blue-100">
                        <h4 class="font-bold text-blue-900 mb-2">Batas Cetak Guru</h4>
                        <div class="flex items-center gap-4">
                            <input type="number" name="teacher_print_limit" value="{{ old('teacher_print_limit', $teacher_print_limit ?? 10) }}" min="1" max="50" required class="flex-1 px-4 py-2.5 border-2 border-blue-200 rounded-xl outline-none text-base sm:text-sm font-bold text-blue-700">
                            <span class="text-sm font-bold text-blue-800">kali cetak</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- 2. JAM OPERASIONAL DISPENSASI (BARU) --}}
            {{-- ========================================== --}}
            <div class="p-5 border-t border-gray-100 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-calendar-check text-indigo-600 mr-2"></i>Jam Operasional Pengajuan Dispensasi
                </h3>
                <p class="text-sm text-gray-500 mb-4">Atur kapan siswa diizinkan mengajukan dispensasi.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-gray-700 mb-1.5">Jam Mulai Pengajuan</label>
                        <input type="time" name="dispensasi_start_time" value="{{ old('dispensasi_start_time', $dispensasi_start_time ?? '07:00') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-base sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-gray-700 mb-1.5">Jam Akhir (Senin - Kamis)</label>
                        <input type="time" name="dispensasi_end_time" value="{{ old('dispensasi_end_time', $dispensasi_end_time ?? '15:00') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-base sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-gray-700 mb-1.5">Jam Akhir (Jumat)</label>
                        <input type="time" name="dispensasi_end_time_friday" value="{{ old('dispensasi_end_time_friday', $dispensasi_end_time_friday ?? '14:00') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-base sm:text-sm">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Hari yang Diizinkan</label>
                    <div class="flex flex-wrap gap-3">
                        @php
                            $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 0 => 'Minggu'];
                            $savedDays = $dispensasi_days ?? [1, 2, 3, 4, 5];
                        @endphp
                        @foreach($days as $num => $name)
                            <label class="inline-flex items-center space-x-2 cursor-pointer bg-white px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                                <input type="checkbox" name="dispensasi_days[]" value="{{ $num }}"
                                       {{ in_array($num, $savedDays) ? 'checked' : '' }}
                                       class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                <span class="text-sm text-gray-700 font-medium">{{ $name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>


            {{-- ========================================== --}}
{{-- 3. GURU FALLBACK WHATSAPP --}}
{{-- ========================================== --}}
<div class="p-5 border-t border-gray-100 bg-white">
    <h3 class="text-base font-bold text-gray-900 mb-2 flex items-center">
        <i class="fab fa-whatsapp text-green-600 mr-2"></i>
        Guru Piket Fallback WhatsApp
    </h3>

    <p class="text-sm text-gray-500 mb-4">
        Guru ini digunakan sebagai fallback ketika Guru Piket dari pengajuan
        atau sesi piket tidak tersedia.
    </p>

    <div>
        <label
            for="fallback_guru_piket_id"
            class="block text-sm font-bold text-gray-700 mb-1.5"
        >
            Guru Fallback
        </label>

        <select
            id="fallback_guru_piket_id"
            name="fallback_guru_piket_id"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl
                   focus:ring-2 focus:ring-green-500 focus:border-green-500
                   outline-none text-sm"
        >
            <option value="">-- Tidak menggunakan fallback --</option>

            @foreach($guruFallback as $guru)
                <option
                    value="{{ $guru->id }}"
                    {{ (string) old('fallback_guru_piket_id', $fallback_guru_piket_id ?? '') === (string) $guru->id ? 'selected' : '' }}
                >
                    {{ $guru->nama_lengkap }}
                    @if($guru->no_telepon)
                        — {{ $guru->no_telepon }}
                    @else
                        — Nomor WhatsApp belum tersedia
                    @endif
                </option>
            @endforeach
        </select>

        @error('fallback_guru_piket_id')
            <p class="mt-1.5 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>
</div>



            {{-- ========================================== --}}
            {{-- 3. JADWAL JAM PELAJARAN (3 POLA) --}}
            {{-- ========================================== --}}
            <div class="p-5 border-t border-gray-100 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-900 mb-2 flex items-center">
                    <i class="fas fa-clock text-indigo-600 mr-2"></i>Jadwal Jam Pelajaran
                </h3>
                <p class="text-sm text-gray-500 mb-4">Atur waktu mulai dan selesai untuk setiap jam pelajaran sesuai pola hari KBM.</p>

                @php
                    $jadwalData = is_array($jam_pelajaran) ? $jam_pelajaran : (json_decode($jam_pelajaran, true) ?? []);
                    $seninSelasa = $jadwalData['senin_selasa'] ?? [];
                    $rabuKamis = $jadwalData['rabu_kamis'] ?? [];
                    $jumat = $jadwalData['jumat'] ?? [];
                    $sabtu = $jadwalData['sabtu'] ?? [];
                    $minggu = $jadwalData['minggu'] ?? [];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- 1. SENIN & SELASA (10 Jam) --}}
                    <div>
                        <h4 class="font-bold text-sm text-gray-700 mb-3 bg-white p-2.5 rounded-lg border flex justify-between items-center shadow-sm">
                            <span class="text-indigo-700"><i class="fas fa-calendar-day mr-1.5"></i>Senin & Selasa</span>
                            <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">10 Jam (45m)</span>
                        </h4>
                        <div class="space-y-2 max-h-96 overflow-y-auto pr-1 custom-scrollbar">
                            @for($i = 1; $i <= 10; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-gray-200 hover:border-indigo-300 transition-colors">
                                    <span class="text-xs font-bold text-gray-600 w-14">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_senin_selasa[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_senin_selasa.'.$i.'.start', $seninSelasa[$i]['start'] ?? '') }}"
                                           required
                                           class="flex-1 text-xs border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none py-1.5 px-2">
                                    <span class="text-gray-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_senin_selasa[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_senin_selasa.'.$i.'.end', $seninSelasa[$i]['end'] ?? '') }}"
                                           required
                                           class="flex-1 text-xs border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none py-1.5 px-2">
                                </div>
                            @endfor
                        </div>
                    </div>

                    {{-- 2. RABU & KAMIS (11 Jam) --}}
                    <div>
                        <h4 class="font-bold text-sm text-gray-700 mb-3 bg-white p-2.5 rounded-lg border flex justify-between items-center shadow-sm">
                            <span class="text-emerald-700"><i class="fas fa-calendar-day mr-1.5"></i>Rabu & Kamis</span>
                            <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">11 Jam (40m)</span>
                        </h4>
                        <div class="space-y-2 max-h-96 overflow-y-auto pr-1 custom-scrollbar">
                            @for($i = 1; $i <= 11; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-gray-200 hover:border-emerald-300 transition-colors">
                                    <span class="text-xs font-bold text-gray-600 w-14">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_rabu_kamis[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_rabu_kamis.'.$i.'.start', $rabuKamis[$i]['start'] ?? '') }}"
                                           required
                                           class="flex-1 text-xs border-gray-300 rounded focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none py-1.5 px-2">
                                    <span class="text-gray-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_rabu_kamis[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_rabu_kamis.'.$i.'.end', $rabuKamis[$i]['end'] ?? '') }}"
                                           required
                                           class="flex-1 text-xs border-gray-300 rounded focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none py-1.5 px-2">
                                </div>
                            @endfor
                        </div>
                    </div>

                    {{-- 3. JUMAT (8 Jam) --}}
                    <div>
                        <h4 class="font-bold text-sm text-gray-700 mb-3 bg-white p-2.5 rounded-lg border flex justify-between items-center shadow-sm">
                            <span class="text-amber-700"><i class="fas fa-calendar-day mr-1.5"></i>Jumat</span>
                            <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded">8 Jam</span>
                        </h4>
                        <div class="space-y-2 max-h-96 overflow-y-auto pr-1 custom-scrollbar">
                            @for($i = 1; $i <= 8; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-gray-200 hover:border-amber-300 transition-colors">
                                    <span class="text-xs font-bold text-gray-600 w-14">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_jumat[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_jumat.'.$i.'.start', $jumat[$i]['start'] ?? '') }}"
                                           required
                                           class="flex-1 text-xs border-gray-300 rounded focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none py-1.5 px-2">
                                    <span class="text-gray-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_jumat[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_jumat.'.$i.'.end', $jumat[$i]['end'] ?? '') }}"
                                           required
                                           class="flex-1 text-xs border-gray-300 rounded focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none py-1.5 px-2">
                                </div>
                            @endfor
                        </div>
                    </div>





                    {{-- 4. SABTU (Testing - 10 Jam) --}}
                    <div>
                        <h4 class="font-bold text-sm text-gray-700 mb-3 bg-white p-2.5 rounded-lg border flex justify-between items-center shadow-sm">
                            <span class="text-purple-700"><i class="fas fa-flask mr-1.5"></i>Sabtu (Testing)</span>
                            <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-2 py-0.5 rounded">10 Jam (Testing)</span>
                        </h4>
                        <div class="space-y-2 max-h-96 overflow-y-auto pr-1 custom-scrollbar">
                            @for($i = 1; $i <= 10; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-gray-200 hover:border-purple-300 transition-colors">
                                    <span class="text-xs font-bold text-gray-600 w-14">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_sabtu[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_sabtu.'.$i.'.start', $sabtu[$i]['start'] ?? '') }}"
                                           required
                                           class="flex-1 text-xs border-gray-300 rounded focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none py-1.5 px-2">
                                    <span class="text-gray-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_sabtu[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_sabtu.'.$i.'.end', $sabtu[$i]['end'] ?? '') }}"
                                           required
                                           class="flex-1 text-xs border-gray-300 rounded focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none py-1.5 px-2">
                                </div>
                            @endfor
                        </div>
                    </div>




                    {{-- 5. MINGGU (Testing - 10 Jam) --}}
                    <div>
                        <h4 class="font-bold text-sm text-gray-700 mb-3 bg-white p-2.5 rounded-lg border flex justify-between items-center shadow-sm">
                            <span class="text-rose-700"><i class="fas fa-flask mr-1.5"></i>Minggu (Testing)</span>
                            <span class="text-xs font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded">10 Jam (Testing)</span>
                        </h4>
                        <div class="space-y-2 max-h-96 overflow-y-auto pr-1 custom-scrollbar">
                            @for($i = 1; $i <= 10; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-gray-200 hover:border-rose-300 transition-colors">
                                    <span class="text-xs font-bold text-gray-600 w-14">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_minggu[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_minggu.'.$i.'.start', $minggu[$i]['start'] ?? '') }}"
                                           required
                                           class="flex-1 text-xs border-gray-300 rounded focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none py-1.5 px-2">
                                    <span class="text-gray-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_minggu[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_minggu.'.$i.'.end', $minggu[$i]['end'] ?? '') }}"
                                           required
                                           class="flex-1 text-xs border-gray-300 rounded focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none py-1.5 px-2">
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
            {{-- AKHIR DARI BAGIAN JAM PELAJARAN --}}

            <div class="p-5 border-t border-gray-100 flex justify-end">
                <button type="submit" class="w-full sm:w-auto min-h-[44px] justify-center bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold transition-all active:scale-95 shadow-lg shadow-indigo-500/30 flex items-center">
                    <i class="fas fa-save mr-2"></i>Simpan Semua Pengaturan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
