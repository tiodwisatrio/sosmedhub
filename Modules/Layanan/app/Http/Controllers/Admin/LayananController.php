<?php

namespace Modules\Layanan\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\Layanan\Http\Requests\StoreLayananRequest;
use Modules\Layanan\Http\Requests\UpdateLayananRequest;
use Modules\Layanan\Models\Layanan;
use Modules\Layanan\Services\LayananService;

class LayananController extends Controller implements HasMiddleware
{
    public function __construct(private LayananService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:layanan.view', only: ['index', 'show']),
            new Middleware('permission:layanan.create', only: ['create', 'store']),
            new Middleware('permission:layanan.edit', only: ['edit', 'update']),
            new Middleware('permission:layanan.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $layanans = Layanan::when(request('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('urutan')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('layanan::admin.index', compact('layanans'));
    }

    public function create()
    {
        return view('layanan::admin.create');
    }

    public function store(StoreLayananRequest $request)
    {
        $data = $request->safe()->except('image');
        $this->service->store($data, $request->file('image'));

        return redirect()->route('admin.layanans.index')
            ->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Layanan $layanan)
    {
        return view('layanan::admin.edit', compact('layanan'));
    }

    public function update(UpdateLayananRequest $request, Layanan $layanan)
    {
        $data = $request->safe()->except('image');
        $this->service->update($layanan, $data, $request->file('image'));

        return redirect()->route('admin.layanans.index')
            ->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Layanan $layanan)
    {
        $this->service->destroy($layanan);

        return redirect()->route('admin.layanans.index')
            ->with('success', 'Layanan berhasil dihapus.');
    }
}
