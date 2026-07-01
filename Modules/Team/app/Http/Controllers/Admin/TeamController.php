<?php

namespace Modules\Team\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\Category\Models\Category;
use Modules\Team\Http\Requests\StoreTeamRequest;
use Modules\Team\Http\Requests\UpdateTeamRequest;
use Modules\Team\Models\Team;
use Modules\Team\Services\TeamService;

class TeamController extends Controller implements HasMiddleware
{
    public function __construct(private TeamService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:team.view', only: ['index', 'show']),
            new Middleware('permission:team.create', only: ['create', 'store']),
            new Middleware('permission:team.edit', only: ['edit', 'update']),
            new Middleware('permission:team.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $teams = Team::with('category')
            ->when(request('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('team::admin.index', compact('teams'));
    }

    public function create()
    {
        $categories = Category::ofType('team')->orderBy('name')->get();

        return view('team::admin.create', compact('categories'));
    }

    public function store(StoreTeamRequest $request)
    {
        $data = $request->safe()->except('image');
        $this->service->store($data, $request->file('image'));

        return redirect()->route('admin.teams.index')
            ->with('success', 'Data tim berhasil ditambahkan.');
    }

    public function edit(Team $team)
    {
        $categories = Category::ofType('team')->orderBy('name')->get();

        return view('team::admin.edit', compact('team', 'categories'));
    }

    public function update(UpdateTeamRequest $request, Team $team)
    {
        $data = $request->safe()->except('image');
        $this->service->update($team, $data, $request->file('image'));

        return redirect()->route('admin.teams.index')
            ->with('success', 'Data tim berhasil diperbarui.');
    }

    public function destroy(Team $team)
    {
        $this->service->destroy($team);

        return redirect()->route('admin.teams.index')
            ->with('success', 'Data tim berhasil dihapus.');
    }
}
