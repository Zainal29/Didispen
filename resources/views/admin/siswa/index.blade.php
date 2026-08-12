@extends('admin.layouts.app')

@section('title', 'Data Siswa')
@section('page-title', 'Manajemen Data Siswa')

@section('content')
@include('components.alert')

<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800">Daftar Siswa</h3>
        <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm transition">
            <i class="fas fa-plus mr-1"></i> Tambah Siswa
        </button>
    </div>

    {{-- Filter --}}
    <div class="p-4 bg-gray-50 border-b">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Masukkan nama siswa..." class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jurusan</label>
                <select name="jurusan_id" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">Semua Jurusan</option>
                    @foreach($jurusans as $j)
                        <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                <select name="kelas_id" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                <i class="fas fa-search mr-1"></i> Cari
            </button>
            <a href="{{ route('admin.siswa.index') }}" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">Nama</th>
                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-left">Jurusan</th>
                    <th class="p-3 text-left">Kelas</th>
                    <th class="p-3 text-left">Terdaftar</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($siswas as $s)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-3 font-semibold">{{ $s->nama_lengkap }}</td>
                    <td class="p-3 text-sm">{{ $s->user->email }}</td>
                    <td class="p-3 text-sm">{{ $s->jurusan->nama_jurusan }}</td>
                    <td class="p-3 text-sm">{{ $s->kelas->nama_kelas }}</td>
                    <td class="p-3 text-sm">{{ $s->created_at->format('d/m/Y') }}</td>
                    <td class="p-3 text-center">
                        <button onclick="editItem({{ $s->id }})" class="text-blue-600 hover:text-blue-800 mr-2" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteItem({{ $s->id }})" class="text-red-600 hover:text-red-800" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-500">
                        <i class="fas fa-user-slash text-4xl mb-2 text-gray-300"></i>
                        <p>Belum ada data siswa</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($siswas->hasPages())
    <div class="p-4 border-t bg-gray-50">
        {{ $siswas->links() }}
    </div>
    @endif
</div>

{{-- Modal Form Tambah/Edit Siswa --}}
<div id="formModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <div class="p-5 border-b">
            <h3 id="modalTitle" class="text-lg font-bold">Tambah Siswa</h3>
        </div>
        <form id="siswaForm" method="POST" action="{{ route('admin.siswa.store') }}" class="p-5 space-y-4">
            @csrf
            <input type="hidden" id="siswaId" name="siswa_id">
            <input type="hidden" id="formMethod" name="_method" value="POST">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <span class="text-red-600 text-sm" id="error_nama_lengkap"></span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIS *</label>
                    <input type="text" id="nis_nip" name="nis_nip" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <span class="text-red-600 text-sm" id="error_nis_nip"></span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" id="email" name="email" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <span class="text-red-600 text-sm" id="error_email"></span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                    <input type="password" id="password" name="password" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <span class="text-red-600 text-sm" id="error_password"></span>
                    <p class="text-xs text-gray-500 mt-1" id="passwordHint">Minimal 6 karakter. Biarkan kosong saat edit.</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jurusan *</label>
                    <select id="jurusan_id" name="jurusan_id" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach($jurusans as $j)
                            <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                    <span class="text-red-600 text-sm" id="error_jurusan_id"></span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kelas *</label>
                    <select id="kelas_id" name="kelas_id" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                    <span class="text-red-600 text-sm" id="error_kelas_id"></span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <span class="text-red-600 text-sm" id="error_tanggal_lahir"></span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                    <input type="tel" id="no_telepon" name="no_telepon" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <span class="text-red-600 text-sm" id="error_no_telepon"></span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <textarea id="alamat" name="alamat" rows="3" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                <span class="text-red-600 text-sm" id="error_alamat"></span>
            </div>
        </form>

        <div class="p-5 border-t bg-gray-50 flex justify-end space-x-2">
            <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-100">Batal</button>
            <button type="submit" form="siswaForm" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Simpan</button>
        </div>
    </div>
</div>

{{-- Modal QR Code --}}
<div id="qrModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl p-6 max-w-sm w-full mx-4 text-center">
        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-800">QR Code Dispensasi</h3>
            <p class="text-sm text-gray-600 mt-1">Tunjukkan layar ini ke Petugas Satpam</p>
        </div>
        
        {{-- ✅ DIPERBAIKI: Menghapus karakter '@' yang tidak sengaja tertulis --}}
        <div id="qrContent" class="flex justify-center items-center bg-gray-50 p-4 rounded-lg border border-dashed border-gray-300 mb-4 min-h-[200px]">
            <p class="text-gray-400 text-sm"><i class="fas fa-spinner fa-spin mr-2"></i> Memuat...</p>
        </div>
        
        <button onclick="closeQRModal()" class="w-full px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition font-medium">
            Tutup
        </button>
    </div>
</div>

@push('scripts')
<script>
function showQRCode(dispensasiId) {
    const modal = document.getElementById('qrModal');
    const content = document.getElementById('qrContent');
    
    modal.classList.remove('hidden');
    content.innerHTML = '<p class="text-gray-400 text-sm"><i class="fas fa-spinner fa-spin mr-2"></i> Memuat QR Code...</p>';

    fetch(`/siswa/qr-code/${dispensasiId}`)
        .then(response => {
            if (!response.ok) throw new Error('Gagal memuat data');
            return response.json();
        })
        .then(data => {
            if (data.qr_code) {
                content.innerHTML = `<img src="/storage/${data.qr_code}" alt="QR Code" class="w-56 h-56 object-contain">`;
            } else {
                content.innerHTML = '<p class="text-red-500 text-sm">QR Code belum tersedia.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<p class="text-red-500 text-sm">Gagal memuat QR Code.</p>';
        });
}

function closeQRModal() {
    document.getElementById('qrModal').classList.add('hidden');
}

document.getElementById('qrModal').addEventListener('click', function(e) {
    if (e.target === this) closeQRModal();
});

// Functions for Siswa Form Modal
function openModal() {
    document.getElementById('siswaForm').reset();
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('siswaId').value = '';
    document.getElementById('modalTitle').textContent = 'Tambah Siswa';
    document.getElementById('passwordHint').textContent = 'Minimal 6 karakter. Biarkan kosong saat edit.';
    document.getElementById('password').required = true;
    document.getElementById('siswaForm').action = "{{ route('admin.siswa.store') }}";
    clearErrors();
    document.getElementById('formModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('formModal').classList.add('hidden');
}

function editItem(siswaId) {
    fetch(`/admin/siswa/${siswaId}/edit`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('siswaForm').reset();
            document.getElementById('siswaId').value = siswaId;
            document.getElementById('formMethod').value = 'PATCH';
            document.getElementById('modalTitle').textContent = 'Edit Siswa';
            document.getElementById('passwordHint').textContent = 'Kosongkan jika tidak ingin mengubah password.';
            document.getElementById('password').required = false;
            
            document.getElementById('nama_lengkap').value = data.nama_lengkap;
            document.getElementById('nis_nip').value = data.user.nis_nip;
            document.getElementById('email').value = data.user.email;
            document.getElementById('jurusan_id').value = data.jurusan_id;
            document.getElementById('kelas_id').value = data.kelas_id;
            document.getElementById('tanggal_lahir').value = data.tanggal_lahir;
            document.getElementById('alamat').value = data.alamat || '';
            document.getElementById('no_telepon').value = data.no_telepon || '';
            
            document.getElementById('siswaForm').action = `/admin/siswa/${siswaId}`;
            clearErrors();
            document.getElementById('formModal').classList.remove('hidden');
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Gagal memuat data siswa');
        });
}

function deleteItem(siswaId) {
    if (confirm('Apakah Anda yakin ingin menghapus siswa ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/siswa/${siswaId}`;
        form.innerHTML = `
            @csrf
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function clearErrors() {
    document.querySelectorAll('[id^="error_"]').forEach(el => el.textContent = '');
}

document.getElementById('siswaForm').addEventListener('submit', function(e) {
    e.preventDefault();
    clearErrors();
    
    const formData = new FormData(this);
    const method = formData.get('_method') || 'POST';
    const url = this.action;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
        },
        body: formData
    })
    .then(res => {
        if (!res.ok) {
            if (res.status === 422) {
                return res.json().then(data => {
                    Object.keys(data.errors).forEach(key => {
                        const errorEl = document.getElementById(`error_${key}`);
                        if (errorEl) {
                            errorEl.textContent = data.errors[key][0];
                        }
                    });
                    throw new Error('Validation error');
                });
            }
            throw new Error('Request failed');
        }
        return res.text();
    })
    .then(() => {
        window.location.href = "{{ route('admin.siswa.index') }}";
    })
    .catch(err => {
        console.error('Error:', err);
        if (err.message !== 'Validation error') {
            alert('Terjadi kesalahan. Silakan coba lagi.');
        }
    });
});

document.getElementById('formModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endpush
@endsection