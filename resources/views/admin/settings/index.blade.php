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
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Jam Mulai Cetak</label>
                        <input type="time" name="print_start_time" value="{{ old('print_start_time', $print_start_time ?? '06:00') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Jam Akhir Cetak</label>
                        <input type="time" name="print_end_time" value="{{ old('print_end_time', $print_end_time ?? '17:00') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                    </div>
                </div>
            </div>

            <div class="p-5 border-t border-gray-100">
                <div class="space-y-5">
                    <div class="p-4 rounded-xl bg-emerald-50 border-2 border-emerald-100">
                        <h4 class="font-bold text-emerald-900 mb-2">Batas Cetak Siswa</h4>
                        <div class="flex items-center gap-4">
                            <input type="number" name="student_print_limit" value="{{ old('student_print_limit', $student_print_limit ?? 3) }}" min="1" max="20" required class="flex-1 px-4 py-2.5 border-2 border-emerald-200 rounded-xl outline-none text-sm font-bold text-emerald-700">
                            <span class="text-sm font-bold text-emerald-800">kali cetak</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-blue-50 border-2 border-blue-100">
                        <h4 class="font-bold text-blue-900 mb-2">Batas Cetak Guru</h4>
                        <div class="flex items-center gap-4">
                            <input type="number" name="teacher_print_limit" value="{{ old('teacher_print_limit', $teacher_print_limit ?? 10) }}" min="1" max="50" required class="flex-1 px-4 py-2.5 border-2 border-blue-200 rounded-xl outline-none text-sm font-bold text-blue-700">
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
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Jam Mulai Pengajuan</label>
                        <input type="time" name="dispensasi_start_time" value="{{ old('dispensasi_start_time', $dispensasi_start_time ?? '07:00') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Jam Akhir (Senin - Kamis)</label>
                        <input type="time" name="dispensasi_end_time" value="{{ old('dispensasi_end_time', $dispensasi_end_time ?? '15:00') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Jam Akhir (Jumat)</label>
                        <input type="time" name="dispensasi_end_time_friday" value="{{ old('dispensasi_end_time_friday', $dispensasi_end_time_friday ?? '14:00') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
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
            {{-- AKHIR DARI BAGIAN DISPENSASI --}}

            <div class="p-5 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold transition-all active:scale-95 shadow-lg shadow-indigo-500/30 flex items-center">
                    <i class="fas fa-save mr-2"></i>Simpan Semua Pengaturan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
