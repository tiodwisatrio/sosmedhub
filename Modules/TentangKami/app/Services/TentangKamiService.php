<?php

namespace Modules\TentangKami\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;
use Modules\TentangKami\Models\TentangKami;

class TentangKamiService
{
    public function update(array $data, ?UploadedFile $gambar, array $stats): TentangKami
    {
        $tentangKami = TentangKami::current();
        $data['tentangkami_deskripsi'] = Purifier::clean($data['tentangkami_deskripsi']);

        if ($data['tentangkami_misi'] ?? null) {
            $data['tentangkami_misi'] = Purifier::clean($data['tentangkami_misi']);
        }

        if ($gambar) {
            if ($tentangKami->tentangkami_gambar) {
                Storage::disk('public')->delete($tentangKami->tentangkami_gambar);
            }

            $data['tentangkami_gambar'] = $gambar->store('tentangkami', 'public');
        }

        $tentangKami->update($data);
        $this->syncStats($tentangKami, $stats);

        return $tentangKami->fresh('stats');
    }

    protected function syncStats(TentangKami $tentangKami, array $stats): void
    {
        $keepIds = [];

        foreach ($stats as $stat) {
            $payload = [
                'tentangkami_stats_label' => $stat['tentangkami_stats_label'],
                'tentangkami_stats_angka' => $stat['tentangkami_stats_angka'],
            ];

            if (($stat['tentangkami_stats_gambar'] ?? null) instanceof UploadedFile) {
                $payload['tentangkami_stats_gambar'] = $stat['tentangkami_stats_gambar']
                    ->store('tentangkami/stats', 'public');
            }

            $item = $tentangKami->stats()->updateOrCreate(
                ['id' => $stat['id'] ?? null],
                $payload
            );

            $keepIds[] = $item->id;
        }

        $removed = $tentangKami->stats()->whereNotIn('id', $keepIds)->get();

        foreach ($removed as $stat) {
            if ($stat->tentangkami_stats_gambar) {
                Storage::disk('public')->delete($stat->tentangkami_stats_gambar);
            }
        }

        $tentangKami->stats()->whereNotIn('id', $keepIds)->delete();
    }
}
