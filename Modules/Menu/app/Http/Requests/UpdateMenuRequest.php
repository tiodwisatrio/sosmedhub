<?php

namespace Modules\Menu\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menu.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:menus,id'],
            'route_name' => ['nullable', 'string', 'max:100'],
            'route_params' => ['nullable', 'string'],
            'active_pattern' => ['nullable', 'string', 'max:150'],
            'permission' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'in:0,1'],
        ];
    }
}
