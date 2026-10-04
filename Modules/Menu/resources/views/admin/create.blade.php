@extends('layouts.admin')

@section('title', 'Tambah Menu')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Tambah Menu</h1>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form --}}
        <div class="lg:col-span-2">
            <div class="bg-card rounded-xl shadow-card border border-border p-6">
                <form id="menu-form" method="POST" action="{{ route('admin.menus.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Label <span class="text-danger">*</span></label>
                        <x-admin.input-text name="label" :value="old('label')" placeholder="Nama yang tampil di sidebar" />
                        @error('label') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Parent Menu <span class="text-slate-400 font-normal">(opsional — kosongkan jika item utama)</span></label>
                        <x-admin.select
                            name="parent_id"
                            :options="$parents->pluck('label', 'id')->toArray()"
                            :selected="old('parent_id')"
                            placeholder="— Tanpa Parent (item utama) —"
                        />
                        @error('parent_id') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Route Name <span class="text-slate-400 font-normal">(opsional — kosongkan jika ini dropdown parent)</span></label>
                        <x-admin.input-text name="route_name" :value="old('route_name')" placeholder="admin.users.index" />
                        @error('route_name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Route Params <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <x-admin.input-text name="route_params" :value="old('route_params')" placeholder='status=active  atau  {"status":"active"}' />
                        <p class="mt-1 text-xs text-slate-400">Format: <code>key=value</code> atau JSON. Contoh: <code>status=active</code></p>
                        @error('route_params') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Active Pattern <span class="text-slate-400 font-normal">(untuk deteksi halaman aktif)</span></label>
                        <x-admin.input-text name="active_pattern" :value="old('active_pattern')" placeholder="admin.users.*" />
                        <p class="mt-1 text-xs text-slate-400">Digunakan untuk <code>request()->routeIs()</code>. Gunakan <code>*</code> sebagai wildcard.</p>
                        @error('active_pattern') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Permission <span class="text-slate-400 font-normal">(opsional — kosongkan jika semua role boleh lihat)</span></label>
                        <x-admin.input-text name="permission" :value="old('permission')" placeholder="user.view" />
                        <p class="mt-1 text-xs text-slate-400">Menu hanya tampil jika user punya permission ini. Contoh: <code>user.view</code></p>
                        @error('permission') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Urutan</label>
                        <x-admin.input-number name="urutan" :value="old('urutan', 0)" min="0" />
                        @error('urutan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Status <span class="text-danger">*</span></label>
                        <x-admin.select
                            name="is_active"
                            :options="['1' => 'Aktif', '0' => 'Nonaktif']"
                            :selected="old('is_active', '1')"
                        />
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <x-admin.button type="submit">Simpan</x-admin.button>
                        <a href="{{ route('admin.menus.index') }}">
                            <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Icon Picker --}}
        @include('menu::admin._icon-picker', ['currentIcon' => old('icon', '')])

    </div>
@endsection
