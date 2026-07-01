<?php

namespace Modules\Banner\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Banner\Models\Banner;

class BannerService
{
    /**
     * @param  array<string, ?UploadedFile>  $files
     */
    public function store(array $data, array $files = []): Banner
    {
        $data = $this->handleUploads($data, $files);

        return Banner::create($data);
    }

    /**
     * @param  array<string, ?UploadedFile>  $files
     */
    public function update(Banner $banner, array $data, array $files = []): void
    {
        foreach ($files as $field => $file) {
            if ($file && $banner->{$field}) {
                Storage::disk('public')->delete($banner->{$field});
            }
        }

        $data = $this->handleUploads($data, $files);

        $banner->update($data);
    }

    public function destroy(Banner $banner): void
    {
        foreach (['gambar_banner'] as $field) {
            if ($banner->{$field}) {
                Storage::disk('public')->delete($banner->{$field});
            }
        }

        $banner->delete();
    }

    /**
     * @param  array<string, ?UploadedFile>  $files
     */
    private function handleUploads(array $data, array $files): array
    {
        foreach ($files as $field => $file) {
            if ($file) {
                $data[$field] = $file->store('banners', 'public');
            }
        }

        return $data;
    }
}
