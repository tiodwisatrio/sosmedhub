<?php

namespace Modules\User\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function store(array $data, ?UploadedFile $avatar, ?string $role): User
    {
        $data['email_verified_at'] = now();
        $data['approval_status'] = $data['approval_status'] ?? User::APPROVAL_APPROVED;

        if ($data['approval_status'] === User::APPROVAL_APPROVED) {
            $data['approved_at'] = $data['approved_at'] ?? now();
            $data['approved_by'] = $data['approved_by'] ?? Auth::id();
        }

        if ($avatar) {
            $data['avatar'] = $avatar->store('avatars', 'public');
        }

        $user = User::create($data);

        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }

    public function update(User $user, array $data, ?UploadedFile $avatar, bool $roleProvided, ?string $role): void
    {
        if ($avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $avatar->store('avatars', 'public');
        }

        $user->fill($data);
        $user->save();

        if ($roleProvided) {
            $user->syncRoles($role ? [$role] : []);
        }
    }
}
