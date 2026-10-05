<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use KFoobar\LaravelSeoTools\Tests\Fixtures\Post;

it('includes static urls from config', function () {
    config(['seo.sitemap.urls' => [
        '/',
        ['loc' => '/contact', 'lastmod' => '2026-03-01'],
        'https://example.test/about',
    ]]);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('<loc>https://example.test</loc>', false)
        ->assertSee('<loc>https://example.test/contact</loc>', false)
        ->assertSee('<lastmod>2026-03-01</lastmod>', false)
        ->assertSee('<loc>https://example.test/about</loc>', false)
        ->assertDontSee('<changefreq>', false)
        ->assertDontSee('<priority>', false);
});

it('includes sitemapable models from config', function () {
    $post = Post::create(['slug' => 'hello-world']);

    config(['seo.sitemap.models' => [Post::class]]);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('<loc>https://example.test/posts/hello-world</loc>', false)
        ->assertSee('<lastmod>'.$post->updated_at->toDateString().'</lastmod>', false);
});

it('rebuilds a cached sitemap when a model is created', function () {
    config([
        'seo.sitemap.cache' => 3600,
        'seo.sitemap.models' => [Post::class],
    ]);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertDontSee('hello-world', false);

    Post::create(['slug' => 'hello-world']);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('<loc>https://example.test/posts/hello-world</loc>', false);
});

it('rejects a sitemap entry without a url', function () {
    config(['seo.sitemap.urls' => ['']]);

    $this->withoutExceptionHandling();

    expect(fn () => $this->get('/sitemap.xml'))
        ->toThrow(InvalidArgumentException::class);
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

it('does not register the sitemap route when disabled before boot', function () {
    $this->seoRoutesEnabled = false;
    $this->refreshApplication();

    expect(Route::has('seo.sitemap'))->toBeFalse()
        ->and(Route::has('seo.robots'))->toBeFalse();
});
