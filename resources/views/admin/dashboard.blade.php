@extends('admin.layouts.app')
@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Overview')

@section('content')
{{-- Header --}}
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Selamat Datang, {{ auth()->user()->name }}!</h2>
    <p class="text-gray-500 text-sm mt-1">Berikut adalah ringkasan aktivitas dispensasi hari ini.</p>
</div>

{{-- Statistik Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    {{-- Menunggu --}}
    <div class="bg-white rounded-xl shadow-sm border-l-4 border-amber-500 p-5 hover:shadow-md transition-shadow duration-300">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Menunggu</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['menunggu'] }}</h3>
            </div>
            <div class="p-3 bg-amber-100 rounded-lg text-amber-600">
                <i class="fas fa-clock text-xl"></i>
            </div>
        </div>
    </div>

    {{-- Disetujui --}}
    <div class="bg-white rounded-xl shadow-sm border-l-4 border-emerald-500 p-5 hover:shadow-md transition-shadow duration-300">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Disetujui</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['disetujui'] }}</h3>
            </div>
            <div class="p-3 bg-emerald-100 rounded-lg text-emerald-600">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>
    </div>

    {{-- Selesai --}}
    <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-5 hover:shadow-md transition-shadow duration-300">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Selesai</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['selesai'] }}</h3>
            </div>
            <div class="p-3 bg-blue-100 rounded-lg text-blue-600">
                <i class="fas fa-flag-checkered text-xl"></i>
            </div>
        </div>
    </div>

    {{-- Ditolak --}}
    <div class="bg-white rounded-xl shadow-sm border-l-4 border-rose-500 p-5 hover:shadow-md transition-shadow duration-300">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Ditolak</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['ditolak'] }}</h3>
            </div>
            <div class="p-3 bg-rose-100 rounded-lg text-rose-600">
                <i class="fas fa-times-circle text-xl"></i>
            </div>
        </div>
    </div>
</div>

{{-- Grafik & Aktivitas Terbaru --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Grafik Pengajuan (Lebar 2/3) --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Grafik Pengajuan 7 Hari Terakhir</h3>
            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Update Real-time</span>
        </div>
        <div class="relative h-64 w-full">
            <canvas id="chartPengajuan"></canvas>
        </div>
    </div>

    {{-- Aktivitas Terbaru (Lebar 1/3) --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Pengajuan Terbaru</h3>
        <div class="space-y-4">
            @forelse($recent as $item)
                <div class="flex items-start space-x-3 pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm">
                            {{ substr($item->siswa->nama_lengkap, 0, 1) }}
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $item->siswa->nama_lengkap }}</p>
                        <p class="text-xs text-gray-500">{{ $item->siswa->kelas?->nama_kelas }} &bull; {{ $item->kategori }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $item->created_at->diffForHumans() }}</p>
                    </div>
                    <div>
                        @php
                            $statusColors = [
                                'menunggu' => 'bg-amber-100 text-amber-700',
                                'disetujui' => 'bg-emerald-100 text-emerald-700',
                                'selesai' => 'bg-blue-100 text-blue-700',
                                'ditolak' => 'bg-rose-100 text-rose-700',
                            ];
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$item->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($item->status) }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 text-center py-4">Belum ada pengajuan terbaru.</p>
            @endforelse
        </div>

        {{-- ✅ DIPERBAIKI: Mengganti panah teks (→) dengan icon Font Awesome agar konsisten --}}
        <a href="{{ route('admin.semua.pengajuan') }}" class="block text-center text-sm text-indigo-600 hover:text-indigo-800 font-medium mt-4 transition-colors">
            Lihat Semua Pengajuan <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
</div>
@endsection

@push('scripts')
{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('chartPengajuan').getContext('2d');

        // Gradient untuk bar chart agar terlihat lebih profesional
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.8)'); // Indigo-600
        gradient.addColorStop(1, 'rgba(79, 70, 229, 0.2)');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($dates),
                datasets: [{
                    label: 'Jumlah Pengajuan',
                    data: @json($counts),
                    backgroundColor: gradient,
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 1,
                    borderRadius: 6, // Sudut membulat pada bar
                    barThickness: 'flex',
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Sembunyikan legend agar lebih bersih
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        padding: 12,
                        titleFont: { size: 13 },
                        bodyFont: { size: 13 },
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 11 }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 11 }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
