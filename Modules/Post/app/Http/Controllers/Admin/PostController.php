<?php

namespace Modules\Post\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\Category\Models\Category;
use Modules\Post\Http\Requests\StorePostRequest;
use Modules\Post\Http\Requests\UpdatePostRequest;
use Modules\Post\Models\Post;
use Modules\Post\Services\PostService;

class PostController extends Controller implements HasMiddleware
{
    public function __construct(private PostService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:post.view', only: ['index']),
            new Middleware('permission:post.create', only: ['create', 'store']),
            new Middleware('permission:post.edit', only: ['edit', 'update']),
            new Middleware('permission:post.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $posts = Post::with('category')->latest()->paginate(15);

        return view('post::admin.index', compact('posts'));
    }

    public function create()
    {
        $categories = Category::ofType('post')->orderBy('name')->get();

        return view('post::admin.create', compact('categories'));
    }

    public function store(StorePostRequest $request)
    {
        $this->service->store(
            $request->safe()->except('image'),
            $request->file('image')
        );

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post berhasil ditambahkan.');
    }

    public function edit(Post $post)
    {
        $categories = Category::ofType('post')->orderBy('name')->get();

        return view('post::admin.edit', compact('post', 'categories'));
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->service->update(
            $post,
            $request->safe()->except('image'),
            $request->file('image')
        );

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post berhasil diperbarui.');
    }

    public function destroy(Post $post)
    {
        $this->service->destroy($post);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post berhasil dihapus.');
    }
}