<?php

namespace Modules\Hero\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\Hero\Http\Requests\StoreHeroRequest;
use Modules\Hero\Http\Requests\UpdateHeroRequest;
use Modules\Hero\Models\Hero;

class HeroController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:hero.view', only: ['index', 'show']),
            new Middleware('permission:hero.create', only: ['create', 'store']),
            new Middleware('permission:hero.edit', only: ['edit', 'update']),
            new Middleware('permission:hero.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $heroes = Hero::when(request('search'), fn ($q, $search) => $q->where('judul_hero', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('hero::admin.index', compact('heroes'));
    }

    public function create()
    {
        return view('hero::admin.create');
    }

    public function store(StoreHeroRequest $request)
    {
        Hero::create($request->validated());

        return redirect()->route('admin.heroes.index')
            ->with('success', 'Hero berhasil ditambahkan.');
    }

    public function edit(Hero $hero)
    {
        return view('hero::admin.edit', compact('hero'));
    }

    public function update(UpdateHeroRequest $request, Hero $hero)
    {
        $hero->update($request->validated());

        return redirect()->route('admin.heroes.index')
            ->with('success', 'Hero berhasil diperbarui.');
    }

    public function destroy(Hero $hero)
    {
        $hero->delete();

        return redirect()->route('admin.heroes.index')
            ->with('success', 'Hero berhasil dihapus.');
    }
}
