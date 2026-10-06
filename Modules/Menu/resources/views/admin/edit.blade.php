@extends('layouts.admin')

@section('title', 'Edit Menu')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Edit Menu</h1>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form --}}
        <div class="lg:col-span-2">
            <div class="bg-card rounded-xl shadow-card border border-border p-6">
                <form id="menu-form" method="POST" action="{{ route('admin.menus.update', $menu) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Label <span class="text-danger">*</span></label>
                        <x-admin.input-text name="label" :value="old('label', $menu->label)" placeholder="Nama yang tampil di sidebar" />
                        @error('label') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Parent Menu <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <x-admin.select
                            name="parent_id"
                            :options="$parents->pluck('label', 'id')->toArray()"
                            :selected="old('parent_id', $menu->parent_id)"
                            placeholder="— Tanpa Parent (item utama) —"
                        />
                        @error('parent_id') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Route Name <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <x-admin.input-text name="route_name" :value="old('route_name', $menu->route_name)" placeholder="admin.users.index" />
                        @error('route_name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Route Params <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <x-admin.input-text
                            name="route_params"
                            :value="old('route_params', $menu->route_params ? http_build_query($menu->route_params) : '')"
                            placeholder="status=active"
                        />
                        <p class="mt-1 text-xs text-slate-400">Format: <code>key=value</code> atau JSON.</p>
                        @error('route_params') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Active Pattern</label>
                        <x-admin.input-text name="active_pattern" :value="old('active_pattern', $menu->active_pattern)" placeholder="admin.users.*" />
                        <p class="mt-1 text-xs text-slate-400">Gunakan <code>*</code> sebagai wildcard untuk <code>routeIs()</code>, koma untuk beberapa pola, dan awalan <code>!</code> untuk mengecualikan, misalnya <code>admin.scheduled-posts.*,!admin.scheduled-posts.create</code>.</p>
                        @error('active_pattern') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Permission <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <x-admin.input-text name="permission" :value="old('permission', $menu->permission)" placeholder="user.view" />
                        <p class="mt-1 text-xs text-slate-400">Kosongkan agar semua role bisa lihat.</p>
                        @error('permission') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Urutan</label>
                        <x-admin.input-number name="urutan" :value="old('urutan', $menu->urutan)" min="0" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Status <span class="text-danger">*</span></label>
                        <x-admin.select
                            name="is_active"
                            :options="['1' => 'Aktif', '0' => 'Nonaktif']"
                            :selected="old('is_active', (string) $menu->is_active)"
                        />
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <x-admin.button type="submit">Perbarui</x-admin.button>
                        <a href="{{ route('admin.menus.index') }}">
                            <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Icon Picker --}}
        @include('menu::admin._icon-picker', ['currentIcon' => old('icon', $menu->icon ?? '')])

    </div>
@endsection
