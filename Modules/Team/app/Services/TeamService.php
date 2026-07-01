<?php

namespace Modules\Team\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Team\Models\Team;

class TeamService
{
    public function store(array $data, ?UploadedFile $image): Team
    {
        if ($image) {
            $data['image'] = $image->store('teams', 'public');
        }

        return Team::create($data);
    }

    public function update(Team $team, array $data, ?UploadedFile $image): void
    {
        if ($image) {
            if ($team->image) {
                Storage::disk('public')->delete($team->image);
            }
            $data['image'] = $image->store('teams', 'public');
        }

        $team->update($data);
    }

    public function destroy(Team $team): void
    {
        if ($team->image) {
            Storage::disk('public')->delete($team->image);
        }

        $team->delete();
    }
}
