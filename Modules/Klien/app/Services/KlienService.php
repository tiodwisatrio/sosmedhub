<?php

namespace Modules\Klien\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Klien\Models\Klien;

class KlienService
{
    /**
     * @param  array<string, ?UploadedFile>  $files
     */
    public function store(array $data, array $files = []): Klien
    {
        $data = $this->handleUploads($data, $files);

        return Klien::create($data);
    }

    /**
     * @param  array<string, ?UploadedFile>  $files
     */
    public function update(Klien $klien, array $data, array $files = []): void
    {
        foreach ($files as $field => $file) {
            if ($file && $klien->{$field}) {
                Storage::disk('public')->delete($klien->{$field});
            }
        }

        $data = $this->handleUploads($data, $files);

        $klien->update($data);
    }

    public function destroy(Klien $klien): void
    {
        foreach (['logo_klien'] as $field) {
            if ($klien->{$field}) {
                Storage::disk('public')->delete($klien->{$field});
            }
        }

        $klien->delete();
    }

    /**
     * @param  array<string, ?UploadedFile>  $files
     */
    private function handleUploads(array $data, array $files): array
    {
        foreach ($files as $field => $file) {
            if ($file) {
                $data[$field] = $file->store('kliens', 'public');
            }
        }

        return $data;
    }
}
