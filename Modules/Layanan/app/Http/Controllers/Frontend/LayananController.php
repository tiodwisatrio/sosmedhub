<?php

namespace Modules\Layanan\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Modules\Layanan\Models\Layanan;

class LayananController extends Controller
{
    public function index()
    {
        $layanans = Layanan::where('status', 1)
            ->orderBy('urutan')
            ->get();

        return view('layanan::frontend.index', compact('layanans'));
    }
}
