<?php

namespace Modules\Post\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Modules\Post\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('category')
            ->where('status', 1)
            ->latest()
            ->paginate(9);

        return view('post::frontend.index', compact('posts'));
    }

    public function show(Post $post)
    {
        abort_unless($post->isPublished(), 404);

        return view('post::frontend.show', compact('post'));
    }
}
