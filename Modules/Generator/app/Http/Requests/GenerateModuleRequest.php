<?php

namespace Modules\Generator\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'regex:/^[A-Za-z][A-Za-z0-9 ]*$/', 'max:50'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.label' => ['required', 'string', 'max:50'],
            'fields.*.name' => ['required', 'string', 'regex:/^[a-z][a-z0-9_]*$/', 'max:50'],
            'fields.*.type' => ['required', Rule::in(['string', 'text', 'richtext', 'integer', 'date', 'boolean', 'image'])],
            'fields.*.nullable' => ['nullable', 'boolean'],
            'has_status' => ['nullable', 'boolean'],
            'has_urutan' => ['nullable', 'boolean'],
            'create_menu' => ['nullable', 'boolean'],
            'menu_icon' => ['nullable', 'string', 'max:50'],
            'menu_parent_id' => ['nullable', 'integer', 'exists:menus,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Nama modul hanya boleh huruf, angka, dan spasi, diawali huruf.',
            'fields.*.name.regex' => 'Nama kolom harus snake_case (huruf kecil, angka, underscore).',
            'fields.required' => 'Minimal harus ada satu kolom.',
        ];
    }
}
