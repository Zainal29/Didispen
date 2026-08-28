@extends('guru.layouts.app')

@section('title', 'Scan QR Dispensasi')
@section('page-title', 'Scan QR Code')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 text-center">
        <h2 class="text-lg font-bold text-gray-800 mb-2">Scanner QR Code Dispensasi</h2>
        <p class="text-sm text-gray-500 mb-4">Arahkan kamera ke QR Code pada layar HP siswa.</p>

        <div id="reader" class="w-full rounded-xl overflow-hidden border-2 border-dashed border-gray-300 bg-gray-50" style="min-height: 300px;"></div>

        <div id="scan-result" class="mt-4 hidden p-4 rounded-xl"></div>

        <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700">
            <i class="fas fa-redo mr-1"></i> Reset Scanner
        </button>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    const html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
    .catch(err => console.error("Gagal start kamera", err));

    function onScanSuccess(decodedText) {
        html5QrCode.pause();
        const resultDiv = document.getElementById('scan-result');
        resultDiv.classList.remove('hidden');
        resultDiv.innerHTML = '<p class="text-blue-600 font-bold"><i class="fas fa-spinner fa-spin"></i> Memverifikasi...</p>';

        fetch('{{ route("guru.scan.verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ qr_data: decodedText })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                resultDiv.className = 'mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200';
                resultDiv.innerHTML = `
                    <p class="text-emerald-700 font-bold text-lg mb-1">${data.message}</p>
                    <p class="text-sm text-emerald-600">Nama: ${data.data.siswa.nama_lengkap}</p>
                    <p class="text-sm text-emerald-600">Kelas: ${data.data.siswa.kelas?.nama_kelas ?? '-'}</p>
                `;
            } else {
                resultDiv.className = 'mt-4 p-4 rounded-xl bg-red-50 border border-red-200';
                resultDiv.innerHTML = `<p class="text-red-700 font-bold">❌ ${data.message}</p>`;
            }
            setTimeout(() => html5QrCode.resume(), 3000); // Resume setelah 3 detik
        })
        .catch(() => {
            resultDiv.innerHTML = '<p class="text-red-700 font-bold">❌ Terjadi kesalahan koneksi.</p>';
            html5QrCode.resume();
        });
    }
</script>
@endpush
@endsection
