<?php

namespace Modules\TentangKami\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\TentangKami\Http\Requests\UpdateTentangKamiRequest;
use Modules\TentangKami\Models\TentangKami;
use Modules\TentangKami\Services\TentangKamiService;

class TentangKamiController extends Controller implements HasMiddleware
{
    public function __construct(private TentangKamiService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:tentang-kami.view', only: ['index']),
            new Middleware('permission:tentang-kami.edit', only: ['update']),
        ];
    }

    public function index()
    {
        $tentangKami = TentangKami::current()->load('stats');

        return view('tentangkami::admin.index', compact('tentangKami'));
    }

    public function update(UpdateTentangKamiRequest $request)
    {
        $data = $request->safe()->except(['tentangkami_gambar', 'stats']);

        $stats = collect($request->input('stats', []))
            ->map(fn ($stat, $index) => [
                ...$stat,
                'tentangkami_stats_gambar' => $request->file("stats.$index.tentangkami_stats_gambar"),
            ])
            ->all();

        $this->service->update($data, $request->file('tentangkami_gambar'), $stats);

        return redirect()->route('admin.tentang-kami.index')
            ->with('success', 'Tentang Kami berhasil disimpan.');
    }
}
