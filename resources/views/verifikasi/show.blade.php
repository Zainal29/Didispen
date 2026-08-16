<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi {{ $dispensasi->nomor_surat }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden">
        {{-- HEADER --}}
        <div class="bg-indigo-600 p-4 text-white flex justify-between items-center">
            <div>
                <h1 class="font-bold text-lg">{{ $dispensasi->nomor_surat }}</h1>
                <p class="text-xs opacity-80">Surat Dispensasi Siswa</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold
                {{ $dispensasi->status === 'disetujui' ? 'bg-green-200 text-green-800' : 
                  ($dispensasi->status === 'keluar' ? 'bg-yellow-200 text-yellow-800' : 
                  ($dispensasi->status === 'selesai' ? 'bg-blue-200 text-blue-800' : 'bg-red-200 text-red-800')) }}">
                {{ strtoupper($dispensasi->status) }}
            </span>
        </div>

        {{-- ISI --}}
        <div class="p-5 space-y-4">
            @if(in_array($dispensasi->status, ['keluar', 'selesai']))
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-center">
                    <p class="text-xs text-amber-800 font-bold">⚠️ QR Code ini sudah pernah di-scan (Status: {{ strtoupper($dispensasi->status) }}) dan tidak berlaku lagi.</p>
                </div>
            @elseif($dispensasi->status === 'disetujui')
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                    <p class="text-xs text-green-800 font-bold">✅ QR Code Valid (Dispensasi Disetujui & Belum Di-scan)</p>
                </div>
            @endif
            <div>
                <p class="text-xs text-gray-500 uppercase">Nama Siswa</p>
                <p class="font-bold text-gray-800">{{ $dispensasi->siswa->nama_lengkap }}</p>
                <p class="text-sm text-gray-600">
                    {{ $dispensasi->siswa->kelas->nama_kelas ?? '-' }} 
                    ({{ $dispensasi->siswa->kelas->jurusan->nama_jurusan ?? '-' }})
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-indigo-50 rounded-lg p-3">
                    <p class="text-xs text-indigo-500 font-bold uppercase">Jam Keluar</p>
                    <p class="font-semibold text-sm text-indigo-800">{{ $dispensasi->jam_keluar }}</p>
                </div>
                <div class="bg-indigo-50 rounded-lg p-3">
                    <p class="text-xs text-indigo-500 font-bold uppercase">Jam Kembali</p>
                    <p class="font-semibold text-sm text-indigo-800">{{ $dispensasi->jam_kembali }}</p>
                </div>
            </div>

            <div>
                <p class="text-xs text-gray-500 uppercase">Tujuan</p>
                <p class="text-sm text-gray-700">{{ $dispensasi->tujuan }}</p>
            </div>

            <div class="border-t pt-3">
                <p class="text-xs text-gray-500">Guru Piket: 
                    <span class="font-semibold text-gray-700">{{ $dispensasi->guruPiket->guru->nama_lengkap ?? '-' }}</span>
                </p>
            </div>
        </div>

        {{-- FOOTER STATUS --}}
        <div class="p-4 text-center text-xs text-gray-400">
            Diverifikasi otomatis oleh Sistem Dispensasi SMKN 1 Bangsri
        </div>
    </div>

</body>
</html>