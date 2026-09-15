<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectDispensasiRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'catatan_admin' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }
}
