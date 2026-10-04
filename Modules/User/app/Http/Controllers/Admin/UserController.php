<?php

namespace Modules\User\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Modules\User\Services\UserService;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public function __construct(private UserService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:user.view', only: ['index']),
            new Middleware('permission:user.create', only: ['create', 'store']),
            new Middleware('permission:user.edit', only: ['edit', 'update', 'approve', 'suspend', 'reject']),
            new Middleware('permission:user.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $users = User::with('roles')
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'developer'))
            ->latest()
            ->paginate(15);

        return view('user::admin.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::where('name', '!=', 'developer')->get();

        return view('user::admin.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->safe()->only(['name', 'email', 'phone', 'status', 'password']);

        $this->service->store($data, $request->file('avatar'), $request->role);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        abort_if($user->hasRole('developer'), 403);

        $roles = Role::where('name', '!=', 'developer')->get();
        $currentRole = $user->roles->first()?->name;

        return view('user::admin.edit', compact('user', 'roles', 'currentRole'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        abort_if($user->hasRole('developer'), 403);

        $data = $request->safe()->only(['name', 'email', 'phone', 'status']);

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $this->service->update(
            $user,
            $data,
            $request->file('avatar'),
            $request->has('role'),
            $request->role
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        abort_if($user->hasRole('developer'), 403);
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat menghapus akun sendiri.');

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    public function approve(User $user)
    {
        abort_if($user->hasRole('developer'), 403);

        $user->forceFill([
            'status' => true,
            'approval_status' => User::APPROVAL_APPROVED,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'rejected_at' => null,
            'rejected_reason' => null,
            'suspended_at' => null,
        ])->save();

        if (! $user->roles()->exists()) {
            $clientRole = Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
            $user->assignRole($clientRole);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil di-approve.');
    }

    public function suspend(User $user)
    {
        abort_if($user->hasRole('developer'), 403);
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat suspend akun sendiri.');

        $user->forceFill([
            'approval_status' => User::APPROVAL_SUSPENDED,
            'suspended_at' => now(),
        ])->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil di-suspend.');
    }

    public function reject(User $user)
    {
        abort_if($user->hasRole('developer'), 403);
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat reject akun sendiri.');

        $user->forceFill([
            'approval_status' => User::APPROVAL_REJECTED,
            'rejected_at' => now(),
            'rejected_reason' => request('rejected_reason'),
            'approved_at' => null,
            'approved_by' => null,
            'suspended_at' => null,
        ])->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditolak.');
    }
}
