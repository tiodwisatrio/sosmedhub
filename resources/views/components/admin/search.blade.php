@props(['placeholder' => 'Cari...', 'action' => ''])

@php $query = request('search', ''); @endphp

<form method="GET" action="{{ $action }}" class="flex items-center rounded-md bg-white w-full sm:w-auto transition-colors duration-150 overflow-hidden">
    <span class="pl-3 text-slate-400 flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
        </svg>
    </span>

    <input
        type="text"
        name="search"
        value="{{ $query }}"
        placeholder="{{ $placeholder }}"
        class="flex-1 px-2.5 py-2 text-sm outline-none border-none bg-transparent text-slate-800 placeholder:text-slate-400 !outline-none !ring-0 !border-0 !shadow-none focus:!ring-0 focus:!outline-none focus:!shadow-none focus:!border-0 min-w-0 w-full sm:w-64"
    >

    @if($query)
        <a href="{{ $action }}" class="pr-2 text-slate-400 hover:text-slate-600 transition-colors flex-shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </a>
    @endif

    <button type="submit" class="px-3 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 border-l border-border transition-colors duration-150 flex-shrink-0">
        Cari
    </button>
</form>
