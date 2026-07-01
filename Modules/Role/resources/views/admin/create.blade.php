@extends('layouts.admin')

@section('title', 'Tambah Role')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Tambah Role</h1>
@endsection

@section('content')
    <div>
        <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-6">
            @csrf

            {{-- Nama Role --}}
            <div class="bg-card rounded-xl shadow-card border border-border p-6">
                <h2 class="text-sm font-semibold text-slate-700 mb-4">Informasi Role</h2>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Role</label>
                    <x-admin.input-text name="name" :value="old('name')" placeholder="contoh: editor, supervisor" class="max-w-sm" />
                    @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Permission Matrix --}}
            <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                    <h2 class="text-sm font-semibold text-slate-700">Hak Akses</h2>
                    <button type="button" id="selectAll" class="text-xs text-primary hover:underline">Pilih Semua</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-slate-50">
                                <th class="text-left px-6 py-3 font-semibold text-slate-600 w-40">Modul</th>
                                <th class="text-center px-4 py-3 font-semibold text-slate-600 text-xs">Lihat</th>
                                <th class="text-center px-4 py-3 font-semibold text-slate-600 text-xs">Tambah</th>
                                <th class="text-center px-4 py-3 font-semibold text-slate-600 text-xs">Edit</th>
                                <th class="text-center px-4 py-3 font-semibold text-slate-600 text-xs">Hapus</th>
                                <th class="text-center px-4 py-3 font-semibold text-slate-600 text-xs">Semua</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($matrix as $moduleName => $perms)
                                @php $rowId = 'row-' . Str::slug($moduleName); @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-medium text-slate-700">{{ $moduleName }}</td>
                                    @foreach (['view', 'create', 'edit', 'delete'] as $action)
                                        @php
                                            $matchPerm = collect($perms)->first(fn($p) => str_ends_with($p, '.' . $action));
                                            $checked = $matchPerm && in_array($matchPerm, old('permissions', []));
                                        @endphp
                                        <td class="px-4 py-3 text-center">
                                            @if ($matchPerm)
                                                <input type="checkbox" name="permissions[]" value="{{ $matchPerm }}"
                                                    {{ $checked ? 'checked' : '' }}
                                                    data-row="{{ $rowId }}"
                                                    class="perm-checkbox row-checkbox w-4 h-4 rounded border-border cursor-pointer accent-teal-600">
                                            @else
                                                <span class="text-slate-200">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" data-select-row="{{ $rowId }}"
                                            class="row-select-all px-2.5 py-1 text-xs font-medium rounded-md border border-border text-slate-600 hover:border-primary hover:text-primary hover:bg-primary-light/30 transition-colors">
                                            Semua
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-admin.button type="submit">Simpan</x-admin.button>
                <a href="{{ route('admin.roles.index') }}">
                    <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                </a>
            </div>
        </form>
    </div>

    <script>
        // Tombol pilih semua global
        const btn = document.getElementById('selectAll');
        let allSelected = false;
        btn.addEventListener('click', function () {
            allSelected = !allSelected;
            document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = allSelected);
            document.querySelectorAll('.row-select-all').forEach(cb => cb.checked = allSelected);
            this.textContent = allSelected ? 'Hapus Semua' : 'Pilih Semua';
        });

        // Tombol "Semua" per baris
        document.querySelectorAll('.row-select-all').forEach(function (btn) {
            const rowId = btn.dataset.selectRow;
            const rowCheckboxes = document.querySelectorAll(`[data-row="${rowId}"]`);

            btn.addEventListener('click', function () {
                const allChecked = [...rowCheckboxes].every(cb => cb.checked);
                rowCheckboxes.forEach(cb => cb.checked = !allChecked);
            });
        });
    </script>
@endsection
