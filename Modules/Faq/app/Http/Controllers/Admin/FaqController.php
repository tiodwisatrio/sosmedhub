<?php

namespace Modules\Faq\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\Faq\Http\Requests\StoreFaqRequest;
use Modules\Faq\Http\Requests\UpdateFaqRequest;
use Modules\Faq\Models\Faq;

class FaqController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:faq.view', only: ['index', 'show']),
            new Middleware('permission:faq.create', only: ['create', 'store']),
            new Middleware('permission:faq.edit', only: ['edit', 'update']),
            new Middleware('permission:faq.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $faqs = Faq::when(request('search'), fn ($q, $search) => $q->where('pertanyaan', 'like', "%{$search}%"))
            ->orderBy('urutan')->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('faq::admin.index', compact('faqs'));
    }

    public function create()
    {
        return view('faq::admin.create');
    }

    public function store(StoreFaqRequest $request)
    {
        Faq::create($request->validated());

        return redirect()->route('admin.faqs.index')
            ->with('success', 'Faq berhasil ditambahkan.');
    }

    public function edit(Faq $faq)
    {
        return view('faq::admin.edit', compact('faq'));
    }

    public function update(UpdateFaqRequest $request, Faq $faq)
    {
        $faq->update($request->validated());

        return redirect()->route('admin.faqs.index')
            ->with('success', 'Faq berhasil diperbarui.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')
            ->with('success', 'Faq berhasil dihapus.');
    }
}
