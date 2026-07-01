<?php

namespace Modules\Banner\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\Banner\Http\Requests\StoreBannerRequest;
use Modules\Banner\Http\Requests\UpdateBannerRequest;
use Modules\Banner\Models\Banner;
use Modules\Banner\Services\BannerService;

class BannerController extends Controller implements HasMiddleware
{
    public function __construct(private BannerService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:banner.view', only: ['index', 'show']),
            new Middleware('permission:banner.create', only: ['create', 'store']),
            new Middleware('permission:banner.edit', only: ['edit', 'update']),
            new Middleware('permission:banner.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $banners = Banner::when(request('search'), fn ($q, $search) => $q->where('nama_banner', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('banner::admin.index', compact('banners'));
    }

    public function create()
    {
        return view('banner::admin.create');
    }

    public function store(StoreBannerRequest $request)
    {
        $this->service->store(
            $request->safe()->except(['gambar_banner']),
            [
                'gambar_banner' => $request->file('gambar_banner'),
            ]
        );

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner berhasil ditambahkan.');
    }

    public function edit(Banner $banner)
    {
        return view('banner::admin.edit', compact('banner'));
    }

    public function update(UpdateBannerRequest $request, Banner $banner)
    {
        $this->service->update(
            $banner,
            $request->safe()->except(['gambar_banner']),
            [
                'gambar_banner' => $request->file('gambar_banner'),
            ]
        );

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner berhasil diperbarui.');
    }

    public function destroy(Banner $banner)
    {
        $this->service->destroy($banner);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner berhasil dihapus.');
    }
}
