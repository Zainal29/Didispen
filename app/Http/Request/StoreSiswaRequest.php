
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiswaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('siswa')?->user_id;
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($userId)],
            'password' => [$this->isMethod('POST') ? 'required' : 'nullable', 'string', 'min:6'],
            'nis_nip' => ['required', 'string', Rule::unique('users', 'nis_nip')->ignore($userId)],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jurusan_id' => ['required', 'exists:jurusan,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
        ];
    }
}
