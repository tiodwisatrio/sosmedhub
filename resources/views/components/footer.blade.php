@php
    $sosmedList = collect([
        ['label' => 'Instagram', 'nama' => $siteSetting->instagram_nama, 'link' => $siteSetting->instagram_link],
        ['label' => 'Facebook', 'nama' => $siteSetting->facebook_nama, 'link' => $siteSetting->facebook_link],
        ['label' => 'TikTok', 'nama' => $siteSetting->tiktok_nama, 'link' => $siteSetting->tiktok_link],
        ['label' => 'YouTube', 'nama' => $siteSetting->youtube_nama, 'link' => $siteSetting->youtube_link],
        ['label' => 'X', 'nama' => $siteSetting->x_nama, 'link' => $siteSetting->x_link],
    ])->filter(fn ($sosmed) => filled($sosmed['link']));
@endphp

<footer id="kontak" class="bg-slate-900 text-white">
    <div class="max-w-6xl mx-auto px-6 py-16 grid grid-cols-1 md:grid-cols-2 gap-12">

        {{-- Kiri: Logo, Nama, Deskripsi --}}
        <div class="max-w-sm">
            <div class="flex items-center gap-2 mb-4">
                @if ($siteSetting->logo_bawah)
                    <img src="{{ Storage::url($siteSetting->logo_bawah) }}"
                         alt="{{ $siteSetting->app_name }}"
                         class="h-9 w-auto object-contain">
                @elseif ($siteSetting->icon)
                    <img src="{{ Storage::url($siteSetting->icon) }}"
                         alt="{{ $siteSetting->app_name }}"
                         class="h-8 w-8 object-contain rounded">
                @endif
                <span class="text-white font-semibold text-lg">
                    {{ $siteSetting->app_name ?? config('app.name') }}
                </span>
            </div>
            @if ($siteSetting->deskripsi)
                <p class="text-sm text-white/50 leading-relaxed">
                    {{ $siteSetting->deskripsi }}
                </p>
            @endif
        </div>

        {{-- Kanan: Menu, Kontak, Sosmed --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-8">

            {{-- Menu --}}
            <div>
                <p class="text-xs font-semibold tracking-widest uppercase text-white/40 mb-4">Menu</p>
                <ul class="space-y-2.5 text-sm text-white/60">
                    <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Beranda</a></li>
                    <li><a href="{{ url('/').'#tentang-kami' }}" class="hover:text-white transition-colors">Tentang Kami</a></li>
                    <li><a href="{{ route('layanan.index') }}" class="hover:text-white transition-colors">Layanan</a></li>
                    <li><a href="{{ route('posts.index') }}" class="hover:text-white transition-colors">Post</a></li>
                    <li><a href="{{ route('kontak') }}" class="hover:text-white transition-colors">Kontak</a></li>
                </ul>
            </div>

            {{-- Kontak --}}
            <div>
                <p class="text-xs font-semibold tracking-widest uppercase text-white/40 mb-4">Kontak</p>
                <ul class="space-y-2.5 text-sm text-white/60">
                    @if ($siteSetting->alamat)
                        <li class="leading-relaxed">{{ $siteSetting->alamat }}</li>
                    @endif
                    @if ($siteSetting->no_telp)
                        <li>{{ $siteSetting->no_telp }}</li>
                    @endif
                    @if ($siteSetting->no_whatsapp)
                        <li>{{ $siteSetting->no_whatsapp }}</li>
                    @endif
                    @if ($siteSetting->email)
                        <li>
                            <a href="mailto:{{ $siteSetting->email }}" class="hover:text-white transition-colors">
                                {{ $siteSetting->email }}
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            {{-- Sosmed --}}
            @if ($sosmedList->isNotEmpty())
                <div>
                    <p class="text-xs font-semibold tracking-widest uppercase text-white/40 mb-4">Sosial Media</p>
                    <ul class="space-y-2.5 text-sm text-white/60">
                        @foreach ($sosmedList as $sosmed)
                            <li>
                                <a href="{{ $sosmed['link'] }}" target="_blank" rel="noopener noreferrer"
                                   class="hover:text-white transition-colors">
                                    {{ $sosmed['nama'] ?: $sosmed['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    {{-- Bawah: Copyright --}}
    <div class="border-t border-white/10">
        <p class="max-w-6xl mx-auto px-6 py-5 text-center text-xs text-white/40">
            &copy; {{ date('Y') }} {{ $siteSetting->app_name ?? config('app.name') }}. Semua hak cipta dilindungi.
        </p>
    </div>
</footer>
