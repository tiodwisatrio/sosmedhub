<?php

namespace Modules\TentangKami\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTentangKamiRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tentangkami_tagline' => ['nullable', 'string', 'max:150'],
            'tentangkami_judul' => ['required', 'string', 'max:150'],
            'tentangkami_deskripsi' => ['required', 'string'],
            'tentangkami_gambar' => ['nullable', 'image', 'max:2048'],
            'tentangkami_visi' => ['nullable', 'string'],
            'tentangkami_misi' => ['nullable', 'string'],

            'stats' => ['nullable', 'array'],
            'stats.*.id' => ['nullable', 'integer', 'exists:tentangkami_stats,id'],
            'stats.*.tentangkami_stats_label' => ['required', 'string', 'max:100'],
            'stats.*.tentangkami_stats_angka' => ['required', 'integer'],
            'stats.*.tentangkami_stats_gambar' => ['nullable', 'image', 'max:1024'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('tentang-kami.edit') ?? false;
    }
}
