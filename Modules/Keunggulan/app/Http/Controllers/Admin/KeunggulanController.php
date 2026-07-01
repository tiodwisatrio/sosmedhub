<?php

namespace Modules\Keunggulan\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\Keunggulan\Http\Requests\StoreKeunggulanRequest;
use Modules\Keunggulan\Http\Requests\UpdateKeunggulanRequest;
use Modules\Keunggulan\Models\Keunggulan;
use Modules\Keunggulan\Services\KeunggulanService;

class KeunggulanController extends Controller implements HasMiddleware
{
    public function __construct(private KeunggulanService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:keunggulan.view', only: ['index', 'show']),
            new Middleware('permission:keunggulan.create', only: ['create', 'store']),
            new Middleware('permission:keunggulan.edit', only: ['edit', 'update']),
            new Middleware('permission:keunggulan.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $keunggulans = Keunggulan::when(request('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('keunggulan::admin.index', compact('keunggulans'));
    }

    public function create()
    {
        return view('keunggulan::admin.create');
    }

    public function store(StoreKeunggulanRequest $request)
    {
        $data = $request->safe()->except('image');
        $this->service->store($data, $request->file('image'));

        return redirect()->route('admin.keunggulans.index')
            ->with('success', 'Keunggulan berhasil ditambahkan.');
    }

    public function edit(Keunggulan $keunggulan)
    {
        return view('keunggulan::admin.edit', compact('keunggulan'));
    }

    public function update(UpdateKeunggulanRequest $request, Keunggulan $keunggulan)
    {
        $data = $request->safe()->except('image');
        $this->service->update($keunggulan, $data, $request->file('image'));

        return redirect()->route('admin.keunggulans.index')
            ->with('success', 'Keunggulan berhasil diperbarui.');
    }

    public function destroy(Keunggulan $keunggulan)
    {
        $this->service->destroy($keunggulan);

        return redirect()->route('admin.keunggulans.index')
            ->with('success', 'Keunggulan berhasil dihapus.');
    }
}