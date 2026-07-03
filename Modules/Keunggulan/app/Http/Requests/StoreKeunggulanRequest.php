<?php

use Illuminate\Foundation\Http\FormRequest;

class StoreKeunggulanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('keunggulan.create') ?? false;
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
