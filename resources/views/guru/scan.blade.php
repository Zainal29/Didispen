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

        <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors">
            <i class="fas fa-redo mr-1"></i> Reset Scanner
        </button>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    const html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };
    let isProcessing = false; // <i class="fas fa-check-circle"></i> Flag untuk mencegah double scan

    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
        .catch(err => console.error("Gagal start kamera", err));

    function onScanSuccess(decodedText) {
        // <i class="fas fa-check-circle"></i> CEK FLAG SEBELUM PROSES
        if (isProcessing) return;

        isProcessing = true;
        html5QrCode.pause();

        const resultDiv = document.getElementById('scan-result');
        resultDiv.classList.remove('hidden');
        resultDiv.innerHTML = '<p class="text-blue-600 font-bold"><i class="fas fa-spinner fa-spin mr-1"></i> Memverifikasi...</p>';

        fetch('{{ route("guru.scan.verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ qr_data: decodedText })
        })
        .then(r => {
            // <i class="fas fa-check-circle"></i> CEK JIKA TERKENA THROTTLE/COOLDOWN (429)
            if (r.status === 429) {
                return {
                    success: false,
                    message: '⏱️ QR Code baru saja di-scan! Mohon tunggu 5 detik.'
                };
            }
            return r.json();
        })
        .then(data => {
            if (data.success) {
                resultDiv.className = 'mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-left';
                resultDiv.innerHTML = `
                    <p class="text-emerald-700 font-bold text-lg mb-2"><i class="fas fa-check-circle mr-1"></i> ${data.message}</p>
                    <div class="space-y-1 text-sm text-emerald-700">
                        <p><strong>Nama:</strong> ${data.data.siswa.nama_lengkap}</p>
                        <p><strong>Kelas:</strong> ${data.data.siswa.kelas?.nama_kelas ?? '-'}</p>
                        <p><strong>Status:</strong> ${data.data.status === 'keluar' ? 'Berhasil Kembali' : 'Berhasil Keluar'}</p>
                    </div>
                `;

                // <i class="fas fa-check-circle"></i> Resume setelah 5 detik
                setTimeout(() => {
                    html5QrCode.resume();
                    resultDiv.classList.add('hidden');
                    isProcessing = false; // <i class="fas fa-check-circle"></i> Reset flag
                }, 5000);

            } else {
                resultDiv.className = 'mt-4 p-4 rounded-xl bg-amber-50 border border-amber-300 text-left';
                resultDiv.innerHTML = `
                    <p class="text-amber-700 font-bold"><i class="fas fa-clock mr-1"></i> ${data.message}</p>
                `;

                // <i class="fas fa-check-circle"></i> Resume lebih cepat untuk error/cooldown
                setTimeout(() => {
                    html5QrCode.resume();
                    resultDiv.classList.add('hidden');
                    isProcessing = false; // <i class="fas fa-check-circle"></i> Reset flag
                }, 3000);
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            const resultDiv = document.getElementById('scan-result');
            resultDiv.className = 'mt-4 p-4 rounded-xl bg-red-50 border border-red-200 text-left';
            resultDiv.innerHTML = '<p class="text-red-700 font-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Terjadi kesalahan koneksi.</p>';

            html5QrCode.resume();
            isProcessing = false; // <i class="fas fa-check-circle"></i> Reset flag
        });
    }
</script>
@endpush
@endsection
