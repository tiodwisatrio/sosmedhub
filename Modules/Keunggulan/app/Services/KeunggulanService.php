<?php

namespace Modules\Keunggulan\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Keunggulan\Models\Keunggulan;

class KeunggulanService
{
    public function store(array $data, ?UploadedFile $image): Keunggulan
    {
        if ($image) {
            $data['image'] = $image->store('keunggulans', 'public');
        }

        return Keunggulan::create($data);
    }

    public function update(Keunggulan $keunggulan, array $data, ?UploadedFile $image): void
    {
        if ($image) {
            if ($keunggulan->image) {
                Storage::disk('public')->delete($keunggulan->image);
            }
            $data['image'] = $image->store('keunggulans', 'public');
        }

        $keunggulan->update($data);
    }

    public function destroy(Keunggulan $keunggulan): void
    {
        if ($keunggulan->image) {
            Storage::disk('public')->delete($keunggulan->image);
        }

        $keunggulan->delete();
    }
}