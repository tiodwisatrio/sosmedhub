@extends('layouts.admin')

@section('title', 'Tentang Kami')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Tentang Kami</h1>
@endsection

@section('content')
    <div class="mx-auto space-y-6">

        <form method="POST" action="{{ route('admin.tentang-kami.update') }}" enctype="multipart/form-data" class="space-y-6"
            x-data="{
                stats: [
                    @foreach ($tentangKami->stats as $stat)
                        {
                            id: {{ $stat->id }},
                            label: @js($stat->tentangkami_stats_label),
                            angka: {{ $stat->tentangkami_stats_angka }},
                            gambarUrl: @js($stat->tentangkami_stats_gambar ? Storage::url($stat->tentangkami_stats_gambar) : null),
                        },
                    @endforeach
                ],
                addStat() {
                    this.stats.push({ id: null, label: '', angka: '', gambarUrl: null });
                },
                removeStat(index) {
                    this.stats.splice(index, 1);
                },
            }">
            @csrf
            @method('PUT')

            {{-- Informasi Utama --}}
            <div class="bg-card rounded-xl shadow-card border border-border p-6 space-y-5">
                <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Informasi Utama</h2>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tagline</label>
                    <x-admin.input-text name="tentangkami_tagline" :value="old('tentangkami_tagline', $tentangKami->tentangkami_tagline)" placeholder="Mitra terpercaya untuk kebutuhan digital Anda" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Judul <span class="text-danger">*</span></label>
                    <x-admin.input-text name="tentangkami_judul" :value="old('tentangkami_judul', $tentangKami->tentangkami_judul)" placeholder="Tentang Kami" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi <span class="text-danger">*</span></label>
                    <x-admin.rich-editor name="tentangkami_deskripsi" :value="old('tentangkami_deskripsi', $tentangKami->tentangkami_deskripsi)" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Gambar</label>
                    @if ($tentangKami->tentangkami_gambar)
                        <div class="mb-2">
                            <img src="{{ Storage::url($tentangKami->tentangkami_gambar) }}" alt="Gambar Tentang Kami" class="h-24 object-contain rounded border border-border bg-slate-50 p-1">
                        </div>
                    @endif
                    <input type="file" name="tentangkami_gambar" accept="image/*"
                        class="block w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-border file:text-sm file:font-medium file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100 cursor-pointer" />
                    <p class="mt-1 text-xs text-slate-400">Format: JPG, PNG, WebP. Maks 2MB.</p>
                    @error('tentangkami_gambar') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Visi & Misi --}}
            <div class="bg-card rounded-xl shadow-card border border-border p-6 space-y-5">
                <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Visi & Misi</h2>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Visi</label>
                    <textarea name="tentangkami_visi" rows="3"
                        class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition"
                        placeholder="Visi perusahaan">{{ old('tentangkami_visi', $tentangKami->tentangkami_visi) }}</textarea>
                    @error('tentangkami_visi') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Misi</label>
                    <x-admin.rich-editor name="tentangkami_misi" :value="old('tentangkami_misi', $tentangKami->tentangkami_misi)" />
                </div>
            </div>

            {{-- Statistik --}}
            <div class="bg-card rounded-xl shadow-card border border-border p-6 space-y-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Statistik</h2>
                    <x-admin.button type="button" @click="addStat()">+ Tambah Statistik</x-admin.button>
                </div>

                <template x-for="(stat, index) in stats" :key="index">
                    <div class="grid grid-cols-1 sm:grid-cols-[2fr_1fr_2fr_auto] gap-3 items-start pb-4 border-b border-border last:border-b-0 last:pb-0">
                        <input type="hidden" :name="`stats[${index}][id]`" :value="stat.id">

                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Label</label>
                            <input type="text" :name="`stats[${index}][tentangkami_stats_label]`" x-model="stat.label"
                                class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition"
                                placeholder="Proyek Selesai">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Angka</label>
                            <input type="number" :name="`stats[${index}][tentangkami_stats_angka]`" x-model="stat.angka"
                                class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition"
                                placeholder="150">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Gambar/Icon</label>
                            <template x-if="stat.gambarUrl">
                                <img :src="stat.gambarUrl" class="h-8 w-8 object-contain rounded border border-border bg-slate-50 p-0.5 mb-1">
                            </template>
                            <input type="file" :name="`stats[${index}][tentangkami_stats_gambar]`" accept="image/*"
                                class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border file:border-border file:text-xs file:font-medium file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100 cursor-pointer">
                        </div>

                        <div class="flex items-end h-full pb-0.5">
                            <button type="button" @click="removeStat(index)" class="text-xs text-danger hover:underline">Hapus</button>
                        </div>
                    </div>
                </template>

                <p class="text-xs text-slate-400" x-show="stats.length === 0">Belum ada statistik. Klik "Tambah Statistik" untuk menambahkan.</p>

                @error('stats')
                    <p class="text-xs text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <x-admin.button type="submit">Simpan Tentang Kami</x-admin.button>
            </div>
        </form>

    </div>
@endsection
