<?php

namespace Modules\Post\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Post\Models\Post;

class PostService
{
    public function store(array $data, ?UploadedFile $image): Post
    {
        $data['slug'] = Post::generateSlug($data['title']);

        if ($image) {
            $data['image'] = $image->store('posts', 'public');
        }

        return Post::create($data);
    }

    public function update(Post $post, array $data, ?UploadedFile $image): void
    {
        $data['slug'] = Post::generateSlug($data['title'], $post->id);

        if ($image) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $data['image'] = $image->store('posts', 'public');
        }

        $post->update($data);
    }

    public function destroy(Post $post): void
    {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();
    }
}