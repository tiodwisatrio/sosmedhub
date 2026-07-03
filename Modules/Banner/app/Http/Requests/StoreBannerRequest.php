<?php

namespace Modules\Banner\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('banner.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama_banner' => ['required', 'string', 'max:255'],
            'deskripsi_banner' => ['nullable', 'string'],
            'gambar_banner' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', 'in:0,1'],
        ];
    }
}
