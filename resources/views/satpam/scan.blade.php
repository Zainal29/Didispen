@extends('satpam.layouts.app')

@section('title', 'Scan QR Code')
@section('page-title', 'Scan QR Code Dispensasi')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold mb-2 text-gray-800">Scan QR Code Siswa</h3>
        <p class="text-sm text-gray-600 mb-4">Arahkan kamera ke QR Code pada surat dispensasi siswa.</p>
        
        <div id="reader" class="w-full max-w-md mx-auto mb-4 border-2 border-dashed border-gray-300 rounded-lg overflow-hidden"></div>
        
        <div id="scanResult" class="hidden mt-4 p-4 rounded-lg border">
            <h4 class="font-bold mb-2 text-lg">Hasil Scan:</h4>
            <div id="resultContent"></div>
        </div>

        <div class="mt-4 text-center">
            <button onclick="restartScanner()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                <i class="fas fa-redo mr-2"></i>Scan Ulang
            </button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

@push('scripts')
<script>
let html5QrCode;
let isScanning = false;

function onScanSuccess(decodedText, decodedResult) {
    if (!isScanning) return;
    isScanning = false; 
    html5QrCode.pause();
    
    fetch('{{ route("satpam.scan.verify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ qr_data: decodedText })
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('scanResult');
        const contentDiv = document.getElementById('resultContent');
        resultDiv.classList.remove('hidden');
        
        if (data.success) {
            resultDiv.className = 'mt-4 p-4 rounded-lg border bg-green-50 border-green-400';
            contentDiv.innerHTML = `
                <p class="text-green-800 font-bold text-lg mb-3">${data.message}</p>
                <div class="bg-white p-3 rounded border border-green-200 text-sm space-y-1 text-left">
                    <p><strong>Nama:</strong> ${data.data.siswa.nama_lengkap}</p>
                    <p><strong>Kelas:</strong> ${data.data.siswa.kelas.nama_kelas}</p>
                    <p><strong>No. Surat:</strong> ${data.data.nomor_surat}</p>
                    <p><strong>Jam Kembali:</strong> ${data.data.jam_kembali}</p>
                </div>
            `;
        } else {
            resultDiv.className = 'mt-4 p-4 rounded-lg border bg-red-50 border-red-400';
            contentDiv.innerHTML = `
                <p class="text-red-800 font-bold text-lg mb-2">❌ GAGAL</p>
                <p class="text-red-700">${data.message}</p>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const resultDiv = document.getElementById('scanResult');
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'mt-4 p-4 rounded-lg border bg-red-50 border-red-400';
        document.getElementById('resultContent').innerHTML = `<p class="text-red-800 font-bold">Terjadi kesalahan koneksi.</p>`;
    })
    .finally(() => {
        setTimeout(() => {
            document.getElementById('scanResult').classList.add('hidden');
            isScanning = true;
            html5QrCode.resume();
        }, 4000);
    });
}

function restartScanner() {
    document.getElementById('scanResult').classList.add('hidden');
    isScanning = true;
    html5QrCode.resume();
}

html5QrCode = new Html5Qrcode("reader");
html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 250 } }, onScanSuccess)
.then(() => { isScanning = true; })
.catch(err => alert('Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.'));
</script>
@endpush
@endsection