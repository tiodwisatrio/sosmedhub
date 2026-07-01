@php
$iconList = \Modules\Menu\Models\Menu::iconOptions();
@endphp

<div>
    <div class="bg-card rounded-xl shadow-card border border-border p-5 space-y-4">
        <p class="text-sm font-medium text-slate-700">Pilih Icon</p>

        {{-- Preview area --}}
        <div class="flex items-center gap-3 p-3 rounded-lg bg-slate-50 border border-border">
            <div id="icon-preview" class="w-8 h-8 text-slate-500 flex items-center justify-center flex-shrink-0">
                @if($currentIcon && array_key_exists($currentIcon, $iconList))
                    <x-dynamic-component :component="'heroicon-o-' . $currentIcon" class="w-6 h-6" />
                @else
                    <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
                    </svg>
                @endif
            </div>
            <div class="min-w-0">
                <p id="icon-name-display" class="text-xs font-medium text-slate-700 truncate">
                    {{ $currentIcon ?: 'Belum dipilih' }}
                </p>
                <p class="text-xs text-slate-400">Icon di sidebar</p>
            </div>
        </div>

        {{-- Grid quick-select --}}
        <div>
            <p class="text-xs font-medium text-slate-500 mb-2">Pilih cepat:</p>
            <div class="grid grid-cols-4 gap-1.5">
                @foreach($iconList as $name => $label)
                    <button
                        type="button"
                        onclick="pickIcon('{{ $name }}')"
                        title="{{ $name }}"
                        data-icon-chip="{{ $name }}"
                        class="icon-chip flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors {{ $currentIcon === $name ? 'border-primary bg-primary-light/30' : 'border-border hover:border-primary hover:bg-primary-light/30' }}"
                    >
                        <x-dynamic-component :component="'heroicon-o-' . $name" class="w-4 h-4 text-slate-500" />
                        <span class="text-[9px] text-slate-400 truncate w-full text-center leading-tight">{{ $label }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Input nama icon --}}
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Nama Icon</label>
            <input
                id="icon-input"
                name="icon"
                form="menu-form"
                type="text"
                value="{{ $currentIcon }}"
                placeholder="contoh: home, star, briefcase"
                class="w-full rounded-md border border-border px-3 py-2 text-sm text-slate-800 bg-white placeholder:text-slate-400 !outline-none !ring-0 !border-border focus:border-primary focus:ring-1 focus:ring-primary/20 transition-colors"
            >
            @error('icon')
                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-xs text-slate-400">
                Lebih banyak di
                <a href="https://heroicons.com" target="_blank" class="text-primary hover:underline">heroicons.com</a>
                (style Outline)
            </p>
        </div>

    </div>
</div>

<script>
    function pickIcon(name) {
        const input = document.getElementById('icon-input');
        if (input) input.value = name;

        document.getElementById('icon-name-display').textContent = name;

        document.querySelectorAll('[data-icon-chip]').forEach(chip => {
            chip.classList.remove('border-primary', 'bg-primary-light/30');
            chip.classList.add('border-border');
        });
        const active = document.querySelector(`[data-icon-chip="${name}"]`);
        if (active) {
            active.classList.remove('border-border');
            active.classList.add('border-primary', 'bg-primary-light/30');
        }
    }

    // Sync highlight jika user mengetik manual di input
    const iconInput = document.getElementById('icon-input');
    if (iconInput) {
        iconInput.addEventListener('input', function () {
            const val = this.value.trim();
            document.getElementById('icon-name-display').textContent = val || 'Belum dipilih';

            document.querySelectorAll('[data-icon-chip]').forEach(chip => {
                chip.classList.remove('border-primary', 'bg-primary-light/30');
                chip.classList.add('border-border');
            });
            if (val) {
                const match = document.querySelector(`[data-icon-chip="${val}"]`);
                if (match) {
                    match.classList.remove('border-border');
                    match.classList.add('border-primary', 'bg-primary-light/30');
                }
            }
        });
    }
</script>
