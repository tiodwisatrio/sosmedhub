{{--
    Pilihan format: Feed, Story, Reels. Boleh lebih dari satu. Format yang sudah terbit terkunci.
    Dipakai di dalam x-data "postComposer".
--}}

@php
    $options = [
        'feed' => ['label' => 'Feed', 'hint' => 'Foto atau carousel di profil', 'icon' => 'heroicon-o-squares-2x2'],
        'story' => ['label' => 'Story', 'hint' => 'Foto atau video, tampil 24 jam', 'icon' => 'heroicon-o-clock'],
        'reel' => ['label' => 'Reels', 'hint' => 'Video pendek, maks '.\Modules\Scheduler\Services\VideoSpec::duration(\Modules\Scheduler\Services\VideoSpec::maxSeconds('reel')), 'icon' => 'heroicon-o-film'],
    ];
@endphp

<div role="group" aria-labelledby="format-label">
    <span id="format-label" class="block text-sm font-medium text-slate-700 mb-1.5">
        Format <span class="text-danger ml-0.5">*</span>
        <span class="ml-1 text-xs font-normal text-slate-400">Pilih satu atau lebih</span>
    </span>

    <div class="grid gap-2 sm:grid-cols-3">
        @foreach ($options as $key => $option)
            <button type="button" @click="toggle('{{ $key }}')"
                :aria-pressed="has('{{ $key }}') ? 'true' : 'false'"
                :disabled="isLocked('{{ $key }}') || unsupported('{{ $key }}')"
                data-format-chip="{{ $key }}"
                class="relative flex items-start gap-3 rounded-lg border px-3.5 py-3 text-left transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary/30 disabled:cursor-not-allowed"
                :class="has('{{ $key }}')
                    ? 'border-primary bg-primary-light/40'
                    : 'border-border bg-white hover:border-primary/40 hover:bg-slate-50'">
                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                    :class="has('{{ $key }}') ? 'bg-primary text-white' : 'bg-slate-100 text-slate-500'">
                    <x-dynamic-component :component="$option['icon']" class="h-4 w-4" />
                </span>

                <span class="min-w-0">
                    <span class="block text-sm font-semibold text-slate-800">{{ $option['label'] }}</span>
                    <span class="block text-xs leading-snug text-slate-500">{{ $option['hint'] }}</span>
                    <span x-show="unsupported('{{ $key }}')" x-cloak class="mt-1 block text-[11px] font-medium text-slate-400">Belum untuk Facebook</span>
                    <span x-show="isLocked('{{ $key }}')" x-cloak class="mt-1 inline-flex items-center gap-1 text-[11px] font-medium text-success-text">
                        <x-heroicon-o-check-circle class="h-3.5 w-3.5" /> Sudah terbit
                    </span>
                </span>

                <span x-show="has('{{ $key }}') && ! isLocked('{{ $key }}')" x-cloak
                    class="absolute right-2.5 top-2.5 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-white" aria-hidden="true">
                    <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                </span>
            </button>
        @endforeach
    </div>

    <template x-for="format in formats" :key="'f-' + format">
        <input type="hidden" name="formats[]" :value="format">
    </template>

    <label x-show="has('reel') && ! isLocked('reel')" x-cloak class="mt-3 flex items-start gap-2.5 text-sm text-slate-600">
        <input type="checkbox" x-model="shareToFeed" class="mt-0.5 rounded border-border text-primary focus:ring-primary/30">
        <span>Tampilkan Reels juga di Feed
            <span class="block text-xs text-slate-400">Bila dimatikan, Reels hanya tampil di tab Reels profil.</span>
        </span>
    </label>
    <input type="hidden" name="share_to_feed" :value="shareToFeed ? 1 : 0">

    @error('formats') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
    @error('formats.*') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
</div>
