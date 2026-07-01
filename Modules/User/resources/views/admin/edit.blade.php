@extends('layouts.admin')

@section('title', 'Edit Pengguna')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Edit Pengguna</h1>
@endsection

@section('content')
    <div>
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama</label>
                    <x-admin.input-text name="name" :value="old('name', $user->name)" placeholder="Nama lengkap" />
                    @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                    <x-admin.input-text name="email" type="email" :value="old('email', $user->email)" placeholder="email@domain.com" />
                    @error('email') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nomor Telepon</label>
                    <x-admin.input-text name="phone" :value="old('phone', $user->phone)" placeholder="+62 812 3456 7890" />
                    @error('phone') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Foto Profil</label>
                    @if ($user->avatar)
                        <div class="flex items-center gap-3 mb-2">
                            <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
                                class="w-12 h-12 rounded-full object-cover border border-border">
                            <span class="text-xs text-slate-400">Foto saat ini. Upload baru untuk mengganti.</span>
                        </div>
                    @endif
                    <input type="file" name="avatar" accept="image/*"
                        class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-light file:text-primary hover:file:bg-primary/10 cursor-pointer" />
                    <p class="mt-1 text-xs text-slate-400">JPG, PNG, GIF. Maks 2MB.</p>
                    @error('avatar') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
                    <x-admin.select
                        name="status"
                        :options="[1 => 'Aktif', 0 => 'Tidak Aktif']"
                        :selected="old('status', $user->status ? 1 : 0)"
                    />
                    @error('status') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Password <span class="text-slate-400 font-normal">(kosongkan jika tidak ingin mengubah)</span>
                    </label>
                    <x-admin.input-text name="password" type="password" placeholder="Password baru" />
                    @error('password') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Konfirmasi Password</label>
                    <x-admin.input-text name="password_confirmation" type="password" placeholder="Ulangi password baru" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Role</label>
                    <x-admin.select
                        name="role"
                        :options="$roles->pluck('name', 'name')->toArray()"
                        :selected="old('role', $currentRole)"
                        placeholder="— Tanpa Role —"
                    />
                    @error('role') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-admin.button type="submit">Perbarui</x-admin.button>
                    <a href="{{ route('admin.users.index') }}">
                        <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
