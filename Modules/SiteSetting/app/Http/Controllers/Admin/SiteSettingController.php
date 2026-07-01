<?php

namespace Modules\SiteSetting\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Modules\SiteSetting\Http\Requests\UpdateSiteSettingRequest;
use Modules\SiteSetting\Models\SiteSetting;

class SiteSettingController extends Controller implements HasMiddleware
{
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

        foreach (['logo_atas', 'logo_bawah', 'icon', 'og_image'] as $field) {
            if ($request->hasFile($field)) {
                if ($setting->$field) {
                    Storage::disk('public')->delete($setting->$field);
                }
                $data[$field] = $request->file($field)->store('site-settings', 'public');
            }
        }

        $setting->update($data);

        return redirect()->route('admin.site-settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }
}
