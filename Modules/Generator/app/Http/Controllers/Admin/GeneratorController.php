<?php

namespace Modules\Generator\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\Generator\Http\Requests\GenerateModuleRequest;
use Modules\Generator\Services\ModuleGeneratorService;
use Modules\Menu\Models\Menu;
use RuntimeException;

class GeneratorController extends Controller implements HasMiddleware
{
    public function __construct(private ModuleGeneratorService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:generator.view', only: ['index']),
            new Middleware('permission:generator.create', only: ['store']),
        ];
    }

    public function index()
    {
        $parents = Menu::whereNull('parent_id')->orderBy('urutan')->get();
        $icons = Menu::iconOptions();

        return view('generator::admin.index', compact('parents', 'icons'));
    }

    public function store(GenerateModuleRequest $request)
    {
        try {
            $result = $this->service->generate($request->validated());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.generator.index')
            ->with('success', "Modul {$result['module']} berhasil dibuat. Refresh halaman untuk melihat menu baru.")
            ->with('generatorResult', $result);
    }
}
