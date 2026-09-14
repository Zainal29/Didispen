@extends('siswa.layouts.app')
@section('title', 'Riwayat Pengajuan')
@section('page-title', 'Riwayat Pengajuan Dispensasi')
@section('content')
@include('components.alert')
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    {{-- Header + Filter --}}
    <div class="p-4 sm:p-5 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/50">
        <div>
            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-clock-rotate-left text-gray-400"></i>Riwayat Pengajuan
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">Pantau seluruh pengajuan dispensasi Anda.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" class="flex-1 sm:flex-none">
                <select name="status" onchange="this.form.submit()" class="w-full sm:w-auto h-10 px-3 rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-700 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
                    <option value="">Semua Status</option>
                    <option value="menunggu"  {{ request('status') == 'menunggu'  ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak"   {{ request('status') == 'ditolak'   ? 'selected' : '' }}>Ditolak</option>
                    <option value="keluar"    {{ request('status') == 'keluar'    ? 'selected' : '' }}>Sedang Keluar</option>
                    <option value="selesai"   {{ request('status') == 'selesai'   ? 'selected' : '' }}>Selesai</option>
                </select>
            </form>
            <a href="{{ route('siswa.pengajuan.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 flex-shrink-0 transition-colors">
                <i class="fas fa-plus mr-1.5"></i>Buat
            </a>
        </div>
    </div>

    {{-- MOBILE: Kartu Riwayat --}}
    <div class="md:hidden divide-y divide-gray-100">
        @forelse($pengajuan as $p)
            @php
                $badges = [
                    'menunggu'  => 'bg-amber-100 text-amber-700',
                    'disetujui' => 'bg-emerald-100 text-emerald-700',
                    'ditolak'   => 'bg-red-100 text-red-700',
                    'keluar'    => 'bg-sky-100 text-sky-700',
                    'selesai'   => 'bg-gray-100 text-gray-700',
                ];
            @endphp
            <div class="p-4 hover:bg-gray-50 transition-colors">
                <div class="flex justify-between items-start gap-2 mb-1.5">
                    <p class="font-mono font-semibold text-gray-900 text-xs">{{ $p->nomor_surat }}</p>
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold flex-shrink-0 {{ $badges[$p->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ ucfirst($p->status) }}
                    </span>
                </div>
                <p class="text-xs text-gray-500">{{ $p->created_at->format('d/m/Y') }} • <span class="capitalize">{{ str_replace('_', ' ', $p->kategori) }}</span></p>
                <p class="text-xs text-gray-500 mt-0.5 truncate"><i class="far fa-clock mr-1"></i>{{ $p->jam_keluar }} – {{ $p->jam_kembali }} • {{ $p->tujuan }}</p>
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                    <a href="{{ route('siswa.pengajuan.show', $p) }}" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                        <i class="fas fa-eye mr-1.5"></i>Detail
                    </a>
                    @if($p->status === 'keluar')
                        @if($p->foto_bukti)
                            <button onclick="showPreview('{{ asset('storage/' . $p->foto_bukti) }}', '{{ $p->nomor_surat }}')" class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors flex items-center justify-center" title="Lihat Foto Bukti">
                                <i class="fas fa-image"></i>
                            </button>
                        @else
                            <button onclick="openUploadModal({{ $p->id }})" class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors flex items-center justify-center" title="Ambil Foto Bukti (Kamera)">
                                <i class="fas fa-camera"></i>
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="p-10 text-center">
                <div class="w-16 h-16 mx-auto rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center text-2xl mb-3"><i class="fas fa-inbox"></i></div>
                <p class="text-gray-700 font-semibold text-sm">Belum ada pengajuan dispensasi</p>
            </div>
        @endforelse
    </div>

    {{-- DESKTOP: Tabel --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 border-b border-gray-200">
                <tr>
                    <th class="p-4 text-left font-semibold">No. Surat</th>
                    <th class="p-4 text-left font-semibold">Tanggal</th>
                    <th class="p-4 text-left font-semibold">Kategori</th>
                    <th class="p-4 text-left font-semibold">Tujuan</th>
                    <th class="p-4 text-left font-semibold">Waktu</th>
                    <th class="p-4 text-left font-semibold">Status</th>
                    <!--<th class="p-4 text-center font-semibold">Foto Bukti</th>-->
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pengajuan as $p)
                    @php
                        $badges = [
                            'menunggu'  => 'bg-amber-100 text-amber-700',
                            'disetujui' => 'bg-emerald-100 text-emerald-700',
                            'ditolak'   => 'bg-red-100 text-red-700',
                            'keluar'    => 'bg-sky-100 text-sky-700',
                            'selesai'   => 'bg-gray-100 text-gray-700',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 font-mono font-semibold text-gray-900 text-xs">{{ $p->nomor_surat }}</td>
                        <td class="p-4 text-gray-600 text-xs">{{ $p->created_at->format('d/m/Y') }}</td>
                        <td class="p-4 capitalize text-gray-700 text-xs">{{ str_replace('_', ' ', $p->kategori) }}</td>
                        <td class="p-4 text-gray-700 text-xs">{{ $p->tujuan }}</td>
                        <td class="p-4 text-gray-600 text-xs">{{ $p->jam_keluar }} – {{ $p->jam_kembali }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-md text-[11px] font-bold {{ $badges[$p->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <!--<td class="p-4 text-center">
                            @if($p->status === 'keluar')
                                @if($p->foto_bukti)
                                    <div class="flex items-center justify-center gap-2">
                                        <img src="{{ asset('storage/' . $p->foto_bukti) }}" class="w-10 h-10 object-cover rounded-lg border border-gray-200 cursor-pointer hover:scale-105 transition-transform" onclick="showPreview('{{ asset('storage/' . $p->foto_bukti) }}', '{{ $p->nomor_surat }}')">
                                        <button onclick="hapusFoto({{ $p->id }})" class="text-red-500 hover:text-red-700 p-1.5 rounded-md hover:bg-red-50 transition-colors" title="Hapus Foto">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>-->
                                <!--@else
                                    <button onclick="openUploadModal({{ $p->id }})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors inline-flex items-center gap-1.5">
                                        <i class="fas fa-camera"></i> Ambil Foto
                                    </button>
                                @endif-->
                            <!--@else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif-->
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center">
                            <div class="w-16 h-16 mx-auto rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center text-2xl mb-3"><i class="fas fa-inbox"></i></div>
                            <p class="text-gray-700 font-semibold text-sm">Belum ada pengajuan dispensasi</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pengajuan->hasPages())
        <div class="p-4 border-t border-gray-200 bg-gray-50">{{ $pengajuan->links() }}</div>
    @endif
</div>

{{-- MODAL AMBIL FOTO BUKTI LANGSUNG KAMERA --}}
<!--<div id="uploadModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-gray-200 overflow-hidden flex flex-col">
        {{-- Header Modal --}}
        <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="fas fa-camera"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Ambil Foto Bukti</h3>
                    <p class="text-[11px] text-gray-500">Kamera langsung aktif saat ini</p>
                </div>
            </div>
            <button type="button" onclick="closeUploadModal()" class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- Body Modal --}}
        <form id="uploadForm" class="p-5 flex flex-col gap-4">
            @csrf
            <input type="hidden" id="dispensasiId">

            {{-- Viewfinder Container --}}
            <div class="relative w-full rounded-xl overflow-hidden bg-black aspect-[4/3] flex items-center justify-center border border-gray-300 shadow-inner">
                {{-- Live Video Stream --}}
                <video id="videoBukti" autoplay playsinline muted class="w-full h-full object-cover"></video>

                {{-- Preview Image (Setelah Foto Diambil) --}}
                <img id="previewImgBukti" class="w-full h-full object-cover hidden" alt="Preview Foto Bukti">

                {{-- Hidden Canvas Snapshot --}}
                <canvas id="canvasBukti" class="hidden"></canvas>

                {{-- Live Badge --}}
                <div id="liveBadgeBukti" class="absolute top-3 left-3 bg-red-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full flex items-center gap-1.5 shadow">
                    <span class="w-2 h-2 rounded-full bg-white animate-ping"></span> LIVE KAMERA
                </div>

                {{-- Switch Camera Button (Front/Back) --}}
                <button type="button" id="btnFlipBukti" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/60 hover:bg-black/80 text-white flex items-center justify-center text-xs transition-colors shadow" title="Putar Kamera">
                    <i class="fas fa-camera-rotate"></i>
                </button>

                {{-- Loading Spinner --}}
                <div id="cameraLoadingBukti" class="absolute inset-0 bg-gray-900 flex flex-col items-center justify-center text-white hidden">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2 text-emerald-400"></i>
                    <p class="text-xs font-semibold">Mengaktifkan kamera...</p>
                </div>

                {{-- Error Container (Jika Izin Ditolak/Tidak Ada Kamera) --}}
                <div id="cameraErrorBukti" class="absolute inset-0 bg-gray-950/95 p-5 flex flex-col items-center justify-center text-center text-white hidden">
                    <div class="w-12 h-12 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl mb-2">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <p class="text-xs font-bold mb-1">Kamera Tidak Dapat Diakses</p>
                    <p class="text-[11px] text-gray-300 mb-3 max-w-xs leading-relaxed">
                        Pastikan izin akses kamera telah diizinkan pada peramban (browser) Anda. Foto harus diambil langsung dari kamera.
                    </p>
                    <button type="button" onclick="retryBuktiCamera()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors inline-flex items-center gap-1">
                        <i class="fas fa-rotate-right"></i> Coba Lagi
                    </button>
                </div>
            </div>

            {{-- Informasi Keaslian --}}
            <div class="text-[11px] text-gray-500 bg-gray-50 border border-gray-200 rounded-lg p-2.5 flex items-center gap-2">
                <i class="fas fa-shield-halved text-emerald-600 text-sm flex-shrink-0"></i>
                <span>Foto diambil seketika dengan stempel waktu otomatis untuk mencegah penggunaan foto lama.</span>
            </div>

            {{-- Action Controls (Sebelum Jepret) --}}
            <div id="controlsCameraBukti" class="flex gap-2">
                <button type="button" onclick="closeUploadModal()" class="w-1/3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-xs transition-colors">
                    Batal
                </button>
                <button type="button" id="btnSnapBukti" class="w-2/3 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white rounded-xl font-bold text-xs shadow flex items-center justify-center gap-2 transition-all">
                    <i class="fas fa-camera text-sm"></i> Jepret Foto Bukti
                </button>
            </div>

            {{-- Action Controls (Setelah Jepret) --}}
            <div id="controlsPreviewBukti" class="hidden flex gap-2">
                <button type="button" id="btnRetakeBukti" class="w-1/2 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-xs transition-colors flex items-center justify-center gap-1.5">
                    <i class="fas fa-rotate-left"></i> Foto Ulang
                </button>
                <button type="submit" id="btnSubmitBukti" class="w-1/2 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white rounded-xl font-bold text-xs shadow flex items-center justify-center gap-1.5 transition-colors">
                    <i class="fas fa-cloud-arrow-up"></i> Upload Foto
                </button>
            </div>
        </form>
    </div>
</div>-->

<!--@push('scripts')
<script>
let buktiStream = null;
let currentFacingModeBukti = 'environment';
let capturedBuktiBlob = null;

const videoBukti = document.getElementById('videoBukti');
const previewImgBukti = document.getElementById('previewImgBukti');
const canvasBukti = document.getElementById('canvasBukti');
const liveBadgeBukti = document.getElementById('liveBadgeBukti');
const btnFlipBukti = document.getElementById('btnFlipBukti');
const cameraLoadingBukti = document.getElementById('cameraLoadingBukti');
const cameraErrorBukti = document.getElementById('cameraErrorBukti');
const controlsCameraBukti = document.getElementById('controlsCameraBukti');
const controlsPreviewBukti = document.getElementById('controlsPreviewBukti');
const btnSnapBukti = document.getElementById('btnSnapBukti');
const btnRetakeBukti = document.getElementById('btnRetakeBukti');
const btnSubmitBukti = document.getElementById('btnSubmitBukti');

function stopBuktiCamera() {
    if (buktiStream) {
        buktiStream.getTracks().forEach(track => track.stop());
        buktiStream = null;
    }
}

async function startBuktiCamera(facingMode = 'environment') {
    stopBuktiCamera();
    cameraErrorBukti.classList.add('hidden');
    previewImgBukti.classList.add('hidden');
    videoBukti.classList.remove('hidden');
    liveBadgeBukti.classList.remove('hidden');
    controlsPreviewBukti.classList.add('hidden');
    controlsCameraBukti.classList.remove('hidden');
    cameraLoadingBukti.classList.remove('hidden');

    try {
        const constraints = {
            video: {
                facingMode: { ideal: facingMode },
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        };
        buktiStream = await navigator.mediaDevices.getUserMedia(constraints);
        videoBukti.srcObject = buktiStream;
        await videoBukti.play();
        cameraLoadingBukti.classList.add('hidden');
    } catch (err) {
        console.warn('Gagal dengan ideal facingMode, mencoba kamera default:', err);
        try {
            buktiStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            videoBukti.srcObject = buktiStream;
            await videoBukti.play();
            cameraLoadingBukti.classList.add('hidden');
        } catch (fallbackErr) {
            console.error('Kamera gagal dibuka:', fallbackErr);
            cameraLoadingBukti.classList.add('hidden');
            cameraErrorBukti.classList.remove('hidden');
        }
    }
}

function retryBuktiCamera() {
    startBuktiCamera(currentFacingModeBukti);
}

function openUploadModal(id) {
    document.getElementById('dispensasiId').value = id;
    capturedBuktiBlob = null;
    document.getElementById('uploadModal').classList.remove('hidden');
    startBuktiCamera(currentFacingModeBukti);
}

function closeUploadModal() {
    stopBuktiCamera();
    capturedBuktiBlob = null;
    document.getElementById('uploadModal').classList.add('hidden');
}

// Flip Camera
btnFlipBukti.addEventListener('click', function() {
    currentFacingModeBukti = (currentFacingModeBukti === 'environment') ? 'user' : 'environment';
    startBuktiCamera(currentFacingModeBukti);
});

// Jepret Foto Bukti
btnSnapBukti.addEventListener('click', function() {
    if (!videoBukti.videoWidth) {
        Swal.fire('Mohon Tunggu', 'Kamera sedang menginisialisasi...', 'info');
        return;
    }

    const width = videoBukti.videoWidth;
    const height = videoBukti.videoHeight;
    canvasBukti.width = width;
    canvasBukti.height = height;
    const ctx = canvasBukti.getContext('2d');

    // Jika kamera depan, gambar mirrored
    if (currentFacingModeBukti === 'user') {
        ctx.translate(width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(videoBukti, 0, 0, width, height);
        ctx.setTransform(1, 0, 0, 1, 0, 0);
    } else {
        ctx.drawImage(videoBukti, 0, 0, width, height);
    }

    // Watermark Stempel Waktu
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    const dateStr = `${pad(now.getDate())}/${pad(now.getMonth()+1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())} WIB`;
    const stampText = `DIGIPEN • BUKTI • ${dateStr}`;

    const fontSize = Math.max(13, Math.round(width / 42));
    ctx.font = `bold ${fontSize}px sans-serif`;
    const textWidth = ctx.measureText(stampText).width;
    const boxWidth = textWidth + 24;
    const boxHeight = fontSize + 16;
    const posX = width - boxWidth - 14;
    const posY = height - boxHeight - 14;

    ctx.fillStyle = 'rgba(0, 0, 0, 0.72)';
    ctx.beginPath();
    ctx.roundRect(posX, posY, boxWidth, boxHeight, 6);
    ctx.fill();

    ctx.fillStyle = '#10b981';
    ctx.beginPath();
    ctx.arc(posX + 12, posY + boxHeight / 2, 4, 0, Math.PI * 2);
    ctx.fill();

    ctx.fillStyle = '#ffffff';
    ctx.fillText(stampText, posX + 22, posY + boxHeight / 2 + fontSize / 3);

    canvasBukti.toBlob(function(blob) {
        capturedBuktiBlob = blob;
        previewImgBukti.src = URL.createObjectURL(blob);
        previewImgBukti.classList.remove('hidden');
        videoBukti.classList.add('hidden');
        liveBadgeBukti.classList.add('hidden');
        controlsCameraBukti.classList.add('hidden');
        controlsPreviewBukti.classList.remove('hidden');
        stopBuktiCamera();
    }, 'image/jpeg', 0.85);
});

// Foto Ulang
btnRetakeBukti.addEventListener('click', function() {
    capturedBuktiBlob = null;
    startBuktiCamera(currentFacingModeBukti);
});

// Upload Form Submit
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('dispensasiId').value;

    if (!capturedBuktiBlob) {
        Swal.fire('Foto Diperlukan', 'Silakan jepret foto bukti dengan kamera terlebih dahulu.', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('foto_bukti', capturedBuktiBlob, `bukti-${Date.now()}.jpg`);

    btnSubmitBukti.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Mengupload...';
    btnSubmitBukti.disabled = true;

    try {
        const res = await fetch(`/siswa/pengajuan/${id}/upload-foto-bukti`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            closeUploadModal();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message || 'Foto bukti berhasil diupload.',
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message || 'Terjadi kesalahan saat upload.', 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
    } finally {
        btnSubmitBukti.innerHTML = '<i class="fas fa-cloud-arrow-up"></i> Upload Foto';
        btnSubmitBukti.disabled = false;
    }
});

async function hapusFoto(id) {
    if (await Swal.fire({
        title: 'Hapus foto?',
        text: 'Foto bukti akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then(r => r.isConfirmed)) {
        try {
            const res = await fetch(`/siswa/pengajuan/${id}/hapus-foto-bukti`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', timer: 1500, showConfirmButton: false }).then(() => location.reload());
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Gagal menghapus foto', 'error');
        }
    }
}

function showPreview(url, nomorSurat) {
    Swal.fire({
        imageUrl: url,
        imageAlt: 'Foto Bukti ' + nomorSurat,
        showConfirmButton: false,
        background: '#fff',
        padding: '1rem'
    });
}
</script>
@endpush-->
@endsection
