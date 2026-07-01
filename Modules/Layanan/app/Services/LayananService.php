<?php

namespace Modules\Layanan\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Layanan\Models\Layanan;

class LayananService
{
    public function store(array $data, ?UploadedFile $image): Layanan
    {
        if ($image) {
            $data['image'] = $image->store('layanans', 'public');
        }

        return Layanan::create($data);
    }

    public function update(Layanan $layanan, array $data, ?UploadedFile $image): void
    {
        if ($image) {
            if ($layanan->image) {
                Storage::disk('public')->delete($layanan->image);
            }
            $data['image'] = $image->store('layanans', 'public');
        }

        $layanan->update($data);
    }

    public function destroy(Layanan $layanan): void
    {
        if ($layanan->image) {
            Storage::disk('public')->delete($layanan->image);
        }

        $layanan->delete();
    }
}
