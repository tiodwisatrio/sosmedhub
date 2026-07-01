@extends('layouts.admin')

@section('title', 'Tambah Pengguna')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Tambah Pengguna</h1>
@endsection

@section('content')
    <div>
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama</label>
                    <x-admin.input-text name="name" :value="old('name')" placeholder="Nama lengkap" />
                    @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                    <x-admin.input-text name="email" type="email" :value="old('email')" placeholder="email@domain.com" />
                    @error('email') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nomor Telepon</label>
                    <x-admin.input-text name="phone" :value="old('phone')" placeholder="+62 812 3456 7890" />
                    @error('phone') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Foto Profil</label>
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
                        :selected="old('status', 1)"
                    />
                    @error('status') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                    <x-admin.input-text name="password" type="password" placeholder="Minimal 8 karakter" />
                    @error('password') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Konfirmasi Password</label>
                    <x-admin.input-text name="password_confirmation" type="password" placeholder="Ulangi password" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Role</label>
                    @if ($roles->isEmpty())
                        <div class="flex items-center gap-2 px-4 py-3 bg-warning-light text-warning-text rounded-lg text-sm border border-warning/20">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                            </svg>
                            Belum ada role tersedia.
                            <a href="{{ route('admin.roles.create') }}" class="font-semibold underline">Buat role dulu</a>
                            sebelum menambah pengguna.
                        </div>
                    @else
                        <x-admin.select
                            name="role"
                            :options="$roles->pluck('name', 'name')->toArray()"
                            :selected="old('role')"
                            placeholder="— Pilih Role —"
                        />
                    @endif
                    @error('role') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-admin.button type="submit" :disabled="$roles->isEmpty()">Simpan</x-admin.button>
                    <a href="{{ route('admin.users.index') }}">
                        <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
