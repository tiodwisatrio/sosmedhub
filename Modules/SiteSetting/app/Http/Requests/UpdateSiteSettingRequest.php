<?php

namespace Modules\SiteSetting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'alamat' => ['nullable', 'string'],
            'no_telp' => ['nullable', 'string', 'max:30'],
            'no_whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'logo_atas' => ['nullable', 'image', 'max:2048'],
            'logo_bawah' => ['nullable', 'image', 'max:2048'],
            'icon' => ['nullable', 'image', 'max:512'],
            'og_image' => ['nullable', 'image', 'max:2048'],
            'iframe_map' => ['nullable', 'url', 'max:500', 'starts_with:https://www.google.com/maps/embed'],
            'instagram_nama' => ['nullable', 'string', 'max:100'],
            'instagram_link' => ['nullable', 'url', 'max:255'],
            'facebook_nama' => ['nullable', 'string', 'max:100'],
            'facebook_link' => ['nullable', 'url', 'max:255'],
            'tiktok_nama' => ['nullable', 'string', 'max:100'],
            'tiktok_link' => ['nullable', 'url', 'max:255'],
            'youtube_nama' => ['nullable', 'string', 'max:100'],
            'youtube_link' => ['nullable', 'url', 'max:255'],
            'x_nama' => ['nullable', 'string', 'max:100'],
            'x_link' => ['nullable', 'url', 'max:255'],
            'shopee_nama' => ['nullable', 'string', 'max:100'],
            'shopee_link' => ['nullable', 'url', 'max:255'],
            'tokopedia_nama' => ['nullable', 'string', 'max:100'],
            'tokopedia_link' => ['nullable', 'url', 'max:255'],
            'blibli_nama' => ['nullable', 'string', 'max:100'],
            'blibli_link' => ['nullable', 'url', 'max:255'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('site-setting.edit') ?? false;
    }

    public function messages(): array
    {
        return [
            'iframe_map.starts_with' => 'Isi hanya URL embed Google Maps (harus diawali https://www.google.com/maps/embed).',
            'iframe_map.url' => 'Isi harus berupa URL, bukan kode HTML.',
        ];
    }
}
