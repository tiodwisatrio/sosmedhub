<?php

use Modules\Category\Models\Category;
use Modules\Post\Models\Post;

function postCategory(): Category
{
    return Category::create([
        'type' => 'post',
        'name' => 'Berita',
        'slug' => 'berita',
    ]);
}

test('script tags are stripped from post content on store', function () {
    $this->actingAs(developerUser());

    $this->post(route('admin.posts.store'), [
        'category_id' => postCategory()->id,
        'title' => 'Judul Post',
        'content' => '<p>Halo</p><script>alert(1)</script>',
        'status' => 1,
    ])->assertRedirect(route('admin.posts.index'));

    $post = Post::firstWhere('title', 'Judul Post');

    expect($post->content)->toContain('<p>Halo</p>');
    expect($post->content)->not->toContain('<script>');
});

test('script tags are stripped from post content on update', function () {
    $this->actingAs(developerUser());

    $post = Post::create([
        'category_id' => postCategory()->id,
        'title' => 'Judul Lama',
        'slug' => 'judul-lama',
        'content' => '<p>Konten lama</p>',
        'status' => 1,
    ]);

    $this->put(route('admin.posts.update', $post), [
        'category_id' => $post->category_id,
        'title' => $post->title,
        'content' => '<p>Konten baru</p><script>alert(document.cookie)</script>',
        'status' => 1,
    ])->assertRedirect(route('admin.posts.index'));

    expect($post->fresh()->content)
        ->toContain('<p>Konten baru</p>')
        ->not->toContain('<script>');
});

test('heading and blockquote formatting from the editor toolbar survives sanitization', function () {
    $this->actingAs(developerUser());

    $this->post(route('admin.posts.store'), [
        'category_id' => postCategory()->id,
        'title' => 'Post Dengan Heading',
        'content' => '<h2>Subjudul</h2><p>Isi</p><blockquote>Kutipan</blockquote>',
        'status' => 1,
    ])->assertRedirect(route('admin.posts.index'));

    $post = Post::firstWhere('title', 'Post Dengan Heading');

    expect($post->content)
        ->toContain('<h2>Subjudul</h2>')
        ->toContain('<blockquote>Kutipan</blockquote>');
});
