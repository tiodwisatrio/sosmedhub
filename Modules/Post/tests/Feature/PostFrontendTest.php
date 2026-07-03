<?php

use Modules\Category\Models\Category;
use Modules\Post\Models\Post;

function postFrontendCategory(): Category
{
    return Category::create([
        'type' => 'post',
        'name' => 'Berita',
        'slug' => 'berita',
    ]);
}

test('published posts appear on the post index page', function () {
    Post::create([
        'category_id' => postFrontendCategory()->id,
        'title' => 'Post Terbit',
        'slug' => 'post-terbit',
        'content' => '<p>Isi post terbit</p>',
        'status' => 1,
    ]);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Post Terbit');
});

test('draft posts do not appear on the post index page', function () {
    Post::create([
        'category_id' => postFrontendCategory()->id,
        'title' => 'Post Draft',
        'slug' => 'post-draft',
        'content' => '<p>Isi draft</p>',
        'status' => 0,
    ]);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertDontSee('Post Draft');
});

test('published post detail page can be viewed', function () {
    $post = Post::create([
        'category_id' => postFrontendCategory()->id,
        'title' => 'Detail Post',
        'slug' => 'detail-post',
        'content' => '<p>Isi lengkap post</p>',
        'status' => 1,
    ]);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('Detail Post')
        ->assertSee('Isi lengkap post', false);
});

test('draft post detail page returns 404', function () {
    $post = Post::create([
        'category_id' => postFrontendCategory()->id,
        'title' => 'Draft Tersembunyi',
        'slug' => 'draft-tersembunyi',
        'content' => '<p>Rahasia</p>',
        'status' => 0,
    ]);

    $this->get(route('posts.show', $post))->assertNotFound();
});
