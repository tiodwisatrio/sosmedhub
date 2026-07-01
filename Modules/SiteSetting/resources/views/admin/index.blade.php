@extends('layouts.admin')

@section('title', 'Pengaturan Situs')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Pengaturan Situs</h1>
@endsection

@section('content')
    <div class="mx-auto space-y-6">

        <form method="POST" action="{{ route('admin.site-settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Informasi Umum --}}
            <div class="bg-card rounded-xl shadow-card border border-border p-6 space-y-5">
                <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Informasi Umum</h2>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Aplikasi <span class="text-danger">*</span></label>
                    <x-admin.input-text name="app_name" :value="old('app_name', $setting->app_name)" placeholder="Nama aplikasi" />
                    @error('app_name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" rows="3"
                        class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition"
                        placeholder="Deskripsi singkat tentang perusahaan/website">{{ old('deskripsi', $setting->deskripsi) }}</textarea>
                    @error('deskripsi') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Alamat</label>
                    <textarea name="alamat" rows="3"
                        class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition"
                        placeholder="Alamat lengkap">{{ old('alamat', $setting->alamat) }}</textarea>
                    @error('alamat') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                    <x-admin.input-text name="email" type="email" :value="old('email', $setting->email)" placeholder="info@example.com" />
                    @error('email') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">No. Telepon</label>
                        <x-admin.input-text name="no_telp" :value="old('no_telp', $setting->no_telp)" placeholder="021xxxxxxx" />
                        @error('no_telp') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">No. WhatsApp</label>
                        <x-admin.input-text name="no_whatsapp" :value="old('no_whatsapp', $setting->no_whatsapp)" placeholder="08xxxxxxxxxx" />
                        @error('no_whatsapp') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Logo & Icon --}}
            <div class="bg-card rounded-xl shadow-card border border-border p-6 space-y-5">
                <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Logo & Icon</h2>

                {{-- Logo Atas --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Logo Atas</label>
                    @if ($setting->logo_atas)
                        <div class="mb-2">
                            <img src="{{ Storage::url($setting->logo_atas) }}" alt="Logo Atas" class="h-12 object-contain rounded border border-border bg-slate-50 p-1">
                        </div>
                    @endif
                    <input type="file" name="logo_atas" accept="image/*"
                        class="block w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-border file:text-sm file:font-medium file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100 cursor-pointer" />
                    <p class="mt-1 text-xs text-slate-400">Format: JPG, PNG, SVG, WebP. Maks 2MB.</p>
                    @error('logo_atas') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                {{-- Logo Bawah --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Logo Bawah</label>
                    @if ($setting->logo_bawah)
                        <div class="mb-2">
                            <img src="{{ Storage::url($setting->logo_bawah) }}" alt="Logo Bawah" class="h-12 object-contain rounded border border-border bg-slate-50 p-1">
                        </div>
                    @endif
                    <input type="file" name="logo_bawah" accept="image/*"
                        class="block w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-border file:text-sm file:font-medium file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100 cursor-pointer" />
                    <p class="mt-1 text-xs text-slate-400">Format: JPG, PNG, SVG, WebP. Maks 2MB.</p>
                    @error('logo_bawah') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                {{-- Icon --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Icon (Favicon)</label>
                    @if ($setting->icon)
                        <div class="mb-2">
                            <img src="{{ Storage::url($setting->icon) }}" alt="Icon" class="h-8 w-8 object-contain rounded border border-border bg-slate-50 p-0.5">
                        </div>
                    @endif
                    <input type="file" name="icon" accept="image/*"
                        class="block w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-border file:text-sm file:font-medium file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100 cursor-pointer" />
                    <p class="mt-1 text-xs text-slate-400">Format: PNG, ICO, SVG. Maks 512KB.</p>
                    @error('icon') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                {{-- OG Image --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">OG Image (Share Image)</label>
                    @if ($setting->og_image)
                        <div class="mb-2">
                            <img src="{{ Storage::url($setting->og_image) }}" alt="OG Image" class="h-16 object-contain rounded border border-border bg-slate-50 p-1">
                        </div>
                    @endif
                    <input type="file" name="og_image" accept="image/*"
                        class="block w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-border file:text-sm file:font-medium file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100 cursor-pointer" />
                    <p class="mt-1 text-xs text-slate-400">Tampil saat link website di-share ke WhatsApp/social media. Rasio 1.91:1, maks 2MB.</p>
                    @error('og_image') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Peta Lokasi --}}
            <div class="bg-card rounded-xl shadow-card border border-border p-6 space-y-5">
                <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Peta Lokasi</h2>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Embed Google Maps</label>
                    <textarea name="iframe_map" rows="3"
                        class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition font-mono text-xs"
                        placeholder='<iframe src="https://www.google.com/maps/embed?..." ...></iframe>'>{{ old('iframe_map', $setting->iframe_map) }}</textarea>
                    <p class="mt-1 text-xs text-slate-400">Tempel kode &lt;iframe&gt; dari Google Maps (Share &rarr; Sematkan peta).</p>
                    @error('iframe_map') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Media Sosial --}}
            <div class="bg-card rounded-xl shadow-card border border-border p-6 space-y-5">
                <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Media Sosial</h2>

                @foreach ([
                    ['key' => 'instagram', 'label' => 'Instagram', 'placeholder_nama' => '@namaakun', 'placeholder_link' => 'https://instagram.com/namaakun'],
                    ['key' => 'facebook', 'label' => 'Facebook', 'placeholder_nama' => 'Nama Halaman', 'placeholder_link' => 'https://facebook.com/namahalaman'],
                    ['key' => 'tiktok', 'label' => 'TikTok', 'placeholder_nama' => '@namaakun', 'placeholder_link' => 'https://tiktok.com/@namaakun'],
                    ['key' => 'youtube', 'label' => 'YouTube', 'placeholder_nama' => 'Nama Channel', 'placeholder_link' => 'https://youtube.com/@namachannel'],
                    ['key' => 'x', 'label' => 'X (Twitter)', 'placeholder_nama' => '@namaakun', 'placeholder_link' => 'https://x.com/namaakun'],
                ] as $sosmed)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-5 border-b border-border last:border-b-0 last:pb-0">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ $sosmed['label'] }} — Nama</label>
                            <x-admin.input-text
                                name="{{ $sosmed['key'] }}_nama"
                                :value="old($sosmed['key'] . '_nama', $setting->{$sosmed['key'] . '_nama'})"
                                placeholder="{{ $sosmed['placeholder_nama'] }}"
                            />
                            @error($sosmed['key'] . '_nama') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ $sosmed['label'] }} — Link</label>
                            <x-admin.input-text
                                name="{{ $sosmed['key'] }}_link"
                                type="url"
                                :value="old($sosmed['key'] . '_link', $setting->{$sosmed['key'] . '_link'})"
                                placeholder="{{ $sosmed['placeholder_link'] }}"
                            />
                            @error($sosmed['key'] . '_link') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Marketplace --}}
            <div class="bg-card rounded-xl shadow-card border border-border p-6 space-y-5">
                <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Marketplace</h2>

                @foreach ([
                    ['key' => 'shopee', 'label' => 'Shopee', 'placeholder_nama' => 'Nama Toko', 'placeholder_link' => 'https://shopee.co.id/namatoko'],
                    ['key' => 'tokopedia', 'label' => 'Tokopedia', 'placeholder_nama' => 'Nama Toko', 'placeholder_link' => 'https://tokopedia.com/namatoko'],
                    ['key' => 'blibli', 'label' => 'Blibli', 'placeholder_nama' => 'Nama Toko', 'placeholder_link' => 'https://blibli.com/merchant/namatoko'],
                ] as $marketplace)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-5 border-b border-border last:border-b-0 last:pb-0">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ $marketplace['label'] }} — Nama</label>
                            <x-admin.input-text
                                name="{{ $marketplace['key'] }}_nama"
                                :value="old($marketplace['key'] . '_nama', $setting->{$marketplace['key'] . '_nama'})"
                                placeholder="{{ $marketplace['placeholder_nama'] }}"
                            />
                            @error($marketplace['key'] . '_nama') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ $marketplace['label'] }} — Link</label>
                            <x-admin.input-text
                                name="{{ $marketplace['key'] }}_link"
                                type="url"
                                :value="old($marketplace['key'] . '_link', $setting->{$marketplace['key'] . '_link'})"
                                placeholder="{{ $marketplace['placeholder_link'] }}"
                            />
                            @error($marketplace['key'] . '_link') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center gap-3">
                <x-admin.button type="submit">Simpan Pengaturan</x-admin.button>
            </div>
        </form>

        {{-- Info Panel --}}
        <div class="bg-card rounded-xl shadow-card border border-border p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Info</h3>
            <ul class="space-y-2 text-xs text-slate-500">
                <li class="flex gap-2">
                    <span class="text-slate-300">•</span>
                    <span><strong>Logo Atas</strong> — tampil di header / navbar.</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-slate-300">•</span>
                    <span><strong>Logo Bawah</strong> — tampil di footer.</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-slate-300">•</span>
                    <span><strong>Icon</strong> — favicon di tab browser.</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-slate-300">•</span>
                    <span>Upload gambar baru akan menggantikan gambar lama otomatis.</span>
                </li>
            </ul>
        </div>

    </div>
@endsection
