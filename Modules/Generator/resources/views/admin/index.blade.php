@extends('layouts.admin')

@section('title', 'Generator Modul')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Generator Modul</h1>
@endsection

@section('content')
    @if (session('generatorResult'))
        @php $result = session('generatorResult'); @endphp
        <div class="bg-card rounded-xl shadow-card border border-border p-6 mb-6">
            <h2 class="text-base font-semibold text-slate-800 mb-1">Modul {{ $result['module'] }} berhasil dibuat</h2>
            <p class="text-sm text-slate-500 mb-4">
                Tabel <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{ $result['table'] }}</code> ·
                Route <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded">admin.{{ $result['route'] }}.index</code> ·
                Permission <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{ $result['permission'] }}.*</code>
            </p>
            <div class="space-y-2">
                @foreach ($result['steps'] as $step)
                    <div class="flex items-start gap-2 text-sm">
                        @if ($step['ok'])
                            <span class="text-success mt-0.5">✓</span>
                        @else
                            <span class="text-danger mt-0.5">✕</span>
                        @endif
                        <span class="font-medium text-slate-700">{{ $step['name'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.generator.store') }}"
          x-data="moduleGenerator()" class="space-y-6">
        @csrf

        {{-- Info Modul --}}
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <h2 class="text-base font-semibold text-slate-800 mb-4">Informasi Modul</h2>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Modul <span class="text-danger">*</span></label>
                <x-admin.input-text name="name" :value="old('name')" placeholder="Contoh: Product, Artikel, Banner" />
                <p class="mt-1.5 text-xs text-slate-400">Gunakan bentuk tunggal. Tabel & route dibuat otomatis (mis. Product → tabel <code>products</code>, route <code>products</code>).</p>
                @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Kolom --}}
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-slate-800">Kolom / Field</h2>
                <x-admin.button type="button" variant="outline" size="sm" x-on:click="addField()">+ Tambah Kolom</x-admin.button>
            </div>

            @error('fields') <p class="mb-3 text-xs text-danger">{{ $message }}</p> @enderror

            <div class="space-y-3">
                <template x-for="(field, index) in fields" :key="field.uid">
                    <div class="grid grid-cols-12 gap-3 items-start bg-slate-50 border border-border rounded-lg p-3">
                        <div class="col-span-3">
                            <label class="block text-xs font-medium text-slate-500 mb-1">Label</label>
                            <input type="text" :name="`fields[${index}][label]`" x-model="field.label"
                                   x-on:input="syncName(field)" placeholder="Nama Produk"
                                   class="w-full rounded-md border border-border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary/20 focus:border-primary">
                        </div>
                        <div class="col-span-3">
                            <label class="block text-xs font-medium text-slate-500 mb-1">Nama Kolom (DB)</label>
                            <input type="text" :name="`fields[${index}][name]`" x-model="field.name"
                                   x-on:input="field.nameTouched = true" placeholder="nama_produk"
                                   class="w-full rounded-md border border-border px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-primary/20 focus:border-primary">
                        </div>
                        <div class="col-span-3">
                            <label class="block text-xs font-medium text-slate-500 mb-1">Tipe</label>
                            <select :name="`fields[${index}][type]`" x-model="field.type"
                                    class="w-full rounded-md border border-border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary/20 focus:border-primary">
                                <template x-for="opt in types" :key="opt.value">
                                    <option :value="opt.value" x-text="opt.label"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-span-2 pt-6">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                <input type="hidden" :name="`fields[${index}][nullable]`" :value="field.nullable ? 1 : 0">
                                <input type="checkbox" x-model="field.nullable"
                                       class="rounded border-border text-primary focus:ring-primary/20">
                                Opsional
                            </label>
                        </div>
                        <div class="col-span-1 pt-6 text-right">
                            <button type="button" x-on:click="removeField(index)"
                                    class="text-danger hover:text-danger/70 text-sm" title="Hapus kolom">✕</button>
                        </div>
                    </div>
                </template>
            </div>

            <p x-show="fields.length === 0" class="text-sm text-slate-400 py-4 text-center">
                Belum ada kolom. Klik "Tambah Kolom".
            </p>
        </div>

        {{-- Opsi --}}
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <h2 class="text-base font-semibold text-slate-800 mb-4">Opsi Tambahan</h2>

            <div class="space-y-3">
                <label class="flex items-center gap-3 text-sm text-slate-700">
                    <input type="hidden" name="has_status" value="0">
                    <input type="checkbox" name="has_status" value="1" {{ old('has_status') ? 'checked' : '' }}
                           class="rounded border-border text-primary focus:ring-primary/20">
                    Tambah kolom <strong>Status</strong> (Aktif/Nonaktif, dengan badge di tabel)
                </label>

                <label class="flex items-center gap-3 text-sm text-slate-700">
                    <input type="hidden" name="has_urutan" value="0">
                    <input type="checkbox" name="has_urutan" value="1" {{ old('has_urutan') ? 'checked' : '' }}
                           class="rounded border-border text-primary focus:ring-primary/20">
                    Tambah kolom <strong>Urutan</strong> (untuk pengurutan manual)
                </label>
            </div>

            <div class="border-t border-border mt-5 pt-5" x-data="{ createMenu: {{ old('create_menu') ? 'true' : 'false' }} }">
                <label class="flex items-center gap-3 text-sm text-slate-700">
                    <input type="hidden" name="create_menu" value="0">
                    <input type="checkbox" name="create_menu" value="1" x-model="createMenu"
                           class="rounded border-border text-primary focus:ring-primary/20">
                    Buat <strong>menu sidebar</strong> otomatis
                </label>

                <div x-show="createMenu" x-cloak class="grid grid-cols-2 gap-4 mt-4">
                    <div x-data="{ icon: '{{ old('menu_icon', 'archive-box') }}' }">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Icon Menu</label>
                        <div class="flex items-center gap-2">
                            <span class="w-9 h-9 flex items-center justify-center rounded-md border border-border bg-slate-50 text-slate-500 flex-shrink-0">
                                <x-dynamic-component :component="'heroicon-o-' . old('menu_icon', 'archive-box')" class="w-5 h-5" />
                            </span>
                            <select name="menu_icon" x-model="icon"
                                    class="w-full rounded-md border border-border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary/20 focus:border-primary">
                                @foreach ($icons as $value => $label)
                                    <option value="{{ $value }}" @selected(old('menu_icon', 'archive-box') === $value)>{{ $label }} ({{ $value }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Parent Menu <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <x-admin.select name="menu_parent_id" :options="$parents->pluck('label', 'id')->toArray()" :selected="old('menu_parent_id')" placeholder="Tanpa parent (menu utama)" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-admin.button type="submit">Generate Modul</x-admin.button>
            <span class="text-xs text-slate-400">Proses memakan beberapa detik (composer dump-autoload + migrate).</span>
        </div>
    </form>

    @push('scripts')
    <script>
        function moduleGenerator() {
            return {
                uid: 0,
                types: [
                    { value: 'string',   label: 'Teks Singkat' },
                    { value: 'text',     label: 'Teks Panjang' },
                    { value: 'richtext', label: 'Editor (Rich Text)' },
                    { value: 'integer',  label: 'Angka' },
                    { value: 'date',     label: 'Tanggal' },
                    { value: 'boolean',  label: 'Ya / Tidak' },
                    { value: 'image',    label: 'Gambar' },
                ],
                fields: [],
                init() {
                    this.addField();
                },
                addField() {
                    this.fields.push({ uid: this.uid++, label: '', name: '', type: 'string', nullable: false, nameTouched: false });
                },
                removeField(index) {
                    this.fields.splice(index, 1);
                },
                syncName(field) {
                    if (!field.nameTouched) {
                        field.name = field.label
                            .toLowerCase()
                            .trim()
                            .replace(/[^a-z0-9]+/g, '_')
                            .replace(/^_+|_+$/g, '');
                    }
                },
            };
        }
    </script>
    <style>[x-cloak]{display:none!important}</style>
    @endpush
@endsection
