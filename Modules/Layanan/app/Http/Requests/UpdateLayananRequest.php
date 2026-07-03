<?php

namespace Modules\Layanan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLayananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('layanan.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:0,1'],
        ];
    }
}
