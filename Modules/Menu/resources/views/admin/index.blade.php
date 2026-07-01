@extends('layouts.admin')

@section('title', 'Menu Sidebar')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Menu Sidebar</h1>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <p class="text-sm text-slate-500">Drag untuk mengatur urutan. Item dengan anak bisa di-expand.</p>
        @can('menu.create')
            <a href="{{ route('admin.menus.create') }}">
                <x-admin.button>+ Tambah Menu</x-admin.button>
            </a>
        @endcan
    </div>

    @if ($menus->isEmpty())
        <div class="bg-card rounded-xl shadow-card border border-border p-12 text-center">
            <svg class="w-12 h-12 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
            </svg>
            <p class="text-slate-400 text-sm">Belum ada menu. Tambahkan menu pertama Anda.</p>
        </div>
    @else
        <div id="menu-root" class="space-y-2">
            @foreach ($menus as $menu)
                <div class="menu-item bg-card rounded-xl border border-border overflow-hidden" data-id="{{ $menu->id }}">
                    {{-- Parent row --}}
                    <div class="flex items-center gap-3 px-4 py-3">
                        <button class="drag-handle cursor-grab active:cursor-grabbing text-slate-300 hover:text-slate-500 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5"/>
                            </svg>
                        </button>

                        {{-- Icon preview --}}
                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                            @if ($menu->icon)
                                <x-dynamic-component :component="'heroicon-o-' . $menu->safeIcon()" class="w-4 h-4 text-slate-500" />
                            @else
                                <x-heroicon-o-bars-3 class="w-4 h-4 text-slate-300" />
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-800 text-sm">{{ $menu->label }}</p>
                            @if ($menu->route_name)
                                <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $menu->route_name }}</p>
                            @else
                                <p class="text-xs text-slate-300 mt-0.5">— parent (dropdown) —</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if ($menu->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-light text-success-text">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-400">Nonaktif</span>
                            @endif
                            @can('menu.edit')
                                <a href="{{ route('admin.menus.edit', $menu) }}">
                                    <x-admin.button variant="outline" size="sm">Edit</x-admin.button>
                                </a>
                            @endcan
                            @can('menu.delete')
                                <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}"
                                    onsubmit="return confirm('Hapus menu ini beserta semua submenu-nya?')">
                                    @csrf @method('DELETE')
                                    <x-admin.button variant="danger" size="sm" type="submit">Hapus</x-admin.button>
                                </form>
                            @endcan
                        </div>
                    </div>

                    {{-- Children (selalu dirender agar bisa jadi drop target) --}}
                    <div class="{{ $menu->children->isNotEmpty() ? 'border-t border-border' : '' }} bg-slate-50/50">
                        <div class="child-list px-4 py-2 space-y-1 min-h-[2.25rem]" data-parent="{{ $menu->id }}">
                            @forelse ($menu->children as $child)
                                <div class="child-item flex items-center gap-3 bg-white rounded-lg border border-border px-3 py-2" data-id="{{ $child->id }}">
                                    <button class="drag-handle cursor-grab active:cursor-grabbing text-slate-300 hover:text-slate-500 flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5"/>
                                        </svg>
                                    </button>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-slate-700 font-medium">{{ $child->label }}</p>
                                        @if ($child->route_name)
                                            <p class="text-xs text-slate-400 font-mono">{{ $child->route_name }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        @if ($child->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-light text-success-text">Aktif</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-400">Nonaktif</span>
                                        @endif
                                        @can('menu.edit')
                                            <a href="{{ route('admin.menus.edit', $child) }}">
                                                <x-admin.button variant="outline" size="sm">Edit</x-admin.button>
                                            </a>
                                        @endcan
                                        @can('menu.delete')
                                            <form method="POST" action="{{ route('admin.menus.destroy', $child) }}"
                                                onsubmit="return confirm('Hapus submenu ini?')">
                                                @csrf @method('DELETE')
                                                <x-admin.button variant="danger" size="sm" type="submit">Hapus</x-admin.button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            @empty
                                <p class="drop-placeholder text-xs text-slate-300 text-center py-1 pointer-events-none select-none">↓ Seret menu ke sini untuk dijadikan submenu</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
    <script>
        const reorderUrl = '{{ route('admin.menus.reorder') }}';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function saveOrder(items, reload = false) {
            fetch(reorderUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ items }),
            }).then(r => r.json()).then(() => {
                if (reload) window.location.reload();
            });
        }

        function collectRoot() {
            return [...rootEl.querySelectorAll(':scope > [data-id]')].map((el, i) => ({
                id: parseInt(el.dataset.id),
                urutan: i,
                parent_id: null,
            }));
        }

        function collectChildren(list) {
            const parentId = parseInt(list.dataset.parent);
            return [...list.querySelectorAll(':scope > [data-id]')].map((el, i) => ({
                id: parseInt(el.dataset.id),
                urutan: i,
                parent_id: parentId,
            }));
        }

        const rootEl = document.getElementById('menu-root');

        if (rootEl) {
            Sortable.create(rootEl, {
                group: {
                    name: 'menus',
                    // Hanya root item tanpa children yang boleh didrag ke child list
                    pull: (to, from, dragEl) => dragEl.querySelectorAll('.child-item').length === 0,
                    put: ['menus'],
                },
                handle: '.drag-handle',
                animation: 150,
                onEnd(evt) {
                    if (evt.from === evt.to) {
                        saveOrder(collectRoot(), false);
                    }
                },
                onAdd() {
                    // Child item dipromosikan ke root
                    saveOrder(collectRoot(), true);
                },
            });
        }

        document.querySelectorAll('.child-list').forEach(list => {
            Sortable.create(list, {
                group: 'menus',
                handle: '.drag-handle',
                animation: 150,
                onEnd(evt) {
                    if (evt.from === evt.to) {
                        saveOrder(collectChildren(list), false);
                    }
                },
                onAdd() {
                    // Item pindah ke child list ini (dari root atau parent lain)
                    saveOrder(collectChildren(list), true);
                },
            });
        });
    </script>
@endsection
