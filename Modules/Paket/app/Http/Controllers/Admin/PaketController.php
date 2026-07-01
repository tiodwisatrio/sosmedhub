<?php

namespace Modules\Paket\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\Paket\Http\Requests\StorePaketRequest;
use Modules\Paket\Http\Requests\UpdatePaketRequest;
use Modules\Paket\Models\Paket;

class PaketController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:paket.view', only: ['index', 'show']),
            new Middleware('permission:paket.create', only: ['create', 'store']),
            new Middleware('permission:paket.edit', only: ['edit', 'update']),
            new Middleware('permission:paket.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $pakets = Paket::when(request('search'), fn ($q, $search) => $q->where('nama_paket', 'like', "%{$search}%"))
            ->orderBy('urutan')->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('paket::admin.index', compact('pakets'));
    }

    public function create()
    {
        return view('paket::admin.create');
    }

    public function store(StorePaketRequest $request)
    {
        Paket::create($request->validated());

        return redirect()->route('admin.pakets.index')
            ->with('success', 'Paket berhasil ditambahkan.');
    }

    public function edit(Paket $paket)
    {
        return view('paket::admin.edit', compact('paket'));
    }

    public function update(UpdatePaketRequest $request, Paket $paket)
    {
        $paket->update($request->validated());

        return redirect()->route('admin.pakets.index')
            ->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Paket $paket)
    {
        $paket->delete();

        return redirect()->route('admin.pakets.index')
            ->with('success', 'Paket berhasil dihapus.');
    }
}
