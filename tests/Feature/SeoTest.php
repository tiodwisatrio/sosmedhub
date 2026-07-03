<?php

use Modules\Category\Models\Category;
use Modules\Post\Models\Post;

test('sitemap.xml lists static pages and published posts, excludes drafts', function () {
    $category = Category::create(['type' => 'post', 'name' => 'Berita', 'slug' => 'berita']);

    $published = Post::create([
        'category_id' => $category->id,
        'title' => 'Post Terbit',
        'slug' => 'post-terbit-sitemap',
        'content' => '<p>Isi</p>',
        'status' => 1,
    ]);

    $draft = Post::create([
        'category_id' => $category->id,
        'title' => 'Post Draft',
        'slug' => 'post-draft-sitemap',
        'content' => '<p>Isi</p>',
        'status' => 0,
    ]);

    $response = $this->get(route('sitemap'));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/xml; charset=UTF-8');
    $response->assertSee(route('posts.show', $published), false);
    $response->assertDontSee(route('posts.show', $draft), false);
    $response->assertSee(route('layanan.index'), false);
    $response->assertSee(route('kontak'), false);
});

test('robots.txt disallows admin area and references the sitemap', function () {
    $response = $this->get('/robots.txt');

    $response->assertOk();
    $response->assertSee('Disallow: /admin', false);
    $response->assertSee('Sitemap: '.route('sitemap'), false);
});

test('post detail page renders seo meta and json-ld', function () {
    $category = Category::create(['type' => 'post', 'name' => 'Berita', 'slug' => 'berita-seo']);

    $post = Post::create([
        'category_id' => $category->id,
        'title' => 'Judul SEO',
        'slug' => 'judul-seo',
        'content' => '<p>Konten untuk deskripsi meta.</p>',
        'status' => 1,
    ]);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('<meta name="description"', false)
        ->assertSee('property="og:title" content="Judul SEO', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('"@type":"BlogPosting"', false);
});
