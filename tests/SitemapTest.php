<?php

declare(strict_types=1);

use KFoobar\LaravelSeoTools\Facades\Sitemap;
use KFoobar\LaravelSeoTools\Tests\Fixtures\Post;

it('includes urls added at runtime', function () {
    Sitemap::add('https://example.test/about', lastmod: '2026-01-15');

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('<loc>https://example.test/about</loc>', false)
        ->assertSee('<lastmod>2026-01-15</lastmod>', false)
        ->assertDontSee('<changefreq>', false)
        ->assertDontSee('<priority>', false);
});

it('includes static urls from config', function () {
    config(['seo.sitemap.urls' => [
        '/',
        ['loc' => '/contact', 'lastmod' => '2026-03-01'],
    ]]);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('<loc>https://example.test</loc>', false)
        ->assertSee('<loc>https://example.test/contact</loc>', false)
        ->assertSee('<lastmod>2026-03-01</lastmod>', false)
        ->assertDontSee('<changefreq>', false)
        ->assertDontSee('<priority>', false);
});

it('includes sitemapable models', function () {
    $post = Post::create(['slug' => 'hello-world']);

    Sitemap::models([Post::class]);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('<loc>https://example.test/posts/hello-world</loc>', false)
        ->assertSee('<lastmod>'.$post->updated_at->toDateString().'</lastmod>', false)
        ->assertDontSee('<changefreq>', false)
        ->assertDontSee('<priority>', false);
});

it('serves xml content type', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();

    expect($response->headers->get('Content-Type'))->toStartWith('application/xml');
    expect($response->getContent())->toContain('<?xml version="1.0" encoding="UTF-8"?>');
    expect($response->getContent())->toContain('urlset');
});

it('can be disabled', function () {
    config(['seo.sitemap.enabled' => false]);

    $this->get('/sitemap.xml')->assertNotFound();
});
