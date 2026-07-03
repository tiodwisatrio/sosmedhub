<?php

namespace Modules\SiteSetting\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\SiteSetting\Http\Requests\UpdateSiteSettingRequest;
use Modules\SiteSetting\Models\SiteSetting;
use Modules\SiteSetting\Services\SiteSettingService;

class SiteSettingController extends Controller implements HasMiddleware
{
    public function __construct(private SiteSettingService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:site-setting.view', only: ['index']),
            new Middleware('permission:site-setting.edit', only: ['update']),
        ];
    }

    public function index()
    {
        $setting = SiteSetting::current();

        return view('sitesetting::admin.index', compact('setting'));
    }

    public function update(UpdateSiteSettingRequest $request)
    {
        $setting = SiteSetting::current();
        $data = $request->safe()->except(['logo_atas', 'logo_bawah', 'icon', 'og_image']);

        $this->service->update($setting, $data, [
            'logo_atas' => $request->file('logo_atas'),
            'logo_bawah' => $request->file('logo_bawah'),
            'icon' => $request->file('icon'),
            'og_image' => $request->file('og_image'),
        ]);

        return redirect()->route('admin.site-settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }
}
