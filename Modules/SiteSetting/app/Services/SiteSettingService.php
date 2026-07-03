<?php

namespace Modules\SiteSetting\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\SiteSetting\Models\SiteSetting;

class SiteSettingService
{
    private const IMAGE_FIELDS = ['logo_atas', 'logo_bawah', 'icon', 'og_image'];

    /**
     * @param  array<string, ?UploadedFile>  $files
     */
    public function update(SiteSetting $setting, array $data, array $files): void
    {
        foreach (self::IMAGE_FIELDS as $field) {
            if ($files[$field] ?? null) {
                if ($setting->$field) {
                    Storage::disk('public')->delete($setting->$field);
                }
                $data[$field] = $files[$field]->store('site-settings', 'public');
            }
        }

        $setting->update($data);
    }
}
