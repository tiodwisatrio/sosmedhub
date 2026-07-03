<?php

namespace Modules\Keunggulan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKeunggulanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('keunggulan.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'urutan' => 'nullable|integer',
            'status' => 'required|in:0,1',
        ];
    }
}
