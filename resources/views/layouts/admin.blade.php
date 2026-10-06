<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteSetting->app_name ?? config('app.name')) — {{ $siteSetting->app_name ?? config('app.name') }} Admin</title>
    @if ($siteSetting->icon)
        <link rel="icon" type="image/x-icon" href="{{ Storage::url($siteSetting->icon) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-page">

<div
    x-data="{
        open: localStorage.getItem('sidebar') !== 'closed',
        mobileOpen: false,
        toggleSidebar() {
            if (window.innerWidth < 768) {
                this.mobileOpen = !this.mobileOpen;
            } else {
                this.open = !this.open;
                localStorage.setItem('sidebar', this.open ? 'open' : 'closed');
            }
        }
    }"
    class="flex h-screen overflow-hidden"
>

    {{-- Backdrop mobile --}}
    <div
        x-show="mobileOpen"
        x-cloak
        @click="mobileOpen = false"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/50 z-30 md:hidden"
    ></div>

    {{-- ================================================================
         SIDEBAR
         ================================================================ --}}
    <aside
        :class="[
            open ? 'md:w-64' : 'md:w-16',
            mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
        ]"
        class="fixed md:static inset-y-0 left-0 w-64 flex flex-col bg-sidebar flex-shrink-0 overflow-hidden transition-all duration-300 ease-in-out z-40"
    >

        {{-- Brand --}}
        <div class="flex items-center h-16 px-4 border-b border-white/10 flex-shrink-0 overflow-hidden">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center flex-shrink-0 overflow-hidden">
                    @if ($siteSetting->icon)
                        <img src="{{ Storage::url($siteSetting->icon) }}" alt="icon" class="w-full h-full object-cover">
                    @else
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                    @endif
                </div>
                <span
                    x-show="open || mobileOpen"
                    x-cloak
                    x-transition:enter="transition-opacity ease-out duration-200 delay-100"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="text-white font-semibold text-sm whitespace-nowrap"
                >
                    {{ $siteSetting->app_name ?? config('app.name') }}
                </span>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 py-4 overflow-y-auto overflow-x-hidden scrollbar-none">
            <ul class="space-y-0.5 px-2">

                {{-- Dynamic menu dari DB --}}
                @foreach ($sidebarMenus ?? [] as $menu)
                    @if ($menu->children->isEmpty())
                        {{-- Single item --}}
                        @php $active = $menu->isActive(); @endphp
                        <li>
                            <a href="{{ $menu->routeUrl() }}"
                                :title="(!open && !mobileOpen) ? '{{ $menu->label }}' : null"
                                @class([
                                    'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150',
                                    'bg-sidebar-active text-sidebar-text-active' => $active,
                                    'text-sidebar-text hover:bg-sidebar-hover hover:text-white' => ! $active,
                                ])
                            >
                                @if ($menu->icon)
                                    <x-dynamic-component :component="'heroicon-o-' . $menu->safeIcon()" class="w-5 h-5 flex-shrink-0" />
                                @else
                                    <span class="w-5 h-5 flex-shrink-0"></span>
                                @endif
                                <span x-show="open || mobileOpen" x-cloak class="whitespace-nowrap">{{ $menu->label }}</span>
                            </a>
                        </li>
                    @else
                        {{-- Dropdown --}}
                        @php $parentActive = $menu->isParentActive(); @endphp
                        <li x-data="{ dropOpen: {{ $parentActive ? 'true' : 'false' }} }">
                            <button
                                @click="(open || mobileOpen) ? (dropOpen = !dropOpen) : null"
                                :title="(!open && !mobileOpen) ? '{{ $menu->label }}' : null"
                                @class([
                                    'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 w-full',
                                    'bg-sidebar-active text-sidebar-text-active' => $parentActive,
                                    'text-sidebar-text hover:bg-sidebar-hover hover:text-white' => ! $parentActive,
                                ])
                            >
                                @if ($menu->icon)
                                    <x-dynamic-component :component="'heroicon-o-' . $menu->safeIcon()" class="w-5 h-5 flex-shrink-0" />
                                @else
                                    <span class="w-5 h-5 flex-shrink-0"></span>
                                @endif
                                <span x-show="open || mobileOpen" x-cloak class="flex-1 text-left whitespace-nowrap">{{ $menu->label }}</span>
                                <svg x-show="open || mobileOpen" x-cloak
                                    :class="dropOpen ? 'rotate-180' : ''"
                                    class="w-4 h-4 flex-shrink-0 transition-transform duration-200"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>
                            <ul x-show="(open || mobileOpen) && dropOpen" x-cloak
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="mt-0.5 ml-4 pl-3 border-l border-white/10 space-y-0.5"
                            >
                                @foreach ($menu->children as $child)
                                    @php $childActive = $child->isActive(); @endphp
                                    <li>
                                        <a href="{{ $child->routeUrl() }}"
                                            @class([
                                                'flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors duration-150',
                                                'bg-sidebar-active text-sidebar-text-active font-medium' => $childActive,
                                                'text-sidebar-text/80 hover:bg-sidebar-hover hover:text-white' => ! $childActive,
                                            ])
                                        >
                                            {{ $child->label }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                @endforeach

            </ul>
        </nav>

        {{-- User Profile --}}
        <div class="flex-shrink-0 border-t border-white/10 p-3">
            <div x-data="{ userMenuOpen: false }" class="relative">
                <button
                    @click="userMenuOpen = !userMenuOpen"
                    :title="(!open && !mobileOpen) ? '{{ Auth::user()?->name }}' : null"
                    class="flex items-center gap-3 w-full px-2 py-2 rounded-lg hover:bg-sidebar-hover transition-colors duration-150 text-left"
                >
                    @if (Auth::user()?->avatar)
                        <img src="{{ Storage::url(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}"
                            class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                    @else
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0 text-white text-xs font-semibold">
                            {{ strtoupper(substr(Auth::user()?->name ?? 'U', 0, 2)) }}
                        </div>
                    @endif
                    <div x-show="open || mobileOpen" x-cloak class="flex-1 min-w-0">
                        <p class="text-sm text-white font-medium truncate">{{ Auth::user()?->name }}</p>
                        <p class="text-xs text-sidebar-text/60 truncate">{{ Auth::user()?->email }}</p>
                    </div>
                    <svg x-show="open || mobileOpen" x-cloak class="w-4 h-4 text-sidebar-text/50 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/>
                    </svg>
                </button>

                {{-- User dropdown --}}
                <div
                    x-show="userMenuOpen"
                    x-cloak
                    @click.outside="userMenuOpen = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute bottom-full left-0 right-0 mb-2 bg-card rounded-lg shadow-dropdown border border-border overflow-hidden"
                >
                    <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                        Profil Saya
                    </a>
                    <div class="border-t border-border"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-2.5 w-full px-4 py-2.5 text-sm text-danger hover:bg-danger-light transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                            </svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </aside>

    {{-- ================================================================
         MAIN AREA
         ================================================================ --}}
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

        {{-- Topbar --}}
        <header class="h-16 bg-card border-b border-border flex items-center gap-4 px-4 sm:px-6 flex-shrink-0 z-10">
            {{-- Sidebar toggle --}}
            <button
                @click="toggleSidebar()"
                class="p-1.5 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors flex-shrink-0"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>

            {{-- Page header --}}
            <div class="flex-1 min-w-0">
                @hasSection('header')
                    @yield('header')
                @endif
            </div>

            {{-- Right actions --}}
            <div class="flex items-center gap-1">
                <button class="p-1.5 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                    </svg>
                </button>
            </div>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="mx-4 sm:mx-6 mt-4 flex items-center gap-3 px-4 py-3 bg-success-light text-success-text rounded-lg text-sm font-medium border border-success/20"
            >
                <svg class="w-4 h-4 text-success flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="mx-4 sm:mx-6 mt-4 flex items-center gap-3 px-4 py-3 bg-danger-light text-danger-text rounded-lg text-sm font-medium border border-danger/20"
            >
                <svg class="w-4 h-4 text-danger flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto p-4 sm:p-6">
            @yield('content')
        </main>

    </div>

</div>

@livewireScripts
@stack('scripts')
</body>
</html>
