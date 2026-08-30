@extends('admin.layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Sistem')

@section('content')

@include('components.alert')

<div class="max-w-4xl mx-auto space-y-6">

    {{-- ✅ 1. JAM OPERASIONAL CETAK --}}
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
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">
                            Jam Mulai Cetak
                            <span class="text-gray-400 font-normal">(Format: 24 jam, contoh: 06:00)</span>
                        </label>
                        <input type="time"
                               name="print_start_time"
                               id="print_start_time"
                               value="{{ old('print_start_time', \Carbon\Carbon::parse($print_start_time ?? '06:00')->format('H:i')) }}"
                               required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-sm">
                        @error('print_start_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">
                            Jam Akhir Cetak
                            <span class="text-gray-400 font-normal">(Format: 24 jam, contoh: 17:00)</span>
                        </label>
                        <input type="time"
                               name="print_end_time"
                               id="print_end_time"
                               value="{{ old('print_end_time', \Carbon\Carbon::parse($print_end_time ?? '17:00')->format('H:i')) }}"
                               required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-sm">
                        @error('print_end_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- SISA FORM BATAS CETAK (Sama seperti sebelumnya) --}}
            <div class="p-5 border-t border-gray-100">
                <div class="space-y-5">
                    {{-- Batas Cetak Siswa --}}
                    <div class="p-4 rounded-xl bg-emerald-50 border-2 border-emerald-100">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-500 text-white flex items-center justify-center">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-emerald-900">Batas Cetak Siswa</h4>
                                <p class="text-xs text-emerald-700">Kuota cetak untuk setiap siswa per dispensasi</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <input type="number" name="student_print_limit"
                                   value="{{ old('student_print_limit', $student_print_limit ?? 3) }}"
                                   min="1" max="20" required
                                   class="flex-1 px-4 py-2.5 border-2 border-emerald-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all text-sm font-bold text-emerald-700">
                            <span class="text-sm font-bold text-emerald-800">kali cetak</span>
                        </div>
                    </div>

                    {{-- Batas Cetak Guru --}}
                    <div class="p-4 rounded-xl bg-blue-50 border-2 border-blue-100">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-500 text-white flex items-center justify-center">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-blue-900">Batas Cetak Guru</h4>
                                <p class="text-xs text-blue-700">Kuota cetak untuk guru piket per dispensasi</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <input type="number" name="teacher_print_limit"
                                   value="{{ old('teacher_print_limit', $teacher_print_limit ?? 10) }}"
                                   min="1" max="50" required
                                   class="flex-1 px-4 py-2.5 border-2 border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-sm font-bold text-blue-700">
                            <span class="text-sm font-bold text-blue-800">kali cetak</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2.5 rounded-xl font-bold transition-all active:scale-95 shadow-lg shadow-purple-500/30">
                        <i class="fas fa-save mr-2"></i>Simpan Semua Pengaturan
                    </button>
                </div>
            </div>
        </form>
    </div>



</div>
@endsection
