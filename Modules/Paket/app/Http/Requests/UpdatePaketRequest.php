<?php

namespace Modules\Paket\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('paket.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama_paket' => ['required', 'string', 'max:255'],
            'deskripsi_paket' => ['nullable', 'string', 'max:255'],
            'harga_paket' => ['nullable', 'string', 'max:255'],
            'gambar_paket' => ['nullable', 'string', 'max:255'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:0,1'],
        ];
    }
}
