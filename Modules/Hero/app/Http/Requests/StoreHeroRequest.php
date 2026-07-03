<?php

namespace Modules\Hero\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHeroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('hero.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'judul_hero' => ['required', 'string', 'max:255'],
            'deskripsi_hero' => ['required', 'string'],
            'button_hero' => ['required', 'string', 'max:255'],
        ];
    }
}
