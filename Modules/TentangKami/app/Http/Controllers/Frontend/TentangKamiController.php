<?php

namespace Modules\TentangKami\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Modules\TentangKami\Models\TentangKami;

class TentangKamiController extends Controller
{
    public function index()
    {
        $tentangKami = TentangKami::current()->load('stats');

        return view('tentangkami::frontend.index', compact('tentangKami'));
    }
}
