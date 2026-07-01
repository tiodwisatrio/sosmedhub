<?php

namespace Modules\Klien\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\Klien\Http\Requests\StoreKlienRequest;
use Modules\Klien\Http\Requests\UpdateKlienRequest;
use Modules\Klien\Models\Klien;
use Modules\Klien\Services\KlienService;

class KlienController extends Controller implements HasMiddleware
{
    public function __construct(private KlienService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:klien.view', only: ['index', 'show']),
            new Middleware('permission:klien.create', only: ['create', 'store']),
            new Middleware('permission:klien.edit', only: ['edit', 'update']),
            new Middleware('permission:klien.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $kliens = Klien::when(request('search'), fn ($q, $search) => $q->where('nama_klien', 'like', "%{$search}%"))
            ->orderBy('urutan')->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('klien::admin.index', compact('kliens'));
    }

    public function create()
    {
        return view('klien::admin.create');
    }

    public function store(StoreKlienRequest $request)
    {
        $this->service->store(
            $request->safe()->except(['logo_klien']),
            [
                'logo_klien' => $request->file('logo_klien'),
            ]
        );

        return redirect()->route('admin.kliens.index')
            ->with('success', 'Klien berhasil ditambahkan.');
    }

    public function edit(Klien $klien)
    {
        return view('klien::admin.edit', compact('klien'));
    }

    public function update(UpdateKlienRequest $request, Klien $klien)
    {
        $this->service->update(
            $klien,
            $request->safe()->except(['logo_klien']),
            [
                'logo_klien' => $request->file('logo_klien'),
            ]
        );

        return redirect()->route('admin.kliens.index')
            ->with('success', 'Klien berhasil diperbarui.');
    }

    public function destroy(Klien $klien)
    {
        $this->service->destroy($klien);

        return redirect()->route('admin.kliens.index')
            ->with('success', 'Klien berhasil dihapus.');
    }
}
