<?php

namespace Modules\Klien\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKlienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_klien' => ['nullable', 'string', 'max:255'],
            'logo_klien' => ['nullable', 'image', 'max:2048'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:0,1'],
        ];
    }
}
