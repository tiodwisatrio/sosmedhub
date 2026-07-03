<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo-meta title="Kontak" description="Hubungi kami untuk informasi lebih lanjut." />
    @if ($siteSetting->icon)
        <link rel="icon" type="image/x-icon" href="{{ Storage::url($siteSetting->icon) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&display=swap');
    </style>
</head>
<body>

<x-navbar />

{{-- ================================================================
     PAGE HEADER
     ================================================================ --}}
<section class="bg-black pt-40 pb-20 px-6 text-center">
    <p class="text-[10px] tracking-[0.32em] uppercase text-white/40 mb-4">Kontak</p>
    <h1 class="text-4xl md:text-5xl lg:text-6xl text-white leading-tight"
        style="font-family: 'Instrument Serif', serif;">
        Mari terhubung dengan kami.
    </h1>
</section>

{{-- ================================================================
     KONTAK CONTENT
     ================================================================ --}}
<section class="bg-white py-16 md:py-24 px-6">
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-12 items-start">

        {{-- Detail Kontak --}}
        <div class="space-y-8">
            @if ($siteSetting->deskripsi)
                <p class="text-slate-500 text-base leading-relaxed max-w-md">
                    {{ $siteSetting->deskripsi }}
                </p>
            @endif

            <div class="space-y-6">
                @if ($siteSetting->alamat)
                    <div>
                        <p class="text-[10px] tracking-[0.28em] uppercase text-slate-400 mb-1.5">Alamat</p>
                        <p class="text-slate-800 text-lg leading-relaxed">{{ $siteSetting->alamat }}</p>
                    </div>
                @endif

                @if ($siteSetting->no_telp)
                    <div>
                        <p class="text-[10px] tracking-[0.28em] uppercase text-slate-400 mb-1.5">Telepon</p>
                        <a href="tel:{{ $siteSetting->no_telp }}" class="text-slate-800 text-lg hover:text-slate-500 transition-colors">
                            {{ $siteSetting->no_telp }}
                        </a>
                    </div>
                @endif

                @if ($siteSetting->no_whatsapp)
                    <div>
                        <p class="text-[10px] tracking-[0.28em] uppercase text-slate-400 mb-1.5">WhatsApp</p>
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $siteSetting->no_whatsapp) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="text-slate-800 text-lg hover:text-slate-500 transition-colors">
                            {{ $siteSetting->no_whatsapp }}
                        </a>
                    </div>
                @endif

                @if ($siteSetting->email)
                    <div>
                        <p class="text-[10px] tracking-[0.28em] uppercase text-slate-400 mb-1.5">Email</p>
                        <a href="mailto:{{ $siteSetting->email }}" class="text-slate-800 text-lg hover:text-slate-500 transition-colors">
                            {{ $siteSetting->email }}
                        </a>
                    </div>
                @endif
            </div>

            @php
                $sosmedList = collect([
                    ['label' => 'Instagram', 'nama' => $siteSetting->instagram_nama, 'link' => $siteSetting->instagram_link],
                    ['label' => 'Facebook', 'nama' => $siteSetting->facebook_nama, 'link' => $siteSetting->facebook_link],
                    ['label' => 'TikTok', 'nama' => $siteSetting->tiktok_nama, 'link' => $siteSetting->tiktok_link],
                    ['label' => 'YouTube', 'nama' => $siteSetting->youtube_nama, 'link' => $siteSetting->youtube_link],
                    ['label' => 'X', 'nama' => $siteSetting->x_nama, 'link' => $siteSetting->x_link],
                ])->filter(fn ($sosmed) => filled($sosmed['link']));
            @endphp
            @if ($sosmedList->isNotEmpty())
                <div>
                    <p class="text-[10px] tracking-[0.28em] uppercase text-slate-400 mb-3">Sosial Media</p>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($sosmedList as $sosmed)
                            <a href="{{ $sosmed['link'] }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center text-sm text-slate-700 border border-slate-300 rounded-full px-4 py-2 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all duration-300">
                                {{ $sosmed['nama'] ?: $sosmed['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Peta --}}
        <div class="rounded-2xl overflow-hidden bg-slate-100" style="aspect-ratio: 4 / 3;">
            @if ($siteSetting->iframe_map)
                <iframe src="{{ $siteSetting->iframe_map }}"
                        class="w-full h-full border-0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
            @else
                <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm">
                    Peta belum tersedia.
                </div>
            @endif
        </div>

    </div>
</section>

<x-footer />

</body>
</html>
